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
$id_chat = $data['id'] ?? 0;

if (!$id_chat) {
    echo json_encode([
        "status" => "error",
        "message" => "ID chat tidak valid"
    ]);
    exit;
}

$cek = mysqli_prepare(
    $conn,
    "SELECT id_chat FROM chat_zivana WHERE id_chat = ? AND id_user = ?"
);
mysqli_stmt_bind_param($cek, "ii", $id_chat, $id_user);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);

if (mysqli_stmt_num_rows($cek) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Chat tidak ditemukan"
    ]);
    exit;
}

$stmtResult = mysqli_prepare(
    $conn,
    "DELETE FROM result_zivana WHERE id_chat = ?"
);
mysqli_stmt_bind_param($stmtResult, "i", $id_chat);
mysqli_stmt_execute($stmtResult);

$stmtChat = mysqli_prepare(
    $conn,
    "DELETE FROM chat_zivana WHERE id_chat = ?"
);
mysqli_stmt_bind_param($stmtChat, "i", $id_chat);

if (!mysqli_stmt_execute($stmtChat)) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menghapus chat"
    ]);
    exit;
}

echo json_encode([
    "status" => "success"
]);
