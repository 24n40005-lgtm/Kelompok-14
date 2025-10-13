<?php
session_start();
include '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: persetujuan_cuti.php');
    exit();
}

$id_pengajuan = $_POST['id_pengajuan'] ?? '';
$aksi = $_POST['aksi'] ?? '';
$alasan_penolakan = $_POST['alasan_penolakan'] ?? '';

// Ambil data PJ
$user_id_pj = $_SESSION['user_id'];
$query_pj = $koneksi->prepare("SELECT nama_lengkap FROM karyawan WHERE user_id = ?");
$query_pj->bind_param("i", $user_id_pj);
$query_pj->execute();
$pj_data = $query_pj->get_result()->fetch_assoc();
$nama_pj = $pj_data['nama_lengkap'];

if ($aksi === 'setujui') {
    // Update status menjadi Disetujui
    $sql = "UPDATE pengajuan_cuti SET status = 'Disetujui', disetujui_oleh = ?, tanggal_persetujuan = NOW() WHERE id = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("si", $nama_pj, $id_pengajuan);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Pengajuan cuti berhasil disetujui!";
    } else {
        $_SESSION['error_message'] = "Gagal menyetujui pengajuan cuti.";
    }
} 
elseif ($aksi === 'tolak') {
    // Update status menjadi Ditolak
    $sql = "UPDATE pengajuan_cuti SET status = 'Ditolak', alasan_penolakan = ?, disetujui_oleh = ?, tanggal_persetujuan = NOW() WHERE id = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ssi", $alasan_penolakan, $nama_pj, $id_pengajuan);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Pengajuan cuti berhasil ditolak!";
    } else {
        $_SESSION['error_message'] = "Gagal menolak pengajuan cuti.";
    }
}

header('Location: persetujuan_cuti.php');
exit();
?>