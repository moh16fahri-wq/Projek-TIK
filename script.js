// ========== APLIKASI SEKOLAH DIGITAL - LENGKAP & TERHUBUNG DATABASE ==========

let data;
let currentUser = null;
let currentRole = null;
let absensiHariIniSelesai = false;

// FUNGSI LOAD DATA DARI DATABASE
async function loadDataAndInit() {
    try {
        const response = await fetch('api.php');
        
        if (!response.ok) {
            throw new Error(`Gagal mengambil data. Status: ${response.status}`);
        }
        
        data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }
        
        console.log("Data dari database berhasil dimuat:", data);

        if (document.getElementById("kata-harian")) {
            setupHalamanAwal();
        } else if (document.getElementById("app")) {
            showView("view-role-selection");
        }

    } catch (error) {
        console.error('Terjadi kesalahan saat memuat data:', error);
        alert('Gagal terhubung ke server atau database. Pastikan XAMPP (Apache & MySQL) sudah berjalan dan file api.php ada.');
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    
    if (input.type === "password") {
        input.type = "text";
        button.textContent = "🙈";
    } else {
        input.type = "password";
        button.textContent = "👁️";
    }
}

function setupHalamanAwal() {
    const quotes = ["Minggu: Istirahat.", "Senin: Mulailah!", "Selasa: Terus bertumbuh.", "Rabu: Jangan takut gagal.", "Kamis: Optimis!", "Jumat: Selesaikan.", "Sabtu: Refleksi."];
    document.getElementById("kata-harian").textContent = quotes[new Date().getDay()];
    document.getElementById("tombol-buka").addEventListener("click", () => window.location.href = "main.html");
}

function showView(viewId) {
    document.querySelectorAll("#app > div").forEach(div => div.classList.add("hidden"));
    document.getElementById(viewId).classList.remove("hidden");
}

function getNomorMinggu(date) {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

function showLogin(role) {
    currentRole = role;
    showView("view-login-form");
    document.querySelectorAll("#view-login-form > div").forEach(div => div.classList.add("hidden"));
    const title = document.getElementById("login-title");
    
    if (role === "admin") {
        title.textContent = "Login Admin";
        document.getElementById("form-admin").classList.remove("hidden");
    } else if (role === "guru") {
        title.textContent = "Login Guru";
        document.getElementById("form-guru").classList.remove("hidden");
        populateGuruDropdown();
    } else if (role === "siswa") {
        title.textContent = "Login Siswa";
        document.getElementById("form-siswa").classList.remove("hidden");
        populateKelasDropdown();
    }
}

function populateGuruDropdown() {
    const select = document.getElementById("guru-select-nama");
    select.innerHTML = '<option value="">-- Pilih Nama Guru --</option>';
    data.users.gurus.forEach(guru => select.innerHTML += `<option value="${guru.id}">${guru.nama}</option>`);
}

function populateKelasDropdown() {
    const select = document.getElementById("siswa-select-kelas");
    select.innerHTML = '<option value="">-- Pilih Kelas --</option>';
    data.kelas.forEach(k => select.innerHTML += `<option value="${k.id}">${k.nama}</option>`);
    populateSiswaDropdown();
}

function populateSiswaDropdown() {
    const kelasId = document.getElementById("siswa-select-kelas").value;
    const select = document.getElementById("siswa-select-nama");
    select.innerHTML = '<option value="">-- Pilih Nama Siswa --</option>';
    if (kelasId) {
        data.users.siswas.filter(s => s.id_kelas == kelasId).forEach(s => {
            select.innerHTML += `<option value="${s.id}">${s.nama}</option>`;
        });
    }
}

function login() {
    let user = null;
    if (currentRole === "admin") {
        user = data.users.admins.find(u => u.username === document.getElementById("admin-user").value && u.password === document.getElementById("admin-pass").value);
    } else if (currentRole === "guru") {
        user = data.users.gurus.find(u => u.id == document.getElementById("guru-select-nama").value && u.password === document.getElementById("guru-pass").value);
    } else if (currentRole === "siswa") {
        user = data.users.siswas.find(u => u.id == document.getElementById("siswa-select-nama").value && u.password === document.getElementById("siswa-pass").value);
    }
    
    if (user) {
        currentUser = user;
        alert("Login Berhasil!");
        showDashboard();
    } else {
        alert("Login Gagal! Periksa kembali data Anda.");
    }
}

function logout() {
    currentUser = null;
    currentRole = null;
    absensiHariIniSelesai = false;
    showView("view-role-selection");
    document.querySelectorAll("input").forEach(i => i.value = "");
}

function toggleProfilPopup() {
    const popup = document.getElementById("profil-popup");
    popup.classList.toggle("hidden");
}

function renderProfilPopup() {
    let dataProfil = '';
    if (currentRole === 'admin') {
        dataProfil = `<div class="profil-info"><p><strong>Username:</strong> ${currentUser.username}</p><p><strong>Role:</strong> Administrator</p></div>`;
    } else if (currentRole === 'guru') {
        const jumlahJadwal = currentUser.jadwal ? currentUser.jadwal.length : 0;
        dataProfil = `<div class="profil-info"><p><strong>Nama:</strong> ${currentUser.nama}</p><p><strong>Email:</strong> ${currentUser.email || '-'}</p><p><strong>ID Guru:</strong> ${currentUser.id}</p><p><strong>Jadwal Mengajar:</strong> ${jumlahJadwal} sesi</p></div>`;
    } else if (currentRole === 'siswa') {
        const namaKelas = data.kelas.find(k => k.id === currentUser.id_kelas)?.nama || '-';
        dataProfil = `<div class="profil-info"><p><strong>Nama:</strong> ${currentUser.nama}</p><p><strong>NIS:</strong> ${currentUser.nis || '-'}</p><p><strong>Kelas:</strong> ${namaKelas}</p></div>`;
    }
    return `<div class="profil-header"><div class="profil-avatar">👤</div><h4>${currentUser.nama || currentUser.username}</h4></div>${dataProfil}<div class="profil-actions"><button class="profil-btn ganti-pass-btn" onclick="showGantiPassword()">🔒 Ganti Password</button><button class="profil-btn logout-btn" onclick="logout()">🚪 Logout</button></div><div id="ganti-password-section" class="hidden"><hr><h5>Ganti Password</h5><div class="password-wrapper"><input type="password" id="old-pass-popup" placeholder="Password Lama"><button type="button" class="toggle-password" onclick="togglePassword('old-pass-popup')">👁️</button></div><div class="password-wrapper"><input type="password" id="new-pass-popup" placeholder="Password Baru"><button type="button" class="toggle-password" onclick="togglePassword('new-pass-popup')">👁️</button></div><div class="password-wrapper"><input type="password" id="confirm-new-pass-popup" placeholder="Konfirmasi Password"><button type="button" class="toggle-password" onclick="togglePassword('confirm-new-pass-popup')">👁️</button></div><button onclick="changePasswordFromPopup()">Simpan Password</button></div>`;
}

function showGantiPassword() {
    document.getElementById("ganti-password-section").classList.toggle("hidden");
}

function changePasswordFromPopup() {
    const oldP = document.getElementById("old-pass-popup").value;
    const newP = document.getElementById("new-pass-popup").value;
    const confirmP = document.getElementById("confirm-new-pass-popup").value;
    if (!oldP || !newP || !confirmP) return alert("Semua kolom harus diisi!");
    if (newP !== confirmP) return alert("Password baru tidak cocok!");
    if (oldP !== currentUser.password) return alert("Password lama salah!");
    currentUser.password = newP;
    alert("Password berhasil diubah!");
    document.getElementById("old-pass-popup").value = "";
    document.getElementById("new-pass-popup").value = "";
    document.getElementById("confirm-new-pass-popup").value = "";
    document.getElementById("ganti-password-section").classList.add("hidden");
}

function showDashboard() {
    showView("view-dashboard");
    const header = document.querySelector("#view-dashboard .header");
    const content = document.getElementById("dashboard-content");
    content.innerHTML = "";
    
    if (!document.getElementById('notification-bell')) {
        header.innerHTML = `<h2 id="dashboard-title">Dashboard</h2><div class="header-actions"><div id="notification-bell" onclick="toggleNotifDropdown()"><span id="notif-badge" class="notification-badge hidden">0</span>🔔</div><div id="notification-dropdown" class="hidden"></div><div class="profil-menu" onclick="toggleProfilPopup()"><div class="profil-icon">👤</div><span class="profil-name">${currentUser.nama || currentUser.username}</span></div><div id="profil-popup" class="hidden"></div></div>`;
    }

    if (currentRole === 'admin') {
        document.getElementById('dashboard-title').textContent = "Dashboard Admin";
        content.innerHTML = renderAdminDashboard();
        renderAdminAnalitik();
    } else if (currentRole === 'guru') {
        document.getElementById('dashboard-title').textContent = `Selamat Datang, ${currentUser.nama}`;
        content.innerHTML = renderGuruDashboard();
        cekJadwalMengajar();
        renderTugasSubmissions();
    } else if (currentRole === 'siswa') {
        document.getElementById('dashboard-title').textContent = `Selamat Datang, ${currentUser.nama}`;
        cekAbsensiSiswaHariIni();
        content.innerHTML = renderSiswaDashboard();
        renderSiswaFeatures();
    }
    
    document.getElementById('profil-popup').innerHTML = renderProfilPopup();
    renderNotificationBell();
}

// ========== ADMIN DASHBOARD ==========

function renderAdminDashboard() {
    return `<div class="tabs"><button class="tab-link active" onclick="openAdminTab(event, 'Analitik')">📈 Analitik</button><button class="tab-link" onclick="openAdminTab(event, 'AbsensiSiswa')">📊 Absensi Siswa</button><button class="tab-link" onclick="openAdminTab(event, 'AbsensiGuru')">👨‍🏫 Absensi Guru</button><button class="tab-link" onclick="openAdminTab(event, 'Manajemen')">⚙️ Manajemen Data</button><button class="tab-link" onclick="openAdminTab(event, 'JadwalGuru')">🗓️ Jadwal Guru</button><button class="tab-link" onclick="openAdminTab(event, 'JadwalPelajaran')">📚 Jadwal Pelajaran</button><button class="tab-link" onclick="openAdminTab(event, 'Pengumuman')">📢 Pengumuman</button></div><div id="Analitik" class="tab-content" style="display:block;"></div><div id="AbsensiSiswa" class="tab-content"></div><div id="AbsensiGuru" class="tab-content"></div><div id="Manajemen" class="tab-content"></div><div id="JadwalGuru" class="tab-content"></div><div id="JadwalPelajaran" class="tab-content"></div><div id="Pengumuman" class="tab-content"></div>`;
}

function openAdminTab(evt, tabName) {
    document.querySelectorAll(".tab-content").forEach(tc => tc.style.display = "none");
    document.querySelectorAll(".tab-link").forEach(tl => tl.className = tl.className.replace(" active", ""));
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
    if (tabName === 'Analitik') renderAdminAnalitik();
    else if (tabName === 'AbsensiSiswa') renderAdminAbsensiSiswa();
    else if (tabName === 'AbsensiGuru') renderAdminAbsensiGuru();
    else if (tabName === 'Manajemen') renderAdminManajemen();
    else if (tabName === 'JadwalGuru') renderAdminJadwal();
    else if (tabName === 'JadwalPelajaran') renderAdminManajemenJadwal();
    else if (tabName === 'Pengumuman') renderAdminPengumuman();
}

function renderAdminAnalitik() {
    const container = document.getElementById("Analitik");
    const totalSiswa = data.users.siswas.length;
    const totalGuru = data.users.gurus.length;
    const totalKelas = data.kelas.length;
    const totalTugas = data.tugas.length;
    
    const today = new Date().toISOString().split('T')[0];
    const absenHariIni = data.absensi.filter(a => a.tanggal === today);
    const hadir = absenHariIni.filter(a => a.status === "masuk").length;
    const izin = absenHariIni.filter(a => a.status === "izin").length;
    const sakit = absenHariIni.filter(a => a.status === "sakit").length;
    
    container.innerHTML = `
        <div class="dashboard-section">
            <h4>📊 Statistik Umum</h4>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                <div style="background: white; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3>${totalSiswa}</h3>
                    <p>Siswa</p>
                </div>
                <div style="background: white; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3>${totalGuru}</h3>
                    <p>Guru</p>
                </div>
                <div style="background: white; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3>${totalKelas}</h3>
                    <p>Kelas</p>
                </div>
                <div style="background: white; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3>${totalTugas}</h3>
                    <p>Tugas</p>
                </div>
            </div>
        </div>
        <div class="dashboard-section">
            <h4>📊 Absensi Hari Ini (${today})</h4>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <div style="background: white; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3 style="color: green;">${hadir}</h3>
                    <p>Hadir</p>
                </div>
                <div style="background: white; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3 style="color: orange;">${izin}</h3>
                    <p>Izin</p>
                </div>
                <div style="background: white; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3 style="color: red;">${sakit}</h3>
                    <p>Sakit</p>
                </div>
            </div>
        </div>
    `;
}

function renderAdminAbsensiSiswa() {
    const container = document.getElementById("AbsensiSiswa");
    const today = new Date().toISOString().split('T')[0];
    
    let html = `<div class="dashboard-section">
        <h4>📊 Rekap Absensi Siswa</h4>
        <div style="margin-bottom: 1rem;">
            <label>Filter Tanggal:</label>
            <input type="date" id="filter-tanggal-siswa" value="${today}" onchange="renderAdminAbsensiSiswa()">
        </div>`;
    
    const filterTanggal = document.getElementById('filter-tanggal-siswa')?.value || today;
    const absensiFiltered = data.absensi.filter(a => a.tanggal === filterTanggal);
    
    if (absensiFiltered.length === 0) {
        html += "<p>Belum ada data absensi untuk tanggal ini.</p>";
    } else {
        html += `<table>
            <tr><th>Waktu</th><th>Nama</th><th>Kelas</th><th>Status</th><th>Keterangan</th><th>Aksi</th></tr>`;
        
        [...absensiFiltered].reverse().forEach(a => {
            const siswa = data.users.siswas.find(s => s.id == a.id_siswa);
            const namaKelas = siswa ? data.kelas.find(k => k.id === siswa.id_kelas)?.nama || "-" : "-";
            const namaSiswa = siswa ? siswa.nama : "Unknown";
            const statusClass = a.status === 'masuk' ? 'success-color' : a.status === 'sakit' ? 'danger-color' : 'warning-color';
            
            html += `<tr>
                <td>${a.waktu || '-'}</td>
                <td>${namaSiswa}</td>
                <td>${namaKelas}</td>
                <td style="color: var(--${statusClass})"><strong>${a.status.toUpperCase()}</strong></td>
                <td>${a.keterangan || '-'}</td>
                <td><button class="small-btn delete" onclick="hapusAbsensi(${a.id})">Hapus</button></td>
            </tr>`;
        });
        html += "</table>";
    }
    
    html += "</div>";
    container.innerHTML = html;
}

function renderAdminAbsensiGuru() {
    const container = document.getElementById("AbsensiGuru");
    const today = new Date().toISOString().split('T')[0];
    
    let html = `<div class="dashboard-section">
        <h4>👨‍🏫 Rekap Absensi Guru (Mengajar)</h4>
        <div style="margin-bottom: 1rem;">
            <label>Filter Tanggal:</label>
            <input type="date" id="filter-tanggal-guru" value="${today}" onchange="renderAdminAbsensiGuru()">
        </div>`;
    
    const filterTanggal = document.getElementById('filter-tanggal-guru')?.value || today;
    const jurnalFiltered = data.jurnal.filter(j => j.tanggal === filterTanggal);
    
    if (jurnalFiltered.length === 0) {
        html += "<p>Belum ada data mengajar untuk tanggal ini.</p>";
    } else {
        html += `<table>
            <tr><th>Guru</th><th>Kelas</th><th>Materi</th><th>Hadir</th><th>Tidak Hadir</th></tr>`;
        
        jurnalFiltered.forEach(j => {
            const guru = data.users.gurus.find(g => g.id == j.id_guru);
            const kelas = data.kelas.find(k => k.id == j.id_kelas);
            const namaGuru = guru ? guru.nama : "Unknown";
            const namaKelas = kelas ? kelas.nama : "Unknown";
            
            const absensiList = j.daftarAbsensi || [];
            const hadir = absensiList.filter(a => a.status === 'hadir').length;
            const tidakHadir = absensiList.length - hadir;
            
            html += `<tr>
                <td>${namaGuru}</td>
                <td>${namaKelas}</td>
                <td>${j.materi}</td>
                <td style="color: var(--success-color)"><strong>${hadir}</strong></td>
                <td style="color: var(--danger-color)"><strong>${tidakHadir}</strong></td>
            </tr>`;
        });
        html += "</table>";
    }
    
    html += "</div>";
    container.innerHTML = html;
}

async function hapusAbsensi(id) {
    if (!confirm("Yakin ingin menghapus data absensi ini?")) return;
    
    try {
        const formData = new FormData();
        formData.append('table', 'absensi');
        formData.append('id', id);
        
        const response = await fetch('delete_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            data.absensi = data.absensi.filter(a => a.id !== id);
            renderAdminAbsensiSiswa();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus absensi');
    }
}

function renderAdminManajemen() {
    const container = document.getElementById("Manajemen");
    container.innerHTML = `
        <div class="dashboard-section">
            <h4>👥 Manajemen Siswa</h4>
            <div class="form-container">
                <h5>Tambah Siswa Baru</h5>
                <input type="text" id="new-siswa-nama" placeholder="Nama Siswa">
                <input type="text" id="new-siswa-nis" placeholder="NIS">
                <select id="new-siswa-kelas">${data.kelas.map(k => `<option value="${k.id}">${k.nama}</option>`).join("")}</select>
                <input type="password" id="new-siswa-pass" placeholder="Password">
                <button onclick="tambahSiswa()">Tambah Siswa</button>
            </div>
            <table>
                <tr><th>ID</th><th>Nama</th><th>NIS</th><th>Kelas</th><th>Aksi</th></tr>
                ${data.users.siswas.map(s => {
                    const namaKelas = data.kelas.find(k => k.id == s.id_kelas)?.nama || "-";
                    return `<tr><td>${s.id}</td><td>${s.nama}</td><td>${s.nis}</td><td>${namaKelas}</td><td><button class="small-btn delete" onclick="hapusSiswa(${s.id})">Hapus</button></td></tr>`;
                }).join("")}
            </table>
        </div>
        <div class="dashboard-section">
            <h4>👨‍🏫 Manajemen Guru</h4>
            <div class="form-container">
                <h5>Tambah Guru Baru</h5>
                <input type="text" id="new-guru-nama" placeholder="Nama Guru">
                <input type="email" id="new-guru-email" placeholder="Email (opsional)">
                <input type="password" id="new-guru-pass" placeholder="Password">
                <button onclick="tambahGuru()">Tambah Guru</button>
            </div>
            <table>
                <tr><th>ID</th><th>Nama</th><th>Email</th><th>Aksi</th></tr>
                ${data.users.gurus.map(g => `<tr><td>${g.id}</td><td>${g.nama}</td><td>${g.email || '-'}</td><td><button class="small-btn delete" onclick="hapusGuru(${g.id})">Hapus</button></td></tr>`).join("")}
            </table>
        </div>
        <div class="dashboard-section">
            <h4>🏫 Manajemen Kelas</h4>
            <div class="form-container">
                <h5>Tambah Kelas Baru</h5>
                <input type="text" id="new-kelas-nama" placeholder="Nama Kelas (contoh: 10 IPA 1)">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <input type="number" step="any" id="new-kelas-lat" placeholder="Latitude" value="-7.257472">
                    <input type="number" step="any" id="new-kelas-lng" placeholder="Longitude" value="112.752090">
                </div>
                <button onclick="ambilLokasiGPS()">📍 Ambil Lokasi Saat Ini</button>
                <button onclick="tambahKelas()">Tambah Kelas</button>
            </div>
            <table>
                <tr><th>ID</th><th>Nama Kelas</th><th>Lokasi GPS</th><th>Aksi</th></tr>
                ${data.kelas.map(k => {
                    const lat = k.lokasi?.latitude || '-';
                    const lng = k.lokasi?.longitude || '-';
                    return `<tr><td>${k.id}</td><td>${k.nama}</td><td>${lat}, ${lng}</td><td><button class="small-btn delete" onclick="hapusKelas(${k.id})">Hapus</button></td></tr>`;
                }).join("")}
            </table>
        </div>
    `;
}

async function tambahSiswa() {
    const nama = document.getElementById("new-siswa-nama").value;
    const nis = document.getElementById("new-siswa-nis").value;
    const id_kelas = parseInt(document.getElementById("new-siswa-kelas").value);
    const password = document.getElementById("new-siswa-pass").value;
    
    if (!nama || !nis || !password) return alert("Semua field harus diisi!");
    
    try {
        const formData = new FormData();
        formData.append('nama', nama);
        formData.append('nis', nis);
        formData.append('id_kelas', id_kelas);
        formData.append('password', password);
        formData.append('email', '');
        
        const response = await fetch('save_siswa.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.users.siswas.push({
                id: result.id,
                nama: nama,
                nis: nis,
                id_kelas: id_kelas,
                password: password,
                email: ''
            });
            
            document.getElementById("new-siswa-nama").value = "";
            document.getElementById("new-siswa-nis").value = "";
            document.getElementById("new-siswa-pass").value = "";
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambah siswa');
    }
}

async function hapusSiswa(id) {
    if (!confirm("Yakin ingin menghapus siswa ini?")) return;
    
    try {
        const formData = new FormData();
        formData.append('table', 'siswas');
        formData.append('id', id);
        
        const response = await fetch('delete_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            data.users.siswas = data.users.siswas.filter(s => s.id !== id);
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus siswa');
    }
}
async function tambahGuru() {
    const nama = document.getElementById("new-guru-nama").value;
    const email = document.getElementById("new-guru-email").value;
    const password = document.getElementById("new-guru-pass").value;
    
    if (!nama || !password) return alert("Nama dan password harus diisi!");
    
    try {
        const formData = new FormData();
        formData.append('nama', nama);
        formData.append('email', email);
        formData.append('password', password);
        
        const response = await fetch('save_guru.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.users.gurus.push({
                id: result.id,
                nama: nama,
                email: email,
                password: password,
                jadwal: []
            });
            
            document.getElementById("new-guru-nama").value = "";
            document.getElementById("new-guru-email").value = "";
            document.getElementById("new-guru-pass").value = "";
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambah guru');
    }
}

async function hapusGuru(id) {
    if (!confirm("Yakin ingin menghapus guru ini?")) return;
    
    try {
        const formData = new FormData();
        formData.append('table', 'gurus');
        formData.append('id', id);
        
        const response = await fetch('delete_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            data.users.gurus = data.users.gurus.filter(g => g.id !== id);
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus guru');
    }
}

function ambilLokasiGPS() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById("new-kelas-lat").value = position.coords.latitude;
                document.getElementById("new-kelas-lng").value = position.coords.longitude;
                alert("Lokasi berhasil diambil!");
            },
            () => {
                alert("Tidak dapat mengakses lokasi GPS.");
            }
        );
    } else {
        alert("Browser tidak mendukung geolokasi.");
    }
}

async function tambahKelas() {
    const nama = document.getElementById("new-kelas-nama").value;
    const lat = parseFloat(document.getElementById("new-kelas-lat").value);
    const lng = parseFloat(document.getElementById("new-kelas-lng").value);
    
    if (!nama || isNaN(lat) || isNaN(lng)) {
        return alert("Semua field harus diisi dengan benar!");
    }
    
    try {
        const formData = new FormData();
        formData.append('nama', nama);
        formData.append('latitude', lat);
        formData.append('longitude', lng);
        
        const response = await fetch('save_kelas.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.kelas.push({
                id: result.id,
                nama: nama,
                lokasi: {
                    latitude: lat,
                    longitude: lng
                }
            });
            
            document.getElementById("new-kelas-nama").value = "";
            document.getElementById("new-kelas-lat").value = "-7.257472";
            document.getElementById("new-kelas-lng").value = "112.752090";
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambah kelas');
    }
}

async function hapusKelas(id) {
    if (!confirm("Yakin ingin menghapus kelas ini? Semua siswa di kelas ini juga akan terhapus!")) return;
    
    try {
        const formData = new FormData();
        formData.append('table', 'kelas');
        formData.append('id', id);
        
        const response = await fetch('delete_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            data.kelas = data.kelas.filter(k => k.id !== id);
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus kelas');
    }
}

function renderAdminJadwal() {
    const container = document.getElementById("JadwalGuru");
    let html = '<div class="dashboard-section"><h4>🗓️ Jadwal Mengajar Guru</h4>';
    
    data.users.gurus.forEach(guru => {
        html += `<div class="jadwal-guru-container"><h5>${guru.nama}</h5>`;
        if (guru.jadwal && guru.jadwal.length > 0) {
            html += '<ul class="jadwal-list">';
            guru.jadwal.forEach((j, index) => {
                const namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                html += `<li class="jadwal-item"><span>${namaHari[j.hari]} - Jam ${j.jam}:00 - ${j.nama_kelas}</span><button class="small-btn delete" onclick="hapusJadwalGuru(${guru.id}, ${index})">Hapus</button></li>`;
            });
            html += '</ul>';
        } else {
            html += '<p>Belum ada jadwal.</p>';
        }
        html += `<div class="jadwal-form">
            <select id="jadwal-kelas-${guru.id}">${data.kelas.map(k => `<option value="${k.id}">${k.nama}</option>`).join("")}</select>
            <select id="jadwal-hari-${guru.id}">
                <option value="1">Senin</option>
                <option value="2">Selasa</option>
                <option value="3">Rabu</option>
                <option value="4">Kamis</option>
                <option value="5">Jumat</option>
            </select>
            <input type="number" id="jadwal-jam-${guru.id}" placeholder="Jam (0-23)" min="0" max="23">
            <button onclick="tambahJadwalGuru(${guru.id})">Tambah</button>
        </div></div>`;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

async function tambahJadwalGuru(guruId) {
    const id_kelas = parseInt(document.getElementById(`jadwal-kelas-${guruId}`).value);
    const hari = parseInt(document.getElementById(`jadwal-hari-${guruId}`).value);
    const jam = parseInt(document.getElementById(`jadwal-jam-${guruId}`).value);
    
    const guru = data.users.gurus.find(g => g.id === guruId);
    if (!guru) return;

    if (guru.jadwal.some(j => j.id_kelas === id_kelas && j.hari === hari && j.jam === jam)) {
        return alert("Jadwal ini sudah ada.");
    }

    const kelas = data.kelas.find(k => k.id === id_kelas);
    guru.jadwal.push({ id_kelas, hari, jam, nama_kelas: kelas.nama });
    
    try {
        const formData = new FormData();
        formData.append('id_guru', guruId);
        formData.append('jadwal', JSON.stringify(guru.jadwal));
        
        const response = await fetch('update_jadwal_guru.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            renderAdminJadwal();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan jadwal');
    }
}

function hapusJadwalGuru(id_guru, index) {
    const guru = data.users.gurus.find(g => g.id === id_guru);
    guru.jadwal.splice(index, 1);
    alert("Jadwal berhasil dihapus!");
    renderAdminJadwal();
}

function renderAdminManajemenJadwal() {
    const container = document.getElementById("JadwalPelajaran");
    let html = '<div class="dashboard-section"><h4>📚 Jadwal Pelajaran Per Kelas</h4>';
    
    data.kelas.forEach(kelas => {
        const jadwal = data.jadwalPelajaran[kelas.id] || [];
        html += `<div class="form-container"><h5>${kelas.nama}</h5>`;
        
        if (jadwal.length > 0) {
            const namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            html += '<table><tr><th>Hari</th><th>Jam</th><th>Mata Pelajaran</th><th>Aksi</th></tr>';
            jadwal.forEach((j, index) => {
                html += `<tr><td>${namaHari[j.hari]}</td><td>${j.jamMulai} - ${j.jamSelesai}</td><td>${j.mataPelajaran}</td><td><button class="small-btn delete" onclick="hapusJadwalPelajaran(${kelas.id}, ${j.id})">Hapus</button></td></tr>`;
            });
            html += '</table>';
        } else {
            html += '<p>Belum ada jadwal pelajaran.</p>';
        }
        
        html += `<h6>Tambah Jadwal Baru</h6>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
            <select id="jp-hari-${kelas.id}">
                <option value="1">Senin</option>
                <option value="2">Selasa</option>
                <option value="3">Rabu</option>
                <option value="4">Kamis</option>
                <option value="5">Jumat</option>
            </select>
            <input type="text" id="jp-mapel-${kelas.id}" placeholder="Mata Pelajaran">
            <input type="time" id="jp-mulai-${kelas.id}">
            <input type="time" id="jp-selesai-${kelas.id}">
        </div>
        <button onclick="tambahJadwalPelajaran(${kelas.id})" style="margin-top: 10px;">Tambah Jadwal</button>
        </div>`;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

async function tambahJadwalPelajaran(id_kelas) {
    const hari = parseInt(document.getElementById(`jp-hari-${id_kelas}`).value);
    const mataPelajaran = document.getElementById(`jp-mapel-${id_kelas}`).value;
    const jamMulai = document.getElementById(`jp-mulai-${id_kelas}`).value;
    const jamSelesai = document.getElementById(`jp-selesai-${id_kelas}`).value;
    
    if (!mataPelajaran || !jamMulai || !jamSelesai) return alert("Semua field harus diisi!");
    
    try {
        const formData = new FormData();
        formData.append('id_kelas', id_kelas);
        formData.append('hari', hari);
        formData.append('jam_mulai', jamMulai);
        formData.append('jam_selesai', jamSelesai);
        formData.append('mata_pelajaran', mataPelajaran);
        
        const response = await fetch('save_jadwal_pelajaran.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            if (!data.jadwalPelajaran[id_kelas]) {
                data.jadwalPelajaran[id_kelas] = [];
            }
            
            data.jadwalPelajaran[id_kelas].push({
                id: result.id,
                hari: hari,
                jamMulai: jamMulai,
                jamSelesai: jamSelesai,
                mataPelajaran: mataPelajaran
            });
            
            renderAdminManajemenJadwal();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan jadwal pelajaran');
    }
}

async function hapusJadwalPelajaran(id_kelas, id_jadwal) {
    if (!confirm("Yakin ingin menghapus jadwal ini?")) return;
    
    try {
        const formData = new FormData();
        formData.append('table', 'jadwal_pelajaran');
        formData.append('id', id_jadwal);
        
        const response = await fetch('delete_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            if (data.jadwalPelajaran[id_kelas]) {
                data.jadwalPelajaran[id_kelas] = data.jadwalPelajaran[id_kelas].filter(j => j.id !== id_jadwal);
            }
            
            renderAdminManajemenJadwal();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus jadwal');
    }
}

function renderAdminPengumuman() {
    const container = document.getElementById("Pengumuman");
    let html = `<div class="dashboard-section"><h4>📢 Kelola Pengumuman</h4>
    <div class="form-container">
        <h5>Buat Pengumuman Baru</h5>
        <input type="text" id="admin-pengumuman-judul" placeholder="Judul Pengumuman">
        <textarea id="admin-pengumuman-isi" placeholder="Isi pengumuman..."></textarea>
        <button onclick="buatPengumumanAdmin()">Kirim Pengumuman</button>
    </div>
    <div id="admin-pengumuman-list"></div>
    </div>`;
    
    container.innerHTML = html;
    renderAdminPengumumanList();
}

async function buatPengumumanAdmin() {
    const judul = document.getElementById("admin-pengumuman-judul").value;
    const isi = document.getElementById("admin-pengumuman-isi").value;
    
    if (!judul || !isi) return alert("Judul dan isi harus diisi!");
    
    try {
        const formData = new FormData();
        formData.append('judul', judul);
        formData.append('isi', isi);
        formData.append('nama_guru', 'Admin');
        
        const response = await fetch('save_pengumuman.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.pengumuman.push({
                id: result.id,
                judul: judul,
                isi: isi,
                nama_guru: "Admin",
                tanggal: new Date().toISOString().split('T')[0]
            });
            
            document.getElementById("admin-pengumuman-judul").value = "";
            document.getElementById("admin-pengumuman-isi").value = "";
            
            renderAdminPengumumanList();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan pengumuman');
    }
}

function renderAdminPengumumanList() {
    const container = document.getElementById("admin-pengumuman-list");
    if (!container) return;
    
    container.innerHTML = "<h4>Daftar Pengumuman</h4>";
    
    if (data.pengumuman.length > 0) {
        [...data.pengumuman].reverse().forEach((p) => {
            container.innerHTML += `
            <div class="announcement-card">
                <h5>${p.judul}</h5>
                <p>${p.isi}</p>
                <small>oleh ${p.nama_guru} - ${p.tanggal}</small>
                <button class="small-btn delete" onclick="hapusPengumuman(${p.id})">Hapus</button>
            </div>`;
        });
    } else {
        container.innerHTML += '<p>Belum ada pengumuman.</p>';
    }
}

async function hapusPengumuman(id) {
    if (!confirm('Yakin ingin menghapus pengumuman ini?')) return;
    
    try {
        const formData = new FormData();
        formData.append('table', 'pengumuman');
        formData.append('id', id);
        
        const response = await fetch('delete_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.pengumuman = data.pengumuman.filter(p => p.id !== id);
            
            renderAdminPengumumanList();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus');
    }
}

// ========== GURU DASHBOARD ==========

function renderGuruDashboard() {
    return `<div class="dashboard-section" id="guru-absen"><h4>🗓️ Absensi & Jadwal</h4><p id="info-absen-guru">Mengecek jadwal...</p><button id="btn-mulai-ajar" onclick="mulaiAjar()" disabled>Mulai Ajar</button><div id="container-absen-kelas" style="margin-top: 1rem;"></div></div><div class="dashboard-section" id="guru-tugas"><h4>📤 Manajemen Tugas</h4><div class="form-container"><h5>Buat Tugas Baru</h5><select id="tugas-kelas">${data.kelas.map(k => `<option value="${k.id}">${k.nama}</option>`).join("")}</select><input type="text" id="tugas-mapel" placeholder="Mata Pelajaran"><input type="text" id="tugas-judul" placeholder="Judul Tugas"><textarea id="tugas-deskripsi" placeholder="Deskripsi tugas..."></textarea><input type="date" id="tugas-deadline"><label>Upload File (Simulasi):</label><input type="file" id="tugas-file"><button onclick="buatTugas()">Kirim Tugas</button></div><div id="submission-container"></div></div><div class="dashboard-section"><h4>📚 Unggah Materi</h4><select id="materi-kelas">${data.kelas.map(k => `<option value="${k.id}">${k.nama}</option>`).join("")}</select><input type="text" id="materi-judul" placeholder="Judul Materi"><textarea id="materi-deskripsi" placeholder="Deskripsi..."></textarea><label>Upload File (Simulasi):</label><input type="file" id="materi-file"><button onclick="unggahMateri()">Unggah</button></div><div class="dashboard-section"><h4>📢 Buat Pengumuman</h4><input type="text" id="pengumuman-judul" placeholder="Judul"><textarea id="pengumuman-isi" placeholder="Isi..."></textarea><button onclick="buatPengumuman()">Kirim</button></div>`;
}

function cekJadwalMengajar() {
    const infoText = document.getElementById("info-absen-guru");
    const btnMulai = document.getElementById("btn-mulai-ajar");
    
    const now = new Date();
    const hariIni = now.getDay();
    const jamSekarang = now.getHours();
    
    const jadwalHariIni = currentUser.jadwal.filter(j => j.hari === hariIni);
    
    if (jadwalHariIni.length === 0) {
        infoText.textContent = "Tidak ada jadwal mengajar hari ini.";
        return;
    }
    
    const jadwalAktif = jadwalHariIni.find(j => j.jam === jamSekarang);
    
    if (jadwalAktif) {
        infoText.innerHTML = `<strong>Jadwal Aktif:</strong> ${jadwalAktif.nama_kelas} (Jam ${jadwalAktif.jam}:00)`;
        btnMulai.disabled = false;
        btnMulai.setAttribute('data-kelas-id', jadwalAktif.id_kelas);
    } else {
        infoText.innerHTML = `<strong>Jadwal Hari Ini:</strong><br>`;
        jadwalHariIni.forEach(j => {
            infoText.innerHTML += `${j.nama_kelas} - Jam ${j.jam}:00<br>`;
        });
    }
}

function mulaiAjar() {
    const kelasId = parseInt(document.getElementById("btn-mulai-ajar").getAttribute('data-kelas-id'));
    const kelas = data.kelas.find(k => k.id === kelasId);
    
    const container = document.getElementById("container-absen-kelas");
    const siswaDiKelas = data.users.siswas.filter(s => s.id_kelas === kelasId);
    const today = new Date().toISOString().split('T')[0];
    
    let html = `<h5>Absensi ${kelas.nama}</h5><table><tr><th>Nama</th><th>Status</th></tr>`;
    
    siswaDiKelas.forEach(siswa => {
        const absensi = data.absensi.find(a => a.id_siswa == siswa.id && a.tanggal === today);
        const status = absensi ? absensi.status : "Belum absen";
        const statusClass = status === "masuk" ? "style='color: green;'" : status === "Belum absen" ? "style='color: red;'" : "";
        html += `<tr><td>${siswa.nama}</td><td ${statusClass}>${status}</td></tr>`;
    });
    
    html += "</table>";
    container.innerHTML = html;
}
// ========== LANJUTAN FUNGSI buatTugas() ==========

async function buatTugas() {
    const id_kelas = parseInt(document.getElementById("tugas-kelas").value);
    const mapel = document.getElementById("tugas-mapel").value;
    const judul = document.getElementById("tugas-judul").value;
    const deskripsi = document.getElementById("tugas-deskripsi").value;
    const deadline = document.getElementById("tugas-deadline").value;
    
    if (!mapel || !judul || !deskripsi || !deadline) {
        return alert("Semua field harus diisi!");
    }
    
    try {
        const formData = new FormData();
        formData.append('judul', judul);
        formData.append('deskripsi', deskripsi);
        formData.append('mapel', mapel);
        formData.append('id_kelas', id_kelas);
        formData.append('deadline', deadline);
        formData.append('id_guru', currentUser.id);
        
        const response = await fetch('save_tugas.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.tugas.push({
                id: result.id,
                judul: judul,
                deskripsi: deskripsi,
                mapel: mapel,
                id_kelas: id_kelas,
                deadline: deadline,
                id_guru: currentUser.id,
                nama_guru: currentUser.nama,
                submissions: []
            });
            
            document.getElementById("tugas-mapel").value = "";
            document.getElementById("tugas-judul").value = "";
            document.getElementById("tugas-deskripsi").value = "";
            document.getElementById("tugas-deadline").value = "";
            
            renderTugasSubmissions();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan tugas');
    }
}

async function unggahMateri() {
    const id_kelas = parseInt(document.getElementById("materi-kelas").value);
    const judul = document.getElementById("materi-judul").value;
    const deskripsi = document.getElementById("materi-deskripsi").value;
    const file = document.getElementById("materi-file").files[0];
    
    if (!judul || !deskripsi) {
        return alert("Judul dan deskripsi harus diisi!");
    }
    
    try {
        const formData = new FormData();
        formData.append('judul', judul);
        formData.append('deskripsi', deskripsi);
        formData.append('id_kelas', id_kelas);
        formData.append('id_guru', currentUser.id);
        formData.append('file_url', file ? file.name : '');
        
        const response = await fetch('save_materi.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.materi.push({
                id: result.id,
                judul: judul,
                deskripsi: deskripsi,
                id_kelas: id_kelas,
                id_guru: currentUser.id,
                nama_guru: currentUser.nama,
                file: file ? file.name : '',
                tanggal: new Date().toISOString().split('T')[0]
            });
            
            document.getElementById("materi-judul").value = "";
            document.getElementById("materi-deskripsi").value = "";
            document.getElementById("materi-file").value = "";
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat meng-upload materi');
    }
}

async function buatPengumuman() {
    const judul = document.getElementById("pengumuman-judul").value;
    const isi = document.getElementById("pengumuman-isi").value;
    if (!judul || !isi) return alert("Judul dan isi harus diisi!");
    
    try {
        const formData = new FormData();
        formData.append('judul', judul);
        formData.append('isi', isi);
        formData.append('nama_guru', currentUser.nama);
        
        const response = await fetch('save_pengumuman.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.pengumuman.push({
                id: result.id,
                judul,
                isi,
                nama_guru: currentUser.nama,
                tanggal: new Date().toISOString().split('T')[0]
            });
            
            createNotification("semua", "siswa", `Pengumuman baru: ${judul}`);
            document.getElementById("pengumuman-judul").value = "";
            document.getElementById("pengumuman-isi").value = "";
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan pengumuman');
    }
}

function renderTugasSubmissions() {
    const container = document.getElementById("submission-container");
    const tugasGuru = data.tugas.filter(t => t.id_guru === currentUser.id);
    if (tugasGuru.length === 0) { 
        container.innerHTML = "<p>Anda belum mengirim tugas apapun.</p>"; 
        return; 
    }
    let html = "";
    tugasGuru.forEach(t => {
        html += `<div class="task-card"><h5>Tugas: ${t.judul} (Kelas: ${data.kelas.find(k => k.id === t.id_kelas).nama})</h5><p><em>Mata Pelajaran: ${t.mapel || 'Umum'}</em></p>`;
        if (t.submissions && t.submissions.length > 0) {
            html += "<ul class='submission-list'>";
            t.submissions.forEach(sub => {
                const submissionDetailHTML = `<strong>${sub.nama_siswa}</strong> mengumpulkan file: <em>${sub.file}</em><div class="grading-container">${sub.nilai !== null ? `<p class="grade-display"><strong>Nilai: ${sub.nilai}</strong></p><p class="feedback-display"><em>Feedback: ${sub.feedback}</em></p>` : `<input type="number" id="nilai-${t.id}-${sub.id_siswa}" placeholder="Nilai (0-100)"><input type="text" id="feedback-${t.id}-${sub.id_siswa}" placeholder="Umpan Balik"><button class="small-btn" onclick="simpanNilai(${t.id}, ${sub.id_siswa})">Simpan</button>`}</div>`;
                html += `<li>${submissionDetailHTML}</li>`;
            });
            html += "</ul>";
        } else { 
            html += "<p>Belum ada siswa yang mengumpulkan.</p>"; 
        }
        html += renderDiskusi(t.id) + `</div>`;
    });
    container.innerHTML = html;
}

function simpanNilai(id_tugas, id_siswa) {
    const nilai = document.getElementById(`nilai-${id_tugas}-${id_siswa}`).value;
    const feedback = document.getElementById(`feedback-${id_tugas}-${id_siswa}`).value;
    if (nilai === "" || nilai < 0 || nilai > 100) return alert("Nilai harus 0-100.");
    const tugas = data.tugas.find(t => t.id === id_tugas);
    const submission = tugas.submissions.find(s => s.id_siswa === id_siswa);
    submission.nilai = parseInt(nilai);
    submission.feedback = feedback || "Tidak ada feedback.";
    createNotification(id_siswa, "siswa", `Tugas '${tugas.judul}' Anda telah dinilai.`);
    alert("Nilai berhasil disimpan!");
    renderTugasSubmissions();
}

// ========== FUNGSI ADMIN - MANAJEMEN DATA ==========

function renderAdminManajemen() {
    const container = document.getElementById("Manajemen");
    container.innerHTML = `
        <div class="dashboard-section">
            <h4>👥 Manajemen Siswa</h4>
            <div class="form-container">
                <h5>Tambah Siswa Baru</h5>
                <input type="text" id="new-siswa-nama" placeholder="Nama Siswa">
                <input type="text" id="new-siswa-nis" placeholder="NIS">
                <select id="new-siswa-kelas">${data.kelas.map(k => `<option value="${k.id}">${k.nama}</option>`).join("")}</select>
                <input type="password" id="new-siswa-pass" placeholder="Password">
                <button onclick="tambahSiswa()">Tambah Siswa</button>
            </div>
            <table>
                <tr><th>ID</th><th>Nama</th><th>NIS</th><th>Kelas</th><th>Aksi</th></tr>
                ${data.users.siswas.map(s => {
                    const namaKelas = data.kelas.find(k => k.id == s.id_kelas)?.nama || "-";
                    return `<tr><td>${s.id}</td><td>${s.nama}</td><td>${s.nis}</td><td>${namaKelas}</td><td><button class="small-btn delete" onclick="hapusSiswa(${s.id})">Hapus</button></td></tr>`;
                }).join("")}
            </table>
        </div>
        <div class="dashboard-section">
            <h4>👨‍🏫 Manajemen Guru</h4>
            <div class="form-container">
                <h5>Tambah Guru Baru</h5>
                <input type="text" id="new-guru-nama" placeholder="Nama Guru">
                <input type="email" id="new-guru-email" placeholder="Email (opsional)">
                <input type="password" id="new-guru-pass" placeholder="Password">
                <button onclick="tambahGuru()">Tambah Guru</button>
            </div>
            <table>
                <tr><th>ID</th><th>Nama</th><th>Email</th><th>Aksi</th></tr>
                ${data.users.gurus.map(g => `<tr><td>${g.id}</td><td>${g.nama}</td><td>${g.email || '-'}</td><td><button class="small-btn delete" onclick="hapusGuru(${g.id})">Hapus</button></td></tr>`).join("")}
            </table>
        </div>
        <div class="dashboard-section">
            <h4>🏫 Manajemen Kelas</h4>
            <div class="form-container">
                <h5>Tambah Kelas Baru</h5>
                <input type="text" id="new-kelas-nama" placeholder="Nama Kelas (contoh: X-A)">
                <input type="number" id="new-kelas-lat" placeholder="Latitude" value="-7.257472" step="any">
                <input type="number" id="new-kelas-lng" placeholder="Longitude" value="112.752090" step="any">
                <button onclick="ambilLokasiGPS()" style="background: var(--secondary-color);">📍 Ambil Lokasi Saat Ini</button>
                <button onclick="tambahKelas()">Tambah Kelas</button>
            </div>
            <table>
                <tr><th>ID</th><th>Nama</th><th>Lokasi (GPS)</th><th>Aksi</th></tr>
                ${data.kelas.map(k => `<tr><td>${k.id}</td><td>${k.nama}</td><td>${k.lokasi.latitude.toFixed(6)}, ${k.lokasi.longitude.toFixed(6)}</td><td><button class="small-btn delete" onclick="hapusKelas(${k.id})">Hapus</button></td></tr>`).join("")}
            </table>
        </div>
    `;
}

async function tambahGuru() {
    const nama = document.getElementById("new-guru-nama").value;
    const email = document.getElementById("new-guru-email").value;
    const password = document.getElementById("new-guru-pass").value;
    
    if (!nama || !password) return alert("Nama dan password harus diisi!");
    
    try {
        const formData = new FormData();
        formData.append('nama', nama);
        formData.append('email', email);
        formData.append('password', password);
        
        const response = await fetch('save_guru.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.users.gurus.push({
                id: result.id,
                nama: nama,
                email: email,
                password: password,
                jadwal: []
            });
            
            document.getElementById("new-guru-nama").value = "";
            document.getElementById("new-guru-email").value = "";
            document.getElementById("new-guru-pass").value = "";
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambah guru');
    }
}

async function hapusGuru(id) {
    if (!confirm("Yakin ingin menghapus guru ini?")) return;
    
    try {
        const formData = new FormData();
        formData.append('table', 'gurus');
        formData.append('id', id);
        
        const response = await fetch('delete_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            data.users.gurus = data.users.gurus.filter(g => g.id !== id);
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus guru');
    }
}

function ambilLokasiGPS() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById("new-kelas-lat").value = position.coords.latitude;
                document.getElementById("new-kelas-lng").value = position.coords.longitude;
                alert("Lokasi berhasil diambil!");
            },
            () => {
                alert("Tidak dapat mengakses lokasi GPS.");
            }
        );
    } else {
        alert("Browser tidak mendukung geolokasi.");
    }
}

