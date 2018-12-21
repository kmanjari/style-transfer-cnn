#import sys

#imageLocation = sys.argv[1]
#imageExtension = sys.argv[2]

# coding: utf-8

# In[2]:

import sys
import time
from PIL import Image
import numpy as np

from keras import backend
from keras.models import Model
from keras.applications.vgg16 import VGG16

from scipy.optimize import fmin_l_bfgs_b
from scipy.misc import imsave

imageLocation = sys.argv[1]
imageExtension = sys.argv[2]

# In[3]:

height = 312
width = 312

# Base Image
content_image = Image.open('uploads/'+imageLocation+'.'+imageExtension)
content_image = content_image.resize((height,width))
content_image

# In[4]:

# Style Image
style_image = Image.open('images/style/scream.jpg')
style_image = style_image.resize((height,width))
style_image


# In[5]:


# Convert content image to numerical data
content_array = np.asarray(content_image, dtype='float32')
content_array.shape


# In[6]:


# Then add one dimension
content_array = np.expand_dims(content_array, axis=0)
content_array.shape


# In[7]:


# Convert style image to numerical data, then add one dimension
style_array = np.asarray(style_image, dtype='float32')
style_array = np.expand_dims(style_array, axis=0)
style_array.shape


# In[8]:


# subtract RGB mean and RGB reverse ordering (RGB -> BGR)
content_array[:,:,:,0] -= 103.939 # R mean
content_array[:,:,:,1] -= 116.779 # G mean
content_array[:,:,:,2] -= 123.68  # B mean
content_array = content_array[:,:,:,::-1]


# In[9]:


style_array[:,:,:,0] -= 103.939
style_array[:,:,:,1] -= 116.779
style_array[:,:,:,2] -= 123.68
style_array = style_array[:,:,:,::-1] # RGB -> BGR


# In[10]:


# making it ready for VGG16
content_image = backend.variable(content_array)
style_image = backend.variable(style_array)
combination_image = backend.placeholder((1,height,width,3))


# In[11]:


input_tensor = backend.concatenate([content_image,style_image,combination_image], axis=0)


# In[12]:


input_tensor


# In[13]:


model = VGG16(input_tensor=input_tensor, weights='imagenet', include_top=False)


# In[14]:


model.summary()


# In[15]:


layers = dict([(layer.name, layer.output) for layer in model.layers])
layers


# In[16]:


layers['block2_conv2'][0,:,:,:]


# In[17]:


# Weights hyperparameters for 3 losses: Content Loss, Style loss, total variation loss
content_weight = 0.025
style_weight = 5.0
total_variation_weight = 1.0


# In[18]:


# initialize the loss
loss = backend.variable(0.)


# In[19]:


def content_loss(content, combination):
    return backend.sum(backend.square(combination - content))

layer_features = layers['block2_conv2']
content_image_features = layer_features[0, :, :, :]
combination_features = layer_features[2, :, :, :]

loss += content_weight * content_loss(content_image_features,
                                      combination_features)


# In[20]:


def content_loss(content, combination):
    return backend.sum(backend.square(combination - content))

layer_features = layers['block2_conv2']
content_image_features = layer_features[0, :, :, :]
combination_features = layer_features[2, :, :, :]

loss += content_weight * content_loss(content_image_features,
                                      combination_features)


# In[21]:


# For style loss we gonna adopt Gram Matrix because it can abstract better
def gram_matrix(x):
    features = backend.batch_flatten(backend.permute_dimensions(x, (2, 0, 1)))
    gram = backend.dot(features, backend.transpose(features))
    return gram


# In[22]:


def style_loss(style, combination):
    S = gram_matrix(style)
    C = gram_matrix(combination)
    channels = 3
    size = height * width
    return backend.sum(backend.square(S - C)) / (4. * (channels ** 2) * (size ** 2))

feature_layers = ['block1_conv2', 'block2_conv2','block3_conv3', 'block4_conv3','block5_conv3']
for layer_name in feature_layers:
    layer_features = layers[layer_name]
    style_features = layer_features[1, :, :, :]
    combination_features = layer_features[2, :, :, :]
    sl = style_loss(style_features, combination_features)
    loss += (style_weight / len(feature_layers)) * sl


# In[23]:


def total_variation_loss(x):
    a = backend.square(x[:, :height-1, :width-1, :] - x[:, 1:, :width-1, :])
    b = backend.square(x[:, :height-1, :width-1, :] - x[:, :height-1, 1:, :])
    return backend.sum(backend.pow(a + b, 1.25))

loss += total_variation_weight * total_variation_loss(combination_image)


# In[24]:


grads = backend.gradients(loss, combination_image)


# In[25]:


outputs = [loss]
outputs += grads
f_outputs = backend.function([combination_image], outputs)

def eval_loss_and_grads(x):
    x = x.reshape((1, height, width, 3))
    outs = f_outputs([x])
    loss_value = outs[0]
    grad_values = outs[1].flatten().astype('float64')
    return loss_value, grad_values

class Evaluator(object):

    def __init__(self):
        self.loss_value = None
        self.grads_values = None

    def loss(self, x):
        assert self.loss_value is None
        loss_value, grad_values = eval_loss_and_grads(x)
        self.loss_value = loss_value
        self.grad_values = grad_values
        return self.loss_value

    def grads(self, x):
        assert self.loss_value is not None
        grad_values = np.copy(self.grad_values)
        self.loss_value = None
        self.grad_values = None
        return grad_values

evaluator = Evaluator()


# In[26]:


x = np.random.uniform(0, 255, (1, height, width, 3)) - 128.

iterations = 1

for i in range(iterations):
    print('Start of iteration', i)
    start_time = time.time()
    x, min_val, info = fmin_l_bfgs_b(evaluator.loss, x.flatten(),
                                     fprime=evaluator.grads, maxfun=20)
    print('Current loss value:', min_val)
    end_time = time.time()
    print('Iteration %d completed in %ds' % (i, end_time - start_time))


# In[27]:


# Reverse back to input image
x = x.reshape((height, width, 3))
x = x[:, :, ::-1] # BGR -> RGB
x[:, :, 0] += 103.939 # Mean R
x[:, :, 1] += 116.779 # Mean G
x[:, :, 2] += 123.68  # Mean B
x = np.clip(x, 0, 255).astype('uint8')

#Image.fromarray(x)

Image.fromarray(x * 255).save(imageLocation + '-final.'+imageExtension)
#pusher = Pusher(app_id='658043', key='daf0db748933713e01b5', secret='0eaeeda98cfec05623c2', cluster='ap2')
#pusher.trigger('response', 'result-'+imageLocation, {'image': imageLocation + '.'+imageExtension})
