<?php
session_start();
include "../config/connect-ziva.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(["status"=>"error","message"=>"Not logged in"]);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$id_chat = intval($data['id_chat'] ?? 0);

if ($id_chat <= 0) {
    echo json_encode(["status"=>"error","message"=>"ID chat invalid"]);
    exit;
}

$id_user = $_SESSION['id_user'];

/* pastiin chat ini milik user yg login */
$cek = $conn->prepare("SELECT id_chat FROM chat_zivana WHERE id_chat=? AND id_user=?");
$cek->bind_param("ii", $id_chat, $id_user);
$cek->execute();
$res = $cek->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status"=>"error","message"=>"Chat tidak ditemukan"]);
    exit;
}

/* hapus result dulu */
$del1 = $conn->prepare("DELETE FROM result_zivana WHERE id_chat=?");
$del1->bind_param("i", $id_chat);
$del1->execute();

/* hapus chat */
$del2 = $conn->prepare("DELETE FROM chat_zivana WHERE id_chat=? AND id_user=?");
$del2->bind_param("ii", $id_chat, $id_user);

if ($del2->execute()) {
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Gagal hapus chat"]);
}
