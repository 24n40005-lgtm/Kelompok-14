<?php
// File: includes/header.php (UPGRADED VERSION)

define('BASE_URL', 'http://localhost/purba-danarta/');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = isset($judul_halaman) ? $judul_halaman : 'Sistem Karyawan';
$current_role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Purba Danarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>dashboard.php">
            <img src="<?php echo BASE_URL; ?>assets/img/yys.png" alt="Logo Perusahaan">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php 
                        if ($current_role === 'Pelamar') echo 'role_pelamar/dashboard.php';
                        else echo 'dashboard.php';
                    ?>">Beranda</a>
                </li>
                
                <?php if ($current_role !== 'Pelamar'): ?>
                <!-- Menu Cuti (untuk semua role kecuali Pelamar) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Cuti</a>
                    <ul class="dropdown-menu">
                        <?php if (in_array($current_role, ['Karyawan', 'Penanggung Jawab', 'Administrator', 'Direksi'])): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_karyawan/form_cuti.php">Pengajuan Cuti</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_karyawan/riwayat_cuti.php">Riwayat Cuti Pribadi</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <?php if (in_array($current_role, ['Penanggung Jawab', 'Administrator', 'Direksi'])): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_pj/persetujuan_cuti.php">Administrasi Cuti</a></li>
                        <?php endif; ?>
                        
                        <?php if (in_array($current_role, ['Penanggung Jawab', 'Administrator', 'Direksi'])): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_karyawan/riwayat_cuti.php?all=1">Riwayat Semua Cuti</a></li>
                        <?php endif; ?>
                        
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>kalender_cuti.php">Kalender Cuti</a></li>
                    </ul>
                </li>
                
                <!-- Menu KHL (untuk semua role kecuali Pelamar) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">KHL</a>
                    <ul class="dropdown-menu">
                        <?php if (in_array($current_role, ['Karyawan', 'Penanggung Jawab', 'Administrator', 'Direksi'])): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_karyawan/form_khl.php">Pengajuan KHL</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_karyawan/riwayat_khl.php">Riwayat KHL Pribadi</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <?php if (in_array($current_role, ['Penanggung Jawab', 'Administrator', 'Direksi'])): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_pj/persetujuan_khl.php">Administrasi KHL</a></li>
                        <?php endif; ?>
                        
                        <?php if (in_array($current_role, ['Penanggung Jawab', 'Administrator', 'Direksi'])): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_karyawan/riwayat_khl.php?all=1">Riwayat Semua KHL</a></li>
                        <?php endif; ?>
                        
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>kalender_khl.php">Kalender KHL</a></li>
                    </ul>
                </li>
                
                <!-- Menu Karyawan (untuk PJ, Admin, Direksi) -->
                <?php if (in_array($current_role, ['Penanggung Jawab', 'Administrator', 'Direksi'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Karyawan</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_pj/data_karyawan.php">Data Karyawan</a></li>
                        <?php if ($current_role === 'Administrator'): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_administrator/daftar_karyawan.php">Kelola Karyawan</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Menu Lamaran Kerja (khusus Administrator) -->
                <?php if ($current_role === 'Administrator'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Lamaran Kerja</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_administrator/administrasi_lamaran.php">Administrasi Lamaran</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>role_administrator/riwayat_pelamar.php">Riwayat Pelamar</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Menu Laporan (khusus Direksi) -->
                <?php if ($current_role === 'Direksi'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>role_direksi/laporan.php">Laporan</a>
                </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="navbar-text me-3">
                        <i class="fas fa-user me-1"></i>
                        <?php echo $_SESSION['nama_lengkap'] ?? 'Guest'; ?> 
                        (<?php echo $current_role; ?>)
                    </span>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-outline-light">Log Out</a>
                </li>
            </ul>
        </div>
    </div>
</nav>