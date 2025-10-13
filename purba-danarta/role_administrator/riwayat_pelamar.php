<?php
// File: role_administrator/riwayat_pelamar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya Administrator yang bisa akses
if ($_SESSION['role'] !== 'Administrator') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Riwayat Pelamar';
include '../includes/header.php';
include '../includes/koneksi.php';

// Filter parameters
$status_filter = $_GET['status'] ?? 'all';
$divisi_filter = $_GET['divisi'] ?? '';
$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_selesai = $_GET['tanggal_selesai'] ?? '';
$search = $_GET['search'] ?? '';

// Build query dengan filter
$sql_where = "WHERE 1=1";
$params = [];
$types = "";

if ($status_filter !== 'all') {
    $sql_where .= " AND p.status_lamaran = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($divisi_filter)) {
    $sql_where .= " AND p.divisi_dilamar = ?";
    $params[] = $divisi_filter;
    $types .= "s";
}

if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {
    $sql_where .= " AND DATE(p.created_at) BETWEEN ? AND ?";
    $params[] = $tanggal_mulai;
    $params[] = $tanggal_selesai;
    $types .= "ss";
} elseif (!empty($tanggal_mulai)) {
    $sql_where .= " AND DATE(p.created_at) >= ?";
    $params[] = $tanggal_mulai;
    $types .= "s";
} elseif (!empty($tanggal_selesai)) {
    $sql_where .= " AND DATE(p.created_at) <= ?";
    $params[] = $tanggal_selesai;
    $types .= "s";
}

if (!empty($search)) {
    $sql_where .= " AND (p.nama_lengkap LIKE ? OR p.nik LIKE ? OR p.email LIKE ? OR p.posisi_dilamar LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= "ssss";
}

$sql = "SELECT p.*, u.username, 
               (SELECT COUNT(*) FROM proses_seleksi ps WHERE ps.pelamar_id = p.id) as total_tahap
        FROM pelamar p 
        JOIN users u ON p.user_id = u.id 
        $sql_where 
        ORDER BY p.created_at DESC";

$stmt = $koneksi->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$pelamar = $stmt->get_result();

// Ambil statistik
$sql_stats = "SELECT 
    status_lamaran, 
    COUNT(*) as total,
    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as hari_ini
    FROM pelamar 
    GROUP BY status_lamaran 
    ORDER BY status_lamaran";
$stats_result = $koneksi->query($sql_stats);
$statistics = [];
$total_hari_ini = 0;

while ($row = $stats_result->fetch_assoc()) {
    $statistics[$row['status_lamaran']] = $row['total'];
    $total_hari_ini += $row['hari_ini'];
}

// Ambil list divisi untuk filter
$sql_divisi = "SELECT DISTINCT divisi_dilamar FROM pelamar ORDER BY divisi_dilamar";
$divisi_list = $koneksi->query($sql_divisi);
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold">Riwayat Pelamar Kerja</h3>
                    <a href="administrasi_lamaran.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Administrasi
                    </a>
                </div>

                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card text-center bg-primary text-white">
                            <div class="card-body py-3">
                                <h6 class="card-title">Total</h6>
                                <p class="card-text fw-bold fs-4"><?php echo array_sum($statistics); ?></p>
                                <small>Hari ini: <?php echo $total_hari_ini; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-secondary text-white">
                            <div class="card-body py-3">
                                <h6 class="card-title">Administrasi</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $statistics['Administrasi'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-info text-white">
                            <div class="card-body py-3">
                                <h6 class="card-title">Wawancara</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $statistics['Wawancara'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-warning text-dark">
                            <div class="card-body py-3">
                                <h6 class="card-title">Psikotes</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $statistics['Psikotes'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body py-3">
                                <h6 class="card-title">Diterima</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $statistics['Diterima'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-danger text-white">
                            <div class="card-body py-3">
                                <h6 class="card-title">Ditolak</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $statistics['Ditolak'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Data</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Status Lamaran</label>
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                        <option value="Administrasi" <?php echo $status_filter === 'Administrasi' ? 'selected' : ''; ?>>Administrasi</option>
                                        <option value="Wawancara" <?php echo $status_filter === 'Wawancara' ? 'selected' : ''; ?>>Wawancara</option>
                                        <option value="Psikotes" <?php echo $status_filter === 'Psikotes' ? 'selected' : ''; ?>>Psikotes</option>
                                        <option value="Kesehatan" <?php echo $status_filter === 'Kesehatan' ? 'selected' : ''; ?>>Kesehatan</option>
                                        <option value="Diterima" <?php echo $status_filter === 'Diterima' ? 'selected' : ''; ?>>Diterima</option>
                                        <option value="Ditolak" <?php echo $status_filter === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Divisi</label>
                                    <select name="divisi" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Divisi</option>
                                        <?php while ($divisi = $divisi_list->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($divisi['divisi_dilamar']); ?>" <?php echo $divisi_filter === $divisi['divisi_dilamar'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($divisi['divisi_dilamar']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai" class="form-control" value="<?php echo $tanggal_mulai; ?>" onchange="this.form.submit()">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" class="form-control" value="<?php echo $tanggal_selesai; ?>" onchange="this.form.submit()">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Pencarian</label>
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Cari..." value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit" class="btn btn-custom-green">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilter()">
                                    <i class="fas fa-refresh me-1"></i>Reset Filter
                                </button>
                                <span class="text-muted ms-3">
                                    Menampilkan <?php echo $pelamar->num_rows; ?> data
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Riwayat -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nama Pelamar</th>
                                <th>Posisi & Divisi</th>
                                <th>Kontak</th>
                                <th>Pendidikan</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pelamar->num_rows > 0): ?>
                                <?php while ($row = $pelamar->fetch_assoc()): 
                                    // Ambil riwayat proses seleksi terbaru
                                    $sql_riwayat = "SELECT tahap, hasil, catatan, created_at 
                                                   FROM proses_seleksi 
                                                   WHERE pelamar_id = ? 
                                                   ORDER BY created_at DESC 
                                                   LIMIT 1";
                                    $stmt_riwayat = $koneksi->prepare($sql_riwayat);
                                    $stmt_riwayat->bind_param("i", $row['id']);
                                    $stmt_riwayat->execute();
                                    $riwayat_terbaru = $stmt_riwayat->get_result()->fetch_assoc();
                                ?>
                                <tr>
                                    <td class="text-center">PL-<?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                        <small class="text-muted">NIK: <?php echo htmlspecialchars($row['nik']); ?></small><br>
                                        <small class="text-muted">@<?php echo htmlspecialchars($row['username']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['posisi_dilamar']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['divisi_dilamar']); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($row['email']); ?></small><br>
                                        <small><?php echo htmlspecialchars($row['no_telepon']); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($row['pendidikan_terakhir']); ?></small><br>
                                        <small>IPK: <?php echo $row['ipk']; ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $status_class = [
                                            'Administrasi' => 'bg-secondary',
                                            'Wawancara' => 'bg-info',
                                            'Psikotes' => 'bg-warning',
                                            'Kesehatan' => 'bg-primary',
                                            'Diterima' => 'bg-success',
                                            'Ditolak' => 'bg-danger'
                                        ];
                                        $class = $status_class[$row['status_lamaran']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $class; ?>"><?php echo $row['status_lamaran']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <small>Tahap: <?php echo $row['total_tahap']; ?></small><br>
                                        <?php if ($riwayat_terbaru): ?>
                                            <small class="text-muted">
                                                <?php echo $riwayat_terbaru['tahap']; ?>: 
                                                <span class="badge bg-<?php echo $riwayat_terbaru['hasil'] == 'Lolos' ? 'success' : 'danger'; ?>">
                                                    <?php echo $riwayat_terbaru['hasil']; ?>
                                                </span>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d-m-Y', strtotime($row['created_at'])); ?><br>
                                        <small class="text-muted">
                                            <?php echo date('H:i', strtotime($row['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="detail_pelamar.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (!in_array($row['status_lamaran'], ['Diterima', 'Ditolak'])): ?>
                                            <button class="btn btn-success" onclick="updateStatus(<?php echo $row['id']; ?>, 'next')" title="Lanjut Tahap">
                                                <i class="fas fa-arrow-right"></i>
                                            </button>
                                            <button class="btn btn-danger" onclick="updateStatus(<?php echo $row['id']; ?>, 'reject')" title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i><br>
                                        <span class="text-muted">Tidak ada data pelamar</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination & Export -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                    
                    <div>
                        <button class="btn btn-success me-2" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-2"></i>Export Excel
                        </button>
                        <button class="btn btn-danger" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetFilter() {
    window.location.href = 'riwayat_pelamar.php';
}

function updateStatus(pelamarId, action) {
    if (action === 'next') {
        if (confirm('Lanjutkan ke tahap berikutnya?')) {
            fetch('proses_update_lamaran.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'pelamar_id=' + pelamarId + '&action=next'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status berhasil diupdate!');
                    location.reload();
                } else {
                    alert('Gagal update status: ' + data.message);
                }
            });
        }
    } else if (action === 'reject') {
        var alasan = prompt('Masukkan alasan penolakan:');
        if (alasan !== null) {
            fetch('proses_update_lamaran.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'pelamar_id=' + pelamarId + '&action=reject&alasan=' + encodeURIComponent(alasan)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pelamar berhasil ditolak!');
                    location.reload();
                } else {
                    alert('Gagal menolak pelamar: ' + data.message);
                }
            });
        }
    }
}

function exportToExcel() {
    // Build export URL dengan parameter filter saat ini
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.open('export_pelamar.php?' + params.toString(), '_blank');
}

function exportToPDF() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    window.open('export_pelamar.php?' + params.toString(), '_blank');
}

// Auto-submit form ketika tanggal diubah
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>