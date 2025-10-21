<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sekolah_digital";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Koneksi gagal']));
}

$conn->set_charset("utf8");

$judul = $_POST['judul'] ?? '';
$isi = $_POST['isi'] ?? '';
$nama_guru = $_POST['nama_guru'] ?? '';
$tanggal = date('Y-m-d');

if (empty($judul) || empty($isi)) {
    echo json_encode(['success' => false, 'message' => 'Judul dan isi harus diisi!']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO pengumuman (judul, isi, nama_guru, tanggal) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $judul, $isi, $nama_guru, $tanggal);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Pengumuman berhasil disimpan!',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>