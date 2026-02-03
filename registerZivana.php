<?php
session_start();
include "connectZivana.php"; 

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    mysqli_query($conn, "
        INSERT INTO user_zivana
        (username, password)
        VALUES
        ('$username', '$password')
    ");

    header("Location: loginZivana.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register-Page</title>
   <link rel="stylesheet" href="assetZivana.css">
</head>

<body>
   <div class="box">
      <form method="post">
         <h2>Register</h2>
         <label for="">Username</label>
         <input type="text" name="username" placeholder="Username">
         <label for="">Password</label>
         <input type="password" name="password" id="password" placeholder="Password">
         <button type="submit" name="register">Register</button>
      </form>
   </div>
</body>

</html>