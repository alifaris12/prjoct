@extends('layouts.matta.master')
@section('title', 'Home — MATTA')
@section('ui-version', 'v1')
@section('ui-kind', 'home')

@php
    $feedList = \App\Models\Feeds::where('is_active', 1)->orderBy('id', 'desc')->take(9)->get();

    function formatItemsToRows($items, $prefix, $defaultForm) {
        $rows = [];
        if (!empty($items)) {
            foreach ($items as $item) {
                $rows[] = [
                    'id' => '#' . $prefix . '-' . ($item->id_trx ?? $item->id),
                    'form' => $item->title ?? $defaultForm,
                    'file' => '-',
                    'name' => $item->nama ?? auth()->user()->name,
                    'nip' => $item->nik ?? auth()->user()->nik,
                    'role' => $item->jabatan ?? 'Karyawan',
                    'unit' => $item->unit_kerja ?? 'ALITA',
                    'date' => !empty($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->format('d M Y') : date('d M Y'),
                    'time' => !empty($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->format('H:i') . ' WIB' : date('H:i') . ' WIB',
                    'status' => 'Waiting Approval'
                ];
            }
        }
        return $rows;
    }

    $moreItems = array_merge(
        $trxBpd ? $trxBpd->all() : [],
        $trxPelatihan ? $trxPelatihan->all() : [],
        $trxRekomendasi ? $trxRekomendasi->all() : [],
        $trxKebutuhan ? $trxKebutuhan->all() : []
    );

    $moduleData = [
        'overtime' => [
            'name' => 'Overtime',
            'list' => 'Daftar Lembur',
            'short' => 'Lembur',
            'apply' => 'Ajukan Lembur',
            'desc' => 'Riwayat & status pengajuan lembur karyawan beserta durasi dan tugas lembur',
            'href' => route('overtime.index'),
            'prefix' => 'OVT',
            'formName' => 'Formulir Lembur',
            'rows' => formatItemsToRows($trxOvertime, 'OVT', 'Formulir Lembur')
        ],
        'evaluation' => [
            'name' => 'Evaluation',
            'list' => 'Daftar Evaluasi',
            'short' => 'Evaluasi',
            'apply' => 'Ajukan Evaluasi',
            'desc' => 'Riwayat & status pengajuan evaluasi kinerja dan KPI berkala karyawan',
            'href' => route('penilaian.index'),
            'prefix' => 'EVL',
            'formName' => 'Formulir Evaluasi Kinerja',
            'rows' => formatItemsToRows($trxPenilaian, 'EVL', 'Formulir Evaluasi')
        ],
        'leave' => [
            'name' => 'Leave',
            'list' => 'Daftar Cuti',
            'short' => 'Cuti',
            'apply' => 'Ajukan Cuti',
            'desc' => 'Riwayat & status pengajuan cuti tahunan, cuti bersama, dan cuti khusus karyawan',
            'href' => route('cuti.index'),
            'prefix' => 'LVE',
            'formName' => 'Formulir Cuti Tahunan',
            'rows' => formatItemsToRows($trxCuti, 'LVE', 'Formulir Cuti')
        ],
        'permit' => [
            'name' => 'Permit',
            'list' => 'Daftar Izin',
            'short' => 'Izin',
            'apply' => 'Ajukan Izin',
            'desc' => 'Riwayat & status pengajuan izin terlambat, pulang awal, dan keluar kantor sementara',
            'href' => route('izin.index'),
            'prefix' => 'PRM',
            'formName' => 'Formulir Izin Keperluan Pribadi',
            'rows' => formatItemsToRows($trxIzin, 'PRM', 'Formulir Izin')
        ],
        'exit-clearance' => [
            'name' => 'Exit Clearance',
            'list' => 'Daftar Izin Keluar',
            'short' => 'Izin Keluar',
            'apply' => 'Ajukan Izin Keluar',
            'desc' => 'Riwayat & status proses serah terima aset, klirens departemen, dan exit clearance',
            'href' => route('nonaktif.index'),
            'prefix' => 'EXC',
            'formName' => 'Formulir Izin Keluar',
            'rows' => formatItemsToRows($trxNonAktif, 'EXC', 'Formulir Exit Clearance')
        ],
        'reimbursement' => [
            'name' => 'Reimbursement',
            'list' => 'Daftar Pengembalian',
            'short' => 'Pengembalian',
            'apply' => 'Ajukan Pengembalian',
            'desc' => 'Riwayat & status pengajuan klaim biaya transportasi, medis, dan operasional kerja',
            'href' => route('reimburse.index'),
            'prefix' => 'RMB',
            'formName' => 'Formulir Pengembalian',
            'rows' => formatItemsToRows($trxReimburse, 'RMB', 'Formulir Reimbursement')
        ],
        'sick-leave' => [
            'name' => 'Sick Leave',
            'list' => 'Daftar Izin Sakit',
            'short' => 'Izin Sakit',
            'apply' => 'Ajukan Izin Sakit',
            'desc' => 'Riwayat & status pengajuan izin sakit karyawan beserta surat keterangan dokter (MC)',
            'href' => route('sakit.index'),
            'prefix' => 'SCK',
            'formName' => 'Surat Keterangan Sakit',
            'rows' => formatItemsToRows($trxSakit, 'SCK', 'Formulir Izin Sakit')
        ],
        'confirmation' => [
            'name' => 'Confirmation',
            'list' => 'Daftar Konfirmasi',
            'short' => 'Konfirmasi',
            'apply' => 'Ajukan Konfirmasi',
            'desc' => 'Riwayat & status konfirmasi kehadiran, penyesuaian jam kerja, dan pengajuan lainnya',
            'href' => route('todolist.index'),
            'prefix' => 'CNF',
            'formName' => 'Formulir Konfirmasi Kehadiran',
            'rows' => formatItemsToRows($moreItems, 'CNF', 'Formulir Konfirmasi')
        ]
    ];
@endphp

@section('content')
<div class="page-top">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('todolist.index') }}">Home</a>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <span class="current" aria-current="page" data-crumb-current>Dashboard</span>
  </nav>
  <div class="version-switch">
    <span>Tampilan</span>
    <div class="seg">
      <a href="javascript:void(0)" class="active" data-ui-set="v1" aria-current="page">Versi 1</a>
    </div>
  </div>
