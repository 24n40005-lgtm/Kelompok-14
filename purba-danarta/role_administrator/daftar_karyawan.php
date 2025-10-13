<?php
// File: role_administrator/daftar_karyawan.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya Administrator yang bisa akses
if ($_SESSION['role'] !== 'Administrator') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Daftar Karyawan';
include '../includes/header.php';
include '../includes/koneksi.php';

// Handle delete karyawan
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $nik_to_delete = $_GET['delete'];
    
    // Cek apakah bukan diri sendiri
    if ($nik_to_delete === $_SESSION['nik']) {
        $_SESSION['error'] = "Tidak dapat menghapus akun sendiri!";
    } else {
        // Soft delete - update status menjadi Non-Aktif
        $sql_update = "UPDATE karyawan SET status_karyawan = 'Non-Aktif' WHERE nik = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("s", $nik_to_delete);
        
        if ($stmt_update->execute()) {
            $_SESSION['success'] = "Data karyawan berhasil dinonaktifkan!";
        } else {
            $_SESSION['error'] = "Gagal menonaktifkan karyawan: " . $stmt_update->error;
        }
    }
    header('Location: daftar_karyawan.php');
    exit();
}

// Ambil data karyawan
$search = $_GET['search'] ?? '';
$divisi_filter = $_GET['divisi'] ?? '';

$sql_where = "WHERE k.status_karyawan = 'Aktif'";
$params = [];
$types = "";

if (!empty($search)) {
    $sql_where .= " AND (k.nama_lengkap LIKE ? OR k.nik LIKE ? OR k.email LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
    $types .= "sss";
}

if (!empty($divisi_filter)) {
    $sql_where .= " AND k.divisi = ?";
    $params[] = $divisi_filter;
    $types .= "s";
}

$sql = "SELECT k.*, u.username, 
               (SELECT nama_role FROM roles r 
                JOIN user_roles ur ON r.id = ur.role_id 
                WHERE ur.user_id = u.id AND r.nama_role != 'Pelamar' 
                LIMIT 1) as role_utama
        FROM karyawan k 
        JOIN users u ON k.user_id = u.id 
        $sql_where 
        ORDER BY k.nama_lengkap";

$stmt = $koneksi->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$karyawan = $stmt->get_result();

// Ambil list divisi untuk filter
$sql_divisi = "SELECT DISTINCT divisi FROM karyawan WHERE status_karyawan = 'Aktif' ORDER BY divisi";
$divisi_list = $koneksi->query($sql_divisi);
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Daftar Karyawan</h3>

                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <!-- Filter dan Pencarian -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Cari nama, NIK, email..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-custom-green">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" onchange="filterByDivisi(this.value)">
                            <option value="">Semua Divisi</option>
                            <?php while ($divisi = $divisi_list->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($divisi['divisi']); ?>" <?php echo $divisi_filter === $divisi['divisi'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($divisi['divisi']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahKaryawanModal">
                            <i class="fas fa-plus me-2"></i>Tambah Karyawan
                        </button>
                    </div>
                </div>

                <!-- Tabel Karyawan -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>NIK</th>
                                <th>Nama Lengkap</th>
                                <th>Divisi</th>
                                <th>Role</th>
                                <th>No. Telepon</th>
                                <th>Email</th>
                                <th>Sisa Cuti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($karyawan->num_rows > 0): ?>
                                <?php while ($row = $karyawan->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nik']); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                        <small class="text-muted">@<?php echo htmlspecialchars($row['username']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($row['role_utama']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['no_telepon']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td>
                                        <small>Tahunan: <strong><?php echo $row['sisa_cuti_tahunan']; ?></strong></small><br>
                                        <small>Lustrum: <strong><?php echo $row['sisa_cuti_lustrum']; ?></strong></small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_karyawan.php?nik=<?php echo $row['nik']; ?>" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($row['nik'] !== $_SESSION['nik']): ?>
                                            <button class="btn btn-danger" onclick="confirmDelete('<?php echo $row['nik']; ?>', '<?php echo htmlspecialchars($row['nama_lengkap']); ?>')" title="Nonaktifkan">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data karyawan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
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
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div class="modal fade" id="tambahKaryawanModal" tabindex="-1" aria-labelledby="tambahKaryawanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahKaryawanModalLabel">Tambah Karyawan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="proses_tambah_karyawan.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK *</label>
                            <input type="text" name="nik" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Divisi *</label>
                            <select name="divisi" class="form-select" required>
                                <option value="">Pilih Divisi</option>
                                <option value="SDM">SDM</option>
                                <option value="IT">IT</option>
                                <option value="Keuangan">Keuangan</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operasional">Operasional</option>
                                <option value="Training">Training</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jabatan *</label>
                            <input type="text" name="jabatan" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon *</label>
                            <input type="tel" name="no_telepon" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-select" required>
                            <option value="">Pilih Role</option>
                            <option value="Karyawan">Karyawan</option>
                            <option value="Penanggung Jawab">Penanggung Jawab</option>
                            <option value="Administrator">Administrator</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterByDivisi(divisi) {
    const url = new URL(window.location.href);
    if (divisi) {
        url.searchParams.set('divisi', divisi);
    } else {
        url.searchParams.delete('divisi');
    }
    window.location.href = url.toString();
}

function confirmDelete(nik, nama) {
    if (confirm(`Apakah Anda yakin ingin menonaktifkan karyawan:\n${nama} (NIK: ${nik})?`)) {
        window.location.href = `daftar_karyawan.php?delete=${nik}`;
    }
}
</script>

<?php include '../includes/footer.php'; ?>