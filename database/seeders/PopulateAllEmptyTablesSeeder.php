<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PopulateAllEmptyTablesSeeder extends Seeder
{
    /**
     * Run the database seeds to populate any empty tables.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $karyawanList = DB::table('mtr_tbl_karyawan')->select('nik', 'nama', 'id_fp', 'wg_id')->limit(100)->get();
        if ($karyawanList->isEmpty()) {
            $this->command->error('Tabel mtr_tbl_karyawan kosong. Harap isi data karyawan terlebih dahulu.');
            return;
        }

        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $prop = 'Tables_in_' . $dbName;

        $skipTables = ['migrations', 'failed_jobs', 'activity_log'];

        foreach ($tables as $t) {
            $table = $t->$prop;
            if (in_array($table, $skipTables)) {
                continue;
            }

            if (DB::table($table)->count() === 0) {
                $this->command->info("Mengisi tabel kosong: {$table}");
                $columns = DB::select("DESCRIBE `{$table}`");
                $rowsToInsert = [];
                $count = 25;

                for ($i = 1; $i <= $count; $i++) {
                    $row = [];
                    $karyawan = $karyawanList[($i - 1) % count($karyawanList)];
                    $date = Carbon::now()->subDays(rand(1, 90));

                    foreach ($columns as $col) {
                        $field = $col->Field;
                        $type = strtolower($col->Type);
                        $extra = strtolower($col->Extra);

                        if (str_contains($extra, 'auto_increment')) {
                            continue;
                        }

                        if ($field === 'created_at' || $field === 'updated_at') {
                            $row[$field] = $date;
                            continue;
                        }

                        if ($field === 'id_kebutuhan' && $table === 'trx_tbl_recruitment') {
                            $row[$field] = $i;
                            continue;
                        }

                        if (str_contains($field, 'nik') && !str_contains($field, 'property')) {
                            $row[$field] = $karyawan->nik;
                            continue;
                        }

                        if (str_contains($field, 'property')) {
                            $row[$field] = json_encode([
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
                                'status_karyawan' => 'Tetap'
                            ]);
                            continue;
                        }

                        if ($field === 'nama' || $field === 'name') {
                            $row[$field] = $karyawan->nama;
                            continue;
                        }

                        if ($field === 'email') {
                            $row[$field] = 'karyawan' . $i . '@alita.id';
                            continue;
                        }

                        if (str_contains($field, 'tanggal') || str_contains($field, 'date')) {
                            $row[$field] = $date->format('Y-m-d');
                            continue;
                        }

                        if (str_contains($field, 'time') || str_contains($field, 'jam')) {
                            $row[$field] = str_contains($type, 'datetime') ? $date->toDateTimeString() : '08:30:00';
                            continue;
                        }

                        if (str_contains($field, 'status')) {
                            $row[$field] = str_contains($type, 'int') ? 1 : 'Active';
                            continue;
                        }

                        if (str_contains($field, 'id_fp') || $field === 'fp_id') {
                            $row[$field] = $karyawan->id_fp ?: $i;
                            continue;
                        }

                        if (str_contains($field, 'wg_id')) {
                            $row[$field] = $karyawan->wg_id ?: 1;
                            continue;
                        }

                        if (str_contains($field, 'is_') || str_contains($field, 'flag')) {
                            $row[$field] = 1;
                            continue;
                        }

                        if (str_contains($type, 'int')) {
                            $row[$field] = 1;
                        } elseif (str_contains($type, 'double') || str_contains($type, 'float') || str_contains($type, 'decimal')) {
                            $row[$field] = 100.00;
                        } elseif (str_contains($type, 'text')) {
                            $row[$field] = "Deskripsi sample untuk {$table} record #{$i}";
                        } else {
                            $row[$field] = "Sample {$i}";
                        }
                    }
                    $rowsToInsert[] = $row;
                }

                DB::table($table)->insert($rowsToInsert);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info('Semua tabel database telah berhasil terisi data!');
    }
}
