<?php
// File: role_direksi/riwayat_cuti.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya Direksi yang bisa akses
if ($_SESSION['role'] !== 'Direksi') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Riwayat Cuti';
include '../includes/header.php';
include '../includes/koneksi.php';

// Filter parameters
$bulan = $_GET['bulan'] ?? date('Y-m');
$divisi = $_GET['divisi'] ?? '';
$status = $_GET['status'] ?? '';

// Build query dengan filter
$sql_where = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($bulan)) {
    $sql_where .= " AND DATE_FORMAT(pc.tanggal_pengajuan, '%Y-%m') = ?";
    $params[] = $bulan;
    $types .= "s";
}

if (!empty($divisi)) {
    $sql_where .= " AND k.divisi = ?";
    $params[] = $divisi;
    $types .= "s";
}

if (!empty($status)) {
    $sql_where .= " AND pc.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql = "SELECT pc.*, k.nama_lengkap, k.divisi, k.jabatan, jc.nama_jenis,
               pc.tanggal_persetujuan, pc.disetujui_oleh
        FROM pengajuan_cuti pc 
        JOIN karyawan k ON pc.karyawan_nik = k.nik 
        JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
        $sql_where 
        ORDER BY pc.tanggal_pengajuan DESC";

$stmt = $koneksi->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$cuti_data = $stmt->get_result();

// Ambil list divisi untuk filter
$sql_divisi = "SELECT DISTINCT divisi FROM karyawan WHERE status_karyawan = 'Aktif' ORDER BY divisi";
$divisi_list = $koneksi->query($sql_divisi);

// Statistik
$sql_stats = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN pc.status = 'Disetujui' THEN 1 END) as disetujui,
    COUNT(CASE WHEN pc.status = 'Ditolak' THEN 1 END) as ditolak,
    COUNT(CASE WHEN pc.status = 'Pending' THEN 1 END) as pending
    FROM pengajuan_cuti pc 
    JOIN karyawan k ON pc.karyawan_nik = k.nik 
    WHERE DATE_FORMAT(pc.tanggal_pengajuan, '%Y-%m') = ?";
$stmt_stats = $koneksi->prepare($sql_stats);
$stmt_stats->bind_param("s", $bulan);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Riwayat Cuti Karyawan</h3>

                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center bg-light">
                            <div class="card-body py-3">
                                <h6 class="card-title text-muted">Total Pengajuan</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $stats['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body py-3">
                                <h6 class="card-title">Disetujui</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $stats['disetujui']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-danger text-white">
                            <div class="card-body py-3">
                                <h6 class="card-title">Ditolak</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $stats['ditolak']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-warning text-dark">
                            <div class="card-body py-3">
                                <h6 class="card-title">Menunggu</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $stats['pending']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <input type="month" class="form-control" value="<?php echo $bulan; ?>" onchange="filterByBulan(this.value)">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Divisi</label>
                        <select class="form-select" onchange="filterByDivisi(this.value)">
                            <option value="">Semua Divisi</option>
                            <?php while ($row = $divisi_list->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['divisi']); ?>" <?php echo $divisi === $row['divisi'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['divisi']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" onchange="filterByStatus(this.value)">
                            <option value="">Semua Status</option>
                            <option value="Disetujui" <?php echo $status === 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="Ditolak" <?php echo $status === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                            <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button class="btn btn-custom-green" onclick="resetFilter()">
                                <i class="fas fa-refresh me-2"></i>Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabel Riwayat -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nama Karyawan</th>
                                <th>Divisi</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Periode Cuti</th>
                                <th>Jenis Cuti</th>
                                <th>Lama</th>
                                <th>Status</th>
                                <th>Disetujui Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($cuti_data->num_rows > 0): ?>
                                <?php while ($row = $cuti_data->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">CT-<?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['jabatan']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                    <td>
                                        <?php echo date('d-m-Y', strtotime($row['tanggal_mulai'])); ?><br>
                                        <small>s/d</small><br>
                                        <?php echo date('d-m-Y', strtotime($row['tanggal_selesai'])); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['nama_jenis']); ?></td>
                                    <td class="text-center"><?php echo $row['lama_hari']; ?> hari</td>
                                    <td class="text-center">
                                        <?php
                                        $status_class = [
                                            'Disetujui' => 'bg-success',
                                            'Ditolak' => 'bg-danger', 
                                            'Pending' => 'bg-warning text-dark'
                                        ];
                                        $class = $status_class[$row['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $class; ?>"><?php echo $row['status']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['disetujui_oleh']): ?>
                                            <?php echo htmlspecialchars($row['disetujui_oleh']); ?><br>
                                            <small class="text-muted">
                                                <?php echo date('d-m-Y H:i', strtotime($row['tanggal_persetujuan'])); ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data cuti</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Export -->
                <div class="text-center mt-4">
                    <button class="btn btn-success" onclick="exportLaporan()">
                        <i class="fas fa-file-excel me-2"></i>Export Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterByBulan(bulan) {
    updateURL('bulan', bulan);
}

function filterByDivisi(divisi) {
    updateURL('divisi', divisi);
}

function filterByStatus(status) {
    updateURL('status', status);
}

function updateURL(param, value) {
    const url = new URL(window.location.href);
    if (value) {
        url.searchParams.set(param, value);
    } else {
        url.searchParams.delete(param);
    }
    window.location.href = url.toString();
}

function resetFilter() {
    window.location.href = 'riwayat_cuti.php';
}

function exportLaporan() {
    alert('Fitur export laporan akan diimplementasikan sesuai kebutuhan.');
}
</script>

<?php include '../includes/footer.php'; ?>