<?php
// File: proses_persetujuan_khl.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/koneksi.php';

// Hanya PJ dan Admin yang bisa approve/reject KHL
$allowed_roles = ['Penanggung Jawab', 'Administrator'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses ditolak!']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses tidak valid!']);
    exit();
}

$khl_id = $_POST['khl_id'] ?? '';
$action = $_POST['action'] ?? '';
$alasan = $_POST['alasan'] ?? '';

if (empty($khl_id) || empty($action)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap!']);
    exit();
}

try {
    // Ambil data KHL
    $sql_khl = "SELECT pk.*, k.nama_lengkap 
                 FROM pengajuan_khl pk 
                 JOIN karyawan k ON pk.karyawan_nik = k.nik 
                 WHERE pk.id = ?";
    $stmt_khl = $koneksi->prepare($sql_khl);
    $stmt_khl->bind_param("i", $khl_id);
    $stmt_khl->execute();
    $khl = $stmt_khl->get_result()->fetch_assoc();

    if (!$khl) {
        throw new Exception("Data KHL tidak ditemukan!");
    }

    // Untuk PJ, hanya bisa approve KHL di divisinya
    if ($_SESSION['role'] === 'Penanggung Jawab') {
        $sql_divisi = "SELECT divisi FROM karyawan WHERE nik = ?";
        $stmt_divisi = $koneksi->prepare($sql_divisi);
        $stmt_divisi->bind_param("s", $_SESSION['nik']);
        $stmt_divisi->execute();
        $divisi_pj = $stmt_divisi->get_result()->fetch_assoc()['divisi'];

        $sql_karyawan_divisi = "SELECT divisi FROM karyawan WHERE nik = ?";
        $stmt_karyawan = $koneksi->prepare($sql_karyawan_divisi);
        $stmt_karyawan->bind_param("s", $khl['karyawan_nik']);
        $stmt_karyawan->execute();
        $divisi_karyawan = $stmt_karyawan->get_result()->fetch_assoc()['divisi'];

        if ($divisi_pj !== $divisi_karyawan) {
            throw new Exception("Anda hanya bisa menyetujui KHL karyawan di divisi Anda!");
        }
    }

    if ($action === 'approve') {
        // Approve KHL
        $sql_update = "UPDATE pengajuan_khl SET status = 'Disetujui', 
                       disetujui_oleh = ?, tanggal_persetujuan = NOW() 
                       WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("si", $_SESSION['nama_lengkap'], $khl_id);

    } elseif ($action === 'reject') {
        // Reject KHL
        if (empty($alasan)) {
            throw new Exception("Alasan penolakan harus diisi!");
        }

        $sql_update = "UPDATE pengajuan_khl SET status = 'Ditolak', 
                       alasan_penolakan = ?, disetujui_oleh = ?, tanggal_persetujuan = NOW() 
                       WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("ssi", $alasan, $_SESSION['nama_lengkap'], $khl_id);
    } else {
        throw new Exception("Aksi tidak valid!");
    }

    if ($stmt_update->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Berhasil memproses KHL!']);
    } else {
        throw new Exception("Gagal update data KHL: " . $stmt_update->error);
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
?>