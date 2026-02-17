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

$msg    = trim($data['msg'] ?? "");
$fitur  = $data['fitur'] ?? "";
$mode   = $data['mode'] ?? null;
$bahasa = $data['bahasa'] ?? null;
$gaya   = $data['gaya'] ?? null;

if ($msg === "" || $fitur === "") {
    echo json_encode(["status"=>"error","message"=>"Pesan dan fitur wajib diisi"]);
    exit;
}

$id_user = $_SESSION['id_user'];

/* ---------------------------
   SIMULASI OUTPUT AI (DEMO)
   Kamu tinggal ganti bagian ini kalau nanti pakai API AI beneran
----------------------------*/

$output_text = "";
$ringkasan = null;

if ($fitur === "rewrite") {
    $output_text = "✅ [Rewrite Mode: $mode]\n\n" . $msg;
    $ringkasan = "Rewrite dilakukan dengan mode: $mode";
} else if ($fitur === "adaptation") {
    $output_text = "🌍 [Adaptation: $bahasa | $gaya]\n\n" . $msg;
    $ringkasan = "Adaptasi bahasa ke: $bahasa, gaya: $gaya";
} else {
    echo json_encode(["status"=>"error","message"=>"Fitur tidak valid"]);
    exit;
}

/* ---------------------------
   1) INSERT KE chat_zivana
----------------------------*/

$stmt = $conn->prepare("
    INSERT INTO chat_zivana (id_user, input_text, fitur, mode_rewrite, bahasa_tujuan, gaya_bahasa)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("isssss", $id_user, $msg, $fitur, $mode, $bahasa, $gaya);

if (!$stmt->execute()) {
    echo json_encode(["status"=>"error","message"=>"Gagal simpan chat"]);
    exit;
}

$id_chat = $conn->insert_id;

/* ---------------------------
   2) INSERT KE result_zivana
----------------------------*/

$stmt2 = $conn->prepare("
    INSERT INTO result_zivana (id_chat, output_text, ringkasan_perubahan)
    VALUES (?, ?, ?)
");

$stmt2->bind_param("iss", $id_chat, $output_text, $ringkasan);

if (!$stmt2->execute()) {
    echo json_encode(["status"=>"error","message"=>"Chat tersimpan, tapi result gagal"]);
    exit;
}

echo json_encode([
    "status"=>"success",
    "id_chat"=>$id_chat
]);
