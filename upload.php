<?php
include "includes/config.php";
require_login();

$upload_dir = "assets/img/uploads/";
$response = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file     = $_FILES['image'];
    $filename = time() . "_" . basename($file['name']);
    $target   = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        $response = $target;
    } else {
        $response = "ERROR";
    }
}
echo $response;
