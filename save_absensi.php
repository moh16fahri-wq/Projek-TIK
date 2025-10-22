<?php
header('Content-Type: application/json');

// Koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sekolah_digital";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Koneksi gagal: ' . $conn->connect_error]));
}

$conn->set_charset("utf8");

// Ambil data dari request
$id_siswa = $_POST['id_siswa'] ?? 0;
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');
$status = $_POST['status'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';
$waktu = date('H:i:s'); // Waktu sekarang

// Log untuk debug
error_log("Menerima absensi: id_siswa=$id_siswa, tanggal=$tanggal, status=$status");

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
    $stmt = $conn->prepare("UPDATE absensi SET status = ?, keterangan = ?, waktu = ? WHERE id_siswa = ? AND tanggal = ?");
    $stmt->bind_param("sssis", $status, $keterangan, $waktu, $id_siswa, $tanggal);
    $message = 'Absensi berhasil diupdate!';
} else {
    // Insert jika belum ada
    $stmt = $conn->prepare("INSERT INTO absensi (id_siswa, tanggal, status, keterangan, waktu) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $id_siswa, $tanggal, $status, $keterangan, $waktu);
    $message = 'Absensi berhasil disimpan!';
}

if ($stmt->execute()) {
    $id = $check->num_rows > 0 ? 0 : $conn->insert_id;
    
    error_log("Absensi berhasil disimpan: id=$id");
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'id' => $id
    ]);
} else {
    error_log("Error menyimpan absensi: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
}

$check->close();
$stmt->close();
$conn->close();
?>
