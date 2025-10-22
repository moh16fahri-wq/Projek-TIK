<?php
// File untuk mengecek struktur database
header('Content-Type: text/html; charset=utf-8');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sekolah_digital";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Koneksi gagal: " . $conn->connect_error);
}

echo "<h2>✅ Koneksi Database Berhasil!</h2>";
echo "<h3>Daftar Tabel:</h3>";

// Cek tabel yang ada
$tables = $conn->query("SHOW TABLES");
echo "<ul>";
while ($row = $tables->fetch_array()) {
    echo "<li><strong>{$row[0]}</strong></li>";
}
echo "</ul>";

// Cek struktur tabel jadwal_pelajaran
echo "<h3>Struktur Tabel jadwal_pelajaran:</h3>";
$result = $conn->query("DESCRIBE jadwal_pelajaran");

if ($result) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Tabel jadwal_pelajaran tidak ditemukan!</p>";
    echo "<p>Silakan jalankan query SQL berikut di phpMyAdmin:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    echo "CREATE TABLE jadwal_pelajaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kelas INT NOT NULL,
    hari INT NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    mata_pelajaran VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_kelas) REFERENCES kelas(id) ON DELETE CASCADE
);";
    echo "</pre>";
}

// Cek data yang ada
echo "<h3>Data dalam jadwal_pelajaran:</h3>";
$data = $conn->query("SELECT * FROM jadwal_pelajaran");
if ($data && $data->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>ID Kelas</th><th>Hari</th><th>Jam Mulai</th><th>Jam Selesai</th><th>Mata Pelajaran</th></tr>";
    while ($row = $data->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['id_kelas']}</td>";
        echo "<td>{$row['hari']}</td>";
        echo "<td>{$row['jam_mulai']}</td>";
        echo "<td>{$row['jam_selesai']}</td>";
        echo "<td>{$row['mata_pelajaran']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Belum ada data.</p>";
}

$conn->close();

echo "<hr>";
echo "<h3>📝 Langkah Selanjutnya:</h3>";
echo "<ol>";
echo "<li>Pastikan tabel <strong>jadwal_pelajaran</strong> sudah ada</li>";
echo "<li>Pastikan file <strong>save_jadwal_pelajaran.php</strong> sudah di-upload</li>";
echo "<li>Coba tambah jadwal dari dashboard admin</li>";
echo "<li>Cek browser console (F12) untuk melihat error detail</li>";
echo "</ol>";
?>