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
$judul = $_POST['judul'] ?? '';
$deskripsi = $_POST['deskripsi'] ?? '';
$id_kelas = $_POST['id_kelas'] ?? 0;
$id_guru = $_POST['id_guru'] ?? 0;
$deadline = $_POST['deadline'] ?? '';
$mapel = $_POST['mapel'] ?? 'Umum';
$created_at = date('Y-m-d H:i:s');

// Validasi input
if (empty($judul) || empty($deskripsi) || $id_kelas == 0 || $id_guru == 0 || empty($deadline)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi!']);
    exit;
}

// Ambil nama guru dari database
$guru_query = $conn->prepare("SELECT nama FROM gurus WHERE id = ?");
$guru_query->bind_param("i", $id_guru);
$guru_query->execute();
$guru_result = $guru_query->get_result();
$guru_data = $guru_result->fetch_assoc();
$nama_guru = $guru_data['nama'] ?? 'Unknown';

// Insert ke database
$stmt = $conn->prepare("INSERT INTO tugas (judul, deskripsi, mapel, id_kelas, id_guru, nama_guru, deadline, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiiiss", $judul, $deskripsi, $mapel, $id_kelas, $id_guru, $nama_guru, $deadline, $created_at);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Tugas berhasil dibuat!',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
}

$guru_query->close();
$stmt->close();
$conn->close();
?>