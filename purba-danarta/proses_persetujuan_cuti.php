<?php
// File: proses_persetujuan_cuti.php (UPGRADED VERSION)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/koneksi.php';

// Hanya PJ dan Admin yang bisa approve/reject cuti
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

$cuti_id = $_POST['cuti_id'] ?? '';
$action = $_POST['action'] ?? '';
$alasan = $_POST['alasan'] ?? '';

if (empty($cuti_id) || empty($action)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap!']);
    exit();
}

try {
    // Ambil data cuti
    $sql_cuti = "SELECT pc.*, k.nama_lengkap, k.divisi, jc.nama_jenis, u.id as user_id
                 FROM pengajuan_cuti pc 
                 JOIN karyawan k ON pc.karyawan_nik = k.nik 
                 JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
                 JOIN users u ON k.user_id = u.id
                 WHERE pc.id = ?";
    $stmt_cuti = $koneksi->prepare($sql_cuti);
    $stmt_cuti->bind_param("i", $cuti_id);
    $stmt_cuti->execute();
    $cuti = $stmt_cuti->get_result()->fetch_assoc();

    if (!$cuti) {
        throw new Exception("Data cuti tidak ditemukan!");
    }

    // Untuk PJ, hanya bisa approve cuti di divisinya
    if ($_SESSION['role'] === 'Penanggung Jawab') {
        $sql_divisi = "SELECT divisi FROM karyawan WHERE nik = ?";
        $stmt_divisi = $koneksi->prepare($sql_divisi);
        $stmt_divisi->bind_param("s", $_SESSION['nik']);
        $stmt_divisi->execute();
        $divisi_pj = $stmt_divisi->get_result()->fetch_assoc()['divisi'];

        if ($divisi_pj !== $cuti['divisi']) {
            throw new Exception("Anda hanya bisa menyetujui cuti karyawan di divisi Anda!");
        }
    }

    if ($action === 'approve') {
        // Approve cuti
        $sql_update = "UPDATE pengajuan_cuti SET status = 'Disetujui', 
                       disetujui_oleh = ?, tanggal_persetujuan = NOW() 
                       WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("si", $_SESSION['nama_lengkap'], $cuti_id);

        // Untuk cuti tahunan & lustrum, kurangi sisa cuti
        if ($cuti['nama_jenis'] === 'Tahunan') {
            $sql_kurang_cuti = "UPDATE karyawan SET sisa_cuti_tahunan = sisa_cuti_tahunan - ? WHERE nik = ?";
            $stmt_kurang = $koneksi->prepare($sql_kurang_cuti);
            $stmt_kurang->bind_param("is", $cuti['lama_hari'], $cuti['karyawan_nik']);
            $stmt_kurang->execute();
        } elseif ($cuti['nama_jenis'] === 'Lustrum') {
            $sql_kurang_cuti = "UPDATE karyawan SET sisa_cuti_lustrum = sisa_cuti_lustrum - ? WHERE nik = ?";
            $stmt_kurang = $koneksi->prepare($sql_kurang_cuti);
            $stmt_kurang->bind_param("is", $cuti['lama_hari'], $cuti['karyawan_nik']);
            $stmt_kurang->execute();
        }

        // Simpan notifikasi (jika ada tabel notifikasi)
        $pesan_notifikasi = "Pengajuan cuti Anda telah DISETUJUI oleh " . $_SESSION['nama_lengkap'];

    } elseif ($action === 'reject') {
        // Reject cuti
        if (empty($alasan)) {
            throw new Exception("Alasan penolakan harus diisi!");
        }

        $sql_update = "UPDATE pengajuan_cuti SET status = 'Ditolak', 
                       alasan_penolakan = ?, disetujui_oleh = ?, tanggal_persetujuan = NOW() 
                       WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("ssi", $alasan, $_SESSION['nama_lengkap'], $cuti_id);

        // Simpan notifikasi
        $pesan_notifikasi = "Pengajuan cuti Anda DITOLAK oleh " . $_SESSION['nama_lengkap'] . ". Alasan: " . $alasan;
    } else {
        throw new Exception("Aksi tidak valid!");
    }

    if ($stmt_update->execute()) {
        
        // **INTEGRASI DENGAN ADMIN & DIREKSI**
        // Data cuti yang sudah diproses otomatis terlihat di:
        // 1. Admin: bisa lihat semua cuti di riwayat_cuti.php?all=1
        // 2. Direksi: bisa lihat semua cuti di riwayat_cuti.php
        // 3. Karyawan: bisa lihat status di riwayat_cuti.php
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Berhasil memproses cuti!',
            'data' => [
                'id' => $cuti_id,
                'status' => $action === 'approve' ? 'Disetujui' : 'Ditolak',
                'disetujui_oleh' => $_SESSION['nama_lengkap'],
                'tanggal_persetujuan' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception("Gagal update data cuti: " . $stmt_update->error);
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
?>