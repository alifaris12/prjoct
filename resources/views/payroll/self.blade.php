@extends('layouts.matta.master')
@section('title', 'Payroll Slip — MATTA')
@section('ui-version', 'v1')
@section('ui-kind', 'payroll')

@php
    $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $hco = explode(',', App\Models\MtrUtility::find(32)->informasi ?? '');
@endphp

@section('content')
<div class="page-top">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('todolist.index') }}">Home</a>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <a href="javascript:void(0)">Payroll</a>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <span class="current" aria-current="page" data-crumb-current>Payroll Slip</span>
  </nav>
</div>

<section class="dt-card" data-datatable data-label="data slip gaji" data-file="payroll-slip" data-page="payroll">
  <div class="dt-head">
    <div class="dt-title-wrap">
      <div class="dt-icon">
        <svg class="icon icon-lg" viewBox="0 0 24 24" aria-hidden="true">
          <rect width="20" height="16" x="2" y="4" rx="2"/>
          <circle cx="8.5" cy="11" r="2"/>
          <path d="M5.5 16c.6-1.6 5.4-1.6 6 0"/>
          <path d="M14 10h4"/>
          <path d="M14 14h4"/>
        </svg>
      </div>
      <div>
        <h1 class="dt-title">Payroll Slip</h1>
        <p class="dt-desc">Kelola, tinjau, dan unduh dokumen slip gaji resmi karyawan</p>
      </div>
    </div>
    <div class="dt-actions">
      <button class="btn btn-primary btn-md" type="button" data-toggle-target="payroll-filters" aria-expanded="false">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <span data-i18n="common.filter">Filter Periode</span>
      </button>
    </div>
  </div>

  @if(count($payroll)>0 || count($payroll_h)>0)
  <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; margin: 16px 24px; font-size: 13px; color: #92400e;">
    <strong>Catatan:</strong> Masukkan tanggal lahir Anda dengan format <code>ddmmyyyy</code> sebagai password untuk membuka file PDF slip gaji (Contoh: jika lahir 2 Maret 2000 maka passwordnya <code>02032000</code>).
  </div>
  @endif

  <div class="dt-filters hidden" id="payroll-filters" style="padding: 16px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
    <form action="{{ route('payroll.slip', (new App\Http\Controllers\OpenDataController)->encryptWithKey(auth()->user()->nik)) }}" method="get" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
      <label class="field" style="margin:0;">
        <span class="field-label">Bulan - Tahun Dari</span>
        <input name="bulan_tahun_dari" class="form-control" type="month" value="{{ $bulan_tahun_dari ?? '' }}" required>
      </label>
      <label class="field" style="margin:0;">
        <span class="field-label">Sampai</span>
        <input name="bulan_tahun_sampai" class="form-control" type="month" value="{{ $bulan_tahun_sampai ?? '' }}" required>
      </label>
      <div style="display:flex; gap:8px;">
        <button class="btn btn-primary btn-md" name="filter" value="true" type="submit">Terapkan Filter</button>
        <a href="{{ route('payroll.slip', (new App\Http\Controllers\OpenDataController)->encryptWithKey(auth()->user()->nik)) }}" class="btn btn-outline btn-md">Reset</a>
      </div>
    </form>
  </div>

  <div class="dt-toolbar">
    <label class="dt-length">
      <span>Tampilkan</span>
      <select class="form-control" data-dt-length aria-label="Jumlah baris">
        <option value="5">5</option>
        <option value="10" selected>10</option>
        <option value="25">25</option>
        <option value="50">50</option>
      </select>
      <span>entri</span>
    </label>
    <label class="dt-search">
      <span class="input-icon">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input class="form-control" type="search" placeholder="Cari data slip gaji..." data-dt-search>
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
          <th data-sort="num">Tahun</th>
          <th data-sort="text">Bulan</th>
          <th data-sort="text">Nama</th>
          <th data-sort="text">NIP</th>
          <th class="center" data-noexport>Slip Gaji</th>
        </tr>
      </thead>
      <tbody>
        @php $no = 0; @endphp
        @forelse ($payroll as $data)
        <tr>
          <td class="num" data-dt-no>{{ ++$no }}</td>
          <td class="strong">{{ $data->tahun }}</td>
          <td class="strong">{{ $bulan[$data->bulan - 1] ?? $data->bulan }}</td>
          <td>
            <div class="person">
              <span class="avatar-initial">{{ substr($data->_nik->nama ?? auth()->user()->name, 0, 2) }}</span>
              <div>
                <div class="person-name">{{ $data->_nik->nama ?? auth()->user()->name }}</div>
                <div class="person-sub">{{ $data->_nik->jabatan ?? 'Karyawan' }}</div>
              </div>
            </div>
          </td>
          <td class="mono">{{ $data->nik }}</td>
          <td class="center">
            @if(in_array(auth()->user()->nik, $hco) || ((!empty($data->paid_at)) && (\Carbon\Carbon::parse($data->paid_at)->isPast() || \Carbon\Carbon::parse($data->paid_at)->isToday())))
              <a href="{{ route('payroll.slip.detail', [(new App\Http\Controllers\OpenDataController)->encryptWithKey($data->id), 'view' => 'pdf']) }}" class="btn btn-soft btn-sm" target="_blank">
                <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                Unduh PDF
              </a>
            @else
              <span class="badge badge-pill badge-gray">Belum Tersedia</span>
            @endif
          </td>
        </tr>
        @empty
          @if(count($payroll_h) == 0)
          <tr>
            <td colspan="6" style="text-align:center; padding:32px; color:#94a3b8;">
              Tidak ada data slip gaji pada periode ini.
            </td>
          </tr>
          @endif
        @endforelse

        @foreach ($payroll_h as $data)
        <tr>
          <td class="num" data-dt-no>{{ ++$no }}</td>
          <td class="strong">{{ $data->tahun }}</td>
          <td class="strong">{{ $bulan[$data->bulan - 1] ?? $data->bulan }}</td>
          <td>
            <div class="person">
              <span class="avatar-initial">{{ substr($data->_nik->nama ?? auth()->user()->name, 0, 2) }}</span>
              <div>
                <div class="person-name">{{ $data->_nik->nama ?? auth()->user()->name }}</div>
                <div class="person-sub">{{ $data->_nik->jabatan ?? 'Karyawan' }}</div>
              </div>
            </div>
          </td>
          <td class="mono">{{ $data->nik }}</td>
          <td class="center">
            @if(in_array(auth()->user()->nik, $hco) || ((!empty($data->paid_at)) && (\Carbon\Carbon::parse($data->paid_at)->isPast() || \Carbon\Carbon::parse($data->paid_at)->isToday())))
              <a href="{{ route('payroll.slip.detail', [(new App\Http\Controllers\OpenDataController)->encryptWithKey($data->id), 'view' => 'pdf_h']) }}" class="btn btn-soft btn-sm" target="_blank">
                <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                Unduh PDF
              </a>
            @else
              <span class="badge badge-pill badge-gray">Belum Tersedia</span>
            @endif
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
@endsection
