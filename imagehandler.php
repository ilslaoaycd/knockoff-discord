<?php
    session_start();
    if (!isset($_SESSION['user'])) {
        echo "403";
        die();
    }

    $newid = $_SESSION['user']['id'] . "_" . uniqid();

    $uploads_dir = './uploads';
    $tmp_name = $_FILES["img"]["tmp_name"];
    $name = basename($_FILES["img"]["name"]);

    $filepath = $uploads_dir . "/" . $newid . $name;
    move_uploaded_file($tmp_name, $filepath);

    list($width, $height, $type, $attr) = getimagesize($filepath);
    $newid = $newid . "_" . $width . "_" . $height . "_e" . $name;

    rename($filepath, $uploads_dir . "/" . $newid);
    echo $newid;
?>