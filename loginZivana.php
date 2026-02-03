<?php
session_start();
include "connectZivana.php"; 

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM user_zivana WHERE username='$username' AND password='$password'");

    if(mysqli_num_rows($q) > 0){
        $_SESSION['login'] = true;
        header("location: dashboard.php");
    } else {
        $error = "Username atau Password salah!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
      <link rel="stylesheet" href="assetZivana.css">
    <!-- <script src="js/star.js" defer></script> -->
</head>
<body>
    <div class="box">
     <?php if(isset($error)){ ?>
        <div class="error"><?= $error ?></div>
    <?php } ?>
    
    <form method="post">
        <h2>Login</h2>
    <input type="text" name="username" placeholder="Masukkan Username" required>
    <input type="password" name="password" placeholder="Masukkan Password" required>
    <button type="submit" name="login">Login</button>
    </form>
    </div>
</body>
</html>