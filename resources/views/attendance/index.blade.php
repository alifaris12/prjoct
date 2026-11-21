@extends('layouts.matta.master')
@section('title', 'Attendance Log — MATTA')

@section('content')
<nav class="breadcrumb" aria-label="Breadcrumb">
    <span>Attendance</span>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <span class="current" aria-current="page">Attendance Log</span>
</nav>

<div class="att-alert" data-att-alert hidden role="status"></div>

<div style="display:contents" data-page="att-log">
<section class="dt-card">
  <div class="dt-head">
    <div class="dt-title-wrap">
      <div class="dt-icon">
        <svg class="icon icon-md" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div>
        <h1 class="dt-title">Daftar Attendance Log</h1>
        <p class="dt-desc">Kelola, pantau, dan verifikasi riwayat presensi harian karyawan</p>
      </div>
    </div>
    <div class="dt-actions">
      <button class="btn btn-outline btn-md" type="submit" form="att-filter-form" name="download" value="true" style="gap:6px;">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
        Export XLS (Log & Rekap)
      </button>
      <button class="btn btn-primary btn-md" type="button" data-toggle-target="log-filters" aria-expanded="false">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
      </button>
    </div>
  </div>

  {{-- Filter Collapse --}}
  <div class="dt-filters hidden" id="log-filters" style="display: flex; flex-direction: column; gap: 12px; background: var(--bg-soft, #f8fafc); border-radius: 8px; padding: 16px; margin: 12px 0;">
    <form action="{{ route('attendance.index') }}" method="get" id="att-filter-form">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div>
          <label class="field" style="display:block; margin-bottom: 8px;">
            <span class="field-label" style="font-weight: 600; font-size: 13px;">Dari Tanggal</span>
            <input class="form-control" name="date_from" id="date_from" type="date" required value="{{ $date_from }}">
          </label>
          <label class="field" style="display:block; margin-bottom: 8px;">
            <span class="field-label" style="font-weight: 600; font-size: 13px;">Sampai Tanggal</span>
            <input class="form-control" name="date_to" id="date_to" type="date" required value="{{ $date_to }}">
          </label>
          <label class="field" style="display:block;">
            <span class="field-label" style="font-weight: 600; font-size: 13px;">Karyawan</span>
            <select class="form-control" id="str_att_nik_nama" name="str_att_nik_nama">
              <option value="">Semua Karyawan</option>
              @foreach ($att_nik_nama as $item)
                <option value="{{ $item->att_nik }}" @if($item->att_nik == $str_att_nik_nama) selected @endif>{{ $item->nama }} ({{ $item->att_nik }})</option>
              @endforeach
            </select>
          </label>
        </div>

        <div>
          <div style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span>Action (Tipe)</span>
            <label style="font-size: 11px; cursor: pointer;"><input type="checkbox" onClick="toggleCheck('att_type[]', this)"> Semua</label>
          </div>
          <div style="max-height: 110px; overflow-y: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 12px; margin-bottom: 10px;">
            @foreach ($att_type as $item)
              <label style="display: block; margin-bottom: 4px; cursor: pointer;">
                <input type="checkbox" name="att_type[]" value="{{ $item }}" @if(in_array($item, $arr_att_type)) checked @endif> {{ $item }}
              </label>
            @endforeach
          </div>

          <div style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span>Status Presensi</span>
            <label style="font-size: 11px; cursor: pointer;"><input type="checkbox" onClick="toggleCheck('att_status[]', this)"> Semua</label>
          </div>
          <div style="max-height: 110px; overflow-y: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 12px;">
            @foreach ($att_status as $item)
              <label style="display: block; margin-bottom: 4px; cursor: pointer;">
                <input type="checkbox" name="att_status[]" value="{{ $item }}" @if(in_array($item, $arr_att_status)) checked @endif> {{ $item }}
              </label>
            @endforeach
          </div>
        </div>

        <div>
          <div style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span>Sumber Data (Type)</span>
            <label style="font-size: 11px; cursor: pointer;"><input type="checkbox" onClick="toggleCheck('att_data_src[]', this)"> Semua</label>
          </div>
          <div style="max-height: 120px; overflow-y: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 12px; margin-bottom: 10px;">
            @foreach ($att_data_src as $item)
              <label style="display: block; margin-bottom: 4px; cursor: pointer;">
                <input type="checkbox" name="att_data_src[]" value="{{ $item }}" @if(in_array($item, $arr_att_data_src)) checked @endif> {{ $item }}
              </label>
            @endforeach
          </div>

          <div style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span>Working Group</span>
            <label style="font-size: 11px; cursor: pointer;"><input type="checkbox" onClick="toggleCheck('wg_id[]', this)"> Semua</label>
          </div>
          <div style="max-height: 120px; overflow-y: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 12px;">
            @foreach ($wg_id as $item)
              <label style="display: block; margin-bottom: 4px; cursor: pointer;">
                <input type="checkbox" name="wg_id[]" value="{{ $item->id }}" @if(in_array($item->id, $arr_wg_id)) checked @endif> {{ $item->wg_name }}
              </label>
            @endforeach
          </div>
        </div>

        <div>
          <div style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span>Perusahaan</span>
            <label style="font-size: 11px; cursor: pointer;"><input type="checkbox" onClick="toggleCheck('perusahaan[]', this)"> Semua</label>
          </div>
          <div style="max-height: 120px; overflow-y: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; font-size: 12px; margin-bottom: 16px;">
            @foreach ($perusahaan as $item)
              <label style="display: block; margin-bottom: 4px; cursor: pointer;">
                <input type="checkbox" name="perusahaan[]" value="{{ $item->id }}" @if(in_array($item->id, $arr_perusahaan)) checked @endif> {{ $item->nama }}
              </label>
            @endforeach
          </div>
          <div style="display: flex; gap: 8px;">
            <button class="btn btn-primary" id="filter" name="filter" value="true" type="submit" style="flex: 1;">
              <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg> Terapkan
            </button>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline" style="text-align: center;">Reset</a>
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- Subbar Tabs --}}
  <div class="dt-subbar">
    <div class="seg" role="tablist" aria-label="Jenis data">
      <button type="button" role="tab" aria-selected="true" aria-controls="panel-raw">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
        Raw Data
      </button>
      <button type="button" role="tab" aria-selected="false" aria-controls="panel-duration">
        Duration
      </button>
    </div>
    <div class="period">
      Periode: <span class="badge badge-pill badge-primary">{{ \Carbon\Carbon::parse($date_from)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($date_to)->format('d M Y') }}</span>
    </div>
  </div>

  {{-- TAB 1: RAW DATA --}}
  <div id="panel-raw" data-datatable data-label="data presensi" data-file="attendance-log">
    <div class="dt-toolbar">
      <label class="dt-length">
        <span>Show</span>
        <select class="form-control" data-dt-length aria-label="Jumlah baris">
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span>entries</span>
      </label>
      <label class="dt-search">
        <span data-i18n="common.search">Search:</span>
        <span class="input-icon">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          <input class="form-control" type="search" placeholder="Cari data presensi..." data-dt-search>
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
            <th data-sort="date">Tanggal<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Jam<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Nama<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">NIP<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th class="center" data-sort="num">ID_FP<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Perusahaan<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Direktorat<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Divisi<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Departemen<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Lokasi<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Working Group<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th class="center" data-sort="text">Action<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th class="center" data-sort="num">Longitude<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th class="center" data-sort="num">Latitude<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th class="center" data-sort="text">Address<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th class="center" data-noexport>Map</th>
            <th class="center" data-sort="text">Type<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th class="center" data-sort="text">Source<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($att as $data)
            @php
              $att_property = json_decode($data->att_property, true) ?? [];
              $karyawan     = $data->_att_nik;

              $actionPill = $data->att_type == 'Clock-in' ? 'pill-blue' : ($data->att_type == 'Clock-out' ? 'pill-green' : 'pill-amber');
              $srcPill = !empty($data->id_fp_trx) && $data->_fp_trx ? 'src-finger' : 'src-matta';
              $srcName = !empty($data->id_fp_trx) && $data->_fp_trx ? 'FINGER-'.$data->_fp_trx->id_mesin : 'MATTA';

              $hasCoords = !empty($data->att_long) && !empty($data->att_lat) && floatval($data->att_long) != 0 && floatval($data->att_lat) != 0;

              $detailJson = [
                'eyebrow' => 'Attendance',
                'title' => $data->att_type . ' · Jam ' . \Carbon\Carbon::parse($data->att_timestamp)->format('H:i:s'),
                'icon' => 'clock',
                'badges' => [
                  [$data->att_type, $data->att_type == 'Clock-in' ? 'blue' : 'green'],
                  [$srcName, str_contains($srcName, 'FINGER') ? 'purple' : 'orange']
                ],
                'person' => [
                  'name' => $karyawan?->nama ?? $data->att_nik,
                  'nip' => $data->att_nik,
                  'role' => $att_property['dep'] ?? ($karyawan?->jabatan ?? '-')
                ],
                'sections' => [
                  [
                    'h' => 'Data Presensi',
                    'rows' => [
                      ['Tanggal', \Carbon\Carbon::parse($data->att_date)->format('d-m-Y')],
                      ['Jam Presensi', \Carbon\Carbon::parse($data->att_timestamp)->format('H:i:s')],
                      ['Action', $data->att_type],
                      ['Type', $data->att_data_src],
                      ['Working Group', $att_property['wg_name'] ?? '-'],
                      ['Source', $srcName],
                      ['ID_FP', !empty($data->id_fp_trx) ? (string)($karyawan?->id_fp ?? '-') : '-']
                    ]
                  ],
                  [
                    'h' => 'Organisasi',
                    'rows' => [
                      ['Perusahaan', $att_property['perusahaan'] ?? '-'],
                      ['Direktorat', $att_property['divisi'] ?? '-'],
                      ['Divisi', $att_property['dep'] ?? '-'],
                      ['Departemen', $att_property['sub_dep'] ?? '-'],
                      ['Lokasi', $karyawan?->alamat_kantor ?? '-']
                    ]
                  ],
                  [
                    'h' => 'Lokasi Presensi',
                    'rows' => [
                      ['Longitude', (string)($data->att_long ?? '-')],
                      ['Latitude', (string)($data->att_lat ?? '-')],
                      ['Address', $data->att_address ?? '-']
                    ]
                  ]
                ],
                'map' => [
                  'lat' => (string)($data->att_lat ?? '0'),
                  'lng' => (string)($data->att_long ?? '0'),
                  'label' => $data->att_address ?: 'Lokasi Presensi',
                  'valid' => $hasCoords
                ]
              ];
            @endphp
            <tr data-detail="{{ json_encode($detailJson) }}">
              <td class="num" data-dt-no></td>
              <td class="strong">{{ \Carbon\Carbon::parse($data->att_date)->format('d-m-Y') }}</td>
              <td class="mono font-semibold">{{ \Carbon\Carbon::parse($data->att_timestamp)->format('H:i:s') }}</td>
              <td class="cell-2">
                <div class="main-line">{{ $karyawan?->nama ?? $data->att_nik }}</div>
                <div class="sub-line">{{ $att_property['dep'] ?? '' }}</div>
              </td>
              <td class="mono">{{ $data->att_nik }}</td>
              <td class="center mono">@if(!empty($data->id_fp_trx)) {{ $karyawan?->id_fp }} @else <span class="muted">-</span> @endif</td>
              <td>{{ $att_property['perusahaan'] ?? '-' }}</td>
              <td>{{ $att_property['divisi'] ?? '-' }}</td>
              <td>{{ $att_property['dep'] ?? '-' }}</td>
              <td>{{ $att_property['sub_dep'] ?? '-' }}</td>
              <td class="strong">{{ $karyawan?->alamat_kantor ?? '-' }}</td>
              <td class="muted-2">{{ $att_property['wg_name'] ?? '-' }}</td>
              <td class="center"><span class="pill {{ $actionPill }}">{{ $data->att_type }}</span></td>
              <td class="center mono muted-2">{{ $data->att_long ?: '-' }}</td>
              <td class="center mono muted-2">{{ $data->att_lat ?: '-' }}</td>
              <td class="center muted-2">{{ $data->att_address ?: '-' }}</td>
              <td class="center">
                @if($hasCoords)
                  <button type="button" class="map-pin" data-row-detail data-focus="map" aria-label="Lihat lokasi">
                    <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                  </button>
                @else
                  <span class="muted">-</span>
                @endif
              </td>
              <td class="center"><span class="pill pill-gray">{{ $data->att_data_src }}</span></td>
              <td class="center"><span class="src {{ $srcPill }}">{{ $srcName }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="dt-footer"><div class="dt-info" data-dt-info></div><div class="pager" data-dt-pager></div></div>
  </div>

  {{-- TAB 2: DURATION --}}
  <div id="panel-duration" class="hidden" data-datatable data-label="data durasi" data-file="attendance-duration">
    <div class="dt-toolbar">
      <label class="dt-length">
        <span>Show</span>
        <select class="form-control" data-dt-length aria-label="Jumlah baris">
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span>entries</span>
      </label>
      <label class="dt-search">
        <span data-i18n="common.search">Search:</span>
        <span class="input-icon">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          <input class="form-control" type="search" placeholder="Cari data durasi..." data-dt-search>
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
            <th class="num" data-sort="num">No<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="date">Tanggal<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Nama<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">NIP<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Jam Masuk<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Jam Keluar<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th data-sort="text">Durasi<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
            <th class="center" data-sort="text">Type<svg class="icon sort" viewBox="0 0 24 24" aria-hidden="true"><path d="m21 16-4 4-4-4"/><path d="M17 20V4"/><path d="m3 8 4-4 4 4"/><path d="M7 4v16"/></svg></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($duration as $item)
            @php $att_property = json_decode($item['att_property'], true) ?? []; @endphp
            <tr>
              <td class="num" data-dt-no></td>
              <td class="strong">{{ \Carbon\Carbon::parse($item['att_date'])->format('d-m-Y') }}</td>
              <td class="cell-2">
                <div class="main-line">{{ $item['nama'] }}</div>
                <div class="sub-line">{{ $att_property['dep'] ?? '' }}</div>
              </td>
              <td class="mono">{{ $item['att_nik'] }}</td>
              <td class="mono">{{ !empty($item['Clock-in']) ? \Carbon\Carbon::parse($item['Clock-in'])->format('H:i') : '-' }}</td>
              <td class="mono">{{ !empty($item['Clock-out']) ? \Carbon\Carbon::parse($item['Clock-out'])->format('H:i') : '-' }}</td>
              <td class="strong">{{ $item['durasi'] ?: '-' }}</td>
              <td class="center">
                @if($item['att_data_src'] == 'Regular')
                  <span class="badge badge-pill badge-green">Regular</span>
                @elseif($item['att_data_src'] == 'Overtime')
                  <span class="badge badge-pill badge-primary">Overtime</span>
                @else
                  <span class="badge badge-pill badge-amber">{{ $item['att_data_src'] ?: '-' }}</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="dt-footer"><div class="dt-info" data-dt-info></div><div class="pager" data-dt-pager></div></div>
  </div>

  <div class="dt-updated">Data fingerprint disinkronkan otomatis setiap 15 menit.</div>
</section>
</div>
@endsection

@section('script')
<script>
function toggleCheck(name, source) {
    document.getElementsByName(name).forEach(cb => cb.checked = source.checked);
}
</script>
@endsection
