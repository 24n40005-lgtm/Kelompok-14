<?php
// File: proses_pengajuan_khl.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/koneksi.php';

// Cek apakah user berhak mengajukan KHL
$allowed_roles = ['Karyawan', 'Penanggung Jawab', 'Administrator', 'Direksi'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk mengajukan KHL!";
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Akses tidak valid!";
    header('Location: role_karyawan/form_khl.php');
    exit();
}

$nik = $_SESSION['nik'];
$proyek = $_POST['proyek'] ?? '';
$tanggal_kerja = $_POST['tanggal_kerja'] ?? '';
$jam_mulai = $_POST['jam_mulai'] ?? '';
$jam_selesai = $_POST['jam_selesai'] ?? '';
$tanggal_libur_pengganti = $_POST['tanggal_libur_pengganti'] ?? '';

// Validasi input
if (empty($proyek) || empty($tanggal_kerja) || empty($jam_mulai) || empty($jam_selesai) || empty($tanggal_libur_pengganti)) {
    $_SESSION['error'] = "Semua field harus diisi!";
    header('Location: role_karyawan/form_khl.php');
    exit();
}

// Validasi tanggal
if ($tanggal_libur_pengganti <= $tanggal_kerja) {
    $_SESSION['error'] = "Tanggal libur pengganti harus setelah tanggal kerja!";
    header('Location: role_karyawan/form_khl.php');
    exit();
}

// Validasi jam
if ($jam_selesai <= $jam_mulai) {
    $_SESSION['error'] = "Jam selesai harus setelah jam mulai!";
    header('Location: role_karyawan/form_khl.php');
    exit();
}

try {
    // Insert pengajuan KHL
    $sql_insert = "INSERT INTO pengajuan_khl (
        karyawan_nik, proyek, tanggal_kerja, jam_mulai, jam_selesai, 
        tanggal_libur_pengganti, status, tanggal_pengajuan
    ) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())";

    $stmt_insert = $koneksi->prepare($sql_insert);
    $stmt_insert->bind_param(
        "ssssss", 
        $nik, $proyek, $tanggal_kerja, $jam_mulai, $jam_selesai, $tanggal_libur_pengganti
    );

    if ($stmt_insert->execute()) {
        $_SESSION['success'] = "Pengajuan KHL berhasil dikirim! Menunggu persetujuan.";
        header('Location: role_karyawan/riwayat_khl.php');
        exit();
    } else {
        throw new Exception("Gagal menyimpan pengajuan KHL: " . $stmt_insert->error);
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: role_karyawan/form_khl.php');
    exit();
}
?>