<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceLogBulkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Menyiapkan 40.000 data absensi dengan NAMA, NIK, ID FP, ALAMAT, KOORDINAT unik (index+1)...');

        $totalRecords = 40000;
        $batchSize = 1000;

        // 1. Ambil list perusahaan valid (selain 0 dan 3) dan working groups
        $companies = [1 => 'Perusahaan 1', 2 => 'Perusahaan 2', 4 => 'Perusahaan 4', 5 => 'Perusahaan 5', 6 => 'Perusahaan 6', 7 => 'Perusahaan 7', 9 => 'Perusahaan 9'];
        $companyIds = array_keys($companies);
        $totalCompanies = count($companyIds);

        // 2. Siapkan 40.000 karyawan unik di mtr_tbl_karyawan jika belum ada
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info('Membersihkan data NIK dummy lama jika ada...');
        DB::table('trx_tbl_attendance')->truncate();
        DB::table('trx_tbl_attendance_log')->truncate();
        DB::table('trx_tbl_fp_data')->truncate();
        DB::table('mtr_tbl_karyawan')->where('nik', 'like', 'NIK-%')->delete();

        $this->command->info('Membuat 40.000 karyawan unik di mtr_tbl_karyawan...');
        $karyawanBatch = [];
        for ($i = 1; $i <= $totalRecords; $i++) {
            $nikStr = sprintf('NIK-%05d', $i);
            $compId = $companyIds[($i - 1) % $totalCompanies];
            $wgId = (($i - 1) % 22) + 1;

            $karyawanBatch[] = [
                'nik'                  => $nikStr,
                'password'             => '$2a$12$jt0R4/OlGuojLUCQ0EkyjOlAQrQpK9V1FW5/sciCnDRTI3tZeOVUy',
                'nama'                 => 'Karyawan ' . $i,
                'is_aktif'             => 1,
                'kode_status'          => 3,
                'alamat_kantor'        => 'Lokasi Kantor ' . $i,
                'id_fp'                => $i,
                'wg_id'                => $wgId,
                'kode_perusahaan'      => $compId,
                'kode_divisi'          => 3,
                'kode_departemen'      => 111,
                'kode_sub_departemen'  => 377,
                'kode_level'           => 1,
                'kode_jabatan'         => 678,
                'findim_project'       => 11,
                'findim_divisi'        => 4,
                'findim_departemen'    => 2,
                'findim_subdepartemen' => 11,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];

            if (count($karyawanBatch) >= $batchSize) {
                DB::table('mtr_tbl_karyawan')->insert($karyawanBatch);
                $karyawanBatch = [];
                $this->command->info("Karyawan terbuat: {$i} / {$totalRecords}");
            }
        }
        if (!empty($karyawanBatch)) {
            DB::table('mtr_tbl_karyawan')->insert($karyawanBatch);
        }

        // 3. Buat 20.000 transaksi di trx_tbl_fp_data
        $this->command->info('Membuat data fingerprint (trx_tbl_fp_data)...');
        $fpBatch = [];
        $totalFp = 20000;
        for ($i = 1; $i <= $totalFp; $i++) {
            $mesinId = ($i % 2 === 0) ? 1 : 2;
            $day = ($i % 27) + 1;
            $fpTime = sprintf('2026-10-%02d %02d:%02d:%02d', $day, rand(7, 21), rand(0, 59), ($i % 60));

            $fpBatch[] = [
                'id'         => $i,
                'id_data'    => $i,
                'id_nip'     => $i,
                'is_state'   => rand(1, 4),
                'waktu'      => $fpTime,
                'is_type'    => ($i % 2 === 0) ? '0' : '1',
                'created_at' => $fpTime,
                'updated_at' => $fpTime,
                'id_mesin'   => $mesinId,
            ];

            if (count($fpBatch) >= $batchSize) {
                DB::table('trx_tbl_fp_data')->insert($fpBatch);
                $fpBatch = [];
            }
        }
        if (!empty($fpBatch)) {
            DB::table('trx_tbl_fp_data')->insert($fpBatch);
        }

        // 4. Rentang tanggal dari 01 Oktober 2026 s/d 27 Oktober 2026
        $dates = [];
        $startRange = Carbon::create(2026, 10, 1);
        $endRange = Carbon::create(2026, 10, 27);
        for ($d = clone $startRange; $d->lte($endRange); $d->addDay()) {
            if (!$d->isSunday()) {
                $dates[] = $d->format('Y-m-d');
            }
        }
        $totalDays = count($dates);     

        $baseLocations = [
            ['lat' => -6.175392, 'long' => 106.827153, 'nama' => 'Jl. Medan Merdeka Barat No. '],
            ['lat' => -6.229728, 'long' => 106.829514, 'nama' => 'Gedung Cyber 2 Lantai '],
            ['lat' => -6.208763, 'long' => 106.845599, 'nama' => 'Jl. Dr. Saharjo No. '],
            ['lat' => -6.302482, 'long' => 106.818314, 'nama' => 'TB Simatupang Kav. '],
            ['lat' => -6.194449, 'long' => 106.822920, 'nama' => 'The Plaza Office Tower No. '],
            ['lat' => -6.261493, 'long' => 106.781136, 'nama' => 'Pondok Indah Office Tower 3 Lantai '],
            ['lat' => -6.225514, 'long' => 106.800185, 'nama' => 'Pacific Place SCBD Lantai '],
            ['lat' => -6.241382, 'long' => 106.800921, 'nama' => 'Jl. Panglima Polim No. '],
            ['lat' => -6.180491, 'long' => 106.828342, 'nama' => 'Jl. Prof. Dr. Satrio Kav. '],
            ['lat' => -6.215582, 'long' => 106.816652, 'nama' => 'Sampoerna Strategic Square Lantai '],
            ['lat' => -6.192314, 'long' => 106.774512, 'nama' => 'Puri Indah Financial Tower No. '],
            ['lat' => -6.372183, 'long' => 106.832711, 'nama' => 'Jl. Margonda Raya No. '],
            ['lat' => -6.238491, 'long' => 106.975412, 'nama' => 'Bekasi Project Center No. '],
            ['lat' => -6.291823, 'long' => 106.674912, 'nama' => 'BSD Green Office Park 9 No. '],
            ['lat' => -6.595182, 'long' => 106.804912, 'nama' => 'Bogor Data Center, Jl. Pajajaran No. '],
            ['lat' => -6.917464, 'long' => 107.619123, 'nama' => 'Bandung Branch, Jl. Asia Afrika No. '],
            ['lat' => -7.257472, 'long' => 112.752088, 'nama' => 'Surabaya Hub, Jl. Pemuda No. '],
        ];
        $totalBaseLocs = count($baseLocations);

        $this->command->info('Membuat 40.000 absensi yang semuanya berbeda (nama, NIK, alamat, koordinat, waktu unik)...');

        $rowsAtt = [];
        $rowsLog = [];

        for ($i = 1; $i <= $totalRecords; $i++) {
            $nikStr = sprintf('NIK-%05d', $i);
            $namaStr = 'Karyawan ' . $i;
            $wgId = (($i - 1) % 22) + 1;
            $wgName = 'Working Group ' . $wgId;
            $compId = $companyIds[($i - 1) % $totalCompanies];
            $compName = $companies[$compId];

            $direktoratName = 'Direktorat ' . ((($i - 1) % 8) + 1);
            $divisiName = 'Divisi ' . ((($i - 1) % 15) + 1);
            $depName = 'Departemen ' . ((($i - 1) % 25) + 1);
            $alamatKantor = 'Lokasi Kantor ' . $i;

            // Tanggal terdistribusi
            $dateStr = $dates[($i - 1) % $totalDays];

            // Lokasi & Koordinat bervariasi unik di setiap baris
            $baseLoc = $baseLocations[($i - 1) % $totalBaseLocs];
            $latOffset = round((($i % 5000) * 0.000031) - 0.077, 6);
            $longOffset = round((($i % 6000) * 0.000029) - 0.087, 6);
            $attLat = round($baseLoc['lat'] + $latOffset, 6);
            $attLong = round($baseLoc['long'] + $longOffset, 6);
            $refLat = round($baseLoc['lat'], 6);
            $refLong = round($baseLoc['long'], 6);

            $attAddress = $baseLoc['nama'] . $i . ', RT ' . ((($i - 1) % 20) + 1) . ' RW ' . ((($i - 1) % 15) + 1);
            $refAddress = $baseLoc['nama'] . $i;

            // Distance gap unik untuk setiap baris
            $distanceGap = round(((($i * 1.73) % 450) + 0.1), 1);

            // Clock gap unik untuk setiap baris
            $clockGapSeconds = ($i * 37) % 3600;
            $clockGap = gmdate('H:i:s', $clockGapSeconds);

            // Type: 80% Regular, 20% Overtime
            $dataSrc = (($i % 5) === 0) ? 'Overtime' : 'Regular';

            // Sumber Absensi: 65% MATTA (mobile), 35% FINGERPRINT
            $isFinger = (($i % 3) === 0);
            $fpTrxId = $isFinger ? ((($i - 1) % $totalFp) + 1) : null;

            // Action: bergantian 50% Clock-in dan 50% Clock-out
            $isClockIn = ($i % 2 === 1);

            if ($isClockIn) {
                $randStatusMod = $i % 100;
                if ($randStatusMod < 68) {
                    $status = 'Book-in';
                    $hour = 7 + (($i % 2));
                    $minute = ($i * 7) % 60;
                } elseif ($randStatusMod < 96) {
                    $status = 'Late-in';
                    $hour = 8 + (($i % 2));
                    $minute = 30 + (($i * 3) % 30);
                } else {
                    $status = 'Leave';
                    $hour = 8;
                    $minute = 0;
                }
                $attType = 'Clock-in';
            } else {
                $randStatusMod = $i % 100;
                if ($randStatusMod < 72) {
                    $status = 'Book-out';
                    $hour = ($dataSrc === 'Overtime') ? (19 + ($i % 3)) : (17 + ($i % 2));
                    $minute = ($i * 11) % 60;
                } elseif ($randStatusMod < 96) {
                    $status = 'Early-out';
                    $hour = 14 + (($i % 3));
                    $minute = ($i * 7) % 60;
                } else {
                    $status = 'Leave';
                    $hour = 17;
                    $minute = 0;
                }
                $attType = 'Clock-out';
            }

            $second = ($i * 13) % 60;
            $timeStr = sprintf('%s %02d:%02d:%02d', $dateStr, $hour, $minute, $second);

            $karyawanProp = json_encode([
                'nik'           => $nikStr,
                'nama'          => $namaStr,
                'id_fp'         => $i,
                'perusahaan'    => $compName,
                'divisi'        => $direktoratName,
                'dep'           => $divisiName,
                'sub_dep'       => $depName,
                'wg_name'       => $wgName,
                'alamat_kantor' => $alamatKantor,
            ]);

            $rowItem = [
                'id'               => $i,
                'att_date'         => $dateStr,
                'att_timestamp'    => $timeStr,
                'att_nik'          => $nikStr,
                'att_type'         => $attType,
                'att_long'         => $attLong,
                'att_lat'          => $attLat,
                'att_address'      => $attAddress,
                'att_ref_long'     => $refLong,
                'att_ref_lat'      => $refLat,
                'att_ref_address'  => $refAddress,
                'att_distance_gap' => $distanceGap,
                'att_clock_gap'    => $clockGap,
                'att_status'       => $status,
                'att_data_src'     => $dataSrc,
                'att_wd_ot_id'     => ($dataSrc === 'Overtime' ? ((($i - 1) % 50) + 1) : 0),
                'att_property'     => $karyawanProp,
                'wg_id'            => $wgId,
                'created_at'       => $timeStr,
                'updated_at'       => $timeStr,
                'id_fp_trx'        => $fpTrxId,
            ];

            $rowsAtt[] = $rowItem;
            unset($rowItem['id_fp_trx']);
            $rowsLog[] = $rowItem;

            if (count($rowsAtt) >= $batchSize) {
                DB::table('trx_tbl_attendance_log')->insert($rowsLog);
                DB::table('trx_tbl_attendance')->insert($rowsAtt);
                $rowsAtt = [];
                $rowsLog = [];
                $this->command->info("Absensi tersimpan: {$i} / {$totalRecords}");
            }
        }

        if (!empty($rowsAtt)) {
            DB::table('trx_tbl_attendance_log')->insert($rowsLog);
            DB::table('trx_tbl_attendance')->insert($rowsAtt);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('SUKSES! 40.000 data telah dibuat dengan variasi unik sempurna (index+1) di seluruh kolom.');
    }
}
