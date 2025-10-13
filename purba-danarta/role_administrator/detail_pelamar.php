<?php
// File: role_administrator/detail_pelamar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya Administrator yang bisa akses
if ($_SESSION['role'] !== 'Administrator') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: administrasi_lamaran.php');
    exit();
}

$pelamar_id = $_GET['id'];
$judul_halaman = 'Detail Pelamar';
include '../includes/header.php';
include '../includes/koneksi.php';

// Ambil data pelamar
$sql = "SELECT p.*, u.username, ps.tahap, ps.hasil, ps.catatan, ps.created_at as tanggal_proses
        FROM pelamar p 
        JOIN users u ON p.user_id = u.id 
        LEFT JOIN proses_seleksi ps ON p.id = ps.pelamar_id 
        WHERE p.id = ? 
        ORDER BY ps.created_at DESC";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $pelamar_id);
$stmt->execute();
$pelamar = $stmt->get_result()->fetch_assoc();

if (!$pelamar) {
    echo "<script>alert('Data pelamar tidak ditemukan!'); window.history.back();</script>";
    exit();
}

// Ambil riwayat proses seleksi
$sql_riwayat = "SELECT * FROM proses_seleksi WHERE pelamar_id = ? ORDER BY created_at ASC";
$stmt_riwayat = $koneksi->prepare($sql_riwayat);
$stmt_riwayat->bind_param("i", $pelamar_id);
$stmt_riwayat->execute();
$riwayat_seleksi = $stmt_riwayat->get_result();
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold">Detail Pelamar</h3>
                    <a href="administrasi_lamaran.php" class="btn btn-secondary">Kembali</a>
                </div>

                <!-- Data Pribadi -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Data Pribadi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($pelamar['nama_lengkap']); ?></p>
                                <p><strong>NIK:</strong> <?php echo htmlspecialchars($pelamar['nik']); ?></p>
                                <p><strong>Username:</strong> <?php echo htmlspecialchars($pelamar['username']); ?></p>
                                <p><strong>Posisi Dilamar:</strong> <?php echo htmlspecialchars($pelamar['posisi_dilamar']); ?></p>
                                <p><strong>Divisi Dilamar:</strong> <?php echo htmlspecialchars($pelamar['divisi_dilamar']); ?></p>
                                <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($pelamar['jenis_kelamin']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($pelamar['email']); ?></p>
                                <p><strong>No. Telepon:</strong> <?php echo htmlspecialchars($pelamar['no_telepon']); ?></p>
                                <p><strong>Agama:</strong> <?php echo htmlspecialchars($pelamar['agama']); ?></p>
                                <p><strong>Pendidikan Terakhir:</strong> <?php echo htmlspecialchars($pelamar['pendidikan_terakhir']); ?></p>
                                <p><strong>IPK:</strong> <?php echo htmlspecialchars($pelamar['ipk']); ?></p>
                                <p><strong>Gaji Diharapkan:</strong> Rp <?php echo number_format($pelamar['gaji_diharapkan'], 0, ',', '.'); ?></p>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Alamat KTP:</strong><br><?php echo nl2br(htmlspecialchars($pelamar['alamat_ktp'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Alamat Domisili:</strong><br><?php echo nl2br(htmlspecialchars($pelamar['alamat_domisili'])); ?></p>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Kontak Darurat:</strong><br>
                                    <?php echo htmlspecialchars($pelamar['kontak_darurat_nama']); ?><br>
                                    <?php echo htmlspecialchars($pelamar['kontak_darurat_telepon']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status Lamaran:</strong> 
                                    <span class="badge bg-<?php 
                                        switch($pelamar['status_lamaran']) {
                                            case 'Diterima': echo 'success'; break;
                                            case 'Ditolak': echo 'danger'; break;
                                            default: echo 'primary';
                                        }
                                    ?>"><?php echo $pelamar['status_lamaran']; ?></span>
                                </p>
                                <p><strong>Tanggal Daftar:</strong> <?php echo date('d-m-Y H:i', strtotime($pelamar['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dokumen -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-file me-2"></i>Dokumen dan Gambar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <strong>Pas Foto:</strong><br>
                                <?php if ($pelamar['path_pas_foto'] && file_exists($pelamar['path_pas_foto'])): ?>
                                    <img src="../<?php echo $pelamar['path_pas_foto']; ?>" alt="Pas Foto" class="img-thumbnail mt-2" style="max-height: 200px;">
                                <?php else: ?>
                                    <span class="text-muted">Tidak ada</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <strong>CV:</strong><br>
                                        <?php if ($pelamar['path_cv'] && file_exists($pelamar['path_cv'])): ?>
                                            <a href="../<?php echo $pelamar['path_cv']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                <i class="fas fa-download me-1"></i>Download CV
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <strong>Surat Lamaran:</strong><br>
                                        <?php if ($pelamar['path_surat_lamaran'] && file_exists($pelamar['path_surat_lamaran'])): ?>
                                            <a href="../<?php echo $pelamar['path_surat_lamaran']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                <i class="fas fa-download me-1"></i>Download Surat
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <strong>Kartu Identitas:</strong><br>
                                        <?php if ($pelamar['path_ktp'] && file_exists($pelamar['path_ktp'])): ?>
                                            <a href="../<?php echo $pelamar['path_ktp']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                <i class="fas fa-download me-1"></i>Download KTP
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <strong>Ijazah & Transkrip:</strong><br>
                                        <?php if ($pelamar['path_ijazah'] && file_exists($pelamar['path_ijazah'])): ?>
                                            <a href="../<?php echo $pelamar['path_ijazah']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                <i class="fas fa-download me-1"></i>Download Ijazah
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <strong>Dokumen Lain:</strong><br>
                                        <?php if ($pelamar['path_dokumen_lain'] && file_exists($pelamar['path_dokumen_lain'])): ?>
                                            <a href="../<?php echo $pelamar['path_dokumen_lain']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                                <i class="fas fa-download me-1"></i>Download Dokumen
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Proses Seleksi -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Proses Seleksi</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($riwayat_seleksi->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tahap</th>
                                            <th>Hasil</th>
                                            <th>Catatan</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($riwayat = $riwayat_seleksi->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($riwayat['tahap']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $riwayat['hasil'] == 'Lolos' ? 'success' : 'danger'; ?>">
                                                    <?php echo $riwayat['hasil']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($riwayat['catatan']); ?></td>
                                            <td><?php echo date('d-m-Y H:i', strtotime($riwayat['created_at'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Belum ada riwayat proses seleksi.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <?php if (!in_array($pelamar['status_lamaran'], ['Diterima', 'Ditolak'])): ?>
                <div class="mt-4 text-center">
                    <button class="btn btn-success btn-lg me-3" onclick="updateStatus(<?php echo $pelamar_id; ?>, 'next')">
                        <i class="fas fa-arrow-right me-2"></i>Lanjut ke Tahap Berikutnya
                    </button>
                    <button class="btn btn-danger btn-lg" onclick="updateStatus(<?php echo $pelamar_id; ?>, 'reject')">
                        <i class="fas fa-times me-2"></i>Tolak Pelamar
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>

<?php include '../includes/footer.php'; ?>