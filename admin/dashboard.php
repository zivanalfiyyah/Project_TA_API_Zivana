<?php
session_start();
include "../config/connect-ziva.php";

if (!isset($_SESSION['id_user']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: ../index-ziva.php");
    exit;
}

$adminId   = (int) $_SESSION['id_user'];
$namaAdmin = isset($_SESSION['nama']) ? $_SESSION['nama'] : "Admin";

if (isset($_GET['delete_user'])) {
    $idDelete = (int) $_GET['delete_user'];

    if ($idDelete !== $adminId) {
        mysqli_query($conn, "DELETE FROM user_zivana WHERE id_user = $idDelete");
    }

    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['toggle_role'])) {
    $idToggle = (int) $_GET['toggle_role'];

    if ($idToggle !== $adminId) {
        $q = mysqli_query($conn, "SELECT role FROM user_zivana WHERE id_user = $idToggle");
        $u = mysqli_fetch_assoc($q);

        if ($u) {
            $current = strtolower(trim($u['role']));
            $newRole = ($current === 'admin') ? 'user' : 'admin';

            mysqli_query($conn, "UPDATE user_zivana SET role='$newRole' WHERE id_user=$idToggle");
        }
    }

    header("Location: dashboard.php");
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$searchEsc = mysqli_real_escape_string($conn, $search);

$user   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM user_zivana"));
$chat   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM chat_zivana"));
$result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM result_zivana"));

if ($search !== "") {
    $usersQuery = mysqli_query($conn, "
        SELECT id_user, nama, email, role 
        FROM user_zivana
        WHERE nama LIKE '%$searchEsc%' OR email LIKE '%$searchEsc%'
        ORDER BY id_user DESC
        LIMIT 50
    ");
} else {
    $usersQuery = mysqli_query($conn, "
        SELECT id_user, nama, email, role 
        FROM user_zivana
        ORDER BY id_user DESC
        LIMIT 50
    ");
}

$chatQuery = mysqli_query($conn, "
    SELECT c.id_chat, c.input_text, c.fitur, c.gaya_bahasa, c.created_at, u.nama
    FROM chat_zivana c
    LEFT JOIN user_zivana u ON c.id_user = u.id_user
    ORDER BY c.id_chat DESC
    LIMIT 8
");

$resultQuery = mysqli_query($conn, "
    SELECT 
        r.id_result,
        r.output_text,
        r.ringkasan_perubahan,
        r.created_at,
        c.fitur,
        c.gaya_bahasa,
        u.nama
    FROM result_zivana r
    LEFT JOIN chat_zivana c ON r.id_chat = c.id_chat
    LEFT JOIN user_zivana u ON c.id_user = u.id_user
    ORDER BY r.id_result DESC
    LIMIT 8
");

$chartChat = mysqli_query($conn, "
    SELECT DATE(created_at) AS tanggal, COUNT(*) AS total
    FROM chat_zivana
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY tanggal ASC
");

$chartResult = mysqli_query($conn, "
    SELECT DATE(created_at) AS tanggal, COUNT(*) AS total
    FROM result_zivana
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY tanggal ASC
");

$chatData = [];
while($row = mysqli_fetch_assoc($chartChat)){
    $chatData[$row['tanggal']] = (int)$row['total'];
}

$resultData = [];
while($row = mysqli_fetch_assoc($chartResult)){
    $resultData[$row['tanggal']] = (int)$row['total'];
}

$labels = [];
$chatCounts = [];
$resultCounts = [];

for($i=6; $i>=0; $i--){
    $date = date("Y-m-d", strtotime("-$i days"));
    $labels[] = $date;
    $chatCounts[] = $chatData[$date] ?? 0;
    $resultCounts[] = $resultData[$date] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <link rel="stylesheet" href="../asset/admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container">

  <div class="topbar">
    <div>
      <h1>Dashboard Admin</h1>
      <p class="subtitle">Halo, <b><?= htmlspecialchars($namaAdmin) ?></b> 👋 Monitoring sistem kamu ada di sini.</p>
    </div>

    <div class="topbar-actions">
      <a class="btn btn-secondary" href="../chat/chat-ziva.php">Masuk ke Chat</a>
      <a class="btn btn-danger" href="../auth/logout-ziva.php">Logout</a>
    </div>
  </div>

  <div class="grid">
    <div class="stat-card">
      <div class="stat-title">Total User</div>
      <div class="stat-value"><?= $user['jml'] ?></div>
      <div class="stat-desc">Jumlah akun terdaftar</div>
    </div>

    <div class="stat-card">
      <div class="stat-title">Total Chat</div>
      <div class="stat-value"><?= $chat['jml'] ?></div>
      <div class="stat-desc">Total input user</div>
    </div>

    <div class="stat-card">
      <div class="stat-title">Total Result AI</div>
      <div class="stat-value"><?= $result['jml'] ?></div>
      <div class="stat-desc">Output rewrite/adaptation</div>
    </div>
  </div>

  <div class="panel chart-panel">
    <div class="panel-header">
      <h2>Statistik 7 Hari Terakhir</h2>
      <span class="muted">Chat vs Result AI</span>
    </div>

    <div class="chart-wrap">
      <canvas id="chart7days"></canvas>
    </div>
  </div>


  <div class="panel-grid">

    <div class="panel">
      <div class="panel-header">
        <h2>Daftar User</h2>

        <form class="search-box" method="GET">
          <input type="text" name="search" placeholder="Cari nama/email..." value="<?= htmlspecialchars($search) ?>">
          <button type="submit">Cari</button>
        </form>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th style="text-align:right;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while($u = mysqli_fetch_assoc($usersQuery)) : ?>
              <?php $isAdmin = (strtolower(trim($u['role'])) === 'admin'); ?>
              <tr>
                <td>#<?= $u['id_user'] ?></td>
                <td><?= htmlspecialchars($u['nama']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>
                  <span class="badge <?= $isAdmin ? 'badge-admin' : 'badge-user' ?>">
                    <?= htmlspecialchars($u['role']) ?>
                  </span>
                </td>
                <td style="text-align:right;">
                  <?php if ((int)$u['id_user'] === $adminId) : ?>
                    <span class="muted">Ini kamu</span>
                  <?php else : ?>
                    <a class="btn-mini"
                       href="?toggle_role=<?= $u['id_user'] ?>"
                       onclick="return confirm('Ubah role user ini?')">
                       <?= $isAdmin ? 'Jadikan User' : 'Jadikan Admin' ?>
                    </a>

                    <a class="btn-mini danger"
                       href="?delete_user=<?= $u['id_user'] ?>"
                       onclick="return confirm('Yakin mau hapus user ini?')">
                       Hapus
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <h2>Chat Terbaru</h2>
      </div>

      <div class="list">
        <?php while($c = mysqli_fetch_assoc($chatQuery)) : ?>
          <div class="list-item">
            <div class="list-title">
              <?= htmlspecialchars($c['nama'] ?? 'Unknown') ?>
              <span class="muted">
                • <?= htmlspecialchars($c['created_at'] ?? '-') ?>
                • <?= htmlspecialchars($c['fitur'] ?? '-') ?>
                • <?= htmlspecialchars($c['gaya_bahasa'] ?? '-') ?>
              </span>
            </div>
            <div class="list-desc">
              <?= htmlspecialchars(mb_strimwidth($c['input_text'] ?? '', 0, 90, '...')) ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>


    <div class="panel">
      <div class="panel-header">
        <h2>Result AI Terbaru</h2>
        <span class="muted">Klik item untuk lihat detail</span>
      </div>

      <div class="list">
        <?php while($r = mysqli_fetch_assoc($resultQuery)) : ?>
          <div class="list-item result-item"
               data-output="<?= htmlspecialchars($r['output_text'] ?? '', ENT_QUOTES) ?>"
               data-summary="<?= htmlspecialchars($r['ringkasan_perubahan'] ?? '', ENT_QUOTES) ?>"
               data-name="<?= htmlspecialchars($r['nama'] ?? 'Unknown', ENT_QUOTES) ?>"
               data-date="<?= htmlspecialchars($r['created_at'] ?? '-', ENT_QUOTES) ?>"
               data-fitur="<?= htmlspecialchars($r['fitur'] ?? '-', ENT_QUOTES) ?>"
               data-gaya="<?= htmlspecialchars($r['gaya_bahasa'] ?? '-', ENT_QUOTES) ?>"
               data-id="<?= htmlspecialchars($r['id_result'] ?? '-', ENT_QUOTES) ?>"
          >
            <div class="list-title">
              <?= htmlspecialchars($r['nama'] ?? 'Unknown') ?>
              <span class="muted">
                • <?= htmlspecialchars($r['created_at'] ?? '-') ?>
                • <?= htmlspecialchars($r['fitur'] ?? '-') ?>
                • <?= htmlspecialchars($r['gaya_bahasa'] ?? '-') ?>
              </span>
            </div>

            <div class="list-desc">
              <?= htmlspecialchars(mb_strimwidth($r['output_text'] ?? '', 0, 90, '...')) ?>
            </div>

            <div class="muted" style="margin-top:6px;">
              ID Result: #<?= htmlspecialchars($r['id_result']) ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

  </div>

  <!-- <div class="footer">
    <div class="footer-card">
      <h3>Catatan</h3>
      <p>
        Dashboard ini otomatis update dari database.
        Kalau kamu mau, aku bisa tambahin fitur export Excel juga.
      </p>
    </div>
  </div> -->

</div>

<div class="modal" id="resultModal">
  <div class="modal-box">
    <div class="modal-head">
      <div>
        <h3 id="mTitle">Detail Result</h3>
        <p class="muted" id="mMeta"></p>
      </div>
      <button class="modal-close" id="closeModal">✕</button>
    </div>

    <div class="modal-body">
      <div class="modal-section">
        <div class="modal-label">Output Text</div>
        <div class="modal-content" id="mOutput"></div>
      </div>

      <div class="modal-section">
        <div class="modal-label">Ringkasan Perubahan</div>
        <div class="modal-content" id="mSummary"></div>
      </div>
    </div>
  </div>
</div>

<script>
const labels = <?= json_encode($labels) ?>;
const chatCounts = <?= json_encode($chatCounts) ?>;
const resultCounts = <?= json_encode($resultCounts) ?>;

const ctx = document.getElementById('chart7days');

new Chart(ctx, {
  type: 'line',
  data: {
    labels: labels,
    datasets: [
      { label: 'Chat', data: chatCounts, tension: 0.35 },
      { label: 'Result AI', data: resultCounts, tension: 0.35 }
    ]
  },
  options: {
  responsive: true,
  maintainAspectRatio: false, 
  plugins: {
    legend: { labels: { color: "#ffffff" } }
  },
  scales: {
    x: { ticks: { color: "rgba(255,255,255,0.7)" } },
    y: { ticks: { color: "rgba(255,255,255,0.7)" }, beginAtZero: true }
  }
}

});

const modal = document.getElementById("resultModal");
const closeModal = document.getElementById("closeModal");

const mMeta = document.getElementById("mMeta");
const mOutput = document.getElementById("mOutput");
const mSummary = document.getElementById("mSummary");

document.querySelectorAll(".result-item").forEach(item => {
  item.addEventListener("click", () => {
    const output = item.dataset.output || "-";
    const summary = item.dataset.summary || "-";
    const name = item.dataset.name || "Unknown";
    const date = item.dataset.date || "-";
    const fitur = item.dataset.fitur || "-";
    const gaya = item.dataset.gaya || "-";
    const id = item.dataset.id || "-";

    mMeta.textContent = `#${id} • ${name} • ${date} • ${fitur} • ${gaya}`;
    mOutput.textContent = output;
    mSummary.textContent = summary;

    modal.classList.add("show");
  });
});

closeModal.addEventListener("click", () => {
  modal.classList.remove("show");
});

modal.addEventListener("click", (e) => {
  if(e.target === modal){
    modal.classList.remove("show");
  }
});
</script>

</body>
</html>
