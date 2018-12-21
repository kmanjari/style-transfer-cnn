<?php

$length           = 15;
$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$charactersLength = strlen($characters);
$randomString     = '';
for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_dir    = "uploads/";
    $key           = $_GET['key'];
    $style         = $_POST['style'];
    $target_file   = $target_dir . basename($_FILES["file"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $uploadOk      = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["file"]["tmp_name"]);
    if ($check !== false) {
        //echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    // Check if file already exists
    if (file_exists($target_dir . $key . '.' . $imageFileType)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["file"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . $key . '.' . $imageFileType)) {
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    shell_exec("/usr/bin/nohup ". "python3 art_generation_2_images.py " . $key . " " . $imageFileType . " " . $style ." >/dev/null 2>&1 &");
    //shell_exec('python3 art_generation_2_images.py ' . $key . ' ' . $imageFileType . ' ' . $style);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Style Transfer</title>
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="http://fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link rel="icon" type="image/png" href="images/favicon.png">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">

  <link rel="stylesheet" href="pe-icon-7-stroke/css/pe-icon-7-stroke.css">
  <link rel="stylesheet" href="css/drop_uploader.css">

  <style type="text/css">
    progress {
      vertical-align: baseline;
    }

    @-webkit-keyframes progress-bar-stripes {
      from {
        background-position: 1rem 0;
      }
      to {
        background-position: 0 0;
      }
    }

    @keyframes progress-bar-stripes {
      from {
        background-position: 1rem 0;
      }
      to {
        background-position: 0 0;
      }
    }

    .progress {
      display: -ms-flexbox;
      display: flex;
      height: 2rem;
      margin: 20px;
      overflow: hidden;
      font-size: 1.5rem;
      background-color: #e9ecef;
      border-radius: 0.25rem;
    }

    .progress-bar {
      display: -ms-flexbox;
      display: flex;
      -ms-flex-direction: column;
      flex-direction: column;
      -ms-flex-pack: center;
      justify-content: center;
      color: #fff;
      text-align: center;
      white-space: nowrap;
      background-color: #007bff;
      transition: width 0.6s ease;
    }

    @media screen and (prefers-reduced-motion: reduce) {
      .progress-bar {
        transition: none;
      }
    }

    .progress-bar-striped {
      background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
      background-size: 1rem 1rem;
    }

    .progress-bar-animated {
      -webkit-animation: progress-bar-stripes 1s linear infinite;
      animation: progress-bar-stripes 1s linear infinite;
    }

    .progress {
      position: relative;
      height: 35px;
    }

    .progress > .progress-type {
      position: absolute;
      left: 0px;
      font-weight: 800;
      padding: 3px 30px 2px 10px;
      color: rgb(255, 255, 255);
      /*background-color: rgba(25, 25, 25, 0.2);*/
    }

    .progress > .progress-completed {
      position: absolute;
      right: 0px;
      font-weight: 800;
      padding: 3px 10px 2px;
    }
  </style>

  <!--script src="js/jquery-2.2.4.min.js"></script-->
  <script src="js/jquery-3.2.1.js"></script>
  <script src="js/drop_uploader.js"></script>

  <script>
      $(document).ready(function () {
          $('input[type=file]').drop_uploader({
              uploader_text: 'Drop files to upload, or',
              browse_text: 'Browse',
              only_one_error_text: 'Only one file allowed',
              not_allowed_error_text: 'File type is not allowed',
              big_file_before_error_text: 'Files, bigger than',
              big_file_after_error_text: 'is not allowed',
              allowed_before_error_text: 'Only',
              allowed_after_error_text: 'files allowed',
              browse_css_class: 'button button-primary',
              browse_css_selector: 'file_browse',
              uploader_icon: '<i class="pe-7s-cloud-upload"></i>',
              file_icon: '<i class="pe-7s-file"></i>',
              progress_color: '#4a90e2',
              time_show_errors: 5,
              layout: 'thumbnails',
              method: 'normal',
              url: 'ajax_upload.php',
              delete_url: 'ajax_delete.php',
          });
      });
  </script>

</head>
<body style="background: #fff;">

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
  <div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
         style="width: 2%;">
      <span class="sr-only"></span>
    </div>
    <span class="progress-type">Waiting for signals</span>
  </div>
<?php } ?>

<div class="container">
  <form method="POST" action="index.php?key=<?php echo $randomString; ?>" enctype="multipart/form-data">
    <div class="row">
      <div class="twelve column" style="margin-top: 5%">
        <h3><b>Upload Content Image</b></h3>
        <input type="file" name="file" multiple>
        <input type="hidden" name="style" id="style">
      </div>

      <div class="twelve columnnew generated-image" style="margin-top: 5%">
        <h3><b>Style Transferred Image</b></h3>
        <img src="images/generated.png" class="final-image">
      </div>
    </div>
    <div class="row">
      <h3><b>Select Style</b></h3>
      <div class="col-md-3">
        <h2>Style One</h2>
        <img src="images/style/design.jpg" class="img-thumbnail selector" data-name="design.jpg">
      </div>

      <div class="col-md-3">
        <h2>Style Two</h2>
        <img src="images/style/scream.jpg" class="img-thumbnail selector" data-name="scream.jpg">
      </div>

      <div class="col-md-3">
        <h2>Style Three</h2>
        <img src="images/style/sketch.jpg" class="img-thumbnail selector" data-name="sketch.jpg">
      </div>
      <div class="col-md-3">
        <h2>Style Four</h2>
        <img src="images/style/rain.jpg" class="img-thumbnail selector" data-name="rain.jpg">
      </div>
    </div>
    <input class="button-primary hidden" type="submit" value="Submit" onsubmit="showModal();">
  </form>
</div>
<script>
    $('.selector').on('click', function () {
        var name = $(this).data('name');
        $('#style').val(name);
    })
</script>
<script src="https://js.pusher.com/4.2/pusher.min.js"></script>

<script>
    //Remember to replace key and cluster with your credentials.
    var pusher = new Pusher('daf0db748933713e01b5', {
        cluster: 'ap2',
        encrypted: true
    });
    var channel = pusher.subscribe('notify');
    channel.bind('result-<?php echo $_GET['key']?>', function (message) {
        if(message.status === 'complete') {
	    $('.progress').attr('display', 'none');
            $('.final-image').attr('src', message.image);
        } else {
            $('.progress-bar').attr('aria-valuenow', message.percentage).css('width', message.percentage + '%');
            $('.progress-type').html(message.progress_message);
            $('.progress').attr('display', 'block');
        }
    });
</script>


</body>
</html>

