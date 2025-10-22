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

$nama = $_POST['nama'] ?? '';
$latitude = $_POST['latitude'] ?? 0;
$longitude = $_POST['longitude'] ?? 0;

if (empty($nama)) {
    echo json_encode(['success' => false, 'message' => 'Nama kelas harus diisi!']);
    exit;
}

// Cek apakah nama kelas sudah ada
$check = $conn->prepare("SELECT id FROM kelas WHERE nama = ?");
$check->bind_param("s", $nama);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Nama kelas sudah ada!']);
    $check->close();
    $conn->close();
    exit;
}

$lokasi_json = json_encode([
    'latitude' => floatval($latitude),
    'longitude' => floatval($longitude)
]);

$stmt = $conn->prepare("INSERT INTO kelas (nama, lokasi) VALUES (?, ?)");
$stmt->bind_param("ss", $nama, $lokasi_json);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Kelas berhasil ditambahkan!',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
}

$check->close();
$stmt->close();
$conn->close();
?>
