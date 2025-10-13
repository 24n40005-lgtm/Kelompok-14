<?php
// File: role_administrator/administrasi_lamaran.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya Administrator yang bisa akses
if ($_SESSION['role'] !== 'Administrator') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Administrasi Lamaran';
include '../includes/header.php';
include '../includes/koneksi.php';

// Ambil data pelamar berdasarkan status
$status_filter = $_GET['status'] ?? 'all';
$sql_where = '';
if ($status_filter !== 'all') {
    $sql_where = "WHERE p.status_lamaran = '$status_filter'";
}

$sql = "SELECT p.*, u.username 
        FROM pelamar p 
        JOIN users u ON p.user_id = u.id 
        $sql_where 
        ORDER BY p.created_at DESC";
$pelamar = $koneksi->query($sql);

// Hitung statistik
$sql_stats = "SELECT status_lamaran, COUNT(*) as total FROM pelamar GROUP BY status_lamaran";
$stats_result = $koneksi->query($sql_stats);
$statistics = [];
while ($row = $stats_result->fetch_assoc()) {
    $statistics[$row['status_lamaran']] = $row['total'];
}
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Administrasi Lamaran Kerja</h3>

                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title">Administrasi</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $statistics['Administrasi'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title">Wawancara</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $statistics['Wawancara'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title">Psikotes</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $statistics['Psikotes'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title">Kesehatan</h6>
                                <p class="card-text fs-4 fw-bold"><?php echo $statistics['Kesehatan'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title">Diterima</h6>
                                <p class="card-text fs-4 fw-bold text-success"><?php echo $statistics['Diterima'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title">Ditolak</h6>
                                <p class="card-text fs-4 fw-bold text-danger"><?php echo $statistics['Ditolak'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <select class="form-select" id="statusFilter" onchange="filterByStatus(this.value)">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="Administrasi" <?php echo $status_filter === 'Administrasi' ? 'selected' : ''; ?>>Administrasi</option>
                            <option value="Wawancara" <?php echo $status_filter === 'Wawancara' ? 'selected' : ''; ?>>Wawancara</option>
                            <option value="Psikotes" <?php echo $status_filter === 'Psikotes' ? 'selected' : ''; ?>>Psikotes</option>
                            <option value="Kesehatan" <?php echo $status_filter === 'Kesehatan' ? 'selected' : ''; ?>>Kesehatan</option>
                            <option value="Diterima" <?php echo $status_filter === 'Diterima' ? 'selected' : ''; ?>>Diterima</option>
                            <option value="Ditolak" <?php echo $status_filter === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Cari nama atau posisi..." id="searchInput">
                            <button class="btn btn-custom-green" type="button" id="searchBtn">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabel Pelamar -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nama Pelamar</th>
                                <th>Posisi</th>
                                <th>Divisi</th>
                                <th>Pendidikan</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pelamar->num_rows > 0): ?>
                                <?php while ($row = $pelamar->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">PL-<?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['posisi_dilamar']); ?></td>
                                    <td><?php echo htmlspecialchars($row['divisi_dilamar']); ?></td>
                                    <td><?php echo htmlspecialchars($row['pendidikan_terakhir']); ?></td>
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
                                    <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="detail_pelamar.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-success" onclick="updateStatus(<?php echo $row['id']; ?>, 'next')" title="Lanjut ke Tahap Berikutnya">
                                                <i class="fas fa-arrow-right"></i>
                                            </button>
                                            <button class="btn btn-danger" onclick="updateStatus(<?php echo $row['id']; ?>, 'reject')" title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data pelamar</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterByStatus(status) {
    window.location.href = 'administrasi_lamaran.php?status=' + status;
}

function updateStatus(pelamarId, action) {
    if (action === 'next') {
        if (confirm('Lanjutkan ke tahap berikutnya?')) {
            // AJAX call to update status
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
                    location.reload();
                } else {
                    alert('Gagal menolak pelamar: ' + data.message);
                }
            });
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>