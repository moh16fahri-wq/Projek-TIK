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
$file_url = $_POST['file_url'] ?? '';
$tanggal = date('Y-m-d');

// Validasi input
if (empty($judul) || empty($deskripsi) || $id_kelas == 0 || $id_guru == 0) {
    echo json_encode(['success' => false, 'message' => 'Judul, deskripsi, kelas, dan guru harus diisi!']);
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
$stmt = $conn->prepare("INSERT INTO materi (judul, deskripsi, id_kelas, id_guru, nama_guru, file, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiisss", $judul, $deskripsi, $id_kelas, $id_guru, $nama_guru, $file_url, $tanggal);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Materi berhasil di-upload!',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
}

$guru_query->close();
$stmt->close();
$conn->close();
?>