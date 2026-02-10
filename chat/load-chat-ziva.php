<?php
session_start();
include "../config/connect-ziva.php";

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['id_user'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil filter fitur dari URL
$fitur = isset($_GET['fitur']) ? $_GET['fitur'] : "";

// Query dasar join chat + result
$sql = "
SELECT 
    c.id_chat,
    c.input_text,
    c.fitur,
    r.output_text,
    r.created_at
FROM chat_zivana c
LEFT JOIN result_zivana r ON c.id_chat = r.id_chat
WHERE c.id_user = ?
";

// Tambahkan filter fitur kalau dipilih
if ($fitur !== "") {
    $sql .= " AND c.fitur = ?";
}

$sql .= " ORDER BY c.id_chat DESC";

// Prepare statement
$stmt = mysqli_prepare($conn, $sql);

if ($fitur !== "") {
    mysqli_stmt_bind_param($stmt, "is", $id_user, $fitur);
} else {
    mysqli_stmt_bind_param($stmt, "i", $id_user);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$chats = [];

while ($row = mysqli_fetch_assoc($result)) {
    $chats[] = $row;
}

// Kalau ada data
if (count($chats) > 0) {
    echo json_encode([
        "status" => "success",
        "chats" => $chats
    ]);
} else {
    echo json_encode([
        "status" => "empty",
        "message" => "Belum ada chat"
    ]);
}
