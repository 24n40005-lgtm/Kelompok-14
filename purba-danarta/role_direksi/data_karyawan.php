<?php
// File: role_direksi/data_karyawan.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya Direksi yang bisa akses
if ($_SESSION['role'] !== 'Direksi') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Data Karyawan';
include '../includes/header.php';
include '../includes/koneksi.php';

// Ambil data karyawan
$sql = "SELECT k.*, u.username,
               (SELECT nama_role FROM roles r 
                JOIN user_roles ur ON r.id = ur.role_id 
                WHERE ur.user_id = u.id AND r.nama_role != 'Pelamar' 
                LIMIT 1) as role_utama
        FROM karyawan k 
        JOIN users u ON k.user_id = u.id 
        WHERE k.status_karyawan = 'Aktif'
        ORDER BY k.divisi, k.nama_lengkap";

$karyawan = $koneksi->query($sql);
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Data Seluruh Karyawan</h3>

                <!-- Statistik Cepat -->
                <div class="row mb-4">
                    <?php
                    $sql_stats = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN divisi = 'SDM' THEN 1 END) as sdm,
                        COUNT(CASE WHEN divisi = 'IT' THEN 1 END) as it,
                        COUNT(CASE WHEN divisi = 'Keuangan' THEN 1 END) as keuangan,
                        COUNT(CASE WHEN divisi = 'Marketing' THEN 1 END) as marketing
                        FROM karyawan WHERE status_karyawan = 'Aktif'";
                    $stats = $koneksi->query($sql_stats)->fetch_assoc();
                    ?>
                    <div class="col-md-2">
                        <div class="card text-center bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title text-muted mb-1">Total</h6>
                                <p class="card-text fw-bold fs-5"><?php echo $stats['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title text-muted mb-1">SDM</h6>
                                <p class="card-text fw-bold fs-5"><?php echo $stats['sdm']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title text-muted mb-1">IT</h6>
                                <p class="card-text fw-bold fs-5"><?php echo $stats['it']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title text-muted mb-1">Keuangan</h6>
                                <p class="card-text fw-bold fs-5"><?php echo $stats['keuangan']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title text-muted mb-1">Marketing</h6>
                                <p class="card-text fw-bold fs-5"><?php echo $stats['marketing']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title text-muted mb-1">Lainnya</h6>
                                <p class="card-text fw-bold fs-5"><?php echo $stats['total'] - ($stats['sdm'] + $stats['it'] + $stats['keuangan'] + $stats['marketing']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Karyawan -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Divisi</th>
                                <th>Jabatan</th>
                                <th>Role</th>
                                <th>Kontak</th>
                                <th>Status Cuti</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($karyawan->num_rows > 0): ?>
                                <?php while ($row = $karyawan->fetch_assoc()): 
                                    // Hitung cuti aktif
                                    $sql_cuti_aktif = "SELECT COUNT(*) as total FROM pengajuan_cuti 
                                                     WHERE karyawan_nik = ? AND status = 'Disetujui' 
                                                     AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai";
                                    $stmt_cuti = $koneksi->prepare($sql_cuti_aktif);
                                    $stmt_cuti->bind_param("s", $row['nik']);
                                    $stmt_cuti->execute();
                                    $cuti_aktif = $stmt_cuti->get_result()->fetch_assoc();
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nik']); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jabatan']); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($row['role_utama']); ?></span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($row['no_telepon']); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cuti_aktif['total'] > 0): ?>
                                            <span class="badge bg-warning text-dark">Sedang Cuti</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">
                                            Tahunan: <?php echo $row['sisa_cuti_tahunan']; ?> hari
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" onclick="lihatDetail('<?php echo $row['nik']; ?>')">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
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

                <!-- Export Options -->
                <div class="text-center mt-4">
                    <button class="btn btn-success me-2" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Export ke Excel
                    </button>
                    <button class="btn btn-danger" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-2"></i>Export ke PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Karyawan -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function lihatDetail(nik) {
    // Simulasi loading data detail
    const modalBody = document.getElementById('modalBody');
    modalBody.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data karyawan...</p>
        </div>
    `;
    
    // Di production, akan panggil AJAX untuk ambil data detail
    setTimeout(() => {
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>NIK:</strong> ${nik}</p>
                    <p><strong>Nama:</strong> Karyawan Contoh</p>
                    <p><strong>Divisi:</strong> IT</p>
                    <p><strong>Jabatan:</strong> Programmer</p>
                    <p><strong>Email:</strong> karyawan@example.com</p>
                </div>
                <div class="col-md-6">
                    <p><strong>No. Telepon:</strong> 08123456789</p>
                    <p><strong>Tanggal Masuk:</strong> 2024-01-15</p>
                    <p><strong>Sisa Cuti Tahunan:</strong> 8 hari</p>
                    <p><strong>Sisa Cuti Lustrum:</strong> 5 hari</p>
                    <p><strong>Status:</strong> <span class="badge bg-success">Aktif</span></p>
                </div>
            </div>
            <hr>
            <div class="mt-3">
                <h6>Riwayat Cuti 3 Bulan Terakhir</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Lama</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2024-10-01</td>
                                <td>Tahunan</td>
                                <td>2 hari</td>
                                <td><span class="badge bg-success">Disetujui</span></td>
                            </tr>
                            <tr>
                                <td>2024-09-15</td>
                                <td>Sakit</td>
                                <td>1 hari</td>
                                <td><span class="badge bg-success">Disetujui</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }, 1000);
    
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
}

function exportToExcel() {
    alert('Fitur export Excel akan diimplementasikan sesuai kebutuhan.');
}

function exportToPDF() {
    alert('Fitur export PDF akan diimplementasikan sesuai kebutuhan.');
}
</script>

<?php include '../includes/footer.php'; ?>