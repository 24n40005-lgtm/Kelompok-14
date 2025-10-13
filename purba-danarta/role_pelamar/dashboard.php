<?php
// File: role_pelamar/dashboard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user adalah pelamar
if ($_SESSION['role'] !== 'Pelamar') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Dashboard Pelamar';
include '../includes/header.php';

// Ambil data pelamar dari database
include '../includes/koneksi.php';
$user_id = $_SESSION['user_id'];

$sql = "SELECT p.*, ps.tahap, ps.hasil, ps.catatan 
        FROM pelamar p 
        LEFT JOIN proses_seleksi ps ON p.id = ps.pelamar_id AND ps.id = (
            SELECT MAX(id) FROM proses_seleksi WHERE pelamar_id = p.id
        )
        WHERE p.user_id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pelamar = $result->fetch_assoc();
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Dashboard Pelamar</h3>
                
                <div class="alert alert-info">
                    <strong>Halo, <?php echo $_SESSION['nama_lengkap']; ?></strong>
                    - Selamat datang di dashboard pelamar.
                </div>

                <!-- Status Lamaran -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Status Lamaran</h5>
                                <?php if ($pelamar): ?>
                                    <div class="mb-3">
                                        <strong>Posisi:</strong> <?php echo htmlspecialchars($pelamar['posisi_dilamar']); ?><br>
                                        <strong>Divisi:</strong> <?php echo htmlspecialchars($pelamar['divisi_dilamar']); ?><br>
                                        <strong>Tahap:</strong> 
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($pelamar['tahap'] ?? 'Administrasi'); ?></span><br>
                                        <strong>Hasil:</strong> 
                                        <span class="badge <?php echo ($pelamar['hasil'] == 'Lolos') ? 'bg-success' : (($pelamar['hasil'] == 'Tidak Lolos') ? 'bg-danger' : 'bg-warning'); ?>">
                                            <?php echo htmlspecialchars($pelamar['hasil'] ?? 'Menunggu'); ?>
                                        </span>
                                    </div>
                                    <?php if ($pelamar['catatan']): ?>
                                        <div class="alert alert-warning">
                                            <strong>Catatan:</strong> <?php echo htmlspecialchars($pelamar['catatan']); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">Belum ada data lamaran.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Pengumuman</h5>
                                <?php if ($pelamar && $pelamar['catatan']): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-bullhorn me-2"></i>
                                        <?php echo htmlspecialchars($pelamar['catatan']); ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Tidak ada pengumuman terbaru.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Lamaran -->
                <?php if ($pelamar): ?>
                <div class="card">
                    <div class="card-header">
                        <h5>Detail Lamaran Anda</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($pelamar['nama_lengkap']); ?></p>
                                <p><strong>NIK:</strong> <?php echo htmlspecialchars($pelamar['nik']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($pelamar['email']); ?></p>
                                <p><strong>No. Telepon:</strong> <?php echo htmlspecialchars($pelamar['no_telepon']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Pendidikan:</strong> <?php echo htmlspecialchars($pelamar['pendidikan_terakhir']); ?></p>
                                <p><strong>IPK:</strong> <?php echo htmlspecialchars($pelamar['ipk']); ?></p>
                                <p><strong>Gaji Diharapkan:</strong> Rp <?php echo number_format($pelamar['gaji_diharapkan'], 0, ',', '.'); ?></p>
                                <p><strong>Tanggal Daftar:</strong> <?php echo date('d-m-Y', strtotime($pelamar['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>