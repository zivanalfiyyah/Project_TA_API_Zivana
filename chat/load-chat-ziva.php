<?php
session_start();
include "../config/connect-ziva.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(["status"=>"error","message"=>"Not logged in"]);
    exit;
}

$id_user = $_SESSION['id_user'];
$fitur = isset($_GET['fitur']) ? $_GET['fitur'] : "";

/*
  Struktur:
  chat_zivana: id_chat, id_user, input_text, fitur, mode_rewrite, bahasa_tujuan, gaya_bahasa, created_at
  result_zivana: id_result, id_chat, output_text, ringkasan_perubahan, created_at
*/

if ($fitur !== "") {
    $stmt = $conn->prepare("
        SELECT c.id_chat, c.input_text, c.fitur, c.created_at,
               r.output_text, r.ringkasan_perubahan
        FROM chat_zivana c
        LEFT JOIN result_zivana r ON r.id_chat = c.id_chat
        WHERE c.id_user = ? AND c.fitur = ?
        ORDER BY c.id_chat DESC
        LIMIT 100
    ");
    $stmt->bind_param("is", $id_user, $fitur);
} else {
    $stmt = $conn->prepare("
        SELECT c.id_chat, c.input_text, c.fitur, c.created_at,
               r.output_text, r.ringkasan_perubahan
        FROM chat_zivana c
        LEFT JOIN result_zivana r ON r.id_chat = c.id_chat
        WHERE c.id_user = ?
        ORDER BY c.id_chat DESC
        LIMIT 100
    ");
    $stmt->bind_param("i", $id_user);
}

$stmt->execute();
$res = $stmt->get_result();

$chats = [];
while($row = $res->fetch_assoc()){
    // kalau output belum ada
    if (!$row['output_text']) {
        $row['output_text'] = "(Belum ada hasil AI)";
    }
    $chats[] = $row;
}

if (count($chats) > 0) {
    echo json_encode(["status"=>"success","chats"=>$chats]);
} else {
    echo json_encode(["status"=>"empty","chats"=>[]]);
}
