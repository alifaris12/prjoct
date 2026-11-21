@extends('layouts.matta.master')
@section('title', 'Overtime Propose — MATTA')
@section('ui-version', 'v1')
@section('ui-kind', 'overtime')

@section('content')
<div class="page-top">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('todolist.index') }}">Home</a>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <span>Overtime</span>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <span class="current" aria-current="page" data-crumb-current>Propose</span>
  </nav>
</div>

@if(Session::has('msg'))
<div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px 16px; margin: 0 0 16px 0; font-size: 14px; color: #065f46;">
  {{ Session::get('msg') }}
</div>
@endif

<section class="dt-card" data-datatable data-label="data overtime" data-file="overtime-propose" data-module="overtime">
  <div class="dt-head">
    <div class="dt-title-wrap">
      <div class="dt-icon">
        <svg class="icon icon-md" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
      </div>
      <div>
        <h1 class="dt-title">Data Overtime (Propose)</h1>
        <p class="dt-desc">Daftar riwayat dan permohonan lembur kerja (Overtime Propose) karyawan secara terintegrasi.</p>
      </div>
    </div>
    <div class="dt-actions">
      @can('staf_only')
      <button class="btn btn-module btn-md" type="button" onclick="PilihKaryawan()" style="padding:0 24px">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Add new
      </button>
      @endcan
      <button class="btn btn-primary btn-md" type="button" data-toggle-target="propose-filters" aria-expanded="false">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
      </button>
    </div>
  </div>

  <div class="dt-filters hidden" id="propose-filters" style="padding: 16px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
    <form action="{{ route('overtime.index') }}" method="get" style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">
      <label class="field" style="margin:0;">
        <span class="field-label">Dari Tanggal</span>
        <input class="form-control" name="date_from" type="date" value="{{ $date_from }}">
      </label>
      <label class="field" style="margin:0;">
        <span class="field-label">Sampai Tanggal</span>
        <input class="form-control" name="date_to" type="date" value="{{ $date_to }}">
      </label>
      <label class="field" style="margin:0;">
        <span class="field-label">Maker By</span>
        <input class="form-control" name="ot_maker_by" type="text" value="{{ $ot_maker_by }}" placeholder="Nama / NIP">
      </label>
      <div style="display:flex; gap:8px;">
        <button class="btn btn-primary btn-md" name="filter" value="true" type="submit">Filter</button>
        <a href="{{ route('overtime.index') }}" class="btn btn-outline btn-md">Reset</a>
      </div>
    </form>
  </div>

  <div class="dt-toolbar">
    <label class="dt-length">
      <span>Tampilkan</span>
      <select class="form-control" data-dt-length aria-label="Jumlah baris">
        <option value="5">5</option>
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50" selected>50</option>
      </select>
      <span>entri</span>
    </label>
    <label class="dt-search">
      <span class="input-icon">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input class="form-control" type="search" placeholder="Cari data overtime..." data-dt-search>
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
    <table class="dt">
      <thead>
        <tr>
          <th class="num" data-sort="num">No</th>
          <th data-sort="date">Created At</th>
          <th data-sort="text">Created By</th>
          <th data-sort="text">Created NIP</th>
          <th data-sort="text">Approved By</th>
          <th data-sort="text">Approved NIP</th>
          <th class="center" data-sort="text">Status</th>
          <th data-sort="date">Start</th>
          <th data-sort="date">Finish</th>
          <th data-sort="text">Title</th>
          <th class="center" data-noexport>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($ot as $data)
        <tr>
          <td class="num" data-dt-no>{{ ++$no }}</td>
          <td>{{ \Carbon\Carbon::parse($data->ot_maker_at)->format('d-m-Y H:i') }}</td>
          <td class="strong">{{ $data->_ot_maker_by->nama ?? '-' }}</td>
          <td class="mono">{{ $data->ot_maker_by }}</td>
          <td>{{ $data->_ot_approver_by->nama ?? '-' }}</td>
          <td class="mono">{{ $data->ot_approver_by }}</td>
          <td class="center">
            @if($data->ot_approver_status == 'Approve')
              <span class="badge badge-pill badge-green">Approve</span>
            @elseif($data->ot_approver_status == 'Waiting')
              <span class="badge badge-pill badge-orange">Waiting</span>
            @else
              <span class="badge badge-pill badge-red">{{ $data->ot_approver_status }}</span>
            @endif
          </td>
          <td>{{ \Carbon\Carbon::parse($data->ot_start_at)->format('d-m-Y H:i') }}</td>
          <td>{{ \Carbon\Carbon::parse($data->ot_end_at)->format('d-m-Y H:i') }}</td>
          <td>{{ $data->ot_title }}</td>
          <td class="center">
            <a href="{{ route('overtime.edit', [$data->id]) }}" class="icon-btn" title="View Detail">
              <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="dt-footer">
    <div class="dt-info" data-dt-info></div>
    <div class="pager" data-dt-pager></div>
  </div>
</section>

<!-- Modal Pilih Karyawan / Info -->
<div class="modal-backdrop" id="modalPilihKaryawan" style="display:none;" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" style="max-width: 500px;">
    <div class="modal-header">
      <div class="modal-title">Pesan Konfirmasi</div>
      <button class="icon-btn" type="button" onclick="tutupModalPilih()" aria-label="Tutup"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
    </div>
    <div class="modal-body" style="padding: 20px 24px; font-size: 14px; line-height: 1.6;">
      {{ App\Models\MtrUtility::find(52)->informasi ?? 'Pastikan jam lembur dan pekerjaan yang diajukan sudah sesuai dengan arahan atasan.' }}
    </div>
    <div class="modal-footer" style="padding: 12px 24px; display: flex; justify-content: flex-end; gap: 8px;">
      <button class="btn btn-outline" type="button" onclick="tutupModalPilih()">Batal</button>
      <button class="btn btn-primary" type="button" onclick="konfirmasiPilih()">Konfirmasi</button>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
function PilihKaryawan() {
  const modal = document.getElementById('modalPilihKaryawan');
  if (modal) {
    modal.style.display = 'flex';
    modal.classList.add('show');
  }
}
function tutupModalPilih() {
  const modal = document.getElementById('modalPilihKaryawan');
  if (modal) {
    modal.style.display = 'none';
    modal.classList.remove('show');
  }
}
function konfirmasiPilih() {
  tutupModalPilih();
  var ot_maker_by = encodeURIComponent("{{ Crypt::encrypt(auth()->user()->nik) }}");
  location.href = "{{ route('overtime.create') }}" + "?ot_maker_by=" + ot_maker_by;
}
</script>
@endsection
