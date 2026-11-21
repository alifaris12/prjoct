@extends('layouts.matta.master')
@section('title', 'Attendance Recap — MATTA')

@section('content')
<nav class="breadcrumb" aria-label="Breadcrumb">
  <span>Attendance</span>
  <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
  <span class="current" aria-current="page">Attendance Recap</span>
</nav>

<div class="att-alert" data-att-alert hidden role="status"></div>

<style>
  .recap-filter-card {
    background: #ffffff;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  }
  .recap-preset-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s ease;
  }
  .recap-preset-btn:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1d4ed8;
  }
  .recap-preset-btn.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
  }
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }
  .kpi-card {
    background: #ffffff;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    position: relative;
    overflow: hidden;
  }
  .kpi-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }
  .kpi-title {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
  }
  .kpi-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .kpi-value {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
    margin-bottom: 6px;
  }
  .kpi-subtitle {
    font-size: 12px;
    color: #64748b;
  }
  .pct-bar-wrap {
    height: 6px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 6px;
    width: 100px;
  }
  .pct-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.3s ease;
  }
  .empty-prompt-card {
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    padding: 48px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
  }
</style>

{{-- FILTER SECTION --}}
<div class="recap-filter-card">
  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 18px;">
    <div style="display: flex; align-items: center; gap: 12px;">
      <div style="background: rgba(37, 99, 235, 0.1); color: #2563eb; border-radius: 12px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
        <svg class="icon icon-md" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
          <line x1="16" x2="16" y1="2" y2="6"/>
          <line x1="8" x2="8" y1="2" y2="6"/>
          <line x1="3" x2="21" y1="10" y2="10"/>
        </svg>
      </div>
      <div>
        <h2 style="font-size: 17px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Filter Rekapitulasi Kehadiran</h2>
        <p style="font-size: 13px; color: #64748b; margin: 0;">Tentukan rentang tanggal (tanggal awal & akhir) untuk menghitung dan melihat persentase kehadiran.</p>
      </div>
    </div>

    {{-- Quick Preset Buttons --}}
    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
      <span style="font-size: 12px; font-weight: 600; color: #64748b;">Preset Cepat:</span>
      <button type="button" class="recap-preset-btn" onclick="applyPreset('cutoff')">
        <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Periode Cut-Off (21 bln lalu – 20 bln ini)
      </button>
      <button type="button" class="recap-preset-btn" onclick="applyPreset('this_month')">
        Bulan Berjalan
      </button>
      <button type="button" class="recap-preset-btn" onclick="applyPreset('last_month')">
        Bulan Lalu
      </button>
    </div>
  </div>

  <form action="{{ route('recap_attendance.index') }}" method="get" id="recap-form">
    <input type="hidden" name="filter" value="true">

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
      
      {{-- Tanggal Dari --}}
      <div>
        <label for="date_from" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
          Tanggal Dari <span style="color: #ef4444;">*</span>
        </label>
        <input type="date" class="form-control" name="date_from" id="date_from" required value="{{ $date_from }}" style="height: 40px; font-weight: 600;">
      </div>

      {{-- Tanggal Sampai --}}
      <div>
        <label for="date_to" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
          Tanggal Sampai <span style="color: #ef4444;">*</span>
        </label>
        <input type="date" class="form-control" name="date_to" id="date_to" required value="{{ $date_to }}" style="height: 40px; font-weight: 600;">
      </div>

      {{-- Status Karyawan --}}
      <div>
        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">Status Karyawan</label>
        <div style="display: flex; gap: 12px; align-items: center; height: 40px; padding: 0 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
          @foreach ($is_aktif as $data)
            <label style="display: inline-flex; align-items: center; gap: 5px; font-size: 13px; cursor: pointer; margin: 0;">
              <input type="checkbox" name="is_aktif[]" value="{{ $data['id'] }}" @if(in_array($data['id'], $arr_is_aktif)) checked @endif> {{ $data['is_aktif'] }}
            </label>
          @endforeach
        </div>
      </div>

    </div>

    {{-- Filter Tambahan untuk HC (Perusahaan & Direktorat) --}}
    @can('hc')
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
        <div style="font-size: 11px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
          <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
          Filter Organisasi (Khusus HC)
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
              <span style="font-size: 12px; font-weight: 600; color: #475569;">Perusahaan</span>
              <label style="font-size: 11px; cursor: pointer; color: #2563eb;"><input type="checkbox" onClick="toggle_perusahaan(this)"> Semua</label>
            </div>
            <div style="max-height: 90px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px; font-size: 12px;">
              @foreach ($perusahaan as $data)
                <label style="display: block; margin-bottom: 3px; cursor: pointer;">
                  <input type="checkbox" name="kode_perusahaan[]" onClick="get_kode_perusahaan()" value="{{ $data->id }}" @if(in_array($data->id, $arr_perusahaan)) checked @endif> {{ $data->nama }}
                </label>
              @endforeach
            </div>
          </div>
          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
              <span style="font-size: 12px; font-weight: 600; color: #475569;">Direktorat</span>
              <label style="font-size: 11px; cursor: pointer; color: #2563eb;"><input type="checkbox" onClick="toggle_divisi(this)"> Semua</label>
            </div>
            <div style="max-height: 90px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px; font-size: 12px;" id="div_kode_divisi">
              @foreach ($divisi as $data)
                <label style="display: block; margin-bottom: 3px; cursor: pointer;">
                  <input type="checkbox" name="kode_divisi[]" onClick="get_kode_divisi()" value="{{ $data->id }}" @if(in_array($data->id, $arr_divisi)) checked @endif> {{ $data->nama }}
                </label>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    @endcan

    <div style="display: flex; gap: 10px; align-items: center;">
      <button class="btn btn-primary btn-md" id="filter" name="filter" value="true" type="submit" style="height: 40px; padding: 0 20px; border-radius: 8px;">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Terapkan Filter & Hitung Rekap
      </button>
      <a href="{{ route('recap_attendance.index') }}" class="btn btn-outline btn-md" style="height: 40px; padding: 0 16px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>
        Reset
      </a>
    </div>
  </form>
