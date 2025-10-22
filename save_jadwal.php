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

// Cek duplikat jadwal
$check = $conn->prepare("SELECT id FROM jadwal_pelajaran WHERE id_kelas = ? AND hari = ? AND jam_mulai = ?");
$check->bind_param("iis", $id_kelas, $hari, $jam_mulai);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Jadwal dengan waktu yang sama sudah ada!']);
    $check->close();
    $conn->close();
    exit;
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

$check->close();
$stmt->close();
$conn->close();
?>