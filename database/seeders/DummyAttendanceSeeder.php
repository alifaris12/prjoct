<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DummyAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $karyawan = DB::table('mtr_tbl_karyawan')->select('nik', 'nama', 'wg_id')->limit(10)->get();

        // 7 hari terakhir di bulan Oktober 2026
        $dates = [
            '2026-10-22',
            '2026-10-23',
            '2026-10-24',
            '2026-10-26',
            '2026-10-27',
            '2026-10-28',
        ];

        $rowsLog = [];
        $rowsAtt = [];

        foreach ($karyawan as $k) {
            $wg_id = $k->wg_id ?: 1;

            foreach ($dates as $d) {
                // Clock-In (antara jam 08:10 s/d 08:45)
                $inMinute = rand(10, 45);
                $inSec = rand(10, 59);
                $inTime = sprintf('%s 08:%02d:%02d', $d, $inMinute, $inSec);
                $statusIn = ($inMinute > 30) ? 'Late-in' : 'Book-in';

                // Clock-Out (antara jam 17:30 s/d 18:15)
                $outMinute = rand(30, 59);
                $outSec = rand(10, 59);
                $outTime = sprintf('%s 17:%02d:%02d', $d, $outMinute, $outSec);
                $statusOut = 'Book-out';

                $baseIn = [
                    'att_date' => $d,
                    'att_timestamp' => $inTime,
                    'att_nik' => $k->nik,
                    'att_type' => 'Clock-in',
                    'att_long' => 106.827153,
                    'att_lat' => -6.175392,
                    'att_address' => 'Kantor Pusat Alita, Jakarta Selatan',
                    'att_ref_long' => 106.827153,
                    'att_ref_lat' => -6.175392,
                    'att_ref_address' => 'Kantor Pusat Alita, Jakarta Selatan',
                    'att_distance_gap' => 0.0,
                    'att_clock_gap' => '00:00:00',
                    'att_status' => $statusIn,
                    'att_data_src' => 'Regular',
                    'att_wd_ot_id' => 0,
                    'att_property' => json_encode(['nik' => $k->nik, 'nama' => $k->nama]),
                    'wg_id' => $wg_id,
                    'created_at' => $inTime,
                    'updated_at' => $inTime,
                ];

                $baseOut = [
                    'att_date' => $d,
                    'att_timestamp' => $outTime,
                    'att_nik' => $k->nik,
                    'att_type' => 'Clock-out',
                    'att_long' => 106.827153,
                    'att_lat' => -6.175392,
                    'att_address' => 'Kantor Pusat Alita, Jakarta Selatan',
                    'att_ref_long' => 106.827153,
                    'att_ref_lat' => -6.175392,
                    'att_ref_address' => 'Kantor Pusat Alita, Jakarta Selatan',
                    'att_distance_gap' => 0.0,
                    'att_clock_gap' => '00:00:00',
                    'att_status' => $statusOut,
                    'att_data_src' => 'Regular',
                    'att_wd_ot_id' => 0,
                    'att_property' => json_encode(['nik' => $k->nik, 'nama' => $k->nama]),
                    'wg_id' => $wg_id,
                    'created_at' => $outTime,
                    'updated_at' => $outTime,
                ];

                $rowsLog[] = $baseIn;
                $rowsLog[] = $baseOut;

                $attIn = $baseIn;
                $attIn['id_fp_trx'] = null;
                $attOut = $baseOut;
                $attOut['id_fp_trx'] = null;
                $rowsAtt[] = $attIn;
                $rowsAtt[] = $attOut;
            }
        }

        DB::table('trx_tbl_attendance_log')->insert($rowsLog);
        DB::table('trx_tbl_attendance')->insert($rowsAtt);

        $this->command->info('Berhasil menginput ' . count($rowsLog) . ' data ke trx_tbl_attendance_log');
        $this->command->info('Berhasil menginput ' . count($rowsAtt) . ' data ke trx_tbl_attendance');
    }
}