</div>

<div data-page="dashboard" class="dashboard-content">
  <div class="att-alert" data-att-alert hidden role="status"></div>

  <div class="dash-greet-wrap">
    <h1 class="greeting">
      <span data-greeting>Good Morning</span>, <span class="accent">{{ auth()->user()->name }}</span>
    </h1>
    <p class="today"><span data-today>{{ date('l, d F Y') }}</span></p>
  </div>

  <section class="dash-top">
    <!-- Card 1: Attendance Card -->
    <div class="card attend-card-v4" data-attend-card>
      <div class="att-head">
        <div class="att-clock-row">
          <span class="att-big-time" data-clock>{{ date('H:i') }}</span>
          <span class="att-wib">WIB</span>
        </div>
        <button type="button" class="icon-btn att-reset" data-att-reset title="Reset presensi (demo)" aria-label="Reset presensi (demo)">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
            <path d="M3 3v5h5"/>
          </svg>
        </button>
      </div>
      <div class="att-record-box">
        <div class="att-record-label" data-attend-title>STATUS PRESENSI</div>
        <div class="att-record-time-row">
          <span class="att-record-time" data-attend-time>08:30</span>
          <span class="att-record-tz">WIB</span>
        </div>
        <div class="att-record-status" data-attend-badge>Tepat Waktu</div>
      </div>
      <button type="button" class="btn btn-clock-out" data-attend-cta data-state="in">
        <svg class="icon-clock-out" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
          <polyline points="10 17 5 12 10 7"></polyline>
          <line x1="5" y1="12" x2="15" y2="12"></line>
        </svg>
        <span data-attend-cta-label>Clock In</span>
      </button>
    </div>

    <!-- Card 2: Calendar Card -->
    <div class="card calendar-v4">
      <div>
        <div class="cal-head">
          <div class="cal-title-wrap">
            <div class="cal-ico-box">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="4" rx="3"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
            </div>
            <div>
              <div class="cal-title" data-cal-title>{{ date('F Y') }}</div>
              <div class="cal-sub">Kalender Presensi</div>
            </div>
          </div>
          <div class="cal-nav">
            <button type="button" data-cal-prev aria-label="Bulan sebelumnya">
              <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button type="button" data-cal-next aria-label="Bulan berikutnya">
              <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </button>
          </div>
        </div>
        <div class="cal-dow-grid">
          <div class="cal-dow-cell cal-dow-sunday">MIN</div>
          <div class="cal-dow-cell">SEN</div>
          <div class="cal-dow-cell">SEL</div>
          <div class="cal-dow-cell">RAB</div>
          <div class="cal-dow-cell">KAM</div>
          <div class="cal-dow-cell">JUM</div>
          <div class="cal-dow-cell">SAB</div>
        </div>
        <div class="cal-days-grid" data-cal-grid></div>
      </div>
      <div class="cal-summary-row">
        <div class="cal-stat-card">
          <div class="cal-stat-val c-green" data-cal-present>21</div>
          <div class="cal-stat-lbl">Hadir</div>
        </div>
        <div class="cal-stat-card">
          <div class="cal-stat-val" style="color: #eab308;" data-cal-late>0</div>
          <div class="cal-stat-lbl">Terlambat</div>
        </div>
        <div class="cal-stat-card">
          <div class="cal-stat-val c-orange" data-cal-alpha>0</div>
          <div class="cal-stat-lbl">Alpha</div>
        </div>
        <div class="cal-stat-card">
          <div class="cal-stat-val c-blue" data-cal-cuti>1</div>
          <div class="cal-stat-lbl">Cuti</div>
        </div>
        <div class="cal-stat-card">
          <div class="cal-stat-val c-amber" data-cal-sakit>0</div>
          <div class="cal-stat-lbl">Sakit</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Container Info / Pengumuman -->
  <section class="card info-container" aria-labelledby="info-sec-title">
    <div class="info-container-head">
      <h2 id="info-sec-title">Info &amp; Pengumuman</h2>
      <a href="{{ route('feeds.view') }}" style="font-size: 13px; font-weight: 600; color: #f05a00; text-decoration: none;">Lihat Semua Feeds &rarr;</a>
    </div>
    <div class="info-container-body">
      <div class="info-carousel-wrapper">
        <button type="button" class="info-car-btn prev" data-info-prev aria-label="Sebelumnya">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6"/>
          </svg>
        </button>

        <div class="info-carousel-viewport">
          <div class="info-carousel-track" data-info-track>
            <!-- Slide 1 -->
            <div class="info-slide">
              @forelse($feedList->take(3) as $f)
              <a href="{{ route('feeds.view') }}" class="info-card">
                <div class="info-card-media">
                  <img src="{{ asset('matta-frontend/assets/img/feeds/pengumuman-penting.jpg') }}" alt="{{ $f->title }}" loading="lazy">
                </div>
                <div class="info-card-body">
                  <span class="info-badge">INFORMASI</span>
                  <div class="info-divider"></div>
                  <h3 class="info-title">{{ Str::limit($f->title, 45) }}</h3>
                </div>
              </a>
              @empty
              <a href="{{ route('feeds.view') }}" class="info-card">
                <div class="info-card-media">
                  <img src="{{ asset('matta-frontend/assets/img/feeds/happy-new-year.jpg') }}" alt="MATTA" loading="lazy">
                </div>
                <div class="info-card-body">
                  <span class="info-badge">PENGUMUMAN</span>
                  <div class="info-divider"></div>
                  <h3 class="info-title">SELAMAT DATANG DI MATTA</h3>
                </div>
              </a>
              @endforelse
            </div>
            @if($feedList->count() > 3)
            <!-- Slide 2 -->
            <div class="info-slide">
              @foreach($feedList->slice(3, 3) as $f)
              <a href="{{ route('feeds.view') }}" class="info-card">
                <div class="info-card-media">
                  <img src="{{ asset('matta-frontend/assets/img/feeds/libur-nasional.jpg') }}" alt="{{ $f->title }}" loading="lazy">
                </div>
                <div class="info-card-body">
                  <span class="info-badge">INFORMASI</span>
                  <div class="info-divider"></div>
                  <h3 class="info-title">{{ Str::limit($f->title, 45) }}</h3>
                </div>
              </a>
              @endforeach
            </div>
            @endif
          </div>
        </div>

        <button type="button" class="info-car-btn next" data-info-next aria-label="Berikutnya">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="m9 18 6-6-6-6"/>
          </svg>
        </button>
      </div>

      <div class="info-dots-wrap" data-info-dots>
        <button type="button" class="info-dot active" data-dot="0" aria-label="Slide 1"></button>
        @if($feedList->count() > 3)
        <button type="button" class="info-dot" data-dot="1" aria-label="Slide 2"></button>
        @endif
      </div>
    </div>
  </section>

  <!-- Activity Section / 8 Modules -->
  <section class="card activity" aria-labelledby="act-title">
    <div class="activity-head" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
      <h2 id="act-title" data-i18n="dash.activity">Aktifitas Karyawan &amp; To-Do List</h2>
      <a href="{{ route('todolist.tasks') }}" class="btn btn-outline btn-sm" style="font-weight: 600; font-size: 13px; color: var(--primary);">
        Pusat Tugas &amp; To-Do List Lengkap &rarr;
      </a>
    </div>
    <div class="activity-body">
      <div class="module-grid">
        <!-- 1. LEMBUR -->
        <button type="button" class="module-card" data-module="overtime" data-module-card="overtime" aria-pressed="true" style="--i:0">
          <span class="m-head"><span class="m-name">LEMBUR</span></span>
          <span class="m-corner-badge">{{ count($trxOvertime) }}</span>
          <img class="m-art" src="{{ asset('matta-frontend/assets/img/modules/overtime.png') }}" alt="" loading="lazy">
        </button>

        <!-- 2. EVALUASI -->
        <button type="button" class="module-card" data-module="evaluation" data-module-card="evaluation" aria-pressed="false" style="--i:1">
          <span class="m-head"><span class="m-name">EVALUASI</span></span>
          <span class="m-corner-badge">{{ count($trxPenilaian) }}</span>
          <img class="m-art" src="{{ asset('matta-frontend/assets/img/modules/evaluation.png') }}" alt="" loading="lazy">
        </button>

        <!-- 3. CUTI -->
        <button type="button" class="module-card" data-module="leave" data-module-card="leave" aria-pressed="false" style="--i:2">
          <span class="m-head"><span class="m-name">CUTI</span></span>
          <span class="m-corner-badge">{{ count($trxCuti) }}</span>
          <img class="m-art" src="{{ asset('matta-frontend/assets/img/modules/leave.png') }}" alt="" loading="lazy">
        </button>

        <!-- 4. IZIN -->
        <button type="button" class="module-card" data-module="permit" data-module-card="permit" aria-pressed="false" style="--i:3">
          <span class="m-head"><span class="m-name">IZIN</span></span>
          <span class="m-corner-badge">{{ count($trxIzin) }}</span>
          <img class="m-art" src="{{ asset('matta-frontend/assets/img/modules/permit.png') }}" alt="" loading="lazy">
        </button>

        <!-- 5. IZIN KELUAR -->
        <button type="button" class="module-card" data-module="exit-clearance" data-module-card="exit-clearance" aria-pressed="false" style="--i:4">
          <span class="m-head"><span class="m-name">IZIN KELUAR</span></span>
          <span class="m-corner-badge">{{ count($trxNonAktif) }}</span>
          <img class="m-art" src="{{ asset('matta-frontend/assets/img/modules/exit-clearance.png') }}" alt="" loading="lazy">
        </button>

        <!-- 6. PENGEMBALIAN -->
        <button type="button" class="module-card" data-module="reimbursement" data-module-card="reimbursement" aria-pressed="false" style="--i:5">
          <span class="m-head"><span class="m-name">PENGEMBALIAN</span></span>
          <span class="m-corner-badge">{{ count($trxReimburse) }}</span>
          <img class="m-art" src="{{ asset('matta-frontend/assets/img/modules/reimbursement.png') }}" alt="" loading="lazy">
        </button>

        <!-- 7. IZIN SAKIT -->
        <button type="button" class="module-card" data-module="sick-leave" data-module-card="sick-leave" aria-pressed="false" style="--i:6">
          <span class="m-head"><span class="m-name">IZIN SAKIT</span></span>
          <span class="m-corner-badge">{{ count($trxSakit) }}</span>
          <img class="m-art" src="{{ asset('matta-frontend/assets/img/modules/sick-leave.png') }}" alt="" loading="lazy">
        </button>

        <!-- 8. KONFIRMASI -->
        <button type="button" class="module-card" data-module="confirmation" data-module-card="confirmation" aria-pressed="false" style="--i:7">
          <span class="m-head"><span class="m-name">MORE</span></span>
          <span class="m-corner-badge {{ count($moreItems) > 5 ? 'badge-plus' : '' }}">{{ count($moreItems) }}</span>
          <img class="m-art" src="{{ asset('matta-frontend/assets/img/modules/confirmation.png') }}" alt="" loading="lazy">
        </button>
      </div>

      <!-- Detail Container (Tabel dinamis di bawah kartu) -->
      <div class="module-detail" data-module-detail data-module="overtime" aria-live="polite">
        <div class="md-head">
          <div class="md-title-wrap">
            <div class="md-ico" aria-hidden="true">
              <img data-md-img src="{{ asset('matta-frontend/assets/img/modules/overtime.png') }}" alt="">
            </div>
            <div>
              <h3 class="md-title" data-md-title>Daftar Lembur</h3>
              <p class="md-desc" data-md-desc>Riwayat &amp; status pengajuan lembur karyawan beserta durasi dan tugas lembur</p>
            </div>
          </div>
          <div class="md-actions">
            <a class="btn btn-apply-act" data-md-link href="{{ route('overtime.index') }}">
              <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
              <span data-md-apply-label>Ajukan Lembur</span>
            </a>
          </div>
        </div>
        <div class="md-body">
          <div class="md-table">
            <div class="scroll">
              <table>
                <thead>
                  <tr>
                    <th>NO</th>
                    <th>ID</th>
                    <th>FORMULIR</th>
                    <th>NAMA KARYAWAN</th>
                    <th>JABATAN</th>
                    <th>UNIT KERJA</th>
                    <th>TANGGAL</th>
                    <th>STATUS</th>
                  </tr>
                </thead>
                <tbody data-md-body>
                  <!-- Populated by app.js from module-data JSON -->
                </tbody>
              </table>
            </div>
            <div class="md-foot">
              <span data-md-info>Menampilkan data pengajuan</span>
              <div class="pager">
                <button type="button" disabled>Previous</button>
                <button type="button" class="active">1</button>
                <button type="button" disabled>Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Dynamic JSON data for modules -->
