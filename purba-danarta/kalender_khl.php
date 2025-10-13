<?php
$judul_halaman = 'Kalender KHL';
include 'includes/header.php';

// Atur locale ke bahasa Indonesia
setlocale(LC_TIME, 'id_ID.utf8', 'id_ID.UTF-8', 'id_ID', 'IND', 'Indonesian_indonesia', 'Indonesian');

// --- LOGIKA PHP UNTUK KALENDER DINAMIS ---

// 1. Tentukan bulan dan tahun
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// 2. Buat objek DateTime
$date = new DateTime("$tahun-$bulan-01");

// 3. Dapatkan informasi bulan
$namaBulan = strftime('%B', $date->getTimestamp());
$jumlahHari = (int)$date->format('t');
$hariAwalBulan = (int)$date->format('w');

// 4. Tentukan link navigasi
$prevDate = (clone $date)->modify('-1 month');
$nextDate = (clone $date)->modify('+1 month');
$prevLink = "?bulan=" . $prevDate->format('m') . "&tahun=" . $prevDate->format('Y');
$nextLink = "?bulan=" . $nextDate->format('m') . "&tahun=" . $nextDate->format('Y');
$todayLink = "?bulan=" . date('m') . "&tahun=" . date('Y');

// 5. Data KHL (diasumsikan ada beberapa hari libur di bulan Desember 2025)
$khlData = [
    '2025-12-01' => [
        ['name' => 'Aan Kundra', 'status' => 'approved', 'workDate' => '2025-12-01', 'startTime' => '08:00', 'endTime' => '17:00', 'substituteDate' => '2025-12-08', 'reason' => 'Penyelesaian proyek akhir tahun']
    ],
    '2025-12-06' => [
        ['name' => 'Caca Angga', 'status' => 'pending', 'workDate' => '2025-12-06', 'startTime' => '09:00', 'endTime' => '15:00', 'substituteDate' => '2025-12-13', 'reason' => 'Inventarisasi aset perusahaan']
    ],
    '2025-12-13' => [
        ['name' => 'Naupal', 'status' => 'approved', 'workDate' => '2025-12-13', 'startTime' => '08:30', 'endTime' => '16:00', 'substituteDate' => '2025-12-20', 'reason' => 'Rapat evaluasi triwulan']
    ],
    '2025-12-20' => [
        ['name' => 'Ady Beken', 'status' => 'rejected', 'workDate' => '2025-12-20', 'startTime' => '10:00', 'endTime' => '14:00', 'substituteDate' => '2025-12-27', 'reason' => 'Training internal departemen']
    ],
    '2025-12-24' => [
        ['name' => 'Aan Kundra', 'status' => 'pending', 'workDate' => '2025-12-24', 'startTime' => '08:00', 'endTime' => '12:00', 'substituteDate' => '2025-12-31', 'reason' => 'Persiapan laporan akhir tahun']
    ],
    '2025-12-25' => [
        ['name' => 'Naupal', 'status' => 'approved', 'workDate' => '2025-12-25', 'startTime' => '09:00', 'endTime' => '17:00', 'substituteDate' => '2026-01-03', 'reason' => 'Pemeliharaan sistem IT']
    ],
    '2025-12-26' => [
        ['name' => 'Naupal', 'status' => 'approved', 'workDate' => '2025-12-26', 'startTime' => '09:00', 'endTime' => '17:00', 'substituteDate' => '2026-01-04', 'reason' => 'Pemeliharaan sistem IT'],
        ['name' => 'Caca Angga', 'status' => 'approved', 'workDate' => '2025-12-26', 'startTime' => '08:00', 'endTime' => '16:00', 'substituteDate' => '2026-01-05', 'reason' => 'Audit internal departemen']
    ],
    '2025-12-31' => [
        ['name' => 'Ady Beken', 'status' => 'approved', 'workDate' => '2025-12-31', 'startTime' => '08:00', 'endTime' => '12:00', 'substituteDate' => '2026-01-10', 'reason' => 'Penutupan buku akhir tahun']
    ]
];

// Data hari libur (contoh)
$holidays = [
    '2025-12-06', '2025-12-07', '2025-12-13', '2025-12-14',
    '2025-12-20', '2025-12-21', '2025-12-25', '2025-12-26',
    '2025-12-27', '2025-12-28'
];
?>

<main class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Kalender KHL Karyawan</h3>
                
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
                        
                        for ($i = 0; $i < $hariAwalBulan; $i++) {
                            echo '<div class="calendar-day other-month"></div>';
                        }
                        
                        for ($hari = 1; $hari <= $jumlahHari; $hari++) {
                            $tanggalPenuh = "$tahun-$bulan-" . str_pad($hari, 2, '0', STR_PAD_LEFT);
                            $classHariIni = (date('Y-m-d') == $tanggalPenuh) ? ' today' : '';
                            $isHoliday = in_array($tanggalPenuh, $holidays) ? ' holiday' : '';

                            echo "<div class='calendar-day{$classHariIni}{$isHoliday}'>";
                            echo "<div class='day-number'>{$hari}</div>";

                            if ($isHoliday) {
                                echo '<div class="day-holiday">Hari Libur</div><div class="holiday-indicator"></div>';
                            }
                            
                            if (isset($khlData[$tanggalPenuh])) {
                                echo '<ul class="event-list">';
                                foreach ($khlData[$tanggalPenuh] as $event) {
                                    $statusText = '';
                                    switch ($event['status']) {
                                        case 'approved': $statusText = 'Disetujui'; break;
                                        case 'pending': $statusText = 'Menunggu'; break;
                                        case 'rejected': $statusText = 'Ditolak'; break;
                                    }
                                    echo "<li class='event-item' data-bs-toggle='modal' data-bs-target='#detailModal' data-date='{$tanggalPenuh}'>{$event['name']} - {$statusText}</li>";
                                }
                                echo '</ul>';
                            }
                            
                            echo '</div>';
                        }
                        
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
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #6c757d;"></div>
                            <span>Hari Libur</span>
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
                <h5 class="modal-title" id="detailModalLabel">Detail KHL Karyawan</h5>
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
    // Pindahkan data KHL dari PHP ke JS
    const khlData = <?php echo json_encode($khlData); ?>;

    const detailModal = document.getElementById('detailModal');
    if (detailModal) {
        detailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const date = button.getAttribute('data-date');
            
            const dateObj = new Date(date + 'T00:00:00');
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const formattedDate = dateObj.toLocaleDateString('id-ID', options);
            
            document.getElementById('modalDate').textContent = `Tanggal: ${formattedDate}`;
            
            const employeeList = document.getElementById('employeeList');
            employeeList.innerHTML = '';
            
            if (khlData[date]) {
                khlData[date].forEach(employee => {
                    const employeeItem = document.createElement('div');
                    employeeItem.className = 'employee-item';
                    
                    const statusText = employee.status === 'approved' ? 'Disetujui' : 
                                      employee.status === 'pending' ? 'Menunggu Persetujuan' : 'Ditolak';
                    const statusClass = employee.status === 'approved' ? 'bg-success' : 
                                      employee.status === 'pending' ? 'bg-warning text-dark' : 'bg-danger';
                    
                    employeeItem.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="employee-name">${employee.name}</div>
                                <div class="text-muted small">${employee.reason}</div>
                            </div>
                            <span class="badge ${statusClass}">${statusText}</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="small"><strong>Tanggal Kerja:</strong> ${employee.workDate}</div>
                                <div class="small"><strong>Jam:</strong> ${employee.startTime} - ${employee.endTime}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small"><strong>Tanggal Libur Pengganti:</strong> ${employee.substituteDate}</div>
                            </div>
                        </div>
                    `;
                    
                    employeeList.appendChild(employeeItem);
                });
            } else {
                employeeList.innerHTML = '<p class="text-center text-muted">Tidak ada KHL yang dijadwalkan pada tanggal ini.</p>';
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>