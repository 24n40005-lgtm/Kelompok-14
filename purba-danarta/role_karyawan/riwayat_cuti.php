<?php
// File: role_karyawan/riwayat_cuti.php

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
    $judul_halaman = "Riwayat Cuti Karyawan";
} else {
    $judul_halaman = "Riwayat Cuti Pribadi";
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
                    - Berikut adalah riwayat pengajuan cuti Anda.
                </div>
                <?php endif; ?>
                
                <!-- Filter dan Pencarian -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <?php if (in_array($_SESSION['role'], ['Penanggung Jawab', 'Administrator', 'Direksi'])): ?>
                        <div class="btn-group" role="group">
                            <a href="riwayat_cuti.php" class="btn <?php echo !$is_all ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Pribadi
                            </a>
                            <a href="riwayat_cuti.php?all=1" class="btn <?php echo $is_all ? 'btn-primary' : 'btn-outline-primary'; ?>">
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
                            <input type="text" class="form-control" placeholder="Cari jenis cuti..." id="searchInput">
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
                                <th>Tanggal Pengajuan</th>
                                <th>Periode Cuti</th>
                                <th>Lama</th>
                                <th>Jenis Cuti</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <?php if ($is_all && in_array($_SESSION['role'], ['Penanggung Jawab', 'Administrator'])): ?>
                                <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($is_all) {
                                // Ambil semua cuti untuk role PJ, Admin, Direksi
                                $sql = "SELECT pc.*, k.nama_lengkap, k.nik as karyawan_nik, jc.nama_jenis 
                                        FROM pengajuan_cuti pc 
                                        JOIN karyawan k ON pc.karyawan_nik = k.nik 
                                        JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
                                        ORDER BY pc.tanggal_pengajuan DESC";
                            } else {
                                // Ambil cuti pribadi
                                $sql = "SELECT pc.*, jc.nama_jenis 
                                        FROM pengajuan_cuti pc 
                                        JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
                                        WHERE pc.karyawan_nik = ? 
                                        ORDER BY pc.tanggal_pengajuan DESC";
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
                                <td class="text-center">CT-<?php echo $row['id']; ?></td>
                                <?php if ($is_all): ?>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['karyawan_nik']); ?></td>
                                <?php endif; ?>
                                <td><?php echo date('d-m-Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['tanggal_mulai'])); ?> s/d <?php echo date('d-m-Y', strtotime($row['tanggal_selesai'])); ?></td>
                                <td><?php echo $row['lama_hari']; ?> Hari</td>
                                <td><?php echo htmlspecialchars($row['nama_jenis']); ?></td>
                                <td class="text-center"><?php echo $status_badge; ?></td>
                                <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                <?php if ($is_all && in_array($_SESSION['role'], ['Penanggung Jawab', 'Administrator']) && $row['status'] == 'Menunggu'): ?>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success" onclick="setujuiCuti(<?php echo $row['id']; ?>)">Setujui</button>
                                        <button class="btn btn-danger" onclick="tolakCuti(<?php echo $row['id']; ?>)">Tolak</button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php
                                }
                            } else {
                                $colspan = $is_all ? 8 : 6;
                                if ($is_all && in_array($_SESSION['role'], ['Penanggung Jawab', 'Administrator'])) $colspan++;
                                echo "<tr><td colspan='$colspan' class='text-center'>Tidak ada data cuti</td></tr>";
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
                    <i class="fas fa-info-circle me-2"></i>Alasan Penolakan Cuti
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

    // Filter bulan
    document.getElementById('filterBulan').addEventListener('change', function() {
        // Implement filter by month
        console.log('Filter by month:', this.value);
    });

    // Search
    document.getElementById('searchBtn').addEventListener('click', function() {
        var searchText = document.getElementById('searchInput').value;
        // Implement search
        console.log('Search:', searchText);
    });
});

function setujuiCuti(cutiId) {
    if (confirm('Apakah Anda yakin ingin menyetujui cuti ini?')) {
        // AJAX call to approve cuti
        fetch('../proses_persetujuan_cuti.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'cuti_id=' + cutiId + '&action=approve'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menyetujui cuti: ' + data.message);
            }
        });
    }
}

function tolakCuti(cutiId) {
    var alasan = prompt('Masukkan alasan penolakan:');
    if (alasan !== null) {
        // AJAX call to reject cuti
        fetch('../proses_persetujuan_cuti.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'cuti_id=' + cutiId + '&action=reject&alasan=' + encodeURIComponent(alasan)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menolak cuti: ' + data.message);
            }
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>