<script type="application/json" id="module-data">
{!! json_encode($moduleData) !!}
</script>

{{-- Compliance Modal PDP (jika berlaku) --}}
@if(!$is_pdp['is_pdp'] && $is_pdp['is_aktif'])
<div class="modal-backdrop show" id="autoShowModal" style="display:flex;">
    <div class="modal modal-lg" role="dialog" aria-modal="true" style="max-width: 750px;">
        <div class="modal-header">
            <h3 class="modal-title w-100 text-center">SURAT PERSETUJUAN PEMROSESAN DATA PRIBADI</h3>
        </div>
        <div class="modal-body" style="font-size: 14px; line-height: 1.6; max-height: 60vh; overflow-y: auto;">
            <ol style="padding-left: 20px;">
                <li>Saya memberikan persetujuan kepada PT Alita Praya Mitra (“Alita”) untuk melakukan pemrosesan Data Pribadi yang melekat pada identitas diri (“Data Pribadi”) yang telah saya berikan dan meratifikasi seluruh pemrosesan Data Pribadi yang telah dilakukan sebelum tanggal persetujuan ini.</li>
                <li>Bahwa Alita akan menggunakan Data Pribadi tersebut untuk kebutuhan penyimpanan sebagai database, pelaporan kepada pihak ketiga yang diwajibkan oleh Peraturan Perundang-undangan di Indonesia dan/atau kegiatan-kegiatan lain yang berkaitan dengan kepentingan bisnis Alita.</li>
                <li>Alita akan menyimpan Data Pribadi tersebut di sistem MATTA dan sistem database yang lain milik Alita dan Alita menjamin tidak ada penyalahgunaan atau penggunaan di luar kebutuhan yang tercantum dalam Ayat 2 di atas.</li>
                <li>Saya telah mengetahui dan/atau menyetujui bahwa terdapat Closed-Circuit Television (CCTV) yang terpasang di lingkungan kerja Alita untuk kepentingan Perlindungan Data Pribadi Alita.</li>
                <li>Saya memahami dan setuju bahwa Alita dapat mengungkapkan Data Pribadi tersebut kepada pihak ketiga lainnya sesuai peraturan perundang-undangan.</li>
            </ol>
        </div>
        <div class="modal-footer">
            <form action="{{ route('pdp.store') }}" method="post" enctype="multipart/form-data" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui data ini?');">
                @csrf
                <input type="hidden" name="is_setuju" value="1">
                <input type="hidden" name="back_url" value="{{ URL::full() }}">
                <button class="btn btn-primary" type="submit">Saya Setuju</button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Survey PORA Modal (jika berlaku) --}}
