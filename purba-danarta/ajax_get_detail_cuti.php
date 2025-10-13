<?php
// File: ajax_get_detail_cuti.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/koneksi.php';

$cuti_id = $_GET['id'] ?? '';

if (empty($cuti_id)) {
    echo '<div class="alert alert-danger">ID cuti tidak valid</div>';
    exit();
}

// Ambil detail cuti
$sql = "SELECT pc.*, k.nama_lengkap, k.nik, k.divisi, k.jabatan, k.email, k.no_telepon,
               jc.nama_jenis, jc.maks_hari, jc.deskripsi,
               DATE_FORMAT(pc.tanggal_pengajuan, '%d-%m-%Y %H:%i') as tgl_pengajuan,
               DATE_FORMAT(pc.tanggal_persetujuan, '%d-%m-%Y %H:%i') as tgl_persetujuan
        FROM pengajuan_cuti pc 
        JOIN karyawan k ON pc.karyawan_nik = k.nik 
        JOIN jenis_cuti jc ON pc.jenis_cuti_id = jc.id 
        WHERE pc.id = ?";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $cuti_id);
$stmt->execute();
$cuti = $stmt->get_result()->fetch_assoc();

if (!$cuti) {
    echo '<div class="alert alert-danger">Data cuti tidak ditemukan</div>';
    exit();
}
?>

<div class="row">
    <div class="col-md-6">
        <h6>Informasi Karyawan</h6>
        <p><strong>Nama:</strong> <?php echo htmlspecialchars($cuti['nama_lengkap']); ?></p>
        <p><strong>NIK:</strong> <?php echo htmlspecialchars($cuti['nik']); ?></p>
        <p><strong>Divisi:</strong> <?php echo htmlspecialchars($cuti['divisi']); ?></p>
        <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($cuti['jabatan']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($cuti['email']); ?></p>
        <p><strong>No. Telepon:</strong> <?php echo htmlspecialchars($cuti['no_telepon']); ?></p>
    </div>
    <div class="col-md-6">
        <h6>Informasi Cuti</h6>
        <p><strong>ID Cuti:</strong> CT-<?php echo $cuti['id']; ?></p>
        <p><strong>Jenis Cuti:</strong> <?php echo htmlspecialchars($cuti['nama_jenis']); ?></p>
        <p><strong>Maksimal Hari:</strong> <?php echo $cuti['maks_hari'] ? $cuti['maks_hari'] . ' hari' : 'Tidak terbatas'; ?></p>
        <p><strong>Tanggal Pengajuan:</strong> <?php echo $cuti['tgl_pengajuan']; ?></p>
        <p><strong>Status:</strong> 
            <span class="badge bg-<?php 
                switch($cuti['status']) {
                    case 'Disetujui': echo 'success'; break;
                    case 'Ditolak': echo 'danger'; break;
                    default: echo 'warning text-dark';
                }
            ?>"><?php echo $cuti['status']; ?></span>
        </p>
        <?php if ($cuti['disetujui_oleh']): ?>
        <p><strong>Disetujui Oleh:</strong> <?php echo htmlspecialchars($cuti['disetujui_oleh']); ?></p>
        <p><strong>Tanggal Persetujuan:</strong> <?php echo $cuti['tgl_persetujuan']; ?></p>
        <?php endif; ?>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6">
        <h6>Periode Cuti</h6>
        <p><strong>Tanggal Mulai:</strong> <?php echo date('d-m-Y', strtotime($cuti['tanggal_mulai'])); ?></p>
        <p><strong>Tanggal Selesai:</strong> <?php echo date('d-m-Y', strtotime($cuti['tanggal_selesai'])); ?></p>
        <p><strong>Lama Cuti:</strong> <?php echo $cuti['lama_hari']; ?> hari</p>
    </div>
    <div class="col-md-6">
        <h6>Keterangan & Alasan</h6>
        <div class="border rounded p-3 bg-light">
            <strong>Keterangan Karyawan:</strong><br>
            <?php echo nl2br(htmlspecialchars($cuti['keterangan'])); ?>
        </div>
        <?php if ($cuti['alasan_penolakan']): ?>
        <div class="border rounded p-3 bg-light mt-2">
            <strong class="text-danger">Alasan Penolakan:</strong><br>
            <?php echo nl2br(htmlspecialchars($cuti['alasan_penolakan'])); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($cuti['status'] === 'Pending'): ?>
<hr>
<div class="text-center">
    <button class="btn btn-success me-2" onclick="setujuiCuti(<?php echo $cuti['id']; ?>, '<?php echo htmlspecialchars($cuti['nama_lengkap']); ?>')">
        <i class="fas fa-check me-2"></i>Setujui Cuti
    </button>
    <button class="btn btn-danger" onclick="tolakCuti(<?php echo $cuti['id']; ?>, '<?php echo htmlspecialchars($cuti['nama_lengkap']); ?>')">
        <i class="fas fa-times me-2"></i>Tolak Cuti
    </button>
</div>
<?php endif; ?>