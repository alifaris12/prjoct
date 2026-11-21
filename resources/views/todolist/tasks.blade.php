@extends('layouts.matta.master')
@section('title', 'To-Do List & Task Center — MATTA')
@section('ui-version', 'v1')
@section('ui-kind', 'tasks')

@php
    function formatTaskRows($items, $prefix, $defaultForm) {
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

    $taskModules = [
        'overtime' => [
            'name' => 'Overtime',
            'label' => 'Lembur',
            'count' => count($trxOvertime),
            'href' => route('overtime.index'),
            'rows' => formatTaskRows($trxOvertime, 'OVT', 'Formulir Lembur')
        ],
        'evaluation' => [
            'name' => 'Evaluation',
            'label' => 'Evaluasi',
            'count' => count($trxPenilaian),
            'href' => route('penilaian.index'),
            'rows' => formatTaskRows($trxPenilaian, 'EVL', 'Formulir Evaluasi Kinerja')
        ],
        'leave' => [
            'name' => 'Leave',
            'label' => 'Cuti',
            'count' => count($trxCuti),
            'href' => route('cuti.index'),
            'rows' => formatTaskRows($trxCuti, 'LVE', 'Formulir Cuti')
        ],
        'permit' => [
            'name' => 'Permit',
            'label' => 'Izin',
            'count' => count($trxIzin),
            'href' => route('izin.index'),
            'rows' => formatTaskRows($trxIzin, 'PRM', 'Formulir Izin')
        ],
        'reimbursement' => [
            'name' => 'Reimbursement',
            'label' => 'Pengembalian',
            'count' => count($trxReimburse),
            'href' => route('reimburse.index'),
            'rows' => formatTaskRows($trxReimburse, 'RMB', 'Formulir Reimbursement')
        ],
        'exit-clearance' => [
            'name' => 'Exit Clearance',
            'label' => 'Izin Keluar',
            'count' => count($trxNonAktif),
            'href' => route('nonaktif.index'),
            'rows' => formatTaskRows($trxNonAktif, 'EXC', 'Formulir Exit Clearance')
        ],
        'other' => [
            'name' => 'Lainnya',
            'label' => 'Lainnya',
            'count' => count($moreItems),
            'href' => '#',
            'rows' => formatTaskRows($moreItems, 'MISC', 'Formulir Dokumen Lainnya')
        ],
    ];

    $totalTasksCount = 0;
    $allRows = [];
    foreach ($taskModules as $key => $mod) {
        $totalTasksCount += $mod['count'];
        foreach ($mod['rows'] as $r) {
            $r['category'] = $key;
            $allRows[] = $r;
        }
    }
@endphp

@section('content')
<div class="page-top" style="margin-bottom: 20px;">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('todolist.index') }}">Home</a>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <span class="current" aria-current="page">To-Do List (Task Center)</span>
  </nav>
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 8px;">
    <div>
      <h1 style="font-size: 24px; font-weight: 700; color: #0f172a; margin: 0;">Pusat Tugas &amp; Persetujuan (To-Do List)</h1>
      <p style="font-size: 14px; color: #64748b; margin: 4px 0 0;">Daftar tiket formulir dan permohonan yang menunggu tindak lanjut atau persetujuan Anda.</p>
    </div>
    <div style="display: flex; gap: 8px;">
      <a href="{{ route('todolist.index') }}" class="btn btn-outline btn-md">
        &larr; Kembali ke Beranda
      </a>
    </div>
  </div>
</div>

<section class="dt-card">
  {{-- Filter Kategori Tugas --}}
  <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
    <span style="font-size: 13px; font-weight: 600; color: #475569; margin-right: 8px;">Filter Modul:</span>
    <button type="button" class="btn btn-sm btn-primary task-filter-btn" data-cat="all">
      Semua ({{ $totalTasksCount }})
    </button>
    @foreach ($taskModules as $key => $mod)
      <button type="button" class="btn btn-sm btn-outline task-filter-btn" data-cat="{{ $key }}">
        {{ $mod['label'] }} ({{ $mod['count'] }})
      </button>
    @endforeach
  </div>

  {{-- Toolbar Pencarian --}}
  <div class="dt-toolbar" style="padding: 16px 20px;">
    <div class="dt-search">
      <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="11" cy="11" r="8"/>
        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="search" id="taskSearchInput" placeholder="Cari ID tiket, nama pemohon, atau jenis formulir..." style="width: 100%;">
    </div>
  </div>

  {{-- Tabel Tugas --}}
  <div class="table-wrap">
    <table class="dt" id="taskTable">
      <thead>
        <tr>
          <th>No</th>
          <th>ID Pengajuan</th>
          <th>Jenis Formulir</th>
          <th>Nama Pemohon</th>
          <th>Unit Kerja</th>
          <th>Tanggal &amp; Waktu</th>
          <th>Status</th>
          <th style="text-align: right;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($allRows as $idx => $row)
        <tr data-cat="{{ $row['category'] }}">
          <td>{{ $idx + 1 }}</td>
          <td><strong style="color: #0284c7;">{{ $row['id'] }}</strong></td>
          <td>{{ $row['form'] }}</td>
          <td>
            <div style="font-weight: 600; color: #0f172a;">{{ $row['name'] }}</div>
            <div style="font-size: 12px; color: #64748b;">{{ $row['nip'] }} &bull; {{ $row['role'] }}</div>
          </td>
          <td>{{ $row['unit'] }}</td>
          <td>
            <div>{{ $row['date'] }}</div>
            <div style="font-size: 11px; color: #94a3b8;">{{ $row['time'] }}</div>
          </td>
          <td>
            <span class="badge" style="background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;">
              {{ $row['status'] }}
            </span>
          </td>
          <td style="text-align: right;">
            <button type="button" class="btn btn-outline btn-sm" onclick="showTaskDetail('{{ $row['id'] }}', '{{ $row['form'] }}', '{{ $row['name'] }}', '{{ $row['nip'] }}', '{{ $row['role'] }}', '{{ $row['unit'] }}', '{{ $row['date'] }}', '{{ $row['time'] }}', '{{ $row['status'] }}')">
              Detail
            </button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align: center; padding: 48px 16px; color: #64748b;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom: 8px;">
              <rect width="18" height="18" x="3" y="3" rx="2"/>
              <path d="m9 12 2 2 4-4"/>
            </svg>
            <div style="font-weight: 600; font-size: 15px; color: #334155;">Tidak Ada Antrean Tugas</div>
            <p style="font-size: 13px; margin: 4px 0 0;">Semua tiket dan permohonan persetujuan telah Anda selesaikan.</p>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>

