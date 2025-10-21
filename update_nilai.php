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
$id_tugas = $_POST['id_tugas'] ?? 0;
$nilai = $_POST['nilai'] ?? 0;
$feedback = $_POST['feedback'] ?? '';

// Validasi input
if ($id_siswa == 0 || $id_tugas == 0 || $nilai < 0 || $nilai > 100) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid! Nilai harus 0-100']);
    exit;
}

// Cek apakah nilai untuk siswa dan tugas ini sudah ada
$check = $conn->prepare("SELECT id FROM nilai WHERE id_siswa = ? AND id_tugas = ?");
$check->bind_param("ii", $id_siswa, $id_tugas);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Update jika sudah ada
    $stmt = $conn->prepare("UPDATE nilai SET nilai = ?, feedback = ?, updated_at = NOW() WHERE id_siswa = ? AND id_tugas = ?");
    $stmt->bind_param("isii", $nilai, $feedback, $id_siswa, $id_tugas);
    $message = 'Nilai berhasil diupdate!';
} else {
    // Insert jika belum ada
    $stmt = $conn->prepare("INSERT INTO nilai (id_siswa, id_tugas, nilai, feedback, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiis", $id_siswa, $id_tugas, $nilai, $feedback);
    $message = 'Nilai berhasil disimpan!';
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