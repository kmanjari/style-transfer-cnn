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
    $target_file   = $target_dir . basename($_FILES["file"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $uploadOk      = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["file"]["tmp_name"]);
    if ($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    // Check if file already exists
    if (file_exists($target_dir.$key. '.'. $imageFileType)) {
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
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir.$key.'.'. $imageFileType)) {
            echo "The file " . basename($_FILES["file"]["name"]) . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    shell_exec('python3 art_generation_2_images.py '. $key.' '.$imageFileType);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Uploader</title>
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="http://fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link rel="icon" type="image/png" href="images/favicon.png">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">

  <link rel="stylesheet" href="pe-icon-7-stroke/css/pe-icon-7-stroke.css">
  <link rel="stylesheet" href="css/drop_uploader.css">

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
<div class="container">
  <div class="row">
    <div class="twelve column" style="margin-top: 5%">
      <h3><b>Upload Content Image</b></h3>
      <form method="POST" action="index.php?key=<?php echo $randomString; ?>" enctype="multipart/form-data">
        <input type="file" name="file" multiple>
        <input class="button-primary" type="submit" value="Submit">
      </form>
    </div>

    <div class="twelve columnnew generated-image" style="margin-top: 5%">
      <h3><b>Style Transferred Image</b></h3>
      <img src="images/generated.png">
    </div>
  </div>

  <div class="row">
    <h3><b>Select Style</b></h3>
    <div class="col-md-3">
      <h2>Style One</h2>
      <img src="images/style/design.jpg" class="img-thumbnail">
    </div>

    <div class="col-md-3">
      <h2>Style Two</h2>
      <img src="images/style/scream.jpg" class="img-thumbnail">
    </div>

    <div class="col-md-3">
      <h2>Style Three</h2>
      <img src="images/style/sketch.jpg" class="img-thumbnail">
    </div>
    <div class="col-md-3">
      <h2>Style Four</h2>
      <img src="images/style/rain.jpg" class="img-thumbnail">
    </div>
  </div>
</div>
</body>
</html>
