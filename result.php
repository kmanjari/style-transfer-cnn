<?php
/**
 * Created by PhpStorm.
 * User: Kanak
 * Date: 27/11/18
 * Time: 12:06 AM
 */

$check = getimagesize($_FILES["file"]["tmp_name"]);
if($check !== false) {
    echo "File is an image - " . $check["mime"] . ".";
    $uploadOk = 1;
} else {
    echo "File is not an image.";
    $uploadOk = 0;
}