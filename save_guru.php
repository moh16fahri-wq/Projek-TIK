<?php
// ========== FILE: save_guru.php ==========
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
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($nama) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Nama dan password harus diisi!']);
    exit;
}

// Cek apakah email sudah ada
if (!empty($email)) {
    $check = $conn->prepare("SELECT id FROM gurus WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar!']);
        $check->close();
        $conn->close();
        exit;
    }
    $check->close();
}

$stmt = $conn->prepare("INSERT INTO gurus (nama, email, password, jadwal) VALUES (?, ?, ?, '[]')");
$stmt->bind_param("sss", $nama, $email, $password);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Guru berhasil ditambahkan!',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
