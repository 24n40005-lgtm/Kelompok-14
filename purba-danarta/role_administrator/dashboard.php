<?php
// File: role_administrator/dashboard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user adalah Administrator
if ($_SESSION['role'] !== 'Administrator') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Dashboard Administrator';
include '../includes/header.php';

// Ambil data statistik dari database
include '../includes/koneksi.php';

// Statistik lamaran kerja
$sql_lamaran = "SELECT status_lamaran, COUNT(*) as total FROM pelamar GROUP BY status_lamaran";
$result_lamaran = $koneksi->query($sql_lamaran);
$stat_lamaran = [
    'Administrasi' => 0,
    'Wawancara' => 0,
    'Psikotes' => 0,
    'Kesehatan' => 0,
    'Diterima' => 0,
    'Ditolak' => 0
];
while ($row = $result_lamaran->fetch_assoc()) {
    $stat_lamaran[$row['status_lamaran']] = $row['total'];
}
$total_pelamar_aktif = $stat_lamaran['Administrasi'] + $stat_lamaran['Wawancara'] + $stat_lamaran['Psikotes'] + $stat_lamaran['Kesehatan'];

// Statistik cuti menunggu
$sql_cuti_pending = "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE status = 'Pending'";
$cuti_pending = $koneksi->query($sql_cuti_pending)->fetch_assoc();

// Statistik KHL menunggu
$sql_khl_pending = "SELECT COUNT(*) as total FROM pengajuan_khl WHERE status = 'Pending'";
$khl_pending = $koneksi->query($sql_khl_pending)->fetch_assoc();

// Cuti pribadi pending
$sql_cuti_pribadi = "SELECT COUNT(*) as total FROM pengajuan_cuti WHERE karyawan_nik = ? AND status = 'Pending'";
$stmt_cuti = $koneksi->prepare($sql_cuti_pribadi);
$stmt_cuti->bind_param("s", $_SESSION['nik']);
$stmt_cuti->execute();
$cuti_pribadi_pending = $stmt_cuti->get_result()->fetch_assoc();
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Dashboard Administrator</h3>
                
                <h4 class="text-dark mb-4">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></h4>

                <!-- Statistik Utama -->
                <div class="row g-4 mb-4">
                    <!-- Lamaran Kerja -->
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Lamaran Kerja</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $total_pelamar_aktif; ?> <span class="fs-6 fw-normal">Pelamar Aktif</span></p>
                                <div class="small">
                                    <div>Administrasi: <?php echo $stat_lamaran['Administrasi']; ?> orang</div>
                                    <div>Wawancara: <?php echo $stat_lamaran['Wawancara']; ?> orang</div>
                                    <div>Psikotes: <?php echo $stat_lamaran['Psikotes']; ?> orang</div>
                                    <div>Kesehatan: <?php echo $stat_lamaran['Kesehatan']; ?> orang</div>
                                </div>
                                <a href="administrasi_lamaran.php" class="btn btn-custom-green btn-sm mt-2">Lihat Rincian</a>
                            </div>
                        </div>
                    </div>

                    <!-- Cuti Menunggu -->
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Cuti Menunggu Persetujuan</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $cuti_pending['total']; ?> <span class="fs-6 fw-normal">Pengajuan Cuti</span></p>
                                <a href="../role_pj/persetujuan_cuti.php" class="btn btn-custom-green btn-sm">Lihat Rincian</a>
                            </div>
                        </div>
                    </div>

                    <!-- KHL Menunggu -->
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">KHL Menunggu Persetujuan</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $khl_pending['total']; ?> <span class="fs-6 fw-normal">Pengajuan KHL</span></p>
                                <a href="../role_pj/persetujuan_khl.php" class="btn btn-custom-green btn-sm">Lihat Rincian</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Pribadi & Quick Actions -->
                <div class="row g-4">
                    <!-- Status Pribadi -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Status Pengajuan Cuti Pribadi</h6>
                                <?php if ($cuti_pribadi_pending['total'] > 0): ?>
                                    <p class="card-text fs-4 fw-bold"><?php echo $cuti_pribadi_pending['total']; ?> <span class="fs-6 fw-normal">Menunggu Persetujuan</span></p>
                                    <a href="../role_karyawan/riwayat_cuti.php" class="btn btn-custom-green btn-sm">Lihat Detail</a>
                                <?php else: ?>
                                    <p class="card-text">Tidak ada pengajuan cuti pending</p>
                                    <a href="../role_karyawan/form_cuti.php" class="btn btn-outline-primary btn-sm">Ajukan Cuti</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Quick Actions</h6>
                                <div class="d-grid gap-2">
                                    <a href="daftar_karyawan.php" class="btn btn-outline-primary">
                                        <i class="fas fa-users me-2"></i>Kelola Karyawan
                                    </a>
                                    <a href="administrasi_lamaran.php" class="btn btn-outline-success">
                                        <i class="fas fa-clipboard-list me-2"></i>Kelola Lamaran
                                    </a>
                                    <a href="master_data.php" class="btn btn-outline-info">
                                        <i class="fas fa-cog me-2"></i>Master Data
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cuti Pegawai Bulan Ini -->
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
                                                      WHERE pc.status = 'Disetujui' 
                                                      AND MONTH(pc.tanggal_mulai) = MONTH(CURRENT_DATE()) 
                                                      ORDER BY pc.tanggal_mulai 
                                                      LIMIT 10";
                                $cuti_bulan_ini = $koneksi->query($sql_cuti_bulan_ini);
                                
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