<?php
// File: role_karyawan/form_cuti.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user adalah karyawan, PJ, Admin, atau Direksi (bisa mengajukan cuti)
$allowed_roles = ['Karyawan', 'Penanggung Jawab', 'Administrator', 'Direksi'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Pengajuan Cuti';
include '../includes/header.php';

// Ambil data karyawan
include '../includes/koneksi.php';
$nik = $_SESSION['nik'];

$sql_karyawan = "SELECT sisa_cuti_tahunan, sisa_cuti_lustrum FROM karyawan WHERE nik = ?";
$stmt = $koneksi->prepare($sql_karyawan);
$stmt->bind_param("s", $nik);
$stmt->execute();
$karyawan = $stmt->get_result()->fetch_assoc();

// Ambil jenis cuti
$sql_jenis_cuti = "SELECT * FROM jenis_cuti ORDER BY id";
$jenis_cuti = $koneksi->query($sql_jenis_cuti);
?>

<div class="main-background">
    <div class="overlay d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="content-container">
                        <h3 class="text-center fw-bold mb-4">Formulir Pengajuan Cuti</h3>

                        <!-- Info Sisa Cuti -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="card text-center bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Sisa Cuti Tahunan</h6>
                                        <p class="card-text fs-4 fw-bold"><?php echo $karyawan['sisa_cuti_tahunan']; ?> Hari</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Sisa Cuti Lustrum</h6>
                                        <p class="card-text fs-4 fw-bold"><?php echo $karyawan['sisa_cuti_lustrum']; ?> Hari</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p>Halo, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong> (NIK: <strong><?php echo $nik; ?></strong>)</p>
                        
                        <form action="../proses_pengajuan_cuti.php" method="POST" id="formCuti">
                            <div class="mb-3">
                                <label for="jenis_cuti" class="form-label">Jenis Cuti:</label>
                                <select class="form-select" id="jenis_cuti" name="jenis_cuti_id" required>
                                    <option value="" selected disabled>Pilih Jenis Cuti</option>
                                    <?php while ($row = $jenis_cuti->fetch_assoc()): ?>
                                        <option value="<?php echo $row['id']; ?>" data-max="<?php echo $row['maks_hari']; ?>">
                                            <?php echo htmlspecialchars($row['nama_jenis']); ?>
                                            <?php if ($row['maks_hari']): ?> (Maks <?php echo $row['maks_hari']; ?> hari)<?php endif; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai:</label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai:</label>
                                    <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                                </div>
                            </div>

                            <!-- Info Lama Hari -->
                            <div class="mb-3">
                                <div class="alert alert-info py-2">
                                    <strong>Lama Cuti: <span id="lama_hari">0</span> hari</strong>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="keterangan" class="form-label">Keterangan:</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Contoh: Keperluan keluarga di luar kota" required></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-custom-green btn-lg" id="submitBtn">Submit Pengajuan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalSelesai = document.getElementById('tanggal_selesai');
    const jenisCuti = document.getElementById('jenis_cuti');
    const lamaHariSpan = document.getElementById('lama_hari');
    const submitBtn = document.getElementById('submitBtn');
    const sisaTahunan = <?php echo $karyawan['sisa_cuti_tahunan']; ?>;
    const sisaLustrum = <?php echo $karyawan['sisa_cuti_lustrum']; ?>;
    
    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    tanggalMulai.min = today;
    tanggalSelesai.min = today;
    
    // Update min end date when start date changes
    tanggalMulai.addEventListener('change', function() {
        tanggalSelesai.min = this.value;
        hitungLamaHari();
    });
    
    tanggalSelesai.addEventListener('change', hitungLamaHari);
    jenisCuti.addEventListener('change', hitungLamaHari);
    
    function hitungLamaHari() {
        let isValid = true;
        
        if (tanggalMulai.value && tanggalSelesai.value) {
            const start = new Date(tanggalMulai.value);
            const end = new Date(tanggal_selesai.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            lamaHariSpan.textContent = diffDays;
            
            // Validasi berdasarkan jenis cuti
            if (jenisCuti.value) {
                const maxDays = jenisCuti.options[jenisCuti.selectedIndex].getAttribute('data-max');
                const jenisCutiText = jenisCuti.options[jenisCuti.selectedIndex].text;
                
                if (maxDays && diffDays > parseInt(maxDays)) {
                    alert(`Jenis cuti ${jenisCutiText} maksimal ${maxDays} hari`);
                    tanggalSelesai.value = '';
                    isValid = false;
                }
                
                // Validasi sisa cuti untuk cuti tahunan
                if (jenisCutiText.includes('Tahunan') && diffDays > sisaTahunan) {
                    alert(`Sisa cuti tahunan hanya ${sisaTahunan} hari. Anda mengajukan ${diffDays} hari.`);
                    tanggalSelesai.value = '';
                    isValid = false;
                }
                
                // Validasi sisa cuti untuk cuti lustrum
                if (jenisCutiText.includes('Lustrum') && diffDays > sisaLustrum) {
                    alert(`Sisa cuti lustrum hanya ${sisaLustrum} hari. Anda mengajukan ${diffDays} hari.`);
                    tanggalSelesai.value = '';
                    isValid = false;
                }
            }
        } else {
            lamaHariSpan.textContent = '0';
        }
        
        submitBtn.disabled = !isValid;
    }
});
</script>

<?php include '../includes/footer.php'; ?>