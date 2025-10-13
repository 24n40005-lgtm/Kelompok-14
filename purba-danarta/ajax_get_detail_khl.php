<?php
// File: ajax_get_detail_khl.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/koneksi.php';

$khl_id = $_GET['id'] ?? '';

if (empty($khl_id)) {
    echo '<div class="alert alert-danger">ID KHL tidak valid</div>';
    exit();
}

// Ambil detail KHL
$sql = "SELECT pk.*, k.nama_lengkap, k.nik, k.divisi, k.jabatan, k.email, k.no_telepon,
               DATE_FORMAT(pk.tanggal_pengajuan, '%d-%m-%Y %H:%i') as tgl_pengajuan,
               DATE_FORMAT(pk.tanggal_persetujuan, '%d-%m-%Y %H:%i') as tgl_persetujuan
        FROM pengajuan_khl pk 
        JOIN karyawan k ON pk.karyawan_nik = k.nik 
        WHERE pk.id = ?";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $khl_id);
$stmt->execute();
$khl = $stmt->get_result()->fetch_assoc();

if (!$khl) {
    echo '<div class="alert alert-danger">Data KHL tidak ditemukan</div>';
    exit();
}
?>

<div class="row">
    <div class="col-md-6">
        <h6>Informasi Karyawan</h6>
        <p><strong>Nama:</strong> <?php echo htmlspecialchars($khl['nama_lengkap']); ?></p>
        <p><strong>NIK:</strong> <?php echo htmlspecialchars($khl['nik']); ?></p>
        <p><strong>Divisi:</strong> <?php echo htmlspecialchars($khl['divisi']); ?></p>
        <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($khl['jabatan']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($khl['email']); ?></p>
        <p><strong>No. Telepon:</strong> <?php echo htmlspecialchars($khl['no_telepon']); ?></p>
    </div>
    <div class="col-md-6">
        <h6>Informasi KHL</h6>
        <p><strong>ID KHL:</strong> KHL-<?php echo $khl['id']; ?></p>
        <p><strong>Tanggal Pengajuan:</strong> <?php echo $khl['tgl_pengajuan']; ?></p>
        <p><strong>Status:</strong> 
            <span class="badge bg-<?php 
                switch($khl['status']) {
                    case 'Disetujui': echo 'success'; break;
                    case 'Ditolak': echo 'danger'; break;
                    default: echo 'warning text-dark';
                }
            ?>"><?php echo $khl['status']; ?></span>
        </p>
        <?php if ($khl['disetujui_oleh']): ?>
        <p><strong>Disetujui Oleh:</strong> <?php echo htmlspecialchars($khl['disetujui_oleh']); ?></p>
        <p><strong>Tanggal Persetujuan:</strong> <?php echo $khl['tgl_persetujuan']; ?></p>
        <?php endif; ?>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6">
        <h6>Jadwal Kerja</h6>
        <p><strong>Tanggal Kerja:</strong> <?php echo date('d-m-Y', strtotime($khl['tanggal_kerja'])); ?></p>
        <p><strong>Jam Mulai:</strong> <?php echo substr($khl['jam_mulai'], 0, 5); ?></p>
        <p><strong>Jam Selesai:</strong> <?php echo substr($khl['jam_selesai'], 0, 5); ?></p>
        <p><strong>Durasi:</strong> 
            <?php
            $start = new DateTime($khl['jam_mulai']);
            $end = new DateTime($khl['jam_selesai']);
            $diff = $start->diff($end);
            echo $diff->h . ' jam ' . $diff->i . ' menit';
            ?>
        </p>
    </div>
    <div class="col-md-6">
        <h6>Libur Pengganti</h6>
        <p><strong>Tanggal Libur:</strong> <?php echo date('d-m-Y', strtotime($khl['tanggal_libur_pengganti'])); ?></p>
        <p><strong>Selisih Hari:</strong> 
            <?php
            $tgl_kerja = new DateTime($khl['tanggal_kerja']);
            $tgl_libur = new DateTime($khl['tanggal_libur_pengganti']);
            $selisih = $tgl_kerja->diff($tgl_libur)->days;
            echo $selisih . ' hari setelah kerja';
            ?>
        </p>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-12">
        <h6>Detail Pekerjaan</h6>
        <div class="border rounded p-3 bg-light">
            <strong>Proyek/Pekerjaan:</strong><br>
            <?php echo nl2br(htmlspecialchars($khl['proyek'])); ?>
        </div>
        <?php if ($khl['alasan_penolakan']): ?>
        <div class="border rounded p-3 bg-light mt-2">
            <strong class="text-danger">Alasan Penolakan:</strong><br>
            <?php echo nl2br(htmlspecialchars($khl['alasan_penolakan'])); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($khl['status'] === 'Pending'): ?>
<hr>
<div class="text-center">
    <button class="btn btn-success me-2" onclick="setujuiKHL(<?php echo $khl['id']; ?>, '<?php echo htmlspecialchars($khl['nama_lengkap']); ?>')">
        <i class="fas fa-check me-2"></i>Setujui KHL
    </button>
    <button class="btn btn-danger" onclick="tolakKHL(<?php echo $khl['id']; ?>, '<?php echo htmlspecialchars($khl['nama_lengkap']); ?>')">
        <i class="fas fa-times me-2"></i>Tolak KHL
    </button>
</div>
<?php endif; ?>