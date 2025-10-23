<?php
header('Content-Type: application/json');

$servername = "sekolahdigital.infinityfree.com";  // MySQL Hostname dari InfinityFree
$username = "if0_40233112";              // MySQL Username dari InfinityFree
$password = "projektiktkj1";      // MySQL Password yang Anda buat
$dbname = "if0_40233112_sekolahdigital";        // MySQL Database dari InfinityFree

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Koneksi gagal']));
}

$conn->set_charset("utf8");

$table = $_POST['table'] ?? '';
$id = $_POST['id'] ?? 0;

if (empty($table) || $id == 0) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

$allowed_tables = ['pengumuman', 'tugas', 'materi', 'siswas', 'gurus', 'kelas', 'jadwal_pelajaran'];

if (!in_array($table, $allowed_tables)) {
    echo json_encode(['success' => false, 'message' => 'Tabel tidak valid']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Data berhasil dihapus!'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus: ' . $conn->error]);
}

$stmt->close();
$conn->close();

?>
