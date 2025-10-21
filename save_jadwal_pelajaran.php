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
$id_kelas = $_POST['id_kelas'] ?? 0;
$hari = $_POST['hari'] ?? 0;
$jam_mulai = $_POST['jam_mulai'] ?? '';
$jam_selesai = $_POST['jam_selesai'] ?? '';
$mata_pelajaran = $_POST['mata_pelajaran'] ?? '';

// Validasi input
if ($id_kelas == 0 || $hari == 0 || empty($jam_mulai) || empty($jam_selesai) || empty($mata_pelajaran)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi!']);
    exit;
}

// Cek apakah tabel jadwal_pelajaran ada
$tableCheck = $conn->query("SHOW TABLES LIKE 'jadwal_pelajaran'");
if ($tableCheck->num_rows == 0) {
    // Buat tabel jika belum ada
    $createTable = "CREATE TABLE jadwal_pelajaran (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_kelas INT NOT NULL,
        hari INT NOT NULL,
        jam_mulai TIME NOT NULL,
        jam_selesai TIME NOT NULL,
        mata_pelajaran VARCHAR(100) NOT NULL,
        FOREIGN KEY (id_kelas) REFERENCES kelas(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($createTable)) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat tabel: ' . $conn->error]);
        exit;
    }
}

// Insert ke database
$stmt = $conn->prepare("INSERT INTO jadwal_pelajaran (id_kelas, hari, jam_mulai, jam_selesai, mata_pelajaran) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $id_kelas, $hari, $jam_mulai, $jam_selesai, $mata_pelajaran);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Jadwal pelajaran berhasil ditambahkan!',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>