<?php
session_start();
include "../config/connect-ziva.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

$id_user = $_SESSION['id_user'];

$data = json_decode(file_get_contents("php://input"), true);

$msg   = trim($data['msg'] ?? "");
$fitur = trim($data['fitur'] ?? "");

if ($msg === "" || $fitur === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Pesan atau fitur kosong"
    ]);
    exit;
}

$hasil_ai = "Hasil AI untuk: \"" . $msg . "\"";

$sqlChat = "INSERT INTO chat_zivana (id_user, input_text, fitur)
            VALUES (?, ?, ?)";

$stmtChat = mysqli_prepare($conn, $sqlChat);
mysqli_stmt_bind_param($stmtChat, "iss", $id_user, $msg, $fitur);

if (!mysqli_stmt_execute($stmtChat)) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal simpan chat"
    ]);
    exit;
}

$id_chat = mysqli_insert_id($conn);

$sqlResult = "INSERT INTO result_zivana (id_chat, output_text)
              VALUES (?, ?)";

$stmtResult = mysqli_prepare($conn, $sqlResult);
mysqli_stmt_bind_param($stmtResult, "is", $id_chat, $hasil_ai);

if (!mysqli_stmt_execute($stmtResult)) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal simpan result"
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "id_chat" => $id_chat,
    "output_text" => $hasil_ai
]);
