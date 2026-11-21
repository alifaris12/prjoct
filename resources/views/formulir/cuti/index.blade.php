@extends('layouts.matta.master')
@section('title', 'Leave — MATTA')

@section('content')
<nav class="breadcrumb" aria-label="Breadcrumb">
  <span>Forms</span>
  <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
  <span class="current" aria-current="page">Leave</span>
</nav>

@if(Session::has('msg'))
  <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-weight: 500;">
    {{ session('msg') }}
  </div>
@endif

<section class="dt-card" data-datatable data-label="data leave" data-file="leave" data-module="leave">
  <div class="dt-head">
    <div class="dt-title-wrap">
      <div class="dt-icon">
        <img src="{{ asset('matta-frontend/assets/img/modules/leave.png') }}" alt="Leave" width="28" height="28">
      </div>
      <div>
        <h1 class="dt-title">Leave</h1>
        <p class="dt-desc">Kelola dan pantau seluruh pengajuan cuti karyawan</p>
      </div>
    </div>
    <div class="dt-actions" style="display: flex; gap: 8px;">
      <button class="btn btn-outline btn-md" type="button" data-toggle-target="cuti-filters" aria-expanded="false">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg> Filter
      </button>
      <button class="btn btn-module btn-md" type="button" onclick="PilihKaryawan()">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Ajukan Cuti
      </button>
    </div>
  </div>

  {{-- Filters --}}
  <div class="dt-filters hidden" id="cuti-filters" style="display: flex; flex-direction: column; gap: 12px; background: var(--bg-soft, #f8fafc); border-radius: 8px; padding: 16px; margin: 12px 0;">
    @can('hc')
      <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 10px 14px; border-radius: 6px; margin-bottom: 8px;">
        <label style="font-weight: 600; font-size: 13px; color: #1e40af; margin-bottom: 4px; display: block;">HC: Ajukan Atas Nama Karyawan</label>
        <select class="form-control" id="nik_karyawan_select" name="nik_karyawan_select">
          <option value="0">Pilih Karyawan Lainnya...</option>
          @foreach ($karyawan as $item)
            <option value="{{ Crypt::encrypt($item->nik) }}">{{ $item->nama }} ({{ $item->nik }})</option>
          @endforeach
        </select>
      </div>
    @endcan

    <form action="{{ route('cuti.index') }}" method="get">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div>
          <label class="field" style="display:block; margin-bottom: 8px;">
            <span class="field-label" style="font-weight: 600; font-size: 13px;">Karyawan</span>
            <select class="form-control" id="nik_karyawan" name="nik_karyawan">
              <option value="" @if("" == $_nik_karyawan) selected @endif>Semua Karyawan</option>
              @foreach ($nik_karyawan as $item)
                <option value="{{ $item->nik }}" @if($item->nik == $_nik_karyawan) selected @endif>{{ $item->nama }} ({{ $item->nik }})</option>
              @endforeach
            </select>
          </label>
          <label class="field" style="display:block; margin-bottom: 8px;">
            <span class="field-label" style="font-weight: 600; font-size: 13px;">Dari Tanggal</span>
            <input class="form-control" name="date_from" id="date_from" type="date" required value="{{ $date_from }}">
          </label>
          <label class="field" style="display:block;">
            <span class="field-label" style="font-weight: 600; font-size: 13px;">Sampai Tanggal</span>
            <input class="form-control" name="date_to" id="date_to" type="date" required value="{{ $date_to }}">
          </label>
        </div>

        <div>
          <div style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span>Status Pengajuan</span>
            <label style="font-size: 11px; cursor: pointer;"><input type="checkbox" onClick="toggle_status_cuti(this)"> Semua</label>
          </div>
          <div style="max-height: 140px; overflow-y: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 12px;">
            @foreach ($status_cuti as $item)
              <label style="display: block; margin-bottom: 4px; cursor: pointer;">
                <input type="checkbox" name="status_cuti[]" value="{{ $item->id }}" @if(in_array($item->id, $arr_status_cuti)) checked @endif> {{ $item->status_cuti }}
              </label>
            @endforeach
          </div>
        </div>

        <div>
          <div style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span>Tipe Cuti</span>
            <label style="font-size: 11px; cursor: pointer;"><input type="checkbox" onClick="toggle_tipe_cuti(this)"> Semua</label>
          </div>
          <div style="max-height: 140px; overflow-y: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 12px;">
            @foreach ($tipe_cuti as $item)
              <label style="display: block; margin-bottom: 4px; cursor: pointer;">
                <input type="checkbox" name="tipe_cuti[]" value="{{ $item->id }}" @if(in_array($item->id, $arr_tipe_cuti)) checked @endif> {{ $item->tipe_cuti }}
              </label>
            @endforeach
          </div>
        </div>
      </div>

      <div style="display: flex; gap: 8px; margin-top: 14px;">
        <button class="btn btn-primary" id="filter" name="filter" value="true" type="submit">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg> Terapkan Filter
        </button>
        <a href="{{ route('cuti.index') }}" class="btn btn-outline">Reset</a>
      </div>
    </form>
  </div>

  {{-- Toolbar --}}
  <div class="dt-toolbar">
    <label class="dt-length">
      <span>Tampilkan</span>
      <select class="form-control" data-dt-length aria-label="Jumlah baris">
        <option value="10" selected>10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>
      <span>entri</span>
    </label>
    <label class="dt-search">
      <span class="input-icon">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input class="form-control" type="search" placeholder="Cari data cuti..." data-dt-search>
      </span>
    </label>
    <div class="dt-exports">
      <button class="btn btn-outline" type="button" data-dt-export="copy"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>Copy</button>
      <button class="btn btn-outline" type="button" data-dt-export="csv"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>CSV</button>
      <button class="btn btn-outline xls" type="button" data-dt-export="excel"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M8 13h2"/><path d="M14 13h2"/><path d="M8 17h2"/><path d="M14 17h2"/></svg>Excel</button>
      <button class="btn btn-outline pdf" type="button" data-dt-export="pdf"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>PDF</button>
      <button class="btn btn-outline" type="button" data-dt-export="print"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>Print</button>
    </div>
  </div>

  <div class="table-wrap">
    <table class="dt dt-wide">
      <thead>
        <tr>
          <th class="num" data-sort="num">No<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th data-sort="text">#ID Formulir<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th data-sort="text">Nama Karyawan<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th data-sort="text">Tipe Cuti<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th data-sort="date">Tanggal Cuti<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th class="center" data-sort="num">Hari<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th data-sort="text">PIC Pengganti<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th data-sort="text">Layer 1<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th data-sort="date">Created At<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th class="center" data-sort="text">Status<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          <th class="center" data-noexport>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($trx as $data)
          @php
            $karyawanData = $data->_karyawan;
            $statusName = $data->_status?->status_cuti ?? 'Draft';
            $badgeClass = 'badge-amber';
            if (str_contains(strtolower($statusName), 'approved') || str_contains(strtolower($statusName), 'disetujui') || str_contains(strtolower($statusName), 'selesai')) {
                $badgeClass = 'badge-green';
            } elseif (str_contains(strtolower($statusName), 'reject') || str_contains(strtolower($statusName), 'ditolak') || str_contains(strtolower($statusName), 'batal')) {
                $badgeClass = 'badge-red';
            }

            $pics = [];
            if(!empty($data->pic_property)) {
                foreach (explode(';', $data->pic_property) as $val) {
                    $decoded = json_decode($val, true);
                    if($decoded && isset($decoded['nama'])) $pics[] = $decoded['nama'];
                }
            }
            $picStr = !empty($pics) ? implode(', ', $pics) : '-';

            $sisa = App\Http\Controllers\TrxCutiController::getSisaCuti($data->nik_karyawan, $data->periode_cuti, $data->id);

            $detailJson = [
              'eyebrow' => 'Leave',
              'title' => '#CUTI-' . $data->id,
              'module' => 'leave',
              'img' => asset('matta-frontend/assets/img/modules/leave.png'),
              'badges' => [[$statusName, $badgeClass == 'badge-green' ? 'green' : ($badgeClass == 'badge-red' ? 'red' : 'amber')]],
              'person' => [
                'name' => $karyawanData?->nama ?? $data->nik_karyawan,
                'nip' => $data->nik_karyawan,
                'role' => $data->_tipe?->tipe_cuti ?? 'Cuti'
              ],
              'sections' => [
                [
                  'h' => 'Detail Pengajuan',
                  'rows' => [
                    ['ID Pengajuan', '#' . $data->id],
                    ['Tipe Cuti', $data->_tipe?->tipe_cuti ?? '-'],
                    ['Tanggal Mulai', \Carbon\Carbon::parse($data->tanggal_mulai)->format('d-m-Y')],
                    ['Tanggal Akhir', \Carbon\Carbon::parse($data->tanggal_akhir)->format('d-m-Y')],
                    ['Jumlah Hari', $data->jumlah_hari . ' hari'],
                    ['PIC Pengganti', $picStr],
                    ['Atasan (Layer 1)', $data->_atasan?->nama ?? '-'],
                    ['Sisa Cuti', (string)$sisa . ' hari'],
                    ['Alasan / Catatan', $data->alasan ?: '-']
                  ]
                ]
              ]
            ];
          @endphp
          <tr data-detail="{{ json_encode($detailJson) }}">
            <td class="num" data-dt-no></td>
            <td><span class="id-chip">#{{ $data->id }}</span></td>
            <td>
              <div class="person">
                <span class="avatar-initial">{{ substr($karyawanData?->nama ?? 'K', 0, 2) }}</span>
                <div>
                  <div class="person-name">{{ $karyawanData?->nama ?? $data->nik_karyawan }}</div>
                  <div class="person-sub mono">{{ $data->nik_karyawan }}</div>
                </div>
              </div>
            </td>
            <td>{{ $data->_tipe?->tipe_cuti ?? '-' }}</td>
            <td class="cell-2 mono">
              <div class="main-line">{{ \Carbon\Carbon::parse($data->tanggal_mulai)->format('d-m-Y') }}</div>
              <div class="sub-line">s/d {{ \Carbon\Carbon::parse($data->tanggal_akhir)->format('d-m-Y') }}</div>
            </td>
            <td class="center mono strong">{{ $data->jumlah_hari }}</td>
            <td>{{ $picStr }}</td>
            <td>{{ $data->_atasan?->nama ?? '-' }}</td>
            <td class="cell-2 mono" style="font-size: 11px;">
              {{ \Carbon\Carbon::parse($data->created_at)->format('d-m-Y H:i') }}
            </td>
            <td class="center">
              <span class="badge badge-pill {{ $badgeClass }}">{{ $statusName }}</span>
            </td>
            <td class="center">
              <div style="display: flex; gap: 4px; justify-content: center;">
                <button class="icon-btn" type="button" data-row-detail aria-label="Lihat detail">
                  <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <a class="icon-btn" href="{{ route('cuti.show', [(new App\Http\Controllers\OpenDataController)->encryptWithKey($data->id), 'view' => (new App\Http\Controllers\OpenDataController)->encryptWithKey('detail')]) }}" title="Halaman Detail" target="_blank">
                  <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                </a>
                <a class="icon-btn" href="{{ route('cuti.show', [(new App\Http\Controllers\OpenDataController)->encryptWithKey($data->id), 'view' => (new App\Http\Controllers\OpenDataController)->encryptWithKey('pdf')]) }}" title="Unduh PDF" target="_blank" style="color: #ef4444;">
                  <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/></svg>
                </a>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="dt-footer"><div class="dt-info" data-dt-info></div><div class="pager" data-dt-pager></div></div>
</section>
@endsection

@section('script')
<script>
function PilihKaryawan() {
    var nik_karyawan = "{{ Crypt::encrypt(auth()->user()->nik) }}";
    @can('hc')
    var sel = document.getElementById('nik_karyawan_select');
    if(sel && sel.value !== '0'){
        nik_karyawan = sel.value;
    }
    @endcan
    location.href = "{{ route('cuti.create') }}?nik_karyawan=" + encodeURIComponent(nik_karyawan);
}
function toggle_status_cuti(source) {
    document.getElementsByName('status_cuti[]').forEach(cb => cb.checked = source.checked);
}
function toggle_tipe_cuti(source) {
    document.getElementsByName('tipe_cuti[]').forEach(cb => cb.checked = source.checked);
}
</script>
@endsection
