<?php
$conn = mysqli_connect("localhost", "root", "", "db_zivana_ta");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
