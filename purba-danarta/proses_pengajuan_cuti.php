<?php
// File: proses_pengajuan_cuti.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/koneksi.php';

// Cek apakah user berhak mengajukan cuti
$allowed_roles = ['Karyawan', 'Penanggung Jawab', 'Administrator', 'Direksi'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk mengajukan cuti!";
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Akses tidak valid!";
    header('Location: role_karyawan/form_cuti.php');
    exit();
}

$nik = $_SESSION['nik'];
$jenis_cuti_id = $_POST['jenis_cuti_id'] ?? '';
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';

// Validasi input
if (empty($jenis_cuti_id) || empty($tanggal_mulai) || empty($tanggal_selesai) || empty($keterangan)) {
    $_SESSION['error'] = "Semua field harus diisi!";
    header('Location: role_karyawan/form_cuti.php');
    exit();
}

// Validasi tanggal
if ($tanggal_mulai > $tanggal_selesai) {
    $_SESSION['error'] = "Tanggal selesai harus setelah tanggal mulai!";
    header('Location: role_karyawan/form_cuti.php');
    exit();
}

try {
    // Hitung lama hari cuti
    $start = new DateTime($tanggal_mulai);
    $end = new DateTime($tanggal_selesai);
    $lama_hari = $end->diff($start)->days + 1; // +1 untuk include start date

    // Cek maksimal hari untuk jenis cuti tertentu
    $sql_jenis = "SELECT maks_hari FROM jenis_cuti WHERE id = ?";
    $stmt_jenis = $koneksi->prepare($sql_jenis);
    $stmt_jenis->bind_param("i", $jenis_cuti_id);
    $stmt_jenis->execute();
    $jenis_cuti = $stmt_jenis->get_result()->fetch_assoc();

    if ($jenis_cuti['maks_hari'] && $lama_hari > $jenis_cuti['maks_hari']) {
        $_SESSION['error'] = "Jenis cuti ini maksimal {$jenis_cuti['maks_hari']} hari!";
        header('Location: role_karyawan/form_cuti.php');
        exit();
    }

    // Untuk cuti tahunan & lustrum, cek sisa cuti
    $sql_sisa = "SELECT sisa_cuti_tahunan, sisa_cuti_lustrum FROM karyawan WHERE nik = ?";
    $stmt_sisa = $koneksi->prepare($sql_sisa);
    $stmt_sisa->bind_param("s", $nik);
    $stmt_sisa->execute();
    $sisa_cuti = $stmt_sisa->get_result()->fetch_assoc();

    $sql_jenis_nama = "SELECT nama_jenis FROM jenis_cuti WHERE id = ?";
    $stmt_nama = $koneksi->prepare($sql_jenis_nama);
    $stmt_nama->bind_param("i", $jenis_cuti_id);
    $stmt_nama->execute();
    $nama_jenis = $stmt_nama->get_result()->fetch_assoc()['nama_jenis'];

    if ($nama_jenis === 'Tahunan' && $lama_hari > $sisa_cuti['sisa_cuti_tahunan']) {
        $_SESSION['error'] = "Sisa cuti tahunan tidak mencukupi! Sisa: {$sisa_cuti['sisa_cuti_tahunan']} hari";
        header('Location: role_karyawan/form_cuti.php');
        exit();
    }

    if ($nama_jenis === 'Lustrum' && $lama_hari > $sisa_cuti['sisa_cuti_lustrum']) {
        $_SESSION['error'] = "Sisa cuti lustrum tidak mencukupi! Sisa: {$sisa_cuti['sisa_cuti_lustrum']} hari";
        header('Location: role_karyawan/form_cuti.php');
        exit();
    }

    // Insert pengajuan cuti
    $sql_insert = "INSERT INTO pengajuan_cuti (
        karyawan_nik, jenis_cuti_id, tanggal_mulai, tanggal_selesai, lama_hari, 
        keterangan, status, tanggal_pengajuan
    ) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())";

    $stmt_insert = $koneksi->prepare($sql_insert);
    $stmt_insert->bind_param(
        "isssis", 
        $nik, $jenis_cuti_id, $tanggal_mulai, $tanggal_selesai, $lama_hari, $keterangan
    );

    if ($stmt_insert->execute()) {
        $_SESSION['success'] = "Pengajuan cuti berhasil dikirim! Menunggu persetujuan.";
        header('Location: role_karyawan/riwayat_cuti.php');
        exit();
    } else {
        throw new Exception("Gagal menyimpan pengajuan cuti: " . $stmt_insert->error);
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: role_karyawan/form_cuti.php');
    exit();
}
?>