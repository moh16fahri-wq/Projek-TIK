<?php
// FILE TEST KONEKSI DATABASE
// Upload file ini ke folder project dan akses lewat browser
// Contoh: http://localhost/sekolah/test_koneksi.php

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Koneksi Database</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        h2 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        .step { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>?? Test Koneksi Database Sekolah Digital</h1>

<?php
// Konfigurasi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sekolah_digital";

echo '<div class="box">';
echo '<h2>?? Langkah 1: Cek Koneksi MySQL</h2>';

// Test koneksi ke MySQL (tanpa database)
$conn_test = @new mysqli($servername, $username, $password);

if ($conn_test->connect_error) {
    echo '<p class="error">? GAGAL: MySQL tidak terhubung!</p>';
    echo '<p>Error: ' . $conn_test->connect_error . '</p>';
    echo '<div class="step">';
    echo '<strong>Solusi:</strong><br>';
    echo '1. Pastikan XAMPP sudah dijalankan<br>';
    echo '2. Klik "Start" pada Apache dan MySQL di XAMPP Control Panel<br>';
    echo '3. Pastikan MySQL berjalan di port 3306<br>';
    echo '4. Refresh halaman ini setelah MySQL aktif';
    echo '</div>';
    exit;
} else {
    echo '<p class="success">? BERHASIL: Koneksi ke MySQL berhasil!</p>';
    echo '<p class="info">Server: ' . $servername . '</p>';
    echo '<p class="info">User: ' . $username . '</p>';
}
echo '</div>';

// Test koneksi ke database
echo '<div class="box">';
echo '<h2>?? Langkah 2: Cek Database</h2>';

$conn = @new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo '<p class="error">? GAGAL: Database "' . $dbname . '" tidak ditemukan!</p>';
    echo '<p>Error: ' . $conn->connect_error . '</p>';
    
    echo '<div class="step">';
    echo '<strong>Solusi - Buat Database:</strong><br>';
    echo '1. Buka phpMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a><br>';
    echo '2. Klik tab "SQL"<br>';
    echo '3. Copy-paste SQL dari file <strong>database_sekolah_digital_FIXED.sql</strong><br>';
    echo '4. Klik "Go" atau "Kirim"<br>';
    echo '5. Refresh halaman ini';
    echo '</div>';
    
    // Coba buat database otomatis
    if ($conn_test->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
        echo '<p class="success">? Database berhasil dibuat otomatis!</p>';
        echo '<p class="info">Silakan jalankan SQL untuk membuat tabel-tabel di phpMyAdmin</p>';
    }
    
    exit;
} else {
    echo '<p class="success">? BERHASIL: Database "' . $dbname . '" ditemukan!</p>';
    $conn->set_charset("utf8");
}
echo '</div>';

// Cek tabel-tabel
echo '<div class="box">';
echo '<h2>?? Langkah 3: Cek Tabel</h2>';

$tables_required = [
    'admins', 'gurus', 'siswas', 'kelas', 'pengumuman', 
    'tugas', 'absensi', 'materi', 'notifikasi', 'jurnal',
    'jadwal_pelajaran', 'catatan_pr', 'diskusi'
];

$tables_result = $conn->query("SHOW TABLES");
$tables_exist = [];

if ($tables_result) {
    while ($row = $tables_result->fetch_array()) {
        $tables_exist[] = $row[0];
    }
}

$missing_tables = array_diff($tables_required, $tables_exist);