{{-- Modal Detail Tugas --}}
<div class="modal-backdrop" id="modalTaskDetail" style="display:none;" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" style="max-width: 600px; width: 90%;">
    <div class="modal-header">
      <div class="modal-title" id="mTaskTitle">Detail Pengajuan Tugas</div>
      <button class="icon-btn" type="button" onclick="closeTaskDetail()" aria-label="Tutup">
        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
      </button>
    </div>
    <div class="modal-body" style="padding: 20px 24px;">
      <table style="width: 100%; font-size: 14px; border-collapse: separate; border-spacing: 0 10px;">
        <tr><td style="width: 35%; color: #64748b;">ID Tiket</td><td id="mTaskId" style="font-weight: 600; color: #0284c7;">-</td></tr>
        <tr><td style="color: #64748b;">Jenis Formulir</td><td id="mTaskForm" style="font-weight: 600;">-</td></tr>
        <tr><td style="color: #64748b;">Nama Pemohon</td><td id="mTaskName">-</td></tr>
        <tr><td style="color: #64748b;">NIP / Jabatan</td><td id="mTaskRole">-</td></tr>
        <tr><td style="color: #64748b;">Unit Kerja</td><td id="mTaskUnit">-</td></tr>
        <tr><td style="color: #64748b;">Waktu Pengajuan</td><td id="mTaskTime">-</td></tr>
        <tr><td style="color: #64748b;">Status</td><td><span class="badge" id="mTaskStatus" style="background:#fef3c7; color:#b45309; padding:4px 10px; border-radius:999px;">Waiting Approval</span></td></tr>
      </table>
    </div>
    <div class="modal-footer" style="padding: 14px 24px; display: flex; justify-content: flex-end; gap: 8px;">
      <button class="btn btn-outline" type="button" onclick="closeTaskDetail()">Tutup</button>
      <a href="{{ route('todolist.index') }}" class="btn btn-primary" id="mTaskActionBtn">Buka di Home &rarr;</a>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const filterBtns = document.querySelectorAll('.task-filter-btn');
  const tableRows = document.querySelectorAll('#taskTable tbody tr');
  const searchInput = document.getElementById('taskSearchInput');

  filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      filterBtns.forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline');
      });
      this.classList.remove('btn-outline');
      this.classList.add('btn-primary');
      const cat = this.dataset.cat;
      tableRows.forEach(tr => {
        if (!tr.dataset.cat) return;
        if (cat === 'all' || tr.dataset.cat === cat) {
          tr.style.display = '';
        } else {
          tr.style.display = 'none';
        }
      });
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const q = this.value.toLowerCase().trim();
      tableRows.forEach(tr => {
        const text = tr.innerText.toLowerCase();
        tr.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }
});

function showTaskDetail(id, form, name, nip, role, unit, date, time, status) {
  document.getElementById('mTaskId').innerText = id;
  document.getElementById('mTaskForm').innerText = form;
  document.getElementById('mTaskName').innerText = name;
  document.getElementById('mTaskRole').innerText = nip + ' / ' + role;
  document.getElementById('mTaskUnit').innerText = unit;
  document.getElementById('mTaskTime').innerText = date + ' pukul ' + time;
  document.getElementById('mTaskStatus').innerText = status;
  const modal = document.getElementById('modalTaskDetail');
  if (modal) {
    modal.style.display = 'flex';
    modal.classList.add('show');
  }
}

function closeTaskDetail() {
  const modal = document.getElementById('modalTaskDetail');
  if (modal) {
    modal.style.display = 'none';
    modal.classList.remove('show');
  }
}
</script>
@endsection

