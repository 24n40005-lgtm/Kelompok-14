<?php
// File: role_administrator/proses_update_lamaran.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/koneksi.php';

// Hanya Administrator yang bisa akses
if ($_SESSION['role'] !== 'Administrator') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses ditolak!']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses tidak valid!']);
    exit();
}

$pelamar_id = $_POST['pelamar_id'] ?? '';
$action = $_POST['action'] ?? '';
$alasan = $_POST['alasan'] ?? '';

if (empty($pelamar_id) || empty($action)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap!']);
    exit();
}

try {
    // Ambil data pelamar
    $sql_pelamar = "SELECT * FROM pelamar WHERE id = ?";
    $stmt_pelamar = $koneksi->prepare($sql_pelamar);
    $stmt_pelamar->bind_param("i", $pelamar_id);
    $stmt_pelamar->execute();
    $pelamar = $stmt_pelamar->get_result()->fetch_assoc();

    if (!$pelamar) {
        throw new Exception("Data pelamar tidak ditemukan!");
    }

    $next_status = '';
    $catatan = '';

    if ($action === 'next') {
        // Proses ke tahap berikutnya
        $status_sequence = [
            'Administrasi' => 'Wawancara',
            'Wawancara' => 'Psikotes', 
            'Psikotes' => 'Kesehatan',
            'Kesehatan' => 'Diterima'
        ];

        $current_status = $pelamar['status_lamaran'];
        
        if (!isset($status_sequence[$current_status])) {
            throw new Exception("Tidak dapat melanjutkan dari status ini!");
        }

        $next_status = $status_sequence[$current_status];
        $catatan = "Lolos tahap " . $current_status . ", lanjut ke " . $next_status;

        // Jika sampai ke tahap Diterima, buat data karyawan
        if ($next_status === 'Diterima') {
            createKaryawanFromPelamar($pelamar, $koneksi);
            $catatan = "Selamat! Anda diterima sebagai karyawan.";
        }

    } elseif ($action === 'reject') {
        // Tolak pelamar
        if (empty($alasan)) {
            throw new Exception("Alasan penolakan harus diisi!");
        }

        $next_status = 'Ditolak';
        $catatan = "Ditolak: " . $alasan;
    } else {
        throw new Exception("Aksi tidak valid!");
    }

    // Update status pelamar
    $sql_update = "UPDATE pelamar SET status_lamaran = ?, catatan_admin = ? WHERE id = ?";
    $stmt_update = $koneksi->prepare($sql_update);
    $stmt_update->bind_param("ssi", $next_status, $catatan, $pelamar_id);

    if (!$stmt_update->execute()) {
        throw new Exception("Gagal update status pelamar: " . $stmt_update->error);
    }

    // Insert ke proses seleksi
    $sql_seleksi = "INSERT INTO proses_seleksi (pelamar_id, tahap, hasil, catatan, created_at) 
                   VALUES (?, ?, ?, ?, NOW())";
    $stmt_seleksi = $koneksi->prepare($sql_seleksi);
    
    $tahap = ($action === 'reject') ? $pelamar['status_lamaran'] : $next_status;
    $hasil = ($action === 'reject') ? 'Tidak Lolos' : 'Lolos';
    
    $stmt_seleksi->bind_param("isss", $pelamar_id, $tahap, $hasil, $catatan);
    $stmt_seleksi->execute();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Status berhasil diupdate!']);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}

// Fungsi untuk membuat data karyawan dari pelamar yang diterima
function createKaryawanFromPelamar($pelamar, $koneksi) {
    // Generate NIK karyawan (format: tahun + bulan + 4 digit random)
    $nik = date('ym') . sprintf('%04d', rand(1000, 9999));
    
    // Cek apakah NIK sudah ada
    $sql_check_nik = "SELECT id FROM karyawan WHERE nik = ?";
    $stmt_check = $koneksi->prepare($sql_check_nik);
    $stmt_check->bind_param("s", $nik);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows > 0) {
        // Jika NIK sudah ada, generate yang baru
        return createKaryawanFromPelamar($pelamar, $koneksi);
    }

    // Insert data karyawan
    $sql_karyawan = "INSERT INTO karyawan (
        user_id, nik, nama_lengkap, divisi, jabatan, jenis_kelamin, alamat_ktp, 
        alamat_domisili, no_telepon, email, agama, kontak_darurat_nama, 
        kontak_darurat_telepon, pendidikan_terakhir, ipk, path_pas_foto, 
        path_ktp, path_ijazah, tanggal_masuk, status_karyawan, sisa_cuti_tahunan, sisa_cuti_lustrum
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'Aktif', 12, 0)";

    $stmt_karyawan = $koneksi->prepare($sql_karyawan);
    
    // Tentukan jabatan berdasarkan posisi
    $jabatan = $pelamar['posisi_dilamar'];
    
    $stmt_karyawan->bind_param(
        "issssssssssssdssss", 
        $pelamar['user_id'], $nik, $pelamar['nama_lengkap'], $pelamar['divisi_dilamar'],
        $jabatan, $pelamar['jenis_kelamin'], $pelamar['alamat_ktp'], $pelamar['alamat_domisili'],
        $pelamar['no_telepon'], $pelamar['email'], $pelamar['agama'], $pelamar['kontak_darurat_nama'],
        $pelamar['kontak_darurat_telepon'], $pelamar['pendidikan_terakhir'], $pelamar['ipk'],
        $pelamar['path_pas_foto'], $pelamar['path_ktp'], $pelamar['path_ijazah']
    );

    if (!$stmt_karyawan->execute()) {
        throw new Exception("Gagal membuat data karyawan: " . $stmt_karyawan->error);
    }

    $karyawan_id = $koneksi->insert_id;

    // Assign role Karyawan ke user
    $sql_role = "INSERT INTO user_roles (user_id, role_id) VALUES (?, 2)"; // 2 = Karyawan
    $stmt_role = $koneksi->prepare($sql_role);
    $stmt_role->bind_param("i", $pelamar['user_id']);
    $stmt_role->execute();

    return $karyawan_id;
}
?>