@if(!$is_survey_pora && now()->lessThanOrEqualTo(\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', '2026-02-05 19:00:00')))
<div class="modal-backdrop show" id="autoShowSurvey" style="display:flex;">
    <div class="modal modal-lg" role="dialog" aria-modal="true" style="max-width: 750px;">
        <div class="modal-header">
            <h3 class="modal-title w-100 text-center">SURVEY CABANG OLAHRAGA PORA 2026</h3>
        </div>
        <div class="modal-body" style="font-size: 14px; line-height: 1.6;">
            <p>Dear Insan ALITA,<br/>Mohon partisipasi untuk mengikuti survey pemilihan cabang olahraga dalam gelaran PORA 2026.</p>
            <form id="survey_pora" action="{{ route('survey_pora') }}" method="post" onsubmit="return confirm('Kirim pilihan survey?');">
                @csrf
                <div style="margin-bottom: 14px;">
                    <b>1. Cabor GROUP (Pilih 2)</b><br>
                    <label><input type="checkbox" name="opsi1[]" value="futsal"> Futsal (5 vs 5)</label><br>
                    <label><input type="checkbox" name="opsi1[]" value="voli"> Voli (6 vs 6)</label><br>
                    <label><input type="checkbox" name="opsi1[]" value="basket"> Basket (3 on 3)</label><br>
                    <small id="error_opsi1" style="color:red;display:none;">Wajib Pilih 2</small>
                </div>
                <div style="margin-bottom: 14px;">
                    <b>2. Cabor INDIVIDU / DUO (Pilih 1)</b><br>
                    <label><input type="checkbox" name="opsi2[]" value="badminton"> Badminton</label><br>
                    <label><input type="checkbox" name="opsi2[]" value="tenis"> Tenis Meja</label><br>
                    <label><input type="checkbox" name="opsi2[]" value="renang"> Renang</label><br>
                    <small id="error_opsi2" style="color:red;display:none;">Wajib Pilih 1</small>
                </div>
                <div style="margin-bottom: 14px;">
                    <b>3. Cabor E-SPORT (Pilih 1)</b><br>
                    <label><input type="checkbox" name="opsi3[]" value="ml"> Mobile Legends</label><br>
                    <label><input type="checkbox" name="opsi3[]" value="fifa"> FIFA</label><br>
                    <small id="error_opsi3" style="color:red;display:none;">Wajib Pilih 1</small>
                </div>
                <input type="hidden" name="back_url" value="{{ URL::full() }}">
                <button class="btn btn-primary" type="submit">Submit Survey</button>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
