<?php
// File: role_pj/dashboard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user adalah Penanggung Jawab
if ($_SESSION['role'] !== 'Penanggung Jawab') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Dashboard Penanggung Jawab';
include '../includes/header.php';

// Ambil data dari database
include '../includes/koneksi.php';
$nik = $_SESSION['nik'];

// Hitung cuti menunggu persetujuan di divisi yang sama
$sql_karyawan = "SELECT divisi FROM karyawan WHERE nik = ?";
$stmt = $koneksi->prepare($sql_karyawan);
$stmt->bind_param("s", $nik);
$stmt->execute();
$divisi_pj = $stmt->get_result()->fetch_assoc()['divisi'];

// Cuti pending di divisi
$sql_cuti_pending = "SELECT COUNT(*) as total FROM pengajuan_cuti pc 
                     JOIN karyawan k ON pc.karyawan_nik = k.nik 
                     WHERE k.divisi = ? AND pc.status = 'Pending'";
$stmt_cuti = $koneksi->prepare($sql_cuti_pending);
$stmt_cuti->bind_param("s", $divisi_pj);
$stmt_cuti->execute();
$cuti_pending = $stmt_cuti->get_result()->fetch_assoc();

// KHL pending di divisi
$sql_khl_pending = "SELECT COUNT(*) as total FROM pengajuan_khl pk 
                    JOIN karyawan k ON pk.karyawan_nik = k.nik 
                    WHERE k.divisi = ? AND pk.status = 'Pending'";
$stmt_khl = $koneksi->prepare($sql_khl_pending);
$stmt_khl->bind_param("s", $divisi_pj);
$stmt_khl->execute();
$khl_pending = $stmt_khl->get_result()->fetch_assoc();
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Dashboard Penanggung Jawab</h3>
                
                <h4 class="text-dark mb-4">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></h4>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Status Pengajuan Cuti Pribadi</h6>
                                <p class="card-text fs-4 fw-bold">1 <span class="fs-6 fw-normal">Cuti Tahunan</span></p>
                                <a href="../role_karyawan/riwayat_cuti.php" class="btn btn-custom-green btn-sm">Menunggu Persetujuan...</a>
                            </div>
                        </div>
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">KHL Menunggu Persetujuan</h6>
                                <p class="card-text fs-4 fw-bold">3 <span class="fs-6 fw-normal">Menunggu Persetujuan...</span></p>
                                <a href="persetujuan_khl.php" class="btn btn-custom-green btn-sm">Lihat Rincian</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Cuti Menunggu Persetujuan</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $cuti_pending['total']; ?> <span class="fs-6 fw-normal">Karyawan</span></p>
                                <a href="persetujuan_cuti.php" class="btn btn-custom-green btn-sm">Lihat Rincian</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Cuti Bulan Ini -->
                <div class="content-container mt-4">
                    <h5 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Cuti Pegawai Bulan Ini (<?php echo date('F Y'); ?>)</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>NIK</th><th>Nama</th><th>Divisi</th><th>Tanggal</th><th>Jenis Cuti</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_cuti_bulan_ini = "SELECT k.nik, k.nama_lengkap, k.divisi, pc.tanggal_mulai, pc.tanggal_selesai, jc.nama_jenis 
                                                      FROM pengajuan_cuti pc 
                                                      JOIN karyawan k ON pc.karyawan_nik = k.nik 
                                                      JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
                                                      WHERE k.divisi = ? AND pc.status = 'Disetujui' 
                                                      AND MONTH(pc.tanggal_mulai) = MONTH(CURRENT_DATE()) 
                                                      ORDER BY pc.tanggal_mulai";
                                $stmt_cuti = $koneksi->prepare($sql_cuti_bulan_ini);
                                $stmt_cuti->bind_param("s", $divisi_pj);
                                $stmt_cuti->execute();
                                $cuti_bulan_ini = $stmt_cuti->get_result();
                                
                                if ($cuti_bulan_ini->num_rows > 0) {
                                    while ($row = $cuti_bulan_ini->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$row['nik']}</td>
                                                <td>{$row['nama_lengkap']}</td>
                                                <td>{$row['divisi']}</td>
                                                <td>{$row['tanggal_mulai']} - {$row['tanggal_selesai']}</td>
                                                <td>{$row['nama_jenis']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>Tidak ada cuti disetujui bulan ini</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="../kalender_cuti.php" class="btn btn-custom-green mt-2">
                        <i class="fas fa-calendar-plus me-2"></i>Lihat Kalender Cuti
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>