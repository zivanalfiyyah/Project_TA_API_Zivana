<?php
session_start();
include "../config/connect-ziva.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index-ziva.php");
    exit;
}

$user  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS jml FROM user_zivana"));
$chat  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS jml FROM chat_zivana"));
$result= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS jml FROM result_zivana"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard Admin</title>
<style>
body{font-family:Segoe UI;background:#0f172a;color:#fff;padding:40px}
.card{background:#020617;padding:20px;border-radius:16px;margin-bottom:15px}
h1{margin-bottom:20px}
a{color:#6366f1;text-decoration:none}
</style>
</head>
<body>

<h1>Dashboard Admin</h1>

<div class="card">Total User: <?= $user['jml'] ?></div>
<div class="card">Total Chat: <?= $chat['jml'] ?></div>
<div class="card">Total Result AI: <?= $result['jml'] ?></div>

<br>
<a href="../chat/chat-ziva.php"
    style="font-weight: bold;">
    Masuk ke Chat
</a> |
<a href="../auth/logout-ziva.php" 
   style="color:#f87171;font-weight:bold;text-decoration:none;">
   Logout
</a>


</body>
</html>
