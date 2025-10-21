<?php
// FILE TEST KONEKSI DATABASE
// Simpan sebagai: test.php di folder C:\xampp\htdocs\sekolah\

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Test Koneksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 { color: #667eea; margin-bottom: 20px; }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 5px solid #10b981;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 5px solid #ef4444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f3f4f6;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-ok { background: #d1fae5; color: #065f46; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .btn:hover { background: #5a67d8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 Test Koneksi Database</h1>
        
<?php
// KONFIGURASI DATABASE
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sekolah_digital";

// COBA KONEKSI
$conn = new mysqli($servername, $username, $password, $dbname);

// CEK KONEKSI
if ($conn->connect_error) {
    echo '<div class="error">';
    echo '<strong>❌ KONEKSI GAGAL!</strong><br>';
    echo 'Error: ' . $conn->connect_error . '<br><br>';
    echo '<strong>Solusi:</strong><br>';
    echo '1. Pastikan XAMPP sudah running (Apache & MySQL hijau)<br>';
    echo '2. Pastikan database "db_sekolah_digital" sudah dibuat<br>';
    echo '3. Cek username dan password database';
    echo '</div>';
    exit;
}

echo '<div class="success">';
echo '<strong>✅ KONEKSI BERHASIL!</strong><br>';
echo '📡 Server: ' . $servername . '<br>';
echo '🗄️ Database: ' . $dbname;
echo '</div>';

$conn->set_charset("utf8mb4");

// CEK TABEL
echo '<h3>📊 Daftar Tabel:</h3>';
$tables = $conn->query("SHOW TABLES");

if ($tables->num_rows > 0) {
    echo '<table>';
    echo '<tr><th>No</th><th>Nama Tabel</th><th>Jumlah Data</th><th>Status</th></tr>';
    $no = 1;
    
    while ($row = $tables->fetch_array()) {
        $table_name = $row[0];
        $count = $conn->query("SELECT COUNT(*) as total FROM $table_name")->fetch_assoc();
        
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td><strong>' . $table_name . '</strong></td>';
        echo '<td>' . $count['total'] . ' record</td>';
        echo '<td><span class="badge badge-ok">✓ OK</span></td>';
        echo '</tr>';
    }
    
    echo '</table>';
} else {
    echo '<div class="error">⚠️ Tidak ada tabel! Silakan import database.</div>';
}

// CEK DATA
echo '<h3>👥 Data User:</h3>';

$admin = $conn->query("SELECT COUNT(*) as total FROM admins")->fetch_assoc();
$guru = $conn->query("SELECT COUNT(*) as total FROM gurus")->fetch_assoc();
$siswa = $conn->query("SELECT COUNT(*) as total FROM siswas")->fetch_assoc();

echo '<div class="success">';
echo '🎓 Admin: <strong>' . $admin['total'] . '</strong> akun<br>';
echo '👩‍🏫 Guru: <strong>' . $guru['total'] . '</strong> orang<br>';
echo '🎒 Siswa: <strong>' . $siswa['total'] . '</strong> orang';
echo '</div>';

// CEK FILE API
echo '<h3>📁 Cek File Aplikasi:</h3>';

$files = [
    'index.html' => 'Halaman Utama',
    'main.html' => 'Halaman Login',
    'script.js' => 'JavaScript',
    'style.css' => 'CSS',
    'api.php' => 'API Utama'
];

echo '<table>';
echo '<tr><th>File</th><th>Keterangan</th><th>Status</th></tr>';

foreach ($files as $file => $desc) {
    $exists = file_exists($file);
    echo '<tr>';
    echo '<td><strong>' . $file . '</strong></td>';
    echo '<td>' . $desc . '</td>';
    echo '<td>';
    if ($exists) {
        echo '<span class="badge badge-ok">✓ Ada</span>';
    } else {
        echo '<span class="badge" style="background:#fee2e2;color:#991b1b;">✗ Tidak Ada</span>';
    }
    echo '</td>';
    echo '</tr>';
}

echo '</table>';

// KESIMPULAN
$all_ok = $tables->num_rows > 0 && $admin['total'] > 0;

if ($all_ok) {
    echo '<div class="success">';
    echo '<h3>🎉 SEMUA SIAP!</h3>';
    echo 'Database sudah terhubung dan aplikasi siap digunakan!';
    echo '</div>';
} else {
    echo '<div class="error">';
    echo '<h3>⚠️ ADA MASALAH!</h3>';
    echo 'Pastikan database sudah di-import dengan benar.';
    echo '</div>';
}

$conn->close();
?>

        <hr style="margin: 30px 0;">
        
        <h3>🚀 Selanjutnya:</h3>
        <a href="index.html" class="btn">Buka Aplikasi</a>
        <a href="api.php" class="btn" style="background: #10b981;">Test API</a>
        <a href="test.php" class="btn" style="background: #f59e0b;">Refresh Test</a>
    </div>
</body>
</html>
