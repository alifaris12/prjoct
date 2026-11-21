<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComprehensiveDummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Ambil data karyawan master untuk referensi NIK dan Nama
        $karyawanList = DB::table('mtr_tbl_karyawan')->select('nik', 'nama', 'wg_id')->limit(50)->get();
        if ($karyawanList->isEmpty()) {
            $this->command->error('Tabel mtr_tbl_karyawan kosong!');
            return;
        }

        $now = Carbon::now();
        $totalPerTable = 100;

        // -------------------------------------------------------------
        // 1. CUTI (trx_tbl_cuti & trx_tbl_cuti_log)
        // -------------------------------------------------------------
        $this->command->info('Membuat data Cuti...');
        $cutiTipeIds = DB::table('mtr_tbl_cuti_tipe')->pluck('id')->toArray() ?: [1, 2];
        $cutiStatusIds = DB::table('mtr_tbl_cuti_status')->pluck('id')->toArray() ?: [1, 2, 4];

        DB::table('trx_tbl_cuti')->truncate();
        DB::table('trx_tbl_cuti_log')->truncate();

        for ($i = 1; $i <= $totalPerTable; $i++) {
            $karyawan = $karyawanList->random();
            $atasan = $karyawanList->random();
            $pic = $karyawanList->random();

            $startDate = Carbon::now()->subDays(rand(1, 90));
            $days = rand(1, 5);
            $endDate = (clone $startDate)->addDays($days - 1);
            $tipeId = $cutiTipeIds[array_rand($cutiTipeIds)];
            $statusId = $cutiStatusIds[array_rand($cutiStatusIds)];

            $cutiId = DB::table('trx_tbl_cuti')->insertGetId([
                'tipe_cuti' => $tipeId,
                'periode_cuti' => $startDate->year,
                'nik_karyawan' => $karyawan->nik,
                'nik_atasan' => $atasan->nik,
                'pic_pengganti' => $pic->nik,
                'karyawan_property' => $this->formatProperty($karyawan),
                'atasan_property' => $this->formatProperty($atasan),
                'pic_property' => $this->formatProperty($pic),
                'tanggal_mulai' => $startDate->format('Y-m-d'),
                'tanggal_akhir' => $endDate->format('Y-m-d'),
                'jumlah_hari' => $days,
                'tanggal_cuti' => $startDate->format('Y-m-d'),
                'alasan' => 'Keperluan keluarga dan istirahat tahunan',
                'alamat' => 'Jakarta',
                'telepon' => '0812' . rand(10000000, 99999999),
                'keterangan' => 'Pengajuan cuti reguler',
                'status_cuti' => $statusId,
                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);

            DB::table('trx_tbl_cuti_log')->insert([
                'id_trx' => $cutiId,
                'id_status' => $statusId,
                'nik_pic' => $karyawan->nik,
                'keterangan' => 'Pengajuan berhasil diproses',
                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);
        }

        // -------------------------------------------------------------
        // 2. SAKIT (trx_tbl_sakit & trx_tbl_sakit_log)
        // -------------------------------------------------------------
        $this->command->info('Membuat data Sakit...');
        $sakitStatusIds = DB::table('mtr_tbl_sakit_status')->pluck('id')->toArray() ?: [1, 4, 6];

        DB::table('trx_tbl_sakit')->truncate();
        DB::table('trx_tbl_sakit_log')->truncate();

        for ($i = 1; $i <= $totalPerTable; $i++) {
            $karyawan = $karyawanList->random();
            $atasan = $karyawanList->random();
            $pic = $karyawanList->random();

            $startDate = Carbon::now()->subDays(rand(1, 90));
            $days = rand(1, 3);
            $endDate = (clone $startDate)->addDays($days - 1);
            $statusId = $sakitStatusIds[array_rand($sakitStatusIds)];

            $sakitId = DB::table('trx_tbl_sakit')->insertGetId([
                'periode_sakit' => $startDate->year,
                'nik_karyawan' => $karyawan->nik,
                'nik_atasan' => $atasan->nik,
                'pic_pengganti' => $pic->nik,
                'karyawan_property' => $this->formatProperty($karyawan),
                'atasan_property' => $this->formatProperty($atasan),
                'pic_property' => $this->formatProperty($pic),
                'tanggal_mulai' => $startDate->format('Y-m-d'),
                'tanggal_akhir' => $endDate->format('Y-m-d'),
                'jumlah_hari' => $days,
                'tanggal_sakit' => $startDate->format('Y-m-d'),
                'alasan' => 'Demam dan flu (istirahat dokter)',
                'alamat' => 'Jakarta Selatan',
                'telepon' => '0813' . rand(10000000, 99999999),
                'keterangan' => 'Surat dokter terlampir',
                'lampiran' => 'surat_dokter_' . $i . '.pdf',
                'status_sakit' => $statusId,
                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);

            DB::table('trx_tbl_sakit_log')->insert([
                'id_trx' => $sakitId,
                'id_status' => $statusId,
                'nik_pic' => $karyawan->nik,
                'keterangan' => 'Status izin sakit diperbarui',
                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);
        }

        // -------------------------------------------------------------
        // 3. IZIN (trx_tbl_ijin & trx_tbl_ijin_log)
        // -------------------------------------------------------------
        $this->command->info('Membuat data Izin...');
        $ijinTipeIds = DB::table('mtr_tbl_ijin_tipe')->pluck('id')->toArray() ?: [1, 2, 3];
        $ijinStatusIds = DB::table('mtr_tbl_ijin_status')->pluck('id')->toArray() ?: [1, 2, 4];

        DB::table('trx_tbl_ijin')->truncate();
        DB::table('trx_tbl_ijin_log')->truncate();

        for ($i = 1; $i <= $totalPerTable; $i++) {
            $karyawan = $karyawanList->random();
            $atasan = $karyawanList->random();

            $date = Carbon::now()->subDays(rand(1, 60));
            $tipeId = $ijinTipeIds[array_rand($ijinTipeIds)];
            $statusId = $ijinStatusIds[array_rand($ijinStatusIds)];

            $ijinId = DB::table('trx_tbl_ijin')->insertGetId([
                'karyawan' => $karyawan->nik,
                'karyawan_property' => $this->formatProperty($karyawan),
                'atasan' => $atasan->nik,
                'atasan_property' => $this->formatProperty($atasan),
                'tipe' => $tipeId,
                'tanggal' => $date->format('Y-m-d'),
                'jam_awal' => '09:00:00',
                'jam_akhir' => '13:00:00',
                'alasan' => 'Izin keperluan pengurusan administrasi kependudukan',
                'status' => $statusId,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            DB::table('trx_tbl_ijin_log')->insert([
                'id_trx' => $ijinId,
                'id_status' => $statusId,
                'nik_pic' => $karyawan->nik,
                'keterangan' => 'Pengajuan izin dicatat',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // -------------------------------------------------------------
        // 4. LEMBUR / OVERTIME (trx_tbl_overtime & trx_tbl_overtime_log)
        // -------------------------------------------------------------
        $this->command->info('Membuat data Overtime...');
        $otTypeIds = DB::table('mtr_tbl_overtime_type')->pluck('id')->toArray() ?: [1, 2, 3];

        DB::table('trx_tbl_overtime')->truncate();
        DB::table('trx_tbl_overtime_log')->truncate();

        for ($i = 1; $i <= $totalPerTable; $i++) {
            $karyawan = $karyawanList->random();
            $atasan = $karyawanList->random();

            $otDate = Carbon::now()->subDays(rand(1, 60));
            $startAt = (clone $otDate)->setTime(18, 0, 0);
            $hours = rand(2, 4);
            $endAt = (clone $startAt)->addHours($hours);
            $typeId = $otTypeIds[array_rand($otTypeIds)];

            $otId = DB::table('trx_tbl_overtime')->insertGetId([
                'ot_maker_at' => $otDate,
                'ot_approver_at' => $otDate,
                'ot_maker_by' => $karyawan->nik,
                'ot_maker_property' => json_encode(['nik' => $karyawan->nik, 'nama' => $karyawan->nama]),
                'ot_approver_by' => $atasan->nik,
                'ot_approver_property' => json_encode(['nik' => $atasan->nik, 'nama' => $atasan->nama]),
                'ot_maker_status' => 'Done',
                'ot_approver_status' => 'Approved',
                'ot_start_at' => $startAt,
                'ot_end_at' => $endAt,
                'ot_length' => $hours,
                'ot_start_clockin' => $startAt,
                'ot_end_clockin' => (clone $startAt)->addMinutes(30),
                'ot_start_clockout' => (clone $endAt)->subMinutes(30),
                'ot_end_clockout' => $endAt,
                'ot_title' => 'Maintenance Server & Deployment Rilis #' . $i,
                'ot_desc' => 'Dukungan lembur untuk deployment sistem dan monitoring performa',
                'ot_type_code' => $typeId,
                'ot_length_rp' => $hours * 50000,
                'created_at' => $otDate,
                'updated_at' => $otDate,
            ]);

            DB::table('trx_tbl_overtime_log')->insert([
                'id_ot' => $otId,
                'ot_approver_status' => 'Approved',
                'created_at' => $otDate,
                'updated_at' => $otDate,
            ]);
        }

        // -------------------------------------------------------------
        // 5. REIMBURSEMENT (trx_tbl_reimburse_master, detail, log)
        // -------------------------------------------------------------
        $this->command->info('Membuat data Reimburse...');
        $reimStatusIds = DB::table('mtr_tbl_reimburse_status')->pluck('id')->toArray() ?: [1, 2, 7];
        $hubunganIds = DB::table('mtr_tbl_reimburse_hubungan')->pluck('id')->toArray() ?: [1, 3, 4];
        $layananIds = DB::table('mtr_tbl_reimburse_layanan')->pluck('id')->toArray() ?: [1, 2, 3];

        DB::table('trx_tbl_reimburse_master')->truncate();
        DB::table('trx_tbl_reimburse_detail')->truncate();
        DB::table('trx_tbl_reimburse_log')->truncate();

        for ($i = 1; $i <= $totalPerTable; $i++) {
            $karyawan = $karyawanList->random();
            $date = Carbon::now()->subDays(rand(1, 90));
            $plafon = 5000000;
            $klaim = rand(150000, 1500000);
            $statusId = $reimStatusIds[array_rand($reimStatusIds)];

            $reimId = DB::table('trx_tbl_reimburse_master')->insertGetId([
                'nik_karyawan' => $karyawan->nik,
                'karyawan_property' => $this->formatProperty($karyawan),
                'periode' => $date->year,
                'plafon' => $plafon,
                'plafon_diambil' => $klaim,
                'sisa_plafon_1' => $plafon - $klaim,
                'klaim_kesehatan' => $klaim,
                'klaim_non_kesehatan' => 0,
                'sisa_plafon_2' => $plafon - $klaim,
                'status_pengajuan' => $statusId,
                'catatan' => 'Klaim rawat jalan dan resep obat',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            DB::table('trx_tbl_reimburse_detail')->insert([
                'id_reimburse' => $reimId,
                'nama_pasien' => $karyawan->nama,
                'id_hubungan' => $hubunganIds[array_rand($hubunganIds)],
                'id_layanan' => $layananIds[array_rand($layananIds)],
                'tanggal' => $date->format('Y-m-d'),
                'jumlah_klaim' => $klaim,
                'keterangan' => 'Kwitansi klinik / apotek',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            DB::table('trx_tbl_reimburse_log')->insert([
                'id_trx' => $reimId,
                'id_status' => $statusId,
                'nik_pic' => $karyawan->nik,
                'keterangan' => 'Klaim reimbursement diproses',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // -------------------------------------------------------------
        // 6. BPD - PERJALANAN DINAS (trx_tbl_bpd_master, info, biaya, log)
        // -------------------------------------------------------------
        $this->command->info('Membuat data BPD (Perjalanan Dinas)...');
        $bpdStatusIds = DB::table('mtr_tbl_bpd_status')->pluck('id')->toArray() ?: [1, 2, 4, 7];
        $projectIds = DB::table('mtr_tbl_bpd_project')->pluck('id')->toArray() ?: [1, 2];
        $kelompokIds = DB::table('mtr_tbl_bpd_kelompok')->pluck('id')->toArray() ?: [1, 2];

        DB::table('trx_tbl_bpd_master')->truncate();
        DB::table('trx_tbl_bpd_detail_info')->truncate();
        DB::table('trx_tbl_bpd_detail_biaya')->truncate();
        DB::table('trx_tbl_bpd_log')->truncate();

        for ($i = 1; $i <= $totalPerTable; $i++) {
            $pengaju = $karyawanList->random();
            $atasan = $karyawanList->random();

            $startDate = Carbon::now()->subDays(rand(1, 90));
            $days = rand(2, 5);
            $endDate = (clone $startDate)->addDays($days);
            $statusId = $bpdStatusIds[array_rand($bpdStatusIds)];
            $projectId = $projectIds[array_rand($projectIds)];
            $kelompokId = $kelompokIds[array_rand($kelompokIds)];

            $bpdId = DB::table('trx_tbl_bpd_master')->insertGetId([
                'is_ca' => 1,
                'periode' => $startDate->year,
                'luar_dalam' => 'Dalam Negeri',
                'is_project' => 1,
                'project_id' => $projectId,
                'kelompok_id' => $kelompokId,
                'is_leader' => 0,
                'pengaju' => $pengaju->nik,
                'pengaju_property' => json_encode(['nik' => $pengaju->nik, 'nama' => $pengaju->nama]),
                'atasan' => $atasan->nik,
                'atasan_property' => json_encode(['nik' => $atasan->nik, 'nama' => $atasan->nama]),
                'uraian' => 'Kunjungan kerja teknis dan instalasi perangkat proyek #' . $i,
                'status_id' => $statusId,
                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);

            $infoId = DB::table('trx_tbl_bpd_detail_info')->insertGetId([
                'id_trx' => $bpdId,
                'jenis' => 'Project',
                'luar_dalam' => 'Dalam Negeri',
                'tanggal_mulai' => $startDate->format('Y-m-d'),
                'tanggal_akhir' => $endDate->format('Y-m-d'),
                'is_approve' => 1,
                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);

            DB::table('trx_tbl_bpd_detail_biaya')->insert([
                'id_trx' => $bpdId,
                'jenis' => 'Transportasi & Akomodasi',
                'id_info' => $infoId,
                'biaya' => 'Tiket dan Hotel',
                'satuan' => 'Paket',
                'jumlah' => $days,
                'nominal' => $days * 650000,
                'is_ca' => 1,
                'is_approve' => 1,
                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);

            DB::table('trx_tbl_bpd_log')->insert([
                'id_trx' => $bpdId,
                'id_status' => $statusId,
                'nik_pic' => $pengaju->nik,
                'keterangan' => 'Pengajuan BPD tercatat di sistem',
                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);
        }

        // -------------------------------------------------------------
        // 7. HOLIDAY / HARI LIBUR NASIONAL 2026 (trx_tbl_holiday)
        // -------------------------------------------------------------
        $this->command->info('Membuat data Hari Libur...');
        DB::table('trx_tbl_holiday')->truncate();
        $holidays = [
            ['2026-01-01', 'Tahun Baru 2026 Masehi', 0],
            ['2026-02-17', 'Tahun Baru Imlek 2577', 0],
            ['2026-03-21', 'Hari Raya Nyepi Tahun Baru Saka 1948', 0],
            ['2026-03-30', 'Hari Raya Idul Fitri 1447 H', 0],
            ['2026-03-31', 'Hari Raya Idul Fitri 1447 H (Hari Kedua)', 0],
            ['2026-04-01', 'Cuti Bersama Idul Fitri 1447 H', 1],
            ['2026-05-01', 'Hari Buruh Internasional', 0],
            ['2026-05-14', 'Kenaikan Yesus Kristus', 0],
            ['2026-05-31', 'Hari Raya Waisak 2570', 0],
            ['2026-06-01', 'Hari Lahir Pancasila', 0],
            ['2026-06-06', 'Hari Raya Idul Adha 1447 H', 0],
            ['2026-07-16', 'Tahun Baru Islam 1448 H', 0],
            ['2026-08-17', 'Hari Kemerdekaan RI Ke-81', 0],
            ['2026-09-24', 'Maulid Nabi Muhammad SAW', 0],
            ['2026-12-25', 'Hari Raya Natal', 0],
        ];

        foreach ($holidays as $h) {
            DB::table('trx_tbl_holiday')->insert([
                'tanggal' => $h[0],
                'libur' => $h[1],
                'is_cuti' => $h[2],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // -------------------------------------------------------------
        // 8. FEEDS / PENGUMUMAN (trx_tbl_feeds)
        // -------------------------------------------------------------
        $this->command->info('Membuat data Feeds & Pengumuman...');
        DB::table('trx_tbl_feeds')->truncate();
        $feedTitles = [
            'Pengumuman Libur Hari Raya & Kebijakan Cuti Bersama',
            'Sosialisasi Penggunaan Portal MATTA Versi Terbaru',
            'Ketentuan Jam Kerja dan Absensi Bulan Ini',
            'Pembaruan Plafon Klaim Kesehatan Karyawan',
            'Pelatihan Teknis Internal dan Program Sertifikasi Pegawai',
            'Jadwal Medical Check Up (MCU) Tahunan',
            'Kegiatan Townhall Meeting dan Apresiasi Kinerja Karyawan',
            'Tips Menjaga Kesehatan & Keselamatan Kerja (K3) di Proyek',
            'Kebijakan Perjalanan Dinas (BPD) & Prosedur Cash Advance',
            'Peringatan Hari Kemerdekaan & Lomba Kebersamaan Karyawan'
        ];

        $firstNik = $karyawanList->first()->nik;
        foreach ($feedTitles as $idx => $t) {
            DB::table('trx_tbl_feeds')->insert([
                'title' => $t,
                'content' => 'Berikut adalah pengumuman resmi terkait ' . strtolower($t) . ' untuk seluruh jajaran karyawan Alita. Mohon dipedomani dengan saksama.',
                'is_active' => 1,
                'is_notif' => 1,
                'created_by' => $firstNik,
                'updated_by' => $firstNik,
                'created_at' => Carbon::now()->subDays(rand(1, 40)),
                'updated_at' => Carbon::now()->subDays(rand(1, 40)),
            ]);
        }

        // -------------------------------------------------------------
        // 9. TODOLIST / TUGAS & APPROVAL (trx_tbl_todolist)
        // -------------------------------------------------------------
        $this->command->info('Membuat data Todolist / Tasks...');
        DB::table('trx_tbl_todolist')->truncate();
        for ($i = 1; $i <= $totalPerTable; $i++) {
            $karyawan = $karyawanList->random();
            $pic = $karyawanList->random();

            DB::table('trx_tbl_todolist')->insert([
                'id_trx' => $i,
                'id_status' => rand(1, 4),
                'nik' => $karyawan->nik,
                'pic' => $pic->nik,
                'title' => 'Persetujuan Pengajuan ' . (['Cuti', 'Lembur', 'Izin', 'Reimburse', 'BPD'][rand(0, 4)]) . ' #' . $i,
                'nama' => $karyawan->nama,
                'jabatan' => 'Staf Teknis',
                'unit_kerja' => 'Operasional IT',
                'link' => '/approval/' . $i,
                'email' => 'user' . $i . '@alita.id',
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        // -------------------------------------------------------------
        // 10. WORKING DAY (trx_tbl_working_day)
        // -------------------------------------------------------------
        $this->command->info('Membuat data Working Day...');
        DB::table('trx_tbl_working_day')->truncate();
        for ($i = 1; $i <= $totalPerTable; $i++) {
            $karyawan = $karyawanList->random();
            $d = Carbon::now()->subDays(rand(1, 30));

            DB::table('trx_tbl_working_day')->insert([
                'wg_id' => $karyawan->wg_id ?: 1,
                'shift' => 1,
                'nik' => $karyawan->nik,
                'working_date' => $d->format('Y-m-d'),
                'clockin' => (clone $d)->setTime(8, 30, 0),
                'clockout' => (clone $d)->setTime(17, 30, 0),
                'start_clockin' => (clone $d)->setTime(7, 30, 0),
                'end_clockin' => (clone $d)->setTime(15, 0, 0),
                'start_clockout' => (clone $d)->setTime(16, 30, 0),
                'end_clockout' => (clone $d)->setTime(23, 59, 59),
                'created_at' => $d,
                'updated_at' => $d,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('SELESAI! Seluruh tabel modul transaksi utama telah terisi dummy data.');
    }

    private function formatProperty($karyawan)
    {
        return json_encode([
            'nik' => $karyawan->nik,
            'nama' => $karyawan->nama,
            'email' => $karyawan->nik . '@alita.id',
            'telepon1' => '+628123456789',
            'perusahaan' => 'PT ALITA PRAYA MITRA',
            'divisi' => 'Direktorat Operasional',
            'dep' => 'Divisi Information Technology',
            'sub_dep' => 'Departemen Application Development',
            'level' => 'Staff',
            'jabatan' => 'Staff IT',
            'id_level' => 1,
            'findim_divisi' => 'DIV-IT',
            'findim_departemen' => 'DEP-DEV',
            'findim_subdepartemen' => 'SUB-APP',
            'findim_project' => 'PRJ-MATTA',
            'status_karyawan' => 'Tetap',
            'tanggal_bergabung' => '2023-01-01',
            'tanggal_berakhir' => '2026-12-31'
        ]);
    }
}
