<?php
// File: proses_registrasi_pelamar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['regist_error'] = "Akses tidak valid!";
    header('Location: registrasi_pelamar.php');
    exit();
}

// Validasi required fields
$required_fields = [
    'nama_lengkap', 'nik', 'posisi_dilamar', 'divisi_dilamar', 'jenis_kelamin', 'agama',
    'alamat_ktp', 'alamat_domisili', 'no_telepon', 'email', 'kontak_darurat_nama',
    'kontak_darurat_telepon', 'pendidikan_terakhir', 'ipk', 'gaji_diharapkan',
    'username', 'password'
];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['regist_error'] = "Field $field harus diisi!";
        header('Location: registrasi_pelamar.php');
        exit();
    }
}

// Validasi file upload
$required_files = ['surat_lamaran', 'cv', 'pas_foto', 'ijazah'];
foreach ($required_files as $file_field) {
    if (!isset($_FILES[$file_field]) || $_FILES[$file_field]['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['regist_error'] = "File $file_field harus diupload!";
        header('Location: registrasi_pelamar.php');
        exit();
    }
}

try {
    $koneksi->begin_transaction();

    // 1. Cek apakah username sudah ada
    $sql_check_username = "SELECT id FROM users WHERE username = ?";
    $stmt_check = $koneksi->prepare($sql_check_username);
    $stmt_check->bind_param("s", $_POST['username']);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows > 0) {
        throw new Exception("Username sudah digunakan!");
    }

    // 2. Cek apakah NIK sudah ada di pelamar atau karyawan
    $sql_check_nik = "SELECT id FROM pelamar WHERE nik = ? UNION SELECT id FROM karyawan WHERE nik = ?";
    $stmt_nik = $koneksi->prepare($sql_check_nik);
    $stmt_nik->bind_param("ss", $_POST['nik'], $_POST['nik']);
    $stmt_nik->execute();
    
    if ($stmt_nik->get_result()->num_rows > 0) {
        throw new Exception("NIK sudah terdaftar!");
    }

    // 3. Upload files
    $upload_dir = "assets/uploads/pelamar/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_paths = [];
    $file_fields = [
        'surat_lamaran' => 'surat_lamaran',
        'cv' => 'cv',
        'pas_foto' => 'pas_foto',
        'ktp' => 'ktp',
        'ijazah' => 'ijazah',
        'dokumen_lain' => 'dokumen_lain'
    ];

    foreach ($file_fields as $field => $prefix) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $file_ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $file_name = $prefix . '_' . time() . '_' . uniqid() . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $file_path)) {
                $file_paths[$field] = $file_path;
            } else {
                throw new Exception("Gagal upload file $field");
            }
        }
    }

    // 4. Create user account
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $sql_user = "INSERT INTO users (username, password, email, created_at) VALUES (?, ?, ?, NOW())";
    $stmt_user = $koneksi->prepare($sql_user);
    $stmt_user->bind_param("sss", $_POST['username'], $hashed_password, $_POST['email']);
    
    if (!$stmt_user->execute()) {
        throw new Exception("Gagal membuat akun user: " . $stmt_user->error);
    }
    
    $user_id = $koneksi->insert_id;

    // 5. Assign role Pelamar
    $sql_role = "INSERT INTO user_roles (user_id, role_id) VALUES (?, 1)"; // 1 = Pelamar
    $stmt_role = $koneksi->prepare($sql_role);
    $stmt_role->bind_param("i", $user_id);
    
    if (!$stmt_role->execute()) {
        throw new Exception("Gagal assign role: " . $stmt_role->error);
    }

    // 6. Insert data pelamar
    $sql_pelamar = "INSERT INTO pelamar (
        user_id, nama_lengkap, nik, posisi_dilamar, divisi_dilamar, jenis_kelamin, 
        alamat_ktp, alamat_domisili, no_telepon, email, agama, kontak_darurat_nama, 
        kontak_darurat_telepon, pendidikan_terakhir, ipk, gaji_diharapkan,
        path_surat_lamaran, path_cv, path_pas_foto, path_ktp, path_ijazah, path_dokumen_lain,
        status_lamaran, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Administrasi', NOW())";

    $stmt_pelamar = $koneksi->prepare($sql_pelamar);
    $stmt_pelamar->bind_param(
        "issssssssssssdssssssss", 
        $user_id, $_POST['nama_lengkap'], $_POST['nik'], $_POST['posisi_dilamar'], 
        $_POST['divisi_dilamar'], $_POST['jenis_kelamin'], $_POST['alamat_ktp'], 
        $_POST['alamat_domisili'], $_POST['no_telepon'], $_POST['email'], $_POST['agama'],
        $_POST['kontak_darurat_nama'], $_POST['kontak_darurat_telepon'], 
        $_POST['pendidikan_terakhir'], $_POST['ipk'], $_POST['gaji_diharapkan'],
        $file_paths['surat_lamaran'], $file_paths['cv'], $file_paths['pas_foto'],
        $file_paths['ktp'] ?? null, $file_paths['ijazah'], $file_paths['dokumen_lain'] ?? null
    );

    if (!$stmt_pelamar->execute()) {
        throw new Exception("Gagal menyimpan data pelamar: " . $stmt_pelamar->error);
    }

    // 7. Insert proses seleksi awal
    $pelamar_id = $koneksi->insert_id;
    $sql_seleksi = "INSERT INTO proses_seleksi (pelamar_id, tahap, hasil, catatan, created_at) 
                   VALUES (?, 'Administrasi', 'Menunggu', 'Pendaftaran berhasil', NOW())";
    $stmt_seleksi = $koneksi->prepare($sql_seleksi);
    $stmt_seleksi->bind_param("i", $pelamar_id);
    $stmt_seleksi->execute();

    $koneksi->commit();

    $_SESSION['regist_success'] = "Pendaftaran berhasil! Silakan login untuk melihat status lamaran.";
    header('Location: login.php');
    exit();

} catch (Exception $e) {
    $koneksi->rollback();
    
    // Hapus file yang sudah terupload jika ada error
    if (isset($file_paths)) {
        foreach ($file_paths as $file_path) {
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    
    $_SESSION['regist_error'] = $e->getMessage();
    header('Location: registrasi_pelamar.php');
    exit();
}
?>