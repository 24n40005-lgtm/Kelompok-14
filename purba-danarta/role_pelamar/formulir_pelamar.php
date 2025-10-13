<?php
$judul_halaman = "Lengkapi Data Lamaran";
include '../includes/header.php';

// Logika untuk redirect jika profil sudah lengkap
// ...
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Formulir Pendaftaran Lamaran Kerja</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Silakan lengkapi data di bawah ini untuk melanjutkan proses lamaran.</p>
                    <form action="proses_formulir_lamaran.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="posisi_dilamar" class="form-label">Posisi yang Dilamar</label>
                            <input type="text" class="form-control" id="posisi_dilamar" name="posisi_dilamar" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat_ktp" class="form-label">Alamat Sesuai KTP</label>
                            <textarea class="form-control" id="alamat_ktp" name="alamat_ktp" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="alamat_domisili" class="form-label">Alamat Domisili</label>
                            <textarea class="form-control" id="alamat_domisili" name="alamat_domisili" rows="2" required></textarea>
                        </div>
                         <div class="mb-3">
                            <label for="cv" class="form-label">Unggah CV (PDF)</label>
                            <input class="form-control" type="file" id="cv" name="cv" required>
                        </div>
                        <div class="d-grid">
                           <button type="submit" class="btn btn-primary">Kirim Lamaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>