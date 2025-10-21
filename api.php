<?php
// Set header JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Koneksi Database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_sekolah_digital";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode([
        'error' => 'Koneksi database gagal: ' . $conn->connect_error,
        'success' => false
    ]));
}

$conn->set_charset("utf8");

// Fungsi Helper
function fetchData($conn, $query) {
    $result = $conn->query($query);
    if (!$result) {
        error_log("Query Error: " . $conn->error . " | Query: " . $query);
        return [];
    }
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Ambil Data dari Database
try {
    $admins = fetchData($conn, "SELECT id, username FROM admins");
    $gurus = fetchData($conn, "SELECT id, nama, email, password, jadwal FROM gurus");
    $siswas = fetchData($conn, "SELECT id, nama, nis, id_kelas, email, password FROM siswas");
    $kelas = fetchData($conn, "SELECT id, nama, lokasi FROM kelas");
    $pengumuman = fetchData($conn, "SELECT id, judul, isi, nama_guru, tanggal FROM pengumuman ORDER BY tanggal DESC, id DESC");
    $tugas = fetchData($conn, "SELECT id, judul, deskripsi, mapel, id_kelas, id_guru, nama_guru, deadline, created_at FROM tugas ORDER BY created_at DESC");
    $absensi = fetchData($conn, "SELECT id, id_siswa, tanggal, status, keterangan, waktu FROM absensi ORDER BY tanggal DESC, waktu DESC");
    $materi = fetchData($conn, "SELECT id, judul, deskripsi, id_kelas, id_guru, nama_guru, file, tanggal FROM materi ORDER BY tanggal DESC, id DESC");
    $notifikasi = fetchData($conn, "SELECT id, id_user, role, message, `read`, timestamp FROM notifikasi ORDER BY timestamp DESC");
    $jurnal = fetchData($conn, "SELECT id, id_guru, id_kelas, tanggal, materi, daftarAbsensi FROM jurnal ORDER BY tanggal DESC");
    $jadwal_pelajaran_raw = fetchData($conn, "SELECT id, id_kelas, hari, jam_mulai, jam_selesai, mata_pelajaran FROM jadwal_pelajaran ORDER BY hari ASC, jam_mulai ASC");
    $catatan_pr = fetchData($conn, "SELECT id, id_siswa, id_jadwal, catatan, mingguDibuat FROM catatan_pr");
    $diskusi = fetchData($conn, "SELECT id, id_tugas, nama, role, pesan, timestamp FROM diskusi ORDER BY timestamp ASC");

    // Proses data jadwal guru
    foreach ($gurus as &$guru) {
        if (isset($guru['jadwal']) && !empty($guru['jadwal'])) {
            $decoded = json_decode($guru['jadwal'], true);
            $guru['jadwal'] = is_array($decoded) ? $decoded : [];
        } else {
            $guru['jadwal'] = [];
        }
    }
    unset($guru); // Break reference

    // Proses data lokasi kelas
    foreach ($kelas as &$k) {
        if (isset($k['lokasi']) && !empty($k['lokasi'])) {
            $decoded = json_decode($k['lokasi'], true);
            if (is_array($decoded) && isset($decoded['latitude']) && isset($decoded['longitude'])) {
                $k['lokasi'] = $decoded;
            } else {
                $k['lokasi'] = ['latitude' => -7.257472, 'longitude' => 112.752090];
            }
        } else {
            $k['lokasi'] = ['latitude' => -7.257472, 'longitude' => 112.752090];
        }
    }
    unset($k); // Break reference

    // Proses data jurnal
    foreach ($jurnal as &$j) {
        if (isset($j['daftarAbsensi']) && !empty($j['daftarAbsensi'])) {
            $decoded = json_decode($j['daftarAbsensi'], true);
            $j['daftarAbsensi'] = is_array($decoded) ? $decoded : [];
        } else {
            $j['daftarAbsensi'] = [];
        }
    }
    unset($j); // Break reference

    // Proses jadwal pelajaran per kelas
    $jadwal_pelajaran_grouped = [];
    foreach ($jadwal_pelajaran_raw as $jadwal) {
        $id_kelas = (int)$jadwal['id_kelas'];
        if (!isset($jadwal_pelajaran_grouped[$id_kelas])) {
            $jadwal_pelajaran_grouped[$id_kelas] = [];
        }
        $jadwal_pelajaran_grouped[$id_kelas][] = [
            'id' => (int)$jadwal['id'],
            'hari' => (int)$jadwal['hari'],
            'jamMulai' => $jadwal['jam_mulai'],
            'jamSelesai' => $jadwal['jam_selesai'],
            'mataPelajaran' => $jadwal['mata_pelajaran']
        ];
    }

    // Proses data tugas - tambahkan submissions array kosong
    foreach ($tugas as &$t) {
        $t['submissions'] = [];
        $t['id'] = (int)$t['id'];
        $t['id_kelas'] = (int)$t['id_kelas'];
        $t['id_guru'] = (int)$t['id_guru'];
    }
    unset($t); // Break reference

    // Proses data absensi - tambahkan relasi siswa
    foreach ($absensi as &$a) {
        $a['id'] = (int)$a['id'];
        $a['id_siswa'] = (int)$a['id_siswa'];
        
        // Cari data siswa
        $siswa = null;
        foreach ($siswas as $s) {
            if ($s['id'] == $a['id_siswa']) {
                $siswa = $s;
                break;
            }
        }
        
        if ($siswa) {
            $a['nama_siswa'] = $siswa['nama'];
            $a['id_kelas'] = (int)$siswa['id_kelas'];
        } else {
            $a['nama_siswa'] = 'Unknown';
            $a['id_kelas'] = 0;
        }
        
        // Pastikan waktu ada
        if (!isset($a['waktu']) || empty($a['waktu'])) {
            $a['waktu'] = '00:00:00';
        }
    }
    unset($a); // Break reference

    // Proses data materi
    foreach ($materi as &$m) {
        $m['id'] = (int)$m['id'];
        $m['id_kelas'] = (int)$m['id_kelas'];
        $m['id_guru'] = (int)$m['id_guru'];
        if (!isset($m['file']) || empty($m['file'])) {
            $m['file'] = '';
        }
    }
    unset($m); // Break reference

    // Proses data notifikasi
    foreach ($notifikasi as &$n) {
        $n['id'] = (int)$n['id'];
        $n['read'] = (bool)$n['read'];
        if (is_numeric($n['id_user'])) {
            $n['id_user'] = (int)$n['id_user'];
        }
        // id_user bisa jadi "semua" (string) atau integer
    }
    unset($n); // Break reference

    // Proses data diskusi
    foreach ($diskusi as &$d) {
        $d['id'] = (int)$d['id'];
        $d['id_tugas'] = (int)$d['id_tugas'];
    }
    unset($d); // Break reference

    // Proses data catatan PR
    foreach ($catatan_pr as &$cp) {
        $cp['id'] = (int)$cp['id'];
        $cp['id_siswa'] = (int)$cp['id_siswa'];
        $cp['id_jadwal'] = (int)$cp['id_jadwal'];
        $cp['mingguDibuat'] = (int)$cp['mingguDibuat'];
    }
    unset($cp); // Break reference

    // Proses data siswa
    foreach ($siswas as &$s) {
        $s['id'] = (int)$s['id'];
        $s['id_kelas'] = (int)$s['id_kelas'];
    }
    unset($s); // Break reference

    // Proses data guru
    foreach ($gurus as &$g) {
        $g['id'] = (int)$g['id'];
    }
    unset($g); // Break reference

    // Proses data admin
    foreach ($admins as &$adm) {
        $adm['id'] = (int)$adm['id'];
        // Tambahkan password untuk login (dalam produksi, sebaiknya di-hash)
        $admin_full = fetchData($conn, "SELECT password FROM admins WHERE id = " . $adm['id']);
        if (!empty($admin_full)) {
            $adm['password'] = $admin_full[0]['password'];
        }
    }
    unset($adm); // Break reference

    // Proses data kelas
    foreach ($kelas as &$kls) {
        $kls['id'] = (int)$kls['id'];
    }
    unset($kls); // Break reference

    // Susun Data
    $data = [
        'success' => true,
        'users' => [
            'admins' => $admins,
            'gurus' => $gurus,
            'siswas' => $siswas,
        ],
        'kelas' => $kelas,
        'pengumuman' => $pengumuman,
        'tugas' => $tugas,
        'absensi' => $absensi,
        'materi' => $materi,
        'notifikasi' => $notifikasi,
        'jurnal' => $jurnal,
        'jadwalPelajaran' => $jadwal_pelajaran_grouped,
        'catatanPR' => $catatan_pr,
        'diskusi' => $diskusi,
        'timestamp' => date('Y-m-d H:i:s'),
        'total_records' => [
            'admins' => count($admins),
            'gurus' => count($gurus),
            'siswas' => count($siswas),
            'kelas' => count($kelas),
            'pengumuman' => count($pengumuman),
            'tugas' => count($tugas),
            'absensi' => count($absensi),
            'materi' => count($materi),
            'notifikasi' => count($notifikasi),
            'jurnal' => count($jurnal),
            'jadwal_pelajaran' => count($jadwal_pelajaran_raw),
            'catatan_pr' => count($catatan_pr),
            'diskusi' => count($diskusi)
        ]
    ];

    // Kirim data sebagai JSON
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

$conn->close();
?>