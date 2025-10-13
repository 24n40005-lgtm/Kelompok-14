<?php
// File: role_karyawan/dashboard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user adalah karyawan
if ($_SESSION['role'] !== 'Karyawan') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Dashboard Karyawan';
include '../includes/header.php';

// Ambil data karyawan dari database
include '../includes/koneksi.php';
$nik = $_SESSION['nik'];

$sql_karyawan = "SELECT * FROM karyawan WHERE nik = ?";
$stmt = $koneksi->prepare($sql_karyawan);
$stmt->bind_param("s", $nik);
$stmt->execute();
$karyawan = $stmt->get_result()->fetch_assoc();

// Hitung pengajuan cuti pending - PERBAIKAN: status = 'Menunggu'
$sql_cuti_pending = "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE karyawan_nik = ? AND status = 'Menunggu'";
$stmt_cuti = $koneksi->prepare($sql_cuti_pending);
$stmt_cuti->bind_param("s", $nik);
$stmt_cuti->execute();
$cuti_pending = $stmt_cuti->get_result()->fetch_assoc();

// Hitung pengajuan KHL pending - PERBAIKAN: status = 'Menunggu'
$sql_khl_pending = "SELECT COUNT(*) as total FROM pengajuan_khl WHERE karyawan_nik = ? AND status = 'Menunggu'";
$stmt_khl = $koneksi->prepare($sql_khl_pending);
$stmt_khl->bind_param("s", $nik);
$stmt_khl->execute();
$khl_pending = $stmt_khl->get_result()->fetch_assoc();
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Dashboard Karyawan</h3>
                
                <h4 class="text-dark mb-4">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></h4>

                <!-- Sisa Cuti -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card text-center bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Sisa Cuti Tahunan</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $karyawan['sisa_cuti_tahunan']; ?> Hari</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-center bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Sisa Cuti Lustrum</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $karyawan['sisa_cuti_lustrum']; ?> Hari</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Pengajuan -->
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Status Pengajuan Cuti</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $cuti_pending['total']; ?> <span class="fs-6 fw-normal">Menunggu Persetujuan</span></p>
                                <a href="riwayat_cuti.php" class="btn btn-custom-green btn-sm">Lihat Riwayat</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Status Pengajuan KHL</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $khl_pending['total']; ?> <span class="fs-6 fw-normal">Menunggu Persetujuan</span></p>
                                <a href="riwayat_khl.php" class="btn btn-custom-green btn-sm">Lihat Riwayat</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="form_cuti.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-calendar-plus me-2"></i>Ajukan Cuti
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid gap-2">
                            <a href="form_khl.php" class="btn btn-success btn-lg">
                                <i class="fas fa-briefcase me-2"></i>Ajukan KHL
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>