<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login & Register</title>
<link rel="stylesheet" href="../asset/auth.css">
</head>
<body>

<div class="auth-wrapper">

  <div class="auth-header" id="authHeader">Login</div>

  <div id="errorMsg" class="error-msg hidden"></div>
  <div id="successMsg" class="success-msg hidden"></div>

  <form id="loginForm">
  <input type="email" id="loginEmail" placeholder="Email" required>
  <input type="password" id="loginPassword" placeholder="Password" required>
  <button type="submit">Login</button>
  <div class="toggle-link" onclick="toggleForm()">Belum punya akun? Register</div>
</form>

<form id="registerForm" class="hidden">
  <input type="text" id="regNama" placeholder="Nama" required>
  <input type="email" id="regEmail" placeholder="Email" required>
  <input type="password" id="regPassword" placeholder="Password" required>
  <input type="password" id="regConfirm" placeholder="Konfirmasi Password" required>
  <button type="submit">Register</button>
  <div class="toggle-link" onclick="toggleForm()">Sudah punya akun? Login</div>
</form>


</div>

<script>
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const authHeader = document.getElementById('authHeader');
const errorMsg = document.getElementById('errorMsg');
const successMsg = document.getElementById('successMsg');

function toggleForm(){
  errorMsg.classList.add('hidden');
  successMsg.classList.add('hidden');

  if(loginForm.classList.contains('hidden')){
    loginForm.classList.remove('hidden');
    registerForm.classList.add('hidden');
    authHeader.textContent = 'Login';
  } else {
    loginForm.classList.add('hidden');
    registerForm.classList.remove('hidden');
    authHeader.textContent = 'Register';
  }
}

loginForm.addEventListener('submit', async function(e){
  e.preventDefault();

  const formData = new FormData();
  formData.append("email", loginEmail.value);
  formData.append("password", loginPassword.value);

  const res = await fetch("../auth/login-ziva.php", {
    method: "POST",
    body: formData
  });

  const text = await res.text();

 if(text === "admin"){
  window.location.href = "../admin/dashboard.php";
  successMsg.textContent = "Login berhasil!";
  successMsg.classList.remove('hidden')
}
else if(text === "user"){
  window.location.href = "../chat/chat-ziva.php";
  successMsg.textContent = "Login berhasil!";
  successMsg.classList.remove('hidden')
}
else{
  errorMsg.textContent = "Email atau password salah";
}

});

registerForm.addEventListener('submit', async function(e){
  e.preventDefault();

  if(regPassword.value !== regConfirm.value){
    errorMsg.textContent = "Password tidak sama";
    errorMsg.classList.remove('hidden');
    return;
  }

  const formData = new FormData();
  formData.append("nama", regNama.value);
  formData.append("email", regEmail.value);
  formData.append("password", regPassword.value);

  const res = await fetch("../auth/register-ziva.php", {
    method: "POST",
    body: formData
  });

 const text = await res.text();

if(text === "success"){
  successMsg.textContent = "Registrasi berhasil! Silakan login.";
  successMsg.classList.remove('hidden');
  toggleForm();

} else if(text === "exists"){
  errorMsg.textContent = "Email sudah terdaftar";

} else if(text === "empty"){
  errorMsg.textContent = "Data belum lengkap";

} else if(text === "query_error"){
  errorMsg.textContent = "Query database error";

} else if(text === "insert_error"){
  errorMsg.textContent = "Gagal menyimpan ke database";

} else {
  errorMsg.textContent = "Error tidak dikenal: " + text;
}

errorMsg.classList.remove('hidden');

});
</script>

</body>
</html>