</div>

@if(!$isFiltered)
  {{-- PROMPT STATE JIKA USER BELUM FILTER TANGGAL --}}
  <div class="empty-prompt-card">
    <div style="width: 64px; height: 64px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
      <svg class="icon icon-lg" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 32px; height: 32px;">
        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
        <line x1="16" x2="16" y1="2" y2="6"/>
        <line x1="8" x2="8" y1="2" y2="6"/>
        <line x1="3" x2="21" y1="10" y2="10"/>
        <path d="m9 16 2 2 4-4"/>
      </svg>
    </div>
    <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0;">Persentase Kehadiran Belum Dihitung</h3>
    <p style="font-size: 14px; color: #64748b; max-width: 520px; line-height: 1.6; margin: 0 0 20px 0;">
      Silakan pilih rentang tanggal pada panel filter di atas (misal dari tanggal 21 bulan lalu s/d tanggal 20 bulan ini), lalu klik <strong>Terapkan Filter & Hitung Rekap</strong> agar persentase kehadiran karyawan keluar.
    </p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
      <button type="button" class="btn btn-outline btn-sm" onclick="applyPreset('cutoff'); document.getElementById('recap-form').submit();">
        Hitung Periode Cut-Off (21 bln lalu – 20 bln ini)
      </button>
      <button type="button" class="btn btn-outline btn-sm" onclick="applyPreset('this_month'); document.getElementById('recap-form').submit();">
        Hitung Bulan Berjalan
      </button>
    </div>
  </div>

