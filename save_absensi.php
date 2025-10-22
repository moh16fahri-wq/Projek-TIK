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
$id_siswa = $_POST['id_siswa'] ?? 0;
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');
$status = $_POST['status'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';

// Validasi input
if ($id_siswa == 0 || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'ID Siswa dan Status harus diisi!']);
    exit;
}

// Cek apakah absensi untuk siswa dan tanggal ini sudah ada
$check = $conn->prepare("SELECT id FROM absensi WHERE id_siswa = ? AND tanggal = ?");
$check->bind_param("is", $id_siswa, $tanggal);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Update jika sudah ada
    $stmt = $conn->prepare("UPDATE absensi SET status = ?, keterangan = ? WHERE id_siswa = ? AND tanggal = ?");
    $stmt->bind_param("ssis", $status, $keterangan, $id_siswa, $tanggal);
    $message = 'Absensi berhasil diupdate!';
} else {
    // Insert jika belum ada
    $stmt = $conn->prepare("INSERT INTO absensi (id_siswa, tanggal, status, keterangan) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $id_siswa, $tanggal, $status, $keterangan);
    $message = 'Absensi berhasil disimpan!';
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => $message
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
}

$check->close();
$stmt->close();
$conn->close();
?>