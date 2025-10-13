<?php
// File: role_direksi/laporan.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya Direksi yang bisa akses
if ($_SESSION['role'] !== 'Direksi') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Laporan';
include '../includes/header.php';
include '../includes/koneksi.php';

// Ambil data untuk laporan
$bulan = $_GET['bulan'] ?? date('Y-m');
$tahun = $_GET['tahun'] ?? date('Y');

// Statistik karyawan
$sql_karyawan = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN jenis_kelamin = 'Laki-laki' THEN 1 END) as pria,
    COUNT(CASE WHEN jenis_kelamin = 'Perempuan' THEN 1 END) as wanita,
    COUNT(CASE WHEN divisi = 'SDM' THEN 1 END) as sdm,
    COUNT(CASE WHEN divisi = 'IT' THEN 1 END) as it,
    COUNT(CASE WHEN divisi = 'Keuangan' THEN 1 END) as keuangan,
    COUNT(CASE WHEN divisi = 'Marketing' THEN 1 END) as marketing
    FROM karyawan WHERE status_karyawan = 'Aktif'";
$stat_karyawan = $koneksi->query($sql_karyawan)->fetch_assoc();

// Statistik cuti bulan ini
$sql_cuti_bulan = "SELECT 
    COUNT(*) as total_cuti,
    COUNT(CASE WHEN status = 'Disetujui' THEN 1 END) as disetujui,
    COUNT(CASE WHEN status = 'Ditolak' THEN 1 END) as ditolak,
    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending
    FROM pengajuan_cuti 
    WHERE MONTH(tanggal_pengajuan) = MONTH(CURRENT_DATE()) 
    AND YEAR(tanggal_pengajuan) = YEAR(CURRENT_DATE())";
$stat_cuti = $koneksi->query($sql_cuti_bulan)->fetch_assoc();

// Statistik KHL bulan ini
$sql_khl_bulan = "SELECT 
    COUNT(*) as total_khl,
    COUNT(CASE WHEN status = 'Disetujui' THEN 1 END) as disetujui,
    COUNT(CASE WHEN status = 'Ditolak' THEN 1 END) as ditolak,
    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending
    FROM pengajuan_khl 
    WHERE MONTH(tanggal_pengajuan) = MONTH(CURRENT_DATE()) 
    AND YEAR(tanggal_pengajuan) = YEAR(CURRENT_DATE())";
$stat_khl = $koneksi->query($sql_khl_bulan)->fetch_assoc();

// Top 5 divisi dengan cuti terbanyak
$sql_top_cuti = "SELECT k.divisi, COUNT(pc.id) as total_cuti
                 FROM pengajuan_cuti pc
                 JOIN karyawan k ON pc.karyawan_nik = k.nik
                 WHERE pc.status = 'Disetujui'
                 AND MONTH(pc.tanggal_pengajuan) = MONTH(CURRENT_DATE())
                 GROUP BY k.divisi
                 ORDER BY total_cuti DESC
                 LIMIT 5";
$top_cuti = $koneksi->query($sql_top_cuti);
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Laporan Kepegawaian</h3>

                <!-- Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="bulanFilter" class="form-label">Filter Bulan:</label>
                        <input type="month" class="form-control" id="bulanFilter" value="<?php echo $bulan; ?>" onchange="filterLaporan(this.value)">
                    </div>
                    <div class="col-md-6">
                        <label for="tahunFilter" class="form-label">Filter Tahun:</label>
                        <select class="form-select" id="tahunFilter" onchange="filterLaporanByTahun(this.value)">
                            <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <!-- Statistik Utama -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">Total Karyawan</h6>
                                <p class="card-text fs-3 fw-bold"><?php echo $stat_karyawan['total']; ?></p>
                                <small>Pria: <?php echo $stat_karyawan['pria']; ?> | Wanita: <?php echo $stat_karyawan['wanita']; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">Cuti Bulan Ini</h6>
                                <p class="card-text fs-3 fw-bold"><?php echo $stat_cuti['total_cuti']; ?></p>
                                <small>Disetujui: <?php echo $stat_cuti['disetujui']; ?> | Pending: <?php echo $stat_cuti['pending']; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-warning text-dark">
                            <div class="card-body">
                                <h6 class="card-title">KHL Bulan Ini</h6>
                                <p class="card-text fs-3 fw-bold"><?php echo $stat_khl['total_khl']; ?></p>
                                <small>Disetujui: <?php echo $stat_khl['disetujui']; ?> | Pending: <?php echo $stat_khl['pending']; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">Divisi Terbanyak</h6>
                                <p class="card-text fs-3 fw-bold"><?php echo $stat_karyawan['sdm'] + $stat_karyawan['it'] + $stat_karyawan['keuangan'] + $stat_karyawan['marketing']; ?></p>
                                <small>SDM: <?php echo $stat_karyawan['sdm']; ?> | IT: <?php echo $stat_karyawan['it']; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Laporan -->
                <div class="row">
                    <!-- Distribusi Karyawan per Divisi -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Distribusi Karyawan per Divisi</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="divisiChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 Divisi dengan Cuti Terbanyak -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Top 5 Divisi dengan Cuti Terbanyak</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Divisi</th>
                                                <th>Total Cuti</th>
                                                <th>Persentase</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($top_cuti->num_rows > 0): ?>
                                                <?php while ($row = $top_cuti->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                                                    <td><?php echo $row['total_cuti']; ?></td>
                                                    <td>
                                                        <?php 
                                                        $percentage = $stat_cuti['total_cuti'] > 0 ? 
                                                            round(($row['total_cuti'] / $stat_cuti['total_cuti']) * 100, 1) : 0;
                                                        echo $percentage . '%';
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">Tidak ada data</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Laporan -->
                <div class="text-center mt-4">
                    <button class="btn btn-success me-2" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-2"></i>Export ke PDF
                    </button>
                    <button class="btn btn-primary" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Export ke Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function filterLaporan(bulan) {
    window.location.href = 'laporan.php?bulan=' + bulan;
}

function filterLaporanByTahun(tahun) {
    window.location.href = 'laporan.php?tahun=' + tahun;
}

function exportToPDF() {
    alert('Fitur export PDF akan diimplementasikan sesuai kebutuhan.');
}

function exportToExcel() {
    alert('Fitur export Excel akan diimplementasikan sesuai kebutuhan.');
}

// Chart.js untuk distribusi divisi
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('divisiChart').getContext('2d');
    const divisiChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['SDM', 'IT', 'Keuangan', 'Marketing', 'Operasional', 'Training'],
            datasets: [{
                data: [
                    <?php echo $stat_karyawan['sdm']; ?>,
                    <?php echo $stat_karyawan['it']; ?>,
                    <?php echo $stat_karyawan['keuangan']; ?>,
                    <?php echo $stat_karyawan['marketing']; ?>,
                    <?php echo $stat_karyawan['total'] - ($stat_karyawan['sdm'] + $stat_karyawan['it'] + $stat_karyawan['keuangan'] + $stat_karyawan['marketing']); ?>,
                    0 // Training, asumsikan 0 untuk contoh
                ],
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>