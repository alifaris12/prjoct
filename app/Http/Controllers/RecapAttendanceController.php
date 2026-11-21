<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Attendance;
use App\Models\TrxCuti;
use App\Models\TrxSakit;
use App\Models\Karyawan;
use App\Models\Perusahaan;
use App\Models\Divisi;
use App\Models\Departemen;
use App\Models\SubDepartemen;
use Carbon\Carbon;
use App\Models\WorkingDay;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RecapAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        set_time_limit(3600);
        $no = 0;
        $perusahaan = Perusahaan::whereNotIn('id', [0, 3])->orderBy('nama', 'asc')->get();
        $divisi = [];
        $departemen = [];
        $sub_departemen = [];

        $arr_perusahaan = [];
        $arr_divisi = [];
        $arr_departemen = [];
        $arr_sub_departemen = [];
        $trx = [];
        $arr_is_aktif = [['id' => 1, 'is_aktif' => 'Aktif'], ['id' => 0, 'is_aktif' => 'Non Aktif']];
        $is_aktif = $arr_is_aktif;

        $date_from = $request->input('date_from', '');
        $date_to = $request->input('date_to', '');
        $isFiltered = $request->filled('filter') && !empty($date_from) && !empty($date_to);

        // Filter Organisasi & Status Karyawan
        $_perusahaan = $request->input('kode_perusahaan', []);
        $_divisi = $request->input('kode_divisi', []);
        $_departemen = $request->input('kode_departemen', []);
        $_sub_departemen = $request->input('kode_sub_departemen', []);
        $_is_aktif = $request->input('is_aktif', [1]);

        if (count($_perusahaan) > 0) $arr_perusahaan = $_perusahaan;
        if (count($_divisi) > 0) $arr_divisi = $_divisi;
        if (count($_departemen) > 0) $arr_departemen = $_departemen;
        if (count($_sub_departemen) > 0) $arr_sub_departemen = $_sub_departemen;
        $arr_is_aktif = count($_is_aktif) > 0 ? $_is_aktif : [1];

        $divisi = Divisi::whereIn('kode_perusahaan', $arr_perusahaan)->get();
        $departemen = Departemen::whereIn('kode_divisi', $arr_divisi)->get();
        $sub_departemen = SubDepartemen::whereIn('kode_departemen', $arr_departemen)->get();

        $aggTotalBase = 0;
        $aggTotalHadir = 0;
        $aggTotalAbsen = 0;
        $aggPersentase = 0;
        $totalKaryawan = 0;

        if ($isFiltered) {
            $arr_nik_nkp = new Karyawan();
            $arr_nik_nkp = $arr_nik_nkp->getNikNkp();

            $karyawanQuery = Karyawan::with(['_wg', '_level', '_subdepartemen'])
                ->whereNotIn('nik', ['Hco.all', 'IT.BM'])
                ->whereIn('is_aktif', $arr_is_aktif);

            if (Gate::allows('hc_nkp')) {
                $karyawanQuery->whereIn('nik', $arr_nik_nkp);
                if (count($arr_sub_departemen) > 0) {
                    $karyawanQuery->whereIn('kode_sub_departemen', $arr_sub_departemen);
                }
            } elseif (Gate::allows('hc')) {
                if (count($arr_sub_departemen) > 0) {
                    $karyawanQuery->whereIn('kode_sub_departemen', $arr_sub_departemen);
                } elseif (count($arr_departemen) > 0) {
                    $subIds = SubDepartemen::whereIn('kode_departemen', $arr_departemen)->pluck('id');
                    $karyawanQuery->whereIn('kode_sub_departemen', $subIds);
                } elseif (count($arr_divisi) > 0) {
                    $depIds = Departemen::whereIn('kode_divisi', $arr_divisi)->pluck('id');
                    $subIds = SubDepartemen::whereIn('kode_departemen', $depIds)->pluck('id');
                    $karyawanQuery->whereIn('kode_sub_departemen', $subIds);
                } elseif (count($arr_perusahaan) > 0) {
                    $divIds = Divisi::whereIn('kode_perusahaan', $arr_perusahaan)->pluck('id');
                    $depIds = Departemen::whereIn('kode_divisi', $divIds)->pluck('id');
                    $subIds = SubDepartemen::whereIn('kode_departemen', $depIds)->pluck('id');
                    $karyawanQuery->whereIn('kode_sub_departemen', $subIds);
                } else {
                    // Jika HC tidak memilih filter perusahaan/divisi/dept, batasi untuk mencegah memory exhaustion
                    $karyawanQuery->limit(200);
                }
            } else {
                // nhc - user login & bawahan
                $_karyawan = [auth()->user()->nik];
                $bawahan1 = Karyawan::select('nik')->where('layer1', auth()->user()->nik)->get();
                foreach ($bawahan1 as $_b1) {
                    $_karyawan[] = $_b1->nik;
                    $bawahan2 = Karyawan::select('nik')->where('layer1', $_b1->nik)->get();
                    foreach ($bawahan2 as $_b2) {
                        $_karyawan[] = $_b2->nik;
                    }
                }
                $karyawanQuery->whereIn('nik', $_karyawan);
            }

            if ($request->filled('nik')) {
                $karyawanQuery->where('nik', $request->input('nik'));
            }

            $karyawan = $karyawanQuery->orderBy('nama', 'asc')->get();
            $totalKaryawan = $karyawan->count();

            $calc = $this->calculateRecap($karyawan, $date_from, $date_to);
            $trx = $calc['trx'];
            $aggTotalBase = $calc['total_base'];
            $aggTotalHadir = $calc['total_hadir'];
            $aggTotalAbsen = max(0, $aggTotalBase - $aggTotalHadir);
            $aggPersentase = $calc['persentase'];
        }

        return view('attendance.recap.index', compact(
            'trx', 'no', 'date_from', 'date_to', 'isFiltered',
            'arr_is_aktif', 'is_aktif',
            'perusahaan', 'divisi', 'departemen', 'sub_departemen',
            'arr_perusahaan', 'arr_divisi', 'arr_departemen', 'arr_sub_departemen',
            'aggTotalBase', 'aggTotalHadir', 'aggTotalAbsen', 'aggPersentase', 'totalKaryawan'
        ));
    }

    /**
     * Hitung rekapitulasi kehadiran karyawan pada rentang tanggal tertentu
     */
    public function calculateRecap($karyawan, $date_from, $date_to)
    {
        $arr_date = $this->dateRange($date_from, $date_to);
        $arr_date = array_values(array_diff($arr_date, ['2024-09-23']));
        $trx = [];
        $totalBase = 0;
        $totalHadir = 0;

        if ($karyawan->isEmpty() || empty($arr_date)) {
            return [
                'trx' => [],
                'total_base' => 0,
                'total_hadir' => 0,
                'persentase' => 0,
                'date_from' => $date_from,
                'date_to' => $date_to,
            ];
        }

        $allNiks = $karyawan->pluck('nik')->all();

        // Preload Holidays
        $holidays = Holiday::whereIn('tanggal', $arr_date)->pluck('tanggal')->flip()->all();

        // Preload Cuti
        $cutiList = TrxCuti::whereIn('nik_karyawan', $allNiks)
            ->whereIn('status_cuti', [4, 6])
            ->whereDate('tanggal_mulai', '<=', $date_to)
            ->whereDate('tanggal_akhir', '>=', $date_from)
            ->get();
        $cutiByNik = [];
        foreach ($cutiList as $c) {
            $cStart = max($date_from, $c->tanggal_mulai);
            $cEnd = min($date_to, $c->tanggal_akhir);
            if ($cStart <= $cEnd) {
                $cDates = $this->dateRange($cStart, $cEnd);
                foreach ($cDates as $cd) {
                    $cutiByNik[$c->nik_karyawan][$cd] = true;
                }
            }
        }

        // Preload Sakit
        $sakitList = TrxSakit::whereIn('nik_karyawan', $allNiks)
            ->whereIn('status_sakit', [4, 6])
            ->whereDate('tanggal_mulai', '<=', $date_to)
            ->whereDate('tanggal_akhir', '>=', $date_from)
            ->get();
        $sakitByNik = [];
        foreach ($sakitList as $s) {
            $sStart = max($date_from, $s->tanggal_mulai);
            $sEnd = min($date_to, $s->tanggal_akhir);
            if ($sStart <= $sEnd) {
                $sDates = $this->dateRange($sStart, $sEnd);
                foreach ($sDates as $sd) {
                    $sakitByNik[$s->nik_karyawan][$sd] = true;
                }
            }
        }

        // Preload WorkingDay for non-regular
        $workingDaysList = WorkingDay::whereIn('nik', $allNiks)
            ->whereIn('working_date', $arr_date)
            ->get()
            ->groupBy('nik');

        // Preload Clockins
        $clockinsList = Attendance::whereIn('att_nik', $allNiks)
            ->whereBetween('att_date', [$date_from, $date_to])
            ->where('att_type', 'Clock-in')
            ->where('att_data_src', 'Regular')
            ->select('att_nik', 'att_date')
            ->get()
            ->groupBy('att_nik');

        foreach ($karyawan as $_karyawan) {
            $nik = $_karyawan->nik;
            $arr_base = [];
            $tanggal_bergabung = $_karyawan->tanggal_bergabung;

            $isReguler = isset($_karyawan->_wg) && $_karyawan->_wg->is_reguler == 1;

            if ($isReguler) {
                foreach ($arr_date as $data) {
                    if (!$this->isWeekend($data) && !isset($holidays[$data])) {
                        if (!isset($cutiByNik[$nik][$data]) && !isset($sakitByNik[$nik][$data])) {
                            if (!$tanggal_bergabung || $tanggal_bergabung <= $data) {
                                $arr_base[] = $data;
                            }
                        }
                    }
                }
            } else {
                $empWorkingDays = $workingDaysList->get($nik) ?? collect();
                foreach ($empWorkingDays as $_data) {
                    $wDate = $_data->working_date;
                    if (!isset($cutiByNik[$nik][$wDate]) && !isset($sakitByNik[$nik][$wDate])) {
                        if (!$tanggal_bergabung || $tanggal_bergabung <= $wDate) {
                            $arr_base[] = $wDate;
                        }
                    }
                }
            }

            // Ambil semua clockin karyawan dalam rentang tanggal
            $empClockins = ($clockinsList->get($nik) ?? collect())
                ->pluck('att_date')
                ->unique()
                ->all();

            // Jika ada Clock-in di luar basis (lembur/piket akhir pekan/libur)
            $extraHolidayClockins = array_diff($empClockins, $arr_base);
            foreach ($extraHolidayClockins as $extraDate) {
                if (!isset($cutiByNik[$nik][$extraDate]) && !isset($sakitByNik[$nik][$extraDate])) {
                    $arr_base[] = $extraDate;
                }
            }

            $arr_base = array_values(array_unique($arr_base));
            $countBase = count($arr_base);
            $validClockins = array_intersect($empClockins, $arr_base);
            $countClock = count($validClockins);

            $totalBase += $countBase;
            $totalHadir += $countClock;
            $persentase = $countBase > 0 ? ($countClock / $countBase) * 100 : 0;

            $trx[] = [
                'nik' => $nik,
                'nama' => $_karyawan->nama,
                'email' => $_karyawan->email,
                'wg_name' => $_karyawan->_wg->wg_name ?? '-',
                'level' => $_karyawan->_level->level ?? '-',
                'subdepartemen' => $_karyawan->_subdepartemen->nama ?? '-',
                'nama_layer_1' => $_karyawan->nama_layer_1 ?? '-',
                'email_layer_1' => $_karyawan->email_layer_1 ?? '-',
                'arr_base' => $countBase,
                'arr_clock' => $countClock,
                'arr_absen' => max(0, $countBase - $countClock),
                'persentase' => round($persentase, 1),
            ];
        }

        $overallPct = $totalBase > 0 ? round(($totalHadir / $totalBase) * 100, 1) : 0;

        return [
            'trx' => $trx,
            'total_base' => $totalBase,
            'total_hadir' => $totalHadir,
            'persentase' => $overallPct,
            'date_from' => $date_from,
            'date_to' => $date_to,
        ];
    }

    // get tanggal tanggal
    public function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d')
    {
        $dates = [];
        $current = strtotime($first);
        $last = strtotime($last);
        while ($current <= $last) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }
        return $dates;
    }

    // cek apakah hari ini sabtu minggu
    public function isWeekend($date)
    {
        $weekDay = date('w', strtotime($date));
        return $weekDay == 0 || $weekDay == 6;
    }

    // cek apakah hari ini adalah hari libur nasional
    public function isHoliday($date)
    {
        $where = ['tanggal' => $date];
        return Holiday::where($where)->exists();
    }

    // hitung total hari kerja kalender dalam satu bulan (Senin-Jumat, di luar tanggal merah)
    public function getMonthWorkingDays($year, $month)
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $workingDays = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            if (!$this->isWeekend($dateStr) && !$this->isHoliday($dateStr)) {
                $workingDays++;
            }
        }
        return $workingDays;
    }

    // cek apakah hari ini adalah hari cutinya
    public function isCuti($nik, $date)
    {
        $where = ['nik_karyawan' => $nik];
        return TrxCuti::where($where)
            ->whereIn('status_cuti', [4, 6])
            ->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_akhir', '>=', $date)
            ->exists();
    }

    // cek apakah hari ini adalah hari izin sakitnya
    public function isSakit($nik, $date)
    {
        $where = ['nik_karyawan' => $nik];
        return TrxSakit::where($where)
            ->whereIn('status_sakit', [4, 6])
            ->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_akhir', '>=', $date)
            ->exists();
    }

    // cek apakah hari ini Clock-in
    public function isClockin($nik, $date)
    {
        $where = ['att_nik' => $nik, 'att_date' => $date, 'att_type' => 'Clock-in', 'att_data_src' => 'Regular'];
        return Attendance::where($where)->exists();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
