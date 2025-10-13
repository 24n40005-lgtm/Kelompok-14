<?php
// File: role_direksi/dashboard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user adalah Direksi
if ($_SESSION['role'] !== 'Direksi') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Dashboard Direksi';
include '../includes/header.php';

// Ambil data statistik dari database
include '../includes/koneksi.php';

// Total karyawan
$sql_total_karyawan = "SELECT COUNT(*) as total FROM karyawan WHERE status_karyawan = 'Aktif'";
$total_karyawan = $koneksi->query($sql_total_karyawan)->fetch_assoc();

// Total pelamar aktif
$sql_pelamar_aktif = "SELECT COUNT(*) as total FROM pelamar WHERE status_lamaran IN ('Administrasi', 'Wawancara', 'Psikotes', 'Kesehatan')";
$pelamar_aktif = $koneksi->query($sql_pelamar_aktif)->fetch_assoc();

// Cuti bulan ini
$sql_cuti_bulan_ini = "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status = 'Disetujui' AND MONTH(tanggal_mulai) = MONTH(CURRENT_DATE())";
$cuti_bulan_ini = $koneksi->query($sql_cuti_bulan_ini)->fetch_assoc();
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Dashboard Direksi</h3>
                
                <h4 class="text-dark mb-4">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></h4>

                <!-- Statistik Overview -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Total Karyawan</h6>
                                <p class="card-text fs-2 fw-bold text-primary"><?php echo $total_karyawan['total']; ?></p>
                                <small class="text-muted">Karyawan Aktif</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Pelamar Aktif</h6>
                                <p class="card-text fs-2 fw-bold text-success"><?php echo $pelamar_aktif['total']; ?></p>
                                <small class="text-muted">Dalam Proses Seleksi</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Cuti Bulan Ini</h6>
                                <p class="card-text fs-2 fw-bold text-warning"><?php echo $cuti_bulan_ini['total']; ?></p>
                                <small class="text-muted">Cuti Disetujui</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Access -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Data Karyawan</h6>
                                <p class="card-text">Akses lengkap data seluruh karyawan</p>
                                <a href="data_karyawan.php" class="btn btn-custom-green btn-sm">Lihat Data Karyawan</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Laporan</h6>
                                <p class="card-text">Laporan kepegawaian dan kinerja</p>
                                <a href="laporan.php" class="btn btn-custom-green btn-sm">Lihat Laporan</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cuti Pegawai Bulan Ini (Simplified) -->
                <div class="content-container mt-4">
                    <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Cuti Pegawai Bulan Ini (<?php echo date('F Y'); ?>)</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama</th><th>Divisi</th><th>Periode</th><th>Jenis Cuti</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_cuti = "SELECT k.nama_lengkap, k.divisi, pc.tanggal_mulai, pc.tanggal_selesai, jc.nama_jenis 
                                           FROM pengajuan_cuti pc 
                                           JOIN karyawan k ON pc.karyawan_nik = k.nik 
                                           JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
                                           WHERE pc.status = 'Disetujui' 
                                           AND MONTH(pc.tanggal_mulai) = MONTH(CURRENT_DATE()) 
                                           ORDER BY pc.tanggal_mulai 
                                           LIMIT 5";
                                $cuti_data = $koneksi->query($sql_cuti);
                                
                                if ($cuti_data->num_rows > 0) {
                                    while ($row = $cuti_data->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$row['nama_lengkap']}</td>
                                                <td>{$row['divisi']}</td>
                                                <td>{$row['tanggal_mulai']} - {$row['tanggal_selesai']}</td>
                                                <td>{$row['nama_jenis']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>Tidak ada cuti disetujui bulan ini</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="riwayat_cuti.php" class="btn btn-custom-green mt-2">
                        <i class="fas fa-list me-2"></i>Lihat Semua Riwayat Cuti
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>