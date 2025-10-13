<?php
// File: includes/init_database.php

function setupDatabase($koneksi) {
    
    // 1. Tabel users
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$koneksi->query($sql_users)) {
        echo "Error creating users table: " . $koneksi->error;
    }
    
    // 2. Tabel roles
    $sql_roles = "CREATE TABLE IF NOT EXISTS roles (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nama_role VARCHAR(50) NOT NULL UNIQUE,
        deskripsi TEXT
    )";
    $koneksi->query($sql_roles);
    
    // 3. Insert default roles
    $default_roles = [
        [1, 'Pelamar', 'User yang mendaftar sebagai calon karyawan'],
        [2, 'Karyawan', 'Karyawan biasa'],
        [3, 'Penanggung Jawab', 'Supervisor/Manager divisi'],
        [4, 'Administrator', 'HRD/Admin sistem'],
        [5, 'Direksi', 'Direktur/Manajemen atas']
    ];
    
    foreach ($default_roles as $role) {
        $sql = "INSERT IGNORE INTO roles (id, nama_role, deskripsi) VALUES (?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("iss", $role[0], $role[1], $role[2]);
        $stmt->execute();
    }
    
    // 4. Tabel user_roles
    $sql_user_roles = "CREATE TABLE IF NOT EXISTS user_roles (
        user_id INT,
        role_id INT,
        PRIMARY KEY (user_id, role_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
    )";
    $koneksi->query($sql_user_roles);
    
    // 5. Tabel pelamar
    $sql_pelamar = "CREATE TABLE IF NOT EXISTS pelamar (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        nama_lengkap VARCHAR(100) NOT NULL,
        nik VARCHAR(20),
        posisi_dilamar VARCHAR(100),
        divisi_dilamar VARCHAR(100),
        jenis_kelamin ENUM('Laki-laki', 'Perempuan'),
        alamat_ktp TEXT,
        alamat_domisili TEXT,
        no_telepon VARCHAR(20),
        email VARCHAR(100),
        agama ENUM('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'),
        kontak_darurat_nama VARCHAR(100),
        kontak_darurat_telepon VARCHAR(20),
        pendidikan_terakhir VARCHAR(100),
        ipk DECIMAL(3,2),
        gaji_diharapkan DECIMAL(12,2),
        path_surat_lamaran VARCHAR(255),
        path_cv VARCHAR(255),
        path_pas_foto VARCHAR(255),
        path_ktp VARCHAR(255),
        path_ijazah VARCHAR(255),
        path_dokumen_lain VARCHAR(255),
        status_lamaran ENUM('Administrasi', 'Wawancara', 'Psikotes', 'Kesehatan', 'Diterima', 'Ditolak') DEFAULT 'Administrasi',
        catatan_admin TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $koneksi->query($sql_pelamar);
    
    // 6. Tabel karyawan
    $sql_karyawan = "CREATE TABLE IF NOT EXISTS karyawan (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        nik VARCHAR(20) UNIQUE NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        divisi VARCHAR(100),
        jabatan VARCHAR(100),
        jenis_kelamin ENUM('Laki-laki', 'Perempuan'),
        alamat_ktp TEXT,
        alamat_domisili TEXT,
        no_telepon VARCHAR(20),
        email VARCHAR(100),
        agama ENUM('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'),
        kontak_darurat_nama VARCHAR(100),
        kontak_darurat_telepon VARCHAR(20),
        pendidikan_terakhir VARCHAR(100),
        ipk DECIMAL(3,2),
        path_pas_foto VARCHAR(255),
        path_ktp VARCHAR(255),
        path_ijazah VARCHAR(255),
        path_dokumen_lain VARCHAR(255),
        tanggal_masuk DATE,
        status_karyawan ENUM('Aktif', 'Non-Aktif') DEFAULT 'Aktif',
        sisa_cuti_tahunan INT DEFAULT 12,
        sisa_cuti_lustrum INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $koneksi->query($sql_karyawan);
    
    // 7. Tabel jenis_cuti
    $sql_jenis_cuti = "CREATE TABLE IF NOT EXISTS jenis_cuti (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nama_jenis VARCHAR(100) NOT NULL,
        maks_hari INT,
        deskripsi TEXT
    )";
    $koneksi->query($sql_jenis_cuti);
    
    // Insert default jenis cuti
    $jenis_cuti = [
        [1, 'Tahunan', 12, 'Cuti tahunan'],
        [2, 'Lustrum', NULL, 'Cuti lustrum berdasarkan masa kerja'],
        [3, 'Cuti Sakit', NULL, 'Cuti karena sakit'],
        [4, 'Cuti Ibadah', NULL, 'Cuti untuk keperluan ibadah'],
        [5, 'Cuti di Luar Tanggungan', NULL, 'Cuti tanpa dibayar'],
        [6, 'Istri Melahirkan/Keguguran', 2, 'Maksimal 2 hari'],
        [7, 'Anak Menikah/Baptis/Khitan', 2, 'Maksimal 2 hari'],
        [8, 'Suami/Istri, Anak/Menantu, Orangtua/Mertua Meninggal', 2, 'Maksimal 2 hari'],
        [9, 'Menikah', 3, 'Maksimal 3 hari'],
        [10, 'Cek Kesehatan/Pindah Rumah', 1, 'Maksimal 1 hari'],
        [11, 'Anggota Keluarga Dalam 1 Rumah Meninggal', 1, 'Maksimal 1 hari']
    ];
    
    foreach ($jenis_cuti as $cuti) {
        $sql = "INSERT IGNORE INTO jenis_cuti (id, nama_jenis, maks_hari, deskripsi) VALUES (?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("isis", $cuti[0], $cuti[1], $cuti[2], $cuti[3]);
        $stmt->execute();
    }
    
    // 8. Tabel pengajuan_cuti
    $sql_pengajuan_cuti = "CREATE TABLE IF NOT EXISTS pengajuan_cuti (
        id INT PRIMARY KEY AUTO_INCREMENT,
        karyawan_nik VARCHAR(20),
        jenis_cuti_id INT,
        tanggal_mulai DATE NOT NULL,
        tanggal_selesai DATE NOT NULL,
        lama_hari INT,
        keterangan TEXT,
        status ENUM('Pending', 'Disetujui', 'Ditolak') DEFAULT 'Pending',
        alasan_penolakan TEXT,
        disetujui_oleh VARCHAR(100),
        tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        tanggal_persetujuan TIMESTAMP NULL,
        FOREIGN KEY (karyawan_nik) REFERENCES karyawan(nik) ON DELETE CASCADE,
        FOREIGN KEY (jenis_cuti_id) REFERENCES jenis_cuti(id)
    )";
    $koneksi->query($sql_pengajuan_cuti);
    
    // 9. Tabel pengajuan_khl
    $sql_pengajuan_khl = "CREATE TABLE IF NOT EXISTS pengajuan_khl (
        id INT PRIMARY KEY AUTO_INCREMENT,
        karyawan_nik VARCHAR(20),
        proyek TEXT NOT NULL,
        tanggal_kerja DATE NOT NULL,
        jam_mulai TIME NOT NULL,
        jam_selesai TIME NOT NULL,
        tanggal_libur_pengganti DATE NOT NULL,
        status ENUM('Pending', 'Disetujui', 'Ditolak') DEFAULT 'Pending',
        alasan_penolakan TEXT,
        disetujui_oleh VARCHAR(100),
        tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        tanggal_persetujuan TIMESTAMP NULL,
        FOREIGN KEY (karyawan_nik) REFERENCES karyawan(nik) ON DELETE CASCADE
    )";
    $koneksi->query($sql_pengajuan_khl);
    
    // 10. Tabel proses_seleksi
    $sql_proses_seleksi = "CREATE TABLE IF NOT EXISTS proses_seleksi (
        id INT PRIMARY KEY AUTO_INCREMENT,
        pelamar_id INT,
        tahap ENUM('Administrasi', 'Wawancara', 'Psikotes', 'Kesehatan', 'Final'),
        tanggal_tahap DATE,
        hasil ENUM('Lolos', 'Tidak Lolos', 'Menunggu'),
        catatan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pelamar_id) REFERENCES pelamar(id) ON DELETE CASCADE
    )";
    $koneksi->query($sql_proses_seleksi);
    
    return true;
}

// Jalankan setup
setupDatabase($koneksi);
?>