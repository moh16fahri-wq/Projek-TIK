<?php
header('Content-Type: application/json');

// Koneksi database
$servername = "sekolahdigital.infinityfree.com";  // MySQL Hostname dari InfinityFree
$username = "if0_40233112";              // MySQL Username dari InfinityFree
$password = "projektiktkj1";      // MySQL Password yang Anda buat
$dbname = "if0_40233112_sekolahdigital";        // MySQL Database dari InfinityFree

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Koneksi gagal: ' . $conn->connect_error]));
}

$conn->set_charset("utf8");

// Ambil data dari request
$nama = $_POST['nama'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi input
if (empty($nama) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Nama dan Password harus diisi!']);
    exit;
}

// Cek apakah email sudah ada (jika email diisi)
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

// Insert ke database dengan jadwal kosong
$jadwal_json = '[]';
$stmt = $conn->prepare("INSERT INTO gurus (nama, email, password, jadwal) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama, $email, $password, $jadwal_json);

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