if (count($missing_tables) > 0) {
    echo '<p class="error">? GAGAL: Beberapa tabel belum dibuat!</p>';
    echo '<p>Tabel yang hilang: <strong>' . implode(', ', $missing_tables) . '</strong></p>';
    
    echo '<div class="step">';
    echo '<strong>Solusi:</strong><br>';
    echo '1. Buka phpMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a><br>';
    echo '2. Pilih database "' . $dbname . '"<br>';
    echo '3. Klik tab "SQL"<br>';
    echo '4. Copy-paste SQL dari file <strong>database_sekolah_digital_FIXED.sql</strong><br>';
    echo '5. Klik "Go"<br>';
    echo '6. Refresh halaman ini';
    echo '</div>';
} else {
    echo '<p class="success">? BERHASIL: Semua tabel sudah dibuat!</p>';
    
    echo '<table>';
    echo '<tr><th>No</th><th>Nama Tabel</th><th>Jumlah Record</th></tr>';
    
    foreach ($tables_exist as $index => $table) {
        $count_result = $conn->query("SELECT COUNT(*) as total FROM $table");
        $count = $count_result->fetch_assoc()['total'];
        echo '<tr>';
        echo '<td>' . ($index + 1) . '</td>';
        echo '<td>' . $table . '</td>';
        echo '<td>' . $count . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
echo '</div>';

// Cek data sample
if (count($missing_tables) == 0) {
    echo '<div class="box">';
    echo '<h2>?? Langkah 4: Cek Data Sample</h2>';
    
    // Cek admin
    $admin_check = $conn->query("SELECT COUNT(*) as total FROM admins");
    $admin_count = $admin_check->fetch_assoc()['total'];
    
    if ($admin_count == 0) {
        echo '<p class="error">? Data admin kosong!</p>';
        echo '<p>Menambahkan data admin default...</p>';
        $conn->query("INSERT INTO admins (username, password) VALUES ('admin', 'admin123')");
        echo '<p class="success">? Data admin ditambahkan!</p>';
    } else {
        echo '<p class="success">? Data admin tersedia (' . $admin_count . ' record)</p>';
    }
    
    // Cek kelas
    $kelas_check = $conn->query("SELECT COUNT(*) as total FROM kelas");
    $kelas_count = $kelas_check->fetch_assoc()['total'];
    
    if ($kelas_count == 0) {
        echo '<p class="error">? Data kelas kosong! Jalankan SQL lengkap dari file.</p>';
    } else {
        echo '<p class="success">? Data kelas tersedia (' . $kelas_count . ' record)</p>';
    }
    
    // Cek guru
    $guru_check = $conn->query("SELECT COUNT(*) as total FROM gurus");
    $guru_count = $guru_check->fetch_assoc()['total'];
    
    if ($guru_count == 0) {
        echo '<p class="error">? Data guru kosong! Jalankan SQL lengkap dari file.</p>';
    } else {
        echo '<p class="success">? Data guru tersedia (' . $guru_count . ' record)</p>';
    }
    
    // Cek siswa
    $siswa_check = $conn->query("SELECT COUNT(*) as total FROM siswas");
    $siswa_count = $siswa_check->fetch_assoc()['total'];
    
    if ($siswa_count == 0) {
        echo '<p class="error">? Data siswa kosong! Jalankan SQL lengkap dari file.</p>';
    } else {
        echo '<p class="success">? Data siswa tersedia (' . $siswa_count . ' record)</p>';
    }
    
    echo '</div>';
}

// Test file API
echo '<div class="box">';
echo '<h2>?? Langkah 5: Cek File API</h2>';

$files_required = [
    'api.php' => 'File utama untuk load data',
    'save_absensi.php' => 'Simpan absensi',
    'save_pengumuman.php' => 'Simpan pengumuman',
    'save_tugas.php' => 'Simpan tugas',
    'save_materi.php' => 'Simpan materi',
    'save_siswa.php' => 'Tambah siswa',
    'delete_data.php' => 'Hapus data',
    'save_jadwal_pelajaran.php' => 'Simpan jadwal pelajaran'
];

$missing_files = [];

foreach ($files_required as $file => $desc) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
        echo '<p class="error">? ' . $file . ' - TIDAK DITEMUKAN</p>';
    } else {
        echo '<p class="success">? ' . $file . ' - ' . $desc . '</p>';
    }
}

if (count($missing_files) > 0) {
    echo '<div class="step">';
    echo '<strong>Solusi:</strong><br>';
    echo 'Upload file-file PHP yang hilang ke folder project Anda:<br>';
    foreach ($missing_files as $file) {
        echo '- ' . $file . '<br>';
    }
    echo '</div>';
}

echo '</div>';

// Test API endpoint
if (file_exists('api.php') && count($missing_tables) == 0) {
    echo '<div class="box">';
    echo '<h2>?? Langkah 6: Test API</h2>';
    
    echo '<p class="info">Testing API endpoint...</p>';
    
    // Test dengan file_get_contents
    $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api.php';
    
    echo '<p>URL API: <a href="' . $api_url . '" target="_blank">' . $api_url . '</a></p>';
    
    $api_test = @file_get_contents($api_url);
    
    if ($api_test === false) {
        echo '<p class="error">? API tidak dapat diakses!</p>';
        echo '<p>Coba akses manual: <a href="api.php" target="_blank">Klik di sini</a></p>';
    } else {
        $api_data = json_decode($api_test, true);
        
        if (isset($api_data['success']) && $api_data['success'] === true) {
            echo '<p class="success">? API berfungsi dengan baik!</p>';
            echo '<p class="info">Total records:</p>';
            echo '<ul>';
            if (isset($api_data['total_records'])) {
                foreach ($api_data['total_records'] as $key => $value) {
                    echo '<li>' . $key . ': ' . $value . '</li>';
                }
            }
            echo '</ul>';
        } else {
            echo '<p class="error">? API error: ' . ($api_data['error'] ?? 'Unknown error') . '</p>';
        }
    }
    
    echo '</div>';
}

// Kesimpulan
echo '<div class="box" style="background: #e8f5e9;">';
echo '<h2>?? KESIMPULAN</h2>';

$all_ok = (
    !$conn->connect_error && 
    count($missing_tables) == 0 && 
    count($missing_files) == 0 &&
    $admin_count > 0 &&
    $kelas_count > 0
);

if ($all_ok) {
    echo '<p class="success" style="font-size: 20px;">? SEMUA CEK BERHASIL!</p>';
    echo '<p style="font-size: 16px;">Database dan file sudah siap digunakan.</p>';
    echo '<p>Silakan akses aplikasi: <a href="index.html" target="_blank"><strong>Buka Aplikasi</strong></a></p>';
} else {
    echo '<p class="error" style="font-size: 20px;">? MASIH ADA MASALAH!</p>';
    echo '<p>Ikuti solusi di atas untuk memperbaikinya.</p>';
    
    echo '<h3>Checklist:</h3>';
    echo '<ul>';
    echo '<li>' . ($conn->connect_error ? '?' : '?') . ' Koneksi MySQL</li>';
    echo '<li>' . (count($missing_tables) == 0 ? '?' : '?') . ' Tabel database</li>';
    echo '<li>' . (count($missing_files) == 0 ? '?' : '?') . ' File PHP</li>';
    echo '<li>' . ($admin_count > 0 ? '?' : '?') . ' Data sample</li>';
    echo '</ul>';
}
echo '</div>';

$conn->close();
?>

<div class="box">
    <h2>?? Refresh Test</h2>
    <button onclick="location.reload()" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
        ?? Refresh Halaman
    </button>
</div>

</body>
</html>