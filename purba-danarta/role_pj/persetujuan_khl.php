<?php
// File: role_pj/persetujuan_khl.php (UPGRADED VERSION)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user adalah Penanggung Jawab
if ($_SESSION['role'] !== 'Penanggung Jawab') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Persetujuan KHL';
include '../includes/header.php';
include '../includes/koneksi.php';

// Ambil divisi PJ
$nik_pj = $_SESSION['nik'];
$sql_divisi = "SELECT divisi FROM karyawan WHERE nik = ?";
$stmt_divisi = $koneksi->prepare($sql_divisi);
$stmt_divisi->bind_param("s", $nik_pj);
$stmt_divisi->execute();
$divisi_pj = $stmt_divisi->get_result()->fetch_assoc()['divisi'];

// Handle filter
$status_filter = $_GET['status'] ?? 'pending';

// Query data KHL berdasarkan divisi PJ
$sql_where = "WHERE k.divisi = ?";
$params = [$divisi_pj];
$types = "s";

if ($status_filter !== 'all') {
    $sql_where .= " AND pk.status = ?";
    $params[] = ucfirst($status_filter);
    $types .= "s";
}

$sql = "SELECT pk.*, k.nama_lengkap, k.jabatan,
               DATE_FORMAT(pk.tanggal_pengajuan, '%d-%m-%Y %H:%i') as tgl_pengajuan_format
        FROM pengajuan_khl pk 
        JOIN karyawan k ON pk.karyawan_nik = k.nik 
        $sql_where 
        ORDER BY pk.tanggal_pengajuan DESC";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$khl_data = $stmt->get_result();

// Hitung statistik
$sql_stats = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN pk.status = 'Pending' THEN 1 END) as pending,
    COUNT(CASE WHEN pk.status = 'Disetujui' THEN 1 END) as disetujui,
    COUNT(CASE WHEN pk.status = 'Ditolak' THEN 1 END) as ditolak
    FROM pengajuan_khl pk 
    JOIN karyawan k ON pk.karyawan_nik = k.nik 
    WHERE k.divisi = ?";
$stmt_stats = $koneksi->prepare($sql_stats);
$stmt_stats->bind_param("s", $divisi_pj);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Persetujuan KHL - Divisi <?php echo $divisi_pj; ?></h3>

                <!-- Statistik -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center bg-light">
                            <div class="card-body py-3">
                                <h6 class="card-title text-muted">Total</h6>
                                <p class="card-text fw-bold fs-4"><?php echo $stats['total']; ?></p>
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
                </div>

                <!-- Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <select class="form-select" onchange="filterByStatus(this.value)">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu Persetujuan</option>
                            <option value="disetujui" <?php echo $status_filter === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="ditolak" <?php echo $status_filter === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Cari nama karyawan..." id="searchInput">
                            <button class="btn btn-custom-green" type="button" id="searchBtn">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabel Persetujuan KHL -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>ID KHL</th>
                                <th>Nama Karyawan</th>
                                <th>Jabatan</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Tanggal Kerja</th>
                                <th>Jam Kerja</th>
                                <th>Tanggal Libur</th>
                                <th>Proyek/Pekerjaan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($khl_data->num_rows > 0): ?>
                                <?php while ($row = $khl_data->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">KHL-<?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                    <td><?php echo $row['tgl_pengajuan_format']; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($row['tanggal_kerja'])); ?></td>
                                    <td>
                                        <?php echo substr($row['jam_mulai'], 0, 5); ?> - 
                                        <?php echo substr($row['jam_selesai'], 0, 5); ?>
                                    </td>
                                    <td><?php echo date('d-m-Y', strtotime($row['tanggal_libur_pengganti'])); ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($row['proyek']); ?>">
                                            <?php echo htmlspecialchars($row['proyek']); ?>
                                        </div>
                                    </td>
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
                                    <td class="text-center">
                                        <?php if ($row['status'] === 'Pending'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-success" onclick="setujuiKHL(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_lengkap']); ?>')">
                                                <i class="fas fa-check"></i> Setujui
                                            </button>
                                            <button class="btn btn-danger" onclick="tolakKHL(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_lengkap']); ?>')">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        </div>
                                        <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="lihatDetailKHL(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data KHL</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail KHL -->
<div class="modal fade" id="detailModalKHL" tabindex="-1" aria-labelledby="detailModalKHLLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalKHLLabel">Detail Pengajuan KHL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBodyKHL">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterByStatus(status) {
    window.location.href = `persetujuan_khl.php?status=${status}`;
}

function setujuiKHL(khlId, nama) {
    if (confirm(`Setujui pengajuan KHL dari:\n${nama}?`)) {
        fetch('../proses_persetujuan_khl.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'khl_id=' + khlId + '&action=approve'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('KHL berhasil disetujui!');
                location.reload();
            } else {
                alert('Gagal menyetujui KHL: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi error: ' + error);
        });
    }
}

function tolakKHL(khlId, nama) {
    const alasan = prompt(`Masukkan alasan penolakan untuk:\n${nama}`);
    if (alasan !== null && alasan.trim() !== '') {
        fetch('../proses_persetujuan_khl.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'khl_id=' + khlId + '&action=reject&alasan=' + encodeURIComponent(alasan)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('KHL berhasil ditolak!');
                location.reload();
            } else {
                alert('Gagal menolak KHL: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi error: ' + error);
        });
    }
}

function lihatDetailKHL(khlId) {
    // Load detail via AJAX
    const modalBody = document.getElementById('modalBodyKHL');
    modalBody.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data...</p>
        </div>
    `;
    
    fetch(`../ajax_get_detail_khl.php?id=${khlId}`)
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading data: ${error}</div>`;
        });
    
    const modal = new bootstrap.Modal(document.getElementById('detailModalKHL'));
    modal.show();
}

// Search functionality
document.getElementById('searchBtn').addEventListener('click', function() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const nama = row.cells[1].textContent.toLowerCase();
        if (nama.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>