<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index-ziva.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Chat</title>
<link rel="stylesheet" href="../asset/chat.css">
</head>
<body>
<div class="chat-wrapper">

  <div class="chat-header">
    Halo, <?= $_SESSION['nama']; ?> |
    <a href="../auth/logout-ziva.php" style="color:#f87171;text-decoration:none;">Logout</a>
  </div>

  <div class="chat-filter">
    <select id="filterFitur">
      <option value="">-- Semua Fitur --</option>
      <option value="rewrite">Rewrite</option>
      <option value="adaptation">Adaptation</option>
    </select>
  </div>

  <div class="chat-body" id="chatBody"></div>

  <div class="chat-input">

    <select id="fitur">
      <option value="">-- Pilih Fitur --</option>
      <option value="rewrite">Source Code Rewrite</option>
      <option value="adaptation">Language Adaptation</option>
    </select>

    <select id="modeRewrite" class="hidden">
      <option value="perbaiki_error">Perbaiki Error</option>
      <option value="rapikan">Rapikan Struktur</option>
      <option value="convert">Konversi Bahasa</option>
      <option value="explain">Penjelasan Kode</option>
    </select>

    <select id="bahasaTujuan" class="hidden">
      <option value="Indonesia">Indonesia</option>
      <option value="English">English</option>
      <option value="Japanese">Japanese</option>
    </select>

    <select id="gayaBahasa" class="hidden">
      <option value="formal">Formal</option>
      <option value="santai">Santai</option>
      <option value="akademis">Akademis</option>
    </select>

    <textarea id="msg" placeholder="Ketik pesan..."></textarea>
    <button onclick="kirimChat()">Kirim</button>
  </div>

  <div class="result-panel collapsed" id="resultPanel">
    <button class="result-toggle" onclick="toggleResult()">Tampilkan Result</button>
    <div id="resultBody"></div>
  </div>

</div>

<script>
const chatBody = document.getElementById('chatBody');
const resultBody = document.getElementById('resultBody');
const filterFitur = document.getElementById('filterFitur');

const fitur = document.getElementById('fitur');
const modeRewrite = document.getElementById('modeRewrite');
const bahasaTujuan = document.getElementById('bahasaTujuan');
const gayaBahasa = document.getElementById('gayaBahasa');

fitur.addEventListener('change', () => {
  if (fitur.value === 'rewrite') {
    modeRewrite.classList.remove('hidden');
    bahasaTujuan.classList.add('hidden');
    gayaBahasa.classList.add('hidden');
  } 
  else if (fitur.value === 'adaptation') {
    modeRewrite.classList.add('hidden');
    bahasaTujuan.classList.remove('hidden');
    gayaBahasa.classList.remove('hidden');
  } 
  else {
    modeRewrite.classList.add('hidden');
    bahasaTujuan.classList.add('hidden');
    gayaBahasa.classList.add('hidden');
  }
});

function toggleResult(){
  document.getElementById('resultPanel').classList.toggle('collapsed');
}

function loadChat(fitur=''){
  fetch("../chat/load_chat.php?fitur="+fitur)
    .then(res=>res.json())
    .then(data=>{
      chatBody.innerHTML='';
      resultBody.innerHTML='';

      if(data.status==='success'){
        data.chats.forEach(c=>{
          chatBody.innerHTML += `
            <div class="bubble user show">${c.input_text}</div>
            <div class="bubble ai show">${c.output_text}</div>
          `;

          resultBody.innerHTML += `
            <div class="result-item show">${c.output_text}</div>
          `;
        });
      } else {
        chatBody.innerHTML = `<div class="bubble ai show">Belum ada chat</div>`;
      }
    });
}

filterFitur.addEventListener('change', ()=> loadChat(filterFitur.value));

function kirimChat(){
  const msg = document.getElementById('msg').value.trim();
  const fiturVal = fitur.value;
  const modeVal = modeRewrite.value;
  const bahasaVal = bahasaTujuan.value;
  const gayaVal = gayaBahasa.value;

  if(!msg || !fiturVal) return alert("Isi pesan & pilih fitur dulu");

  fetch("../chat/save_chat.php", {
    method:"POST",
    headers:{"Content-Type":"application/json"},
    body: JSON.stringify({
      msg, 
      fitur: fiturVal,
      mode: modeVal,
      bahasa: bahasaVal,
      gaya: gayaVal
    })
  })
  .then(res=>res.json())
  .then(data=>{
    if(data.status==='success'){
      document.getElementById('msg').value='';
      loadChat(filterFitur.value);
    } else {
      alert("Gagal simpan chat");
    }
  });
}

window.onload = () => loadChat();
</script>
</body>
</html>
