<?php
// FILE TEST KONEKSI
$conn = new mysqli("localhost", "root", "", "db_sekolah_digital");

if ($conn->connect_error) {
    die("? KONEKSI GAGAL: " . $conn->connect_error);
}

echo "<h1 style='color: green;'>? KONEKSI BERHASIL!</h1>";
echo "<p>Database: db_sekolah_digital</p>";

$result = $conn->query("SELECT COUNT(*) as total FROM admins");
$data = $result->fetch_assoc();
echo "<p>Jumlah Admin: " . $data['total'] . "</p>";

$result = $conn->query("SELECT COUNT(*) as total FROM gurus");
$data = $result->fetch_assoc();
echo "<p>Jumlah Guru: " . $data['total'] . "</p>";

$result = $conn->query("SELECT COUNT(*) as total FROM siswas");
$data = $result->fetch_assoc();
echo "<p>Jumlah Siswa: " . $data['total'] . "</p>";

echo "<h2 style='color: blue;'>?? DATABASE SIAP DIGUNAKAN!</h2>";
echo "<p><a href='index.html' style='background: blue; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>?? BUKA APLIKASI</a></p>";

$conn->close();
?>
