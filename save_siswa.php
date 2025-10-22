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
$nama = $_POST['nama'] ?? '';
$nis = $_POST['nis'] ?? '';
$id_kelas = $_POST['id_kelas'] ?? 0;
$password = $_POST['password'] ?? '';
$email = $_POST['email'] ?? '';

// Validasi input
if (empty($nama) || empty($nis) || $id_kelas == 0 || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Nama, NIS, Kelas, dan Password harus diisi!']);
    exit;
}

// Cek apakah NIS sudah ada
$check = $conn->prepare("SELECT id FROM siswas WHERE nis = ?");
$check->bind_param("s", $nis);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'NIS sudah terdaftar!']);
    $check->close();
    $conn->close();
    exit;
}

// Insert ke database
$stmt = $conn->prepare("INSERT INTO siswas (nama, nis, id_kelas, password, email) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiss", $nama, $nis, $id_kelas, $password, $email);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Siswa berhasil ditambahkan!',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
}

$check->close();
$stmt->close();
$conn->close();
?>