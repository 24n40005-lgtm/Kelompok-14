<?php
// File: role_karyawan/riwayat_khl.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$allowed_roles = ['Karyawan', 'Penanggung Jawab', 'Administrator', 'Direksi'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header('Location: ../login.php');
    exit();
}

// Tentukan judul halaman
$is_all = isset($_GET['all']) && in_array($_SESSION['role'], ['Penanggung Jawab', 'Administrator', 'Direksi']);
if ($is_all) {
    $judul_halaman = "Riwayat KHL Karyawan";
} else {
    $judul_halaman = "Riwayat KHL Pribadi";
}

include '../includes/header.php';
include '../includes/koneksi.php';

$nik = $_SESSION['nik'];
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4"><?php echo $judul_halaman; ?></h3>
                
                <?php if (!$is_all): ?>
                <div class="alert alert-info">
                    <strong>Halo, <?php echo $_SESSION['nama_lengkap']; ?></strong> (NIK: <?php echo $nik; ?>)
                    - Berikut adalah riwayat pengajuan KHL Anda.
                </div>
                <?php endif; ?>
                
                <!-- Filter dan Pencarian -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <?php if (in_array($_SESSION['role'], ['Penanggung Jawab', 'Administrator', 'Direksi'])): ?>
                        <div class="btn-group" role="group">
                            <a href="riwayat_khl.php" class="btn <?php echo !$is_all ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Pribadi
                            </a>
                            <a href="riwayat_khl.php?all=1" class="btn <?php echo $is_all ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Semua Karyawan
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <input type="month" class="form-control" value="<?php echo date('Y-m'); ?>" id="filterBulan">
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Cari proyek..." id="searchInput">
                            <button class="btn btn-custom-green" type="button" id="searchBtn">
                                <i class="fas fa-search"></i> Cari
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
                                <?php if ($is_all): ?>
                                    <th>Nama Karyawan</th>
                                    <th>NIK</th>
                                <?php endif; ?>
                                <th>Tanggal Kerja</th>
                                <th>Jam Kerja</th>
                                <th>Tanggal Libur Pengganti</th>
                                <th>Proyek/Pekerjaan</th>
                                <th>Status</th>
                                <?php if ($is_all && in_array($_SESSION['role'], ['Penanggung Jawab', 'Administrator'])): ?>
                                <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($is_all) {
                                // Ambil semua KHL untuk role PJ, Admin, Direksi
                                $sql = "SELECT pk.*, k.nama_lengkap, k.nik as karyawan_nik 
                                        FROM pengajuan_khl pk 
                                        JOIN karyawan k ON pk.karyawan_nik = k.nik 
                                        ORDER BY pk.tanggal_pengajuan DESC";
                            } else {
                                // Ambil KHL pribadi
                                $sql = "SELECT * FROM pengajuan_khl WHERE karyawan_nik = ? ORDER BY tanggal_pengajuan DESC";
                            }
                            
                            $stmt = $koneksi->prepare($sql);
                            if (!$is_all) {
                                $stmt->bind_param("s", $nik);
                            }
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    // PERBAIKAN: Sesuaikan status dengan database
                                    $status_badge = '';
                                    switch ($row['status']) {
                                        case 'Disetujui':
                                            $status_badge = '<span class="badge bg-success">Disetujui</span>';
                                            break;
                                        case 'Ditolak':
                                            $status_badge = '<button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#alasanModal" 
                                                data-alasan="' . htmlspecialchars($row['alasan_penolakan']) . '">Ditolak</button>';
                                            break;
                                        default:
                                            $status_badge = '<span class="badge bg-warning text-dark">Menunggu</span>';
                                    }
                            ?>
                            <tr>
                                <td class="text-center">KHL-<?php echo $row['id']; ?></td>
                                <?php if ($is_all): ?>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['karyawan_nik']); ?></td>
                                <?php endif; ?>
                                <td><?php echo date('d-m-Y', strtotime($row['tanggal_kerja'])); ?></td>
                                <td><?php echo substr($row['jam_mulai'], 0, 5); ?> - <?php echo substr($row['jam_selesai'], 0, 5); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['tanggal_libur_pengganti'])); ?></td>
                                <td><?php echo htmlspecialchars($row['proyek']); ?></td>
                                <td class="text-center"><?php echo $status_badge; ?></td>
                                <?php if ($is_all && in_array($_SESSION['role'], ['Penanggung Jawab', 'Administrator']) && $row['status'] == 'Menunggu'): ?>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success" onclick="setujuiKHL(<?php echo $row['id']; ?>)">Setujui</button>
                                        <button class="btn btn-danger" onclick="tolakKHL(<?php echo $row['id']; ?>)">Tolak</button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php
                                }
                            } else {
                                $colspan = $is_all ? 7 : 5;
                                if ($is_all && in_array($_SESSION['role'], ['Penanggung Jawab', 'Administrator'])) $colspan++;
                                echo "<tr><td colspan='$colspan' class='text-center'>Tidak ada data KHL</td></tr>";
                            }
                            ?>
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

<!-- Modal Alasan Penolakan -->
<div class="modal fade" id="alasanModal" tabindex="-1" aria-labelledby="alasanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alasanModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Alasan Penolakan KHL
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modalAlasanText">Memuat alasan...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal Alasan
    var alasanModal = document.getElementById('alasanModal');
    alasanModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var alasan = button.getAttribute('data-alasan');
        var modalBody = alasanModal.querySelector('.modal-body p#modalAlasanText');
        modalBody.textContent = alasan || 'Tidak ada alasan yang diberikan.';
    });
});

function setujuiKHL(khlId) {
    if (confirm('Apakah Anda yakin ingin menyetujui KHL ini?')) {
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
                location.reload();
            } else {
                alert('Gagal menyetujui KHL: ' + data.message);
            }
        });
    }
}

function tolakKHL(khlId) {
    var alasan = prompt('Masukkan alasan penolakan:');
    if (alasan !== null) {
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
                location.reload();
            } else {
                alert('Gagal menolak KHL: ' + data.message);
            }
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>