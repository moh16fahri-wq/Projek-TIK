<?php
header('Content-Type: application/json');

// Koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sekolah_digital";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Koneksi gagal']));
}

$conn->set_charset("utf8");

// Ambil data dari request
$id_guru = $_POST['id_guru'] ?? 0;
$jadwal_json = $_POST['jadwal'] ?? '';

// Validasi input
if ($id_guru == 0 || empty($jadwal_json)) {
    echo json_encode(['success' => false, 'message' => 'ID Guru dan Jadwal harus diisi!']);
    exit;
}

// Update jadwal guru di database
$stmt = $conn->prepare("UPDATE gurus SET jadwal = ? WHERE id = ?");
$stmt->bind_param("si", $jadwal_json, $id_guru);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Jadwal guru berhasil diperbarui!'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>