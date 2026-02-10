<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../config/connect-ziva.php";

$nama     = $_POST['nama'] ?? '';
$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($nama == '' || $email == '' || $password == '') {
    echo "empty";
    exit;
}

$cek = mysqli_query($conn, "SELECT id_user FROM user_zivana WHERE email='$email'");

if (!$cek) {
    echo "query_error";
    exit;
}

if (mysqli_num_rows($cek) > 0) {
    echo "exists";
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = mysqli_query($conn, "
    INSERT INTO user_zivana (nama, email, password)
    VALUES ('$nama', '$email', '$hash')
");

if ($insert) {
    echo "success";
} else {
    echo "insert_error";
}
