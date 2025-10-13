<?php
// File: role_administrator/master_data.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya Administrator yang bisa akses
if ($_SESSION['role'] !== 'Administrator') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Master Data';
include '../includes/header.php';
include '../includes/koneksi.php';

// Handle form submission untuk update jatah cuti
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_cuti'])) {
    $nik = $_POST['nik'] ?? '';
    $sisa_tahunan = $_POST['sisa_tahunan'] ?? '';
    $sisa_lustrum = $_POST['sisa_lustrum'] ?? '';

    if (!empty($nik) && is_numeric($sisa_tahunan) && is_numeric($sisa_lustrum)) {
        $sql_update = "UPDATE karyawan SET sisa_cuti_tahunan = ?, sisa_cuti_lustrum = ? WHERE nik = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("iis", $sisa_tahunan, $sisa_lustrum, $nik);
        
        if ($stmt_update->execute()) {
            $success_message = "Jatah cuti berhasil diupdate!";
        } else {
            $error_message = "Gagal update jatah cuti: " . $stmt_update->error;
        }
    }
}

// Ambil data karyawan
$sql_karyawan = "SELECT k.*, u.username 
                 FROM karyawan k 
                 JOIN users u ON k.user_id = u.id 
                 WHERE k.status_karyawan = 'Aktif' 
                 ORDER BY k.nama_lengkap";
$karyawan = $koneksi->query($sql_karyawan);
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Master Data Sistem</h3>

                <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Management Jatah Cuti -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Management Jatah Cuti Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>NIK</th>
                                        <th>Nama Karyawan</th>
                                        <th>Divisi</th>
                                        <th>Sisa Cuti Tahunan</th>
                                        <th>Sisa Cuti Lustrum</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($karyawan->num_rows > 0): ?>
                                        <?php while ($row = $karyawan->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nik']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                            <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="nik" value="<?php echo $row['nik']; ?>">
                                                    <input type="number" name="sisa_tahunan" value="<?php echo $row['sisa_cuti_tahunan']; ?>" 
                                                           class="form-control form-control-sm" min="0" max="365" style="width: 80px; display: inline-block;">
                                            </td>
                                            <td>
                                                    <input type="number" name="sisa_lustrum" value="<?php echo $row['sisa_cuti_lustrum']; ?>" 
                                                           class="form-control form-control-sm" min="0" max="365" style="width: 80px; display: inline-block;">
                                            </td>
                                            <td>
                                                    <button type="submit" name="update_cuti" class="btn btn-sm btn-success">
                                                        <i class="fas fa-save me-1"></i>Update
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data karyawan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Informasi Sistem -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Sistem</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                // Hitung statistik
                                $sql_stats = "SELECT 
                                    (SELECT COUNT(*) FROM users) as total_users,
                                    (SELECT COUNT(*) FROM karyawan WHERE status_karyawan = 'Aktif') as total_karyawan,
                                    (SELECT COUNT(*) FROM pelamar) as total_pelamar,
                                    (SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'Pending') as cuti_pending,
                                    (SELECT COUNT(*) FROM pengajuan_khl WHERE status = 'Pending') as khl_pending";
                                $stats = $koneksi->query($sql_stats)->fetch_assoc();
                                ?>
                                <p><strong>Total Users:</strong> <?php echo $stats['total_users']; ?></p>
                                <p><strong>Karyawan Aktif:</strong> <?php echo $stats['total_karyawan']; ?></p>
                                <p><strong>Total Pelamar:</strong> <?php echo $stats['total_pelamar']; ?></p>
                                <p><strong>Cuti Menunggu:</strong> <?php echo $stats['cuti_pending']; ?></p>
                                <p><strong>KHL Menunggu:</strong> <?php echo $stats['khl_pending']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="daftar_karyawan.php" class="btn btn-outline-primary">
                                        <i class="fas fa-users me-2"></i>Kelola Data Karyawan
                                    </a>
                                    <a href="administrasi_lamaran.php" class="btn btn-outline-success">
                                        <i class="fas fa-clipboard-list me-2"></i>Kelola Lamaran
                                    </a>
                                    <button class="btn btn-outline-info" onclick="backupDatabase()">
                                        <i class="fas fa-database me-2"></i>Backup Database
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function backupDatabase() {
    if (confirm('Lakukan backup database?')) {
        // Simulasi backup (dalam production, ini akan panggil script PHP untuk backup)
        alert('Fitur backup database akan diimplementasikan sesuai environment server.');
    }
}
</script>

<?php include '../includes/footer.php'; ?>