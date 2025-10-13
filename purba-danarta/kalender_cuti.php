<?php
$judul_halaman = 'Kalender Cuti';
include 'includes/header.php';

// Atur locale ke bahasa Indonesia
setlocale(LC_TIME, 'id_ID.utf8', 'id_ID.UTF-8', 'id_ID', 'IND', 'Indonesian_indonesia', 'Indonesian');

// --- LOGIKA PHP UNTUK KALENDER DINAMIS ---

// 1. Tentukan bulan dan tahun yang akan ditampilkan
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// 2. Buat objek DateTime untuk awal bulan
$date = new DateTime("$tahun-$bulan-01");

// 3. Dapatkan informasi bulan
$namaBulan = strftime('%B', $date->getTimestamp());
$jumlahHari = (int)$date->format('t');
$hariAwalBulan = (int)$date->format('w'); // 0 (Minggu) - 6 (Sabtu)

// 4. Tentukan bulan & tahun sebelumnya dan berikutnya untuk navigasi
$prevDate = (clone $date)->modify('-1 month');
$nextDate = (clone $date)->modify('+1 month');
$prevLink = "?bulan=" . $prevDate->format('m') . "&tahun=" . $prevDate->format('Y');
$nextLink = "?bulan=" . $nextDate->format('m') . "&tahun=" . $nextDate->format('Y');
$todayLink = "?bulan=" . date('m') . "&tahun=" . date('Y');

// 5. Pindahkan data cuti dari JS ke PHP agar dinamis
// Kunci array adalah format 'Y-m-d'
$leaveData = [
    '2025-12-01' => [
        ['name' => 'Caca Angga', 'type' => 'annual', 'reason' => 'Liburan keluarga', 'duration' => '3 hari (1-3 Des 2025)']
    ],
    '2025-12-02' => [
        ['name' => 'Caca Angga', 'type' => 'annual', 'reason' => 'Liburan keluarga', 'duration' => '3 hari (1-3 Des 2025)']
    ],
    '2025-12-03' => [
        ['name' => 'Caca Angga', 'type' => 'annual', 'reason' => 'Liburan keluarga', 'duration' => '3 hari (1-3 Des 2025)']
    ],
    '2025-12-10' => [
        ['name' => 'Aan Kundra', 'type' => 'sick', 'reason' => 'Demam tinggi', 'duration' => '1 hari (10 Des 2025)']
    ],
    '2025-12-12' => [
        ['name' => 'Naupal', 'type' => 'emergency', 'reason' => 'Keluarga sakit', 'duration' => '1 hari (12 Des 2025)']
    ],
    '2025-12-15' => [
        ['name' => 'Ady Beken', 'type' => 'annual', 'reason' => 'Wisata ke Bali', 'duration' => '3 hari (15-17 Des 2025)']
    ],
    '2025-12-16' => [
        ['name' => 'Ady Beken', 'type' => 'annual', 'reason' => 'Wisata ke Bali', 'duration' => '3 hari (15-17 Des 2025)']
    ],
    '2025-12-17' => [
        ['name' => 'Ady Beken', 'type' => 'annual', 'reason' => 'Wisata ke Bali', 'duration' => '3 hari (15-17 Des 2025)']
    ],
    '2025-12-18' => [
        ['name' => 'Caca Angga', 'type' => 'sick', 'reason' => 'Flu berat', 'duration' => '1 hari (18 Des 2025)']
    ],
    '2025-12-23' => [
        ['name' => 'Aan Kundra', 'type' => 'emergency', 'reason' => 'Perbaikan rumah', 'duration' => '1 hari (23 Des 2025)']
    ],
    '2025-12-25' => [
        ['name' => 'Naupal', 'type' => 'annual', 'reason' => 'Natal bersama keluarga', 'duration' => '2 hari (25-26 Des 2025)']
    ],
    '2025-12-26' => [
        ['name' => 'Naupal', 'type' => 'annual', 'reason' => 'Natal bersama keluarga', 'duration' => '2 hari (25-26 Des 2025)']
    ],
    '2025-12-31' => [
        ['name' => 'Ady Beken', 'type' => 'annual', 'reason' => 'Tahun Baru', 'duration' => '1 hari (31 Des 2025)']
    ]
];
?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/main.min.css' rel='stylesheet' />

<main class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Kalender Cuti Karyawan</h3>
                
                <div class="calendar-container">
                    <div class="calendar-header">
                        <div class="d-flex align-items-center">
                            <a href="<?php echo $prevLink; ?>" class="btn btn-outline-primary">&lt;</a>
                            <a href="<?php echo $nextLink; ?>" class="btn btn-outline-primary ms-1">&gt;</a>
                            <a href="<?php echo $todayLink; ?>" class="btn btn-outline-secondary ms-2">Hari Ini</a>
                        </div>
                        <h4 class="calendar-title mx-auto"><?php echo $namaBulan . ' ' . $tahun; ?></h4>
                        <div class="calendar-nav">
                            <button class="active">Bulan</button>
                            <button>Minggu</button>
                            <button>Hari</button>
                        </div>
                    </div>
                    
                    <div class="calendar-grid">
                        <div class="calendar-day-header">Minggu</div>
                        <div class="calendar-day-header">Senin</div>
                        <div class="calendar-day-header">Selasa</div>
                        <div class="calendar-day-header">Rabu</div>
                        <div class="calendar-day-header">Kamis</div>
                        <div class="calendar-day-header">Jumat</div>
                        <div class="calendar-day-header">Sabtu</div>
                        
                        <?php
                        // --- GENERATE GRID KALENDER DENGAN PHP ---
                        
                        // 1. Tambahkan sel kosong sebelum hari pertama
                        for ($i = 0; $i < $hariAwalBulan; $i++) {
                            echo '<div class="calendar-day other-month"></div>';
                        }
                        
                        // 2. Buat sel untuk setiap hari dalam bulan
                        for ($hari = 1; $hari <= $jumlahHari; $hari++) {
                            $tanggalPenuh = "$tahun-$bulan-" . str_pad($hari, 2, '0', STR_PAD_LEFT);
                            $classHariIni = (date('Y-m-d') == $tanggalPenuh) ? ' today' : '';

                            echo "<div class='calendar-day{$classHariIni}'>";
                            echo "<div class='day-number'>{$hari}</div>";
                            
                            // Cek jika ada data cuti pada tanggal ini
                            if (isset($leaveData[$tanggalPenuh])) {
                                echo '<ul class="event-list">';
                                foreach ($leaveData[$tanggalPenuh] as $event) {
                                    $namaPendek = strtok($event['name'], " ");
                                    $jenisCuti = explode(' ', $event['type'])[0];
                                    
                                    // Tentukan jenis cuti dari data PHP
                                    $tipeCutiTeks = '';
                                    switch ($event['type']) {
                                        case 'annual':
                                            $tipeCutiTeks = 'Cuti Tahunan';
                                            break;
                                        case 'sick':
                                            $tipeCutiTeks = 'Cuti Sakit';
                                            break;
                                        case 'emergency':
                                            $tipeCutiTeks = 'Cuti Darurat';
                                            break;
                                    }

                                    echo "<li class='event-item' data-bs-toggle='modal' data-bs-target='#detailModal' data-date='{$tanggalPenuh}'>{$event['name']} - {$tipeCutiTeks}</li>";
                                }
                                echo '</ul>';
                            }
                            
                            echo '</div>';
                        }

                        // 3. Tambahkan sel kosong setelah hari terakhir untuk melengkapi grid
                        $totalSel = $hariAwalBulan + $jumlahHari;
                        $sisaSel = (7 - ($totalSel % 7)) % 7;
                        for ($i = 0; $i < $sisaSel; $i++) {
                            echo '<div class="calendar-day other-month"></div>';
                        }
                        ?>
                    </div>
                    
                    <div class="legend mt-4">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #198754;"></div>
                            <span>Disetujui</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #fd7e14;"></div>
                            <span>Menunggu Persetujuan</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #dc3545;"></div>
                            <span>Ditolak</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Cuti Karyawan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="mb-3" id="modalDate">Tanggal:</h6>
                <div class="employee-list" id="employeeList">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data cuti sekarang berada di PHP, kita pindahkan ke JS untuk modal
    const leaveData = <?php echo json_encode($leaveData); ?>;

    const detailModal = document.getElementById('detailModal');
    if (detailModal) {
        detailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const date = button.getAttribute('data-date');
            
            const dateObj = new Date(date + 'T00:00:00'); // Tambah T00:00:00 untuk menghindari masalah zona waktu
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const formattedDate = dateObj.toLocaleDateString('id-ID', options);
            
            document.getElementById('modalDate').textContent = `Tanggal: ${formattedDate}`;
            
            const employeeList = document.getElementById('employeeList');
            employeeList.innerHTML = '';
            
            if (leaveData[date]) {
                leaveData[date].forEach(employee => {
                    const employeeItem = document.createElement('div');
                    employeeItem.className = 'employee-item';
                    
                    employeeItem.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="employee-name">${employee.name}</div>
                                <div class="text-muted small">${employee.reason}</div>
                            </div>
                            <span class="badge ${employee.type === 'annual' ? 'bg-success' : employee.type === 'sick' ? 'bg-danger' : 'bg-warning text-dark'}">
                                ${employee.type === 'annual' ? 'Disetujui' : employee.type === 'sick' ? 'Ditolak' : 'Menunggu Persetujuan'}
                            </span>
                        </div>
                        <div class="small text-muted">Durasi: ${employee.duration}</div>
                    `;
                    
                    employeeList.appendChild(employeeItem);
                });
            } else {
                employeeList.innerHTML = '<p class="text-center text-muted">Tidak ada karyawan yang cuti pada tanggal ini.</p>';
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>