<?php include 'includes/header.php'; ?>

<div class="card" style="background-color: #A9D0E0;">
    <div class="card-body">
        <h3 class="text-center">Desember 2025</h3>
        <table class="table table-bordered text-center" style="background-color: white;">
            <thead>
                <tr class="table-primary">
                    <th>Senin</th>
                    <th>Selasa</th>
                    <th>Rabu</th>
                    <th>Kamis</th>
                    <th>Jumat</th>
                    <th>Sabtu</th>
                    <th>Minggu</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Logika PHP sederhana untuk generate kalender
                $bulan = 12;
                $tahun = 2025;
                $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
                $hari_pertama = date('N', strtotime("$tahun-$bulan-01"));

                echo "<tr>";
                // Sel kosong sebelum tanggal 1
                for ($i = 1; $i < $hari_pertama; $i++) {
                    echo "<td></td>";
                }

                $hari_count = $hari_pertama;
                for ($tgl = 1; $tgl <= $jumlah_hari; $tgl++) {
                    // Cek ke database apakah ada karyawan yang cuti di tanggal ini
                    // $ada_cuti = ... (query ke db)
                    // if ($ada_cuti) { echo "<td class='bg-warning'>$tgl</td>"; } else { echo "<td>$tgl</td>"; }
                    
                    echo "<td>$tgl</td>"; // Versi sederhana

                    if ($hari_count % 7 == 0) {
                        echo "</tr><tr>";
                    }
                    $hari_count++;
                }
                echo "</tr>";
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>