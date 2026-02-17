<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ../public/index-ziva.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ziva AI Chat</title>

<link rel="stylesheet" href="../asset/chat.css">
</head>

<body>

<div class="chat-wrapper">

  <div class="chat-header">
    <div class="title">
      <h1>Ziva AI</h1>
      <p>Rewrite • Adaptation</p>
    </div>

    <div class="user-info">
      <span>Halo, <b><?= htmlspecialchars($_SESSION['nama']); ?></b></span>
      <a class="logout" href="../auth/logout-ziva.php">Logout</a>
    </div>
  </div>

  <div class="chat-filter">
    <label for="filterFitur">Filter Chat</label>
    <select id="filterFitur">
      <option value="">Semua Fitur</option>
      <option value="rewrite">Rewrite</option>
      <option value="adaptation">Adaptation</option>
    </select>

    <div class="tips">
      <span>Tips:</span>
      <span>Enter = kirim</span>
      <span>Shift+Enter = baris baru</span>
    </div>
  </div>

  <div class="chat-body" id="chatBody"></div>

  <div class="chat-input">

    <div class="row">
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
    </div>

    <textarea id="msg" placeholder="Ketik pesan..."></textarea>

    <div class="actions">
      <button id="btnKirim" onclick="kirimChat()">Kirim</button>
      <button class="danger" onclick="hapusSemuaChat()">Hapus Semua</button>
    </div>

    <div class="note">
      Output AI akan tersimpan otomatis ke tabel <b>result_zivana</b>.
    </div>
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

function nowTime(){
  const d = new Date();
  return d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
}

function escapeHtml(text) {
  return text
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function addTyping(){
  chatBody.innerHTML += `
    <div class="bubble-wrap">
      <div class="bubble ai">
        <div class="typing">
          <span>AI sedang mengetik</span>
          <span class="dot"></span>
          <span class="dot"></span>
          <span class="dot"></span>
        </div>
      </div>
      <div class="meta ai">${nowTime()}</div>
    </div>
  `;
  chatBody.scrollTop = chatBody.scrollHeight;
}

function loadChat(fitur=''){
  fetch("../chat/load-chat-ziva.php?fitur="+fitur)
    .then(res=>res.json())
    .then(data=>{
      chatBody.innerHTML='';
      resultBody.innerHTML='';

      if(data.status==='success'){
        data.chats.forEach(c=>{
          const jam = c.created_at_time ?? '';
          chatBody.innerHTML += `
            <div class="bubble-wrap" style="align-self:flex-end;">
              <div class="bubble user">${escapeHtml(c.input_text)}</div>
              <div class="meta user">${jam}</div>
            </div>

            <div class="bubble-wrap" style="align-self:flex-start;">
              <div class="bubble ai">
                ${escapeHtml(c.output_text)}
                <button class="copy-btn" onclick="copyText(\`${escapeHtml(c.output_text)}\`)">Copy</button>
              </div>
              <div class="meta ai">${jam}</div>
            </div>
          `;

          resultBody.innerHTML += `
            <div class="result-item">${escapeHtml(c.output_text)}</div>
          `;
        });

        chatBody.scrollTop = chatBody.scrollHeight;
      } else {
        chatBody.innerHTML = `
          <div class="bubble-wrap">
            <div class="bubble ai">Belum ada chat.</div>
            <div class="meta ai">${nowTime()}</div>
          </div>
        `;
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

  if(!msg || !fiturVal) return alert("Isi pesan & pilih fitur dulu!");

  chatBody.innerHTML += `
    <div class="bubble-wrap" style="align-self:flex-end;">
      <div class="bubble user">${escapeHtml(msg)}</div>
      <div class="meta user">${nowTime()}</div>
    </div>
  `;
  chatBody.scrollTop = chatBody.scrollHeight;

  addTyping();

  document.getElementById('btnKirim').innerHTML = 'Mengirim...';
  document.getElementById('btnKirim').disabled = true;

  fetch("../chat/save-chat-ziva.php", {
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
    document.getElementById('btnKirim').innerHTML = 'Kirim';
    document.getElementById('btnKirim').disabled = false;

    if(data.status==='success'){
      document.getElementById('msg').value='';
      loadChat(filterFitur.value);
    } else {
      alert(data.message ?? "Gagal simpan chat!");
      loadChat(filterFitur.value);
    }
  })
  .catch(()=>{
    document.getElementById('btnKirim').innerHTML = 'Kirim';
    document.getElementById('btnKirim').disabled = false;
    alert("Server error!");
    loadChat(filterFitur.value);
  });
}

function hapusSemuaChat(){
  if(!confirm("Yakin hapus semua chat?")) return;

  fetch("../chat/delete-chat-ziva.php", {
    method:"POST"
  })
  .then(res=>res.json())
  .then(data=>{
    if(data.status==='success'){
      loadChat(filterFitur.value);
    } else {
      alert("Gagal hapus chat!");
    }
  });
}

function copyText(text){
  navigator.clipboard.writeText(text)
    .then(()=> alert("Berhasil dicopy!"))
    .catch(()=> alert("Gagal copy"));
}

document.getElementById("msg").addEventListener("keydown", function(e){
  if(e.key === "Enter" && !e.shiftKey){
    e.preventDefault();
    kirimChat();
  }
});

window.onload = () => loadChat();
</script>

</body>
</html>