@else
  {{-- KONTEN REKAPITULASI SETELAH FILTER DIJALANKAN --}}

  {{-- 1. KPI SUMMARY CARDS --}}
  <div class="kpi-grid">
    {{-- KPI 1: Rata-rata Persentase Kehadiran --}}
    <div class="kpi-card" style="border-left: 4px solid {{ $aggPersentase >= 90 ? '#10b981' : ($aggPersentase >= 75 ? '#f59e0b' : '#ef4444') }};">
      <div class="kpi-card-header">
        <span class="kpi-title">Tingkat Kehadiran</span>
        <div class="kpi-icon-wrap" style="background: {{ $aggPersentase >= 90 ? '#ecfdf5' : ($aggPersentase >= 75 ? '#fffbeb' : '#fef2f2') }}; color: {{ $aggPersentase >= 90 ? '#10b981' : ($aggPersentase >= 75 ? '#f59e0b' : '#ef4444') }};">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
      </div>
      <div>
        <div class="kpi-value" style="color: {{ $aggPersentase >= 90 ? '#059669' : ($aggPersentase >= 75 ? '#d97706' : '#dc2626') }};">
          {{ $aggPersentase }}%
        </div>
        <div class="kpi-subtitle" style="display: flex; align-items: center; gap: 8px;">
          <span class="badge badge-pill {{ $aggPersentase >= 90 ? 'badge-green' : ($aggPersentase >= 75 ? 'badge-amber' : 'badge-red') }}">
            {{ $aggPersentase >= 90 ? 'Sangat Baik' : ($aggPersentase >= 75 ? 'Cukup Baik' : 'Perlu Evaluasi') }}
          </span>
          <span>Rata-rata kehadiran</span>
        </div>
      </div>
    </div>

    {{-- KPI 2: Periode Rekap --}}
    <div class="kpi-card" style="border-left: 4px solid #3b82f6;">
      <div class="kpi-card-header">
        <span class="kpi-title">Periode Terpilih</span>
        <div class="kpi-icon-wrap" style="background: #eff6ff; color: #2563eb;">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
            <line x1="16" x2="16" y1="2" y2="6"/>
            <line x1="8" x2="8" y1="2" y2="6"/>
            <line x1="3" x2="21" y1="10" y2="10"/>
          </svg>
        </div>
      </div>
      <div>
        <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
          {{ \Carbon\Carbon::parse($date_from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($date_to)->format('d M Y') }}
        </div>
        <div class="kpi-subtitle">
          Total <strong>{{ $totalKaryawan }}</strong> karyawan dianalisis
        </div>
      </div>
    </div>

    {{-- KPI 3: Total Kehadiran --}}
    <div class="kpi-card" style="border-left: 4px solid #10b981;">
      <div class="kpi-card-header">
        <span class="kpi-title">Total Hadir (Clock-in)</span>
        <div class="kpi-icon-wrap" style="background: #ecfdf5; color: #10b981;">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <polyline points="16 11 18 13 22 9"/>
          </svg>
        </div>
      </div>
      <div>
        <div class="kpi-value">{{ number_format($aggTotalHadir) }} <span style="font-size: 14px; font-weight: 500; color: #64748b;">hari</span></div>
        <div class="kpi-subtitle">
          Dari total {{ number_format($aggTotalBase) }} hari kerja basis
        </div>
      </div>
    </div>

    {{-- KPI 4: Total Ketidakhadiran --}}
    <div class="kpi-card" style="border-left: 4px solid #f59e0b;">
      <div class="kpi-card-header">
        <span class="kpi-title">Total Absen</span>
        <div class="kpi-icon-wrap" style="background: #fffbeb; color: #f59e0b;">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" x2="12" y1="8" y2="12"/>
            <line x1="12" x2="12.01" y1="16" y2="16"/>
          </svg>
        </div>
      </div>
      <div>
        <div class="kpi-value">{{ number_format($aggTotalAbsen) }} <span style="font-size: 14px; font-weight: 500; color: #64748b;">hari</span></div>
        <div class="kpi-subtitle">
          Rasio ketidakhadiran: {{ $aggTotalBase > 0 ? round(($aggTotalAbsen / $aggTotalBase) * 100, 1) : 0 }}%
        </div>
      </div>
    </div>
  </div>

  {{-- 2. TABEL REKAPITULASI KEHADIRAN KARYAWAN --}}
  <section class="dt-card">
    <div class="dt-head">
      <div class="dt-title-wrap">
        <div class="dt-icon">
          <svg class="icon icon-md" viewBox="0 0 24 24" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        </div>
        <div>
          <h1 class="dt-title">Data Rekapitulasi Kehadiran Karyawan</h1>
          <p class="dt-desc">Rincian hari kerja, clock-in, dan persentase kehadiran per karyawan periode {{ \Carbon\Carbon::parse($date_from)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($date_to)->format('d M Y') }}</p>
        </div>
      </div>
    </div>

    <div data-datatable data-label="rekapitulasi kehadiran" data-file="attendance-recap">
      <div class="dt-toolbar">
        <label class="dt-length">
          <span>Show</span>
          <select class="form-control" data-dt-length aria-label="Jumlah baris">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="-1">Semua</option>
          </select>
          <span>entries</span>
        </label>
        <label class="dt-search">
          <span data-i18n="common.search">Search:</span>
          <span class="input-icon">
            <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input class="form-control" type="search" placeholder="Cari nama, NIP, departemen..." data-dt-search>
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
              <th data-sort="text">NIP<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
              <th data-sort="text">Nama Karyawan<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
              <th data-sort="text">Departemen<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
              <th data-sort="text">Level<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
              <th data-sort="text">Working Group<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
              <th class="center" data-sort="num">Hari Kerja<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
              <th class="center" data-sort="num">Hadir<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
              <th class="center" data-sort="num">Absen<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
              <th data-sort="num">Persentase Kehadiran<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($trx as $data)
              @php
                $pct = $data['persentase'];
                $pctColor = $pct >= 90 ? '#10b981' : ($pct >= 75 ? '#f59e0b' : '#ef4444');
                $badgeClass = $pct >= 90 ? 'badge-green' : ($pct >= 75 ? 'badge-amber' : 'badge-red');
              @endphp
              <tr>
                <td class="num" data-dt-no></td>
                <td class="mono font-semibold">{{ $data['nik'] }}</td>
                <td class="cell-2">
                  <div class="main-line font-bold">{{ $data['nama'] }}</div>
                  @if(!empty($data['email']))
                    <div class="sub-line">{{ $data['email'] }}</div>
                  @endif
                </td>
                <td>{{ $data['subdepartemen'] }}</td>
                <td><span class="badge badge-pill badge-sky">{{ $data['level'] }}</span></td>
                <td>{{ $data['wg_name'] }}</td>
                <td class="center font-semibold">{{ $data['arr_base'] }}</td>
                <td class="center font-bold" style="color: #059669;">{{ $data['arr_clock'] }}</td>
                <td class="center font-bold" style="color: {{ $data['arr_absen'] > 0 ? '#dc2626' : '#64748b' }};">{{ $data['arr_absen'] }}</td>
                <td>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="badge badge-pill {{ $badgeClass }}" style="min-width: 52px; text-align: center;">
                      {{ number_format($pct, 1) }}%
                    </span>
                    <div class="pct-bar-wrap">
                      <div class="pct-bar-fill" style="width: {{ min(100, $pct) }}%; background: {{ $pctColor }};"></div>
                    </div>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="center" style="padding: 32px 16px; color: #64748b;">
                  Tidak ada data kehadiran yang sesuai dengan filter yang dipilih.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="dt-footer"><div class="dt-info" data-dt-info></div><div class="pager" data-dt-pager></div></div>
    </div>
  </section>
@endif

@endsection

@section('script')
<script>
// Fungsi Presets Rentang Tanggal Cepat
function applyPreset(type) {
    const today = new Date();
    let fromDate, toDate;

    if (type === 'cutoff') {
        // Cut-off standar payroll: tanggal 21 bulan lalu s/d tanggal 20 bulan ini
        const curYear = today.getFullYear();
        const curMonth = today.getMonth(); // 0-indexed (0 = Jan)

        // 21 bulan lalu
        const prevMonthDate = new Date(curYear, curMonth - 1, 21);
        // 20 bulan ini
        const thisMonthDate = new Date(curYear, curMonth, 20);

        fromDate = formatDateYMD(prevMonthDate);
        toDate = formatDateYMD(thisMonthDate);
    } else if (type === 'this_month') {
        // Bulan berjalan: tanggal 1 s/d tanggal terakhir bulan ini
        const curYear = today.getFullYear();
        const curMonth = today.getMonth();

        const firstDay = new Date(curYear, curMonth, 1);
        const lastDay = new Date(curYear, curMonth + 1, 0);

        fromDate = formatDateYMD(firstDay);
        toDate = formatDateYMD(lastDay);
    } else if (type === 'last_month') {
        // Bulan lalu: tanggal 1 s/d tanggal terakhir bulan lalu
        const curYear = today.getFullYear();
        const curMonth = today.getMonth();

        const firstDay = new Date(curYear, curMonth - 1, 1);
        const lastDay = new Date(curYear, curMonth, 0);

        fromDate = formatDateYMD(firstDay);
        toDate = formatDateYMD(lastDay);
    }

    if (fromDate && toDate) {
        document.getElementById('date_from').value = fromDate;
        document.getElementById('date_to').value = toDate;
    }
}

function formatDateYMD(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

@can('hc')
function toggle_perusahaan(source) {
    document.getElementsByName('kode_perusahaan[]').forEach(cb => cb.checked = source.checked);
    getDivisi();
}
function toggle_divisi(source) {
    document.getElementsByName('kode_divisi[]').forEach(cb => cb.checked = source.checked);
}
function get_kode_perusahaan() { getDivisi(); }
function get_kode_divisi() { }

function getDivisi() {
    let p = [];
    document.querySelectorAll('input[name="kode_perusahaan[]"]:checked').forEach(cb => p.push(cb.value));
    if (p.length === 0) return;
    fetch("{{ route('getDivisi') }}/" + p.join(','))
      .then(res => res.json())
      .then(data => {
        let div = document.getElementById('div_kode_divisi');
        if (!div) return;
        div.innerHTML = '';
        data.forEach(item => {
          div.innerHTML += `<label style="display:block;margin-bottom:3px;cursor:pointer;"><input type="checkbox" name="kode_divisi[]" onClick="get_kode_divisi()" value="${item.id}"> ${item.nama}</label>`;
        });
      }).catch(()=>{});
}
@endcan
</script>
@endsection
