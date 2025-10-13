<?php
// File: role_karyawan/form_khl.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user berhak mengajukan KHL
$allowed_roles = ['Karyawan', 'Penanggung Jawab', 'Administrator', 'Direksi'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Pengajuan KHL';
include '../includes/header.php';
?>

<div class="main-background">
    <div class="overlay d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-container">
                        <h3 class="text-center fw-bold mb-4">Pengajuan Kerja Hari Libur (KHL)</h3>
                        
                        <p>Halo, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong> (NIK: <strong><?php echo $_SESSION['nik']; ?></strong>)</p>
                        
                        <form action="../proses_pengajuan_khl.php" method="POST" id="formKHL">
                            <div class="mb-3">
                                <label for="proyek" class="form-label">Proyek/Pekerjaan yang Dilakukan:</label>
                                <textarea class="form-control" id="proyek" name="proyek" rows="3" placeholder="Jelaskan pekerjaan yang akan Anda lakukan" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_kerja" class="form-label">Tanggal Kerja:</label>
                                    <input type="date" class="form-control" id="tanggal_kerja" name="tanggal_kerja" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_libur_pengganti" class="form-label">Tanggal Libur Pengganti:</label>
                                    <input type="date" class="form-control" id="tanggal_libur_pengganti" name="tanggal_libur_pengganti" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="jam_mulai" class="form-label">Jam Mulai:</label>
                                    <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="jam_selesai" class="form-label">Jam Selesai:</label>
                                    <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                                </div>
                            </div>

                            <!-- Validasi Jam -->
                            <div class="mb-3">
                                <div class="alert alert-info py-2">
                                    <strong>Durasi Kerja: <span id="durasi_jam">0</span> jam</strong>
                                </div>
                            </div>
                            
                            <div class="d-grid mt-4">
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
    const tanggalKerja = document.getElementById('tanggal_kerja');
    const tanggalLibur = document.getElementById('tanggal_libur_pengganti');
    const jamMulai = document.getElementById('jam_mulai');
    const jamSelesai = document.getElementById('jam_selesai');
    const durasiJam = document.getElementById('durasi_jam');
    const submitBtn = document.getElementById('submitBtn');
    
    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    tanggalKerja.min = today;
    tanggalLibur.min = today;
    
    // Validasi: tanggal libur harus setelah tanggal kerja
    tanggalKerja.addEventListener('change', function() {
        const minLiburDate = new Date(this.value);
        minLiburDate.setDate(minLiburDate.getDate() + 1);
        tanggalLibur.min = minLiburDate.toISOString().split('T')[0];
    });

    // Hitung durasi jam
    jamMulai.addEventListener('change', hitungDurasi);
    jamSelesai.addEventListener('change', hitungDurasi);

    function hitungDurasi() {
        if (jamMulai.value && jamSelesai.value) {
            const start = new Date(`1970-01-01T${jamMulai.value}`);
            const end = new Date(`1970-01-01T${jamSelesai.value}`);
            const diffMs = end - start;
            const diffHours = diffMs / (1000 * 60 * 60);
            
            if (diffHours <= 0) {
                alert('Jam selesai harus setelah jam mulai');
                jamSelesai.value = '';
                durasiJam.textContent = '0';
                submitBtn.disabled = true;
            } else {
                durasiJam.textContent = diffHours.toFixed(1);
                submitBtn.disabled = false;
            }
        } else {
            durasiJam.textContent = '0';
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>