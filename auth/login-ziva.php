<?php
session_start();
include "../config/connect-ziva.php";

$email    = $_POST['email'];
$password = $_POST['password'];

$data = mysqli_query($conn, "SELECT * FROM user_zivana WHERE email='$email'");
$user = mysqli_fetch_assoc($data);

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['nama']    = $user['nama'];
    $_SESSION['role']    = $user['role'];

    if ($user['role'] === 'admin') {
        echo "admin";
    } else {
        echo "user";
    }
} else {
    echo "error";
}