async function tambahKelas() {
    const nama = document.getElementById("new-kelas-nama").value;
    const lat = parseFloat(document.getElementById("new-kelas-lat").value);
    const lng = parseFloat(document.getElementById("new-kelas-lng").value);
    
    if (!nama || isNaN(lat) || isNaN(lng)) {
        return alert("Semua field harus diisi dengan benar!");
    }
    
    try {
        const formData = new FormData();
        formData.append('nama', nama);
        formData.append('latitude', lat);
        formData.append('longitude', lng);
        
        const response = await fetch('save_kelas.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            
            data.kelas.push({
                id: result.id,
                nama: nama,
                lokasi: {
                    latitude: lat,
                    longitude: lng
                }
            });
            
            document.getElementById("new-kelas-nama").value = "";
            document.getElementById("new-kelas-lat").value = "-7.257472";
            document.getElementById("new-kelas-lng").value = "112.752090";
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambah kelas');
    }
}

async function hapusKelas(id) {
    if (!confirm("Yakin ingin menghapus kelas ini? Semua siswa di kelas ini juga akan terhapus!")) return;
    
    try {
        const formData = new FormData();
        formData.append('table', 'kelas');
        formData.append('id', id);
        
        const response = await fetch('delete_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            data.kelas = data.kelas.filter(k => k.id !== id);
            renderAdminManajemen();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus kelas');
    }
}

// ========== FUNGSI ADMIN - ABSENSI LANJUTAN ==========

function renderAdminAbsensi() {
    const container = document.getElementById("Absensi");
    
    let html = `
        <div class="dashboard-section">
            <h4>📊 Filter Absensi</h4>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem;">
                <select id="filter-kelas">
                    <option value="">Semua Kelas</option>
                    ${data.kelas.map(k => `<option value="${k.id}">${k.nama}</option>`).join("")}
                </select>
                <input type="date" id="filter-tanggal" value="${new Date().toISOString().split('T')[0]}">
                <button onclick="renderAdminAbsensi()">🔍 Filter</button>
            </div>
        </div>
        <div class="dashboard-section">
            <h4>📋 Rekap Absensi Siswa</h4>
    `;
    
    const filterKelas = document.getElementById("filter-kelas")?.value || "";
    const filterTanggal = document.getElementById("filter-tanggal")?.value || new Date().toISOString().split('T')[0];
    
    let absensiFiltered = data.absensi.filter(a => a.tanggal === filterTanggal);
    
    if (filterKelas) {
        absensiFiltered = absensiFiltered.filter(a => a.id_kelas == filterKelas);
    }
    
    if (absensiFiltered.length === 0) {
        html += "<p>Tidak ada data absensi untuk filter ini.</p>";
    } else {
        html += `<table>
            <tr><th>Tanggal</th><th>Nama</th><th>Kelas</th><th>Status</th><th>Waktu</th><th>Aksi</th></tr>`;
        
        absensiFiltered.forEach(a => {
            const siswa = data.users.siswas.find(s => s.id == a.id_siswa);
            const namaKelas = siswa ? data.kelas.find(k => k.id === siswa.id_kelas)?.nama || "-" : "-";
            const namaSiswa = siswa ? siswa.nama : "Unknown";
            const statusColor = a.status === 'masuk' ? 'green' : a.status === 'izin' ? 'orange' : 'red';
            
            html += `<tr>
                <td>${a.tanggal}</td>
                <td>${namaSiswa}</td>
                <td>${namaKelas}</td>
                <td style="color: ${statusColor}; font-weight: 600;">${a.status}</td>
                <td>${a.waktu || '-'}</td>
                <td><button class="small-btn delete" onclick="hapusAbsensi(${a.id})">Hapus</button></td>
            </tr>`;
        });
        html += "</table>";
    }
    
    html += "</div>";
    container.innerHTML = html;
}

async function hapusAbsensi(id) {
    if (!confirm('Yakin ingin menghapus data absensi ini?')) return;
    
    try {
        const formData = new FormData();
        formData.append('table', 'absensi');
        formData.append('id', id);
        
        const response = await fetch('delete_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            data.absensi = data.absensi.filter(a => a.id !== id);
            renderAdminAbsensi();
        } else {
            alert('Error: ' + result.message);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus');
    }
}

// ========== INISIALISASI APLIKASI ==========
document.addEventListener('DOMContentLoaded', loadDataAndInit);
