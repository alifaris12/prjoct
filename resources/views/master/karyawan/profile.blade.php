@extends('layouts.matta.master')
@section('title', 'My Profile — MATTA')

@section('content')
<nav class="breadcrumb" aria-label="Breadcrumb">
  <a href="{{ route('todolist.index') }}">Home</a>
  <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
  <span class="current" aria-current="page">My Profile</span>
</nav>

@if ($message = Session::get('success'))
  <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
    {{ $message }}
  </div>
@endif

@if (isset($errors) && count($errors) > 0)
  <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
    <h6 style="font-weight: 700; margin-bottom: 6px;">Perhatian / Error:</h6>
    <ul style="padding-left: 20px; margin: 0; font-size: 13px;">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="profile-wrap">
  {{-- Hero Section --}}
  <section class="profile-hero">
    <div class="hero-avatar">
      @if (file_exists(public_path().'/assets/images/matta/karyawan/'.$karyawan->nik.'/'.$karyawan->nik.'.jpg'))
        <img src="{{ asset('assets/images/matta/karyawan/'.$karyawan->nik.'/'.$karyawan->nik.'.jpg') }}" alt="{{ $karyawan->nama }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
      @else
        <svg class="icon icon-fill icon-lg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0Z"/></svg>
      @endif
      <span class="online" title="Online"></span>
    </div>
    <h1 class="hero-name">{{ $karyawan->nama }}</h1>
    <p class="hero-email">{{ $karyawan->email }}</p>
    <p class="hero-meta">
      {{ $karyawan->_jabatan?->jabatan ?? 'Karyawan' }} &nbsp;•&nbsp; {{ $karyawan->_perusahaan?->nama ?? 'PT ALITA PRAYA MITRA' }}
    </p>
  </section>

  {{-- Two Column Details --}}
  <div class="profile-cols">
    {{-- Left: Edit Form Data Pribadi --}}
    <form class="p-card" id="form-profile" action="{{ route('karyawan.update_profile', (new App\Http\Controllers\OpenDataController)->encryptWithKey($karyawan->nik)) }}" method="post" enctype="multipart/form-data" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan perubahan data profil ini?');">
      @method('POST')
      @csrf

      <div class="p-card-head">
        <div>
          <h3>Data Pribadi Karyawan</h3>
          <p>Informasi profil personal dan kontak</p>
        </div>
        <span class="badge badge-pill badge-dot {{ $karyawan->is_aktif == 1 ? 'badge-green' : 'badge-red' }}">
          {{ $karyawan->is_aktif == 1 ? 'Aktif' : 'Non Aktif' }}
        </span>
      </div>

      <div class="p-card-body">
        <div class="p-section">Data Identitas</div>

        <div class="p-row">
          <label>Nama Lengkap</label>
          <input class="form-control" value="{{ $karyawan->nama }}" readonly style="background: #f1f5f9; cursor: not-allowed;">
        </div>

        <div class="p-row">
          <label>Tanggal Lahir</label>
          <input class="form-control" type="date" name="tanggal_lahir" id="tanggal_lahir" value="{{ $karyawan->tanggal_lahir }}">
        </div>

        <div class="p-row">
          <label>Tempat Lahir</label>
          <input class="form-control" type="text" name="tempat_lahir" id="tempat_lahir" value="{{ $karyawan->tempat_lahir }}">
        </div>

        <div class="p-row">
          <label>Jenis Kelamin</label>
          <select class="form-control" id="kode_kelamin" name="kode_kelamin">
            <option value="" disabled>Pilih...</option>
            @foreach ($kelamin as $item)
              <option value="{{ $item->id }}" @if($item->id == $karyawan->kode_kelamin) selected @endif>{{ $item->kelamin }}</option>
            @endforeach
          </select>
        </div>

        <div class="p-row">
          <label>Agama</label>
          <select class="form-control" id="kode_agama" name="kode_agama">
            <option value="" disabled>Pilih...</option>
            @foreach ($agama as $item)
              <option value="{{ $item->id }}" @if($item->id == $karyawan->kode_agama) selected @endif>{{ $item->agama }}</option>
            @endforeach
          </select>
        </div>

        <div class="p-row">
          <label>Kewarganegaraan</label>
          <select class="form-control" id="kode_warga" name="kode_warga">
            <option value="" disabled>Pilih...</option>
            @foreach ($warga as $item)
              <option value="{{ $item->id }}" @if($item->id == $karyawan->kode_warga) selected @endif>{{ $item->kewarganegaraan }}</option>
            @endforeach
          </select>
        </div>

        <div class="p-row">
          <label>Status Sipil / Nikah</label>
          <select class="form-control" id="kode_kawin" name="kode_kawin">
            <option value="" disabled>Pilih...</option>
            @foreach ($kawin as $item)
              <option value="{{ $item->id }}" @if($item->id == $karyawan->kode_kawin) selected @endif>{{ $item->status_kawin }}</option>
            @endforeach
          </select>
        </div>

        <div class="p-row">
          <label>Nama Pasangan</label>
          <input class="form-control" type="text" name="nama_pasangan" id="nama_pasangan" value="{{ $karyawan->nama_pasangan }}" placeholder="Nama Suami / Istri (jika ada)">
        </div>

        <div class="p-section" style="margin-top: 14px;">Kontak &amp; Alamat</div>

        <div class="p-row">
          <label>Telepon 1 (Utama)</label>
          <input class="form-control mono" type="text" name="telepon1" id="telepon1" value="{{ $karyawan->telepon1 }}">
        </div>

        <div class="p-row">
          <label>Telepon 2 (Opsional)</label>
          <input class="form-control mono" type="text" name="telepon2" id="telepon2" value="{{ $karyawan->telepon2 }}">
        </div>

        <div class="p-row align-top">
          <label>Alamat Domisili</label>
          <textarea class="form-control" name="alamat_domisili" id="alamat_domisili" rows="2">{{ $karyawan->alamat_domisili }}</textarea>
        </div>

        <div class="p-row align-top">
          <label>Alamat KTP</label>
          <textarea class="form-control" name="alamat_ktp" id="alamat_ktp" rows="2">{{ $karyawan->alamat_ktp }}</textarea>
        </div>

        <div class="p-section" style="margin-top: 14px;">Nomor Identitas &amp; BPJS</div>

        <div class="p-row">
          <label>NIK KTP</label>
          <input class="form-control mono" type="text" name="no_ktp" id="no_ktp" value="{{ $karyawan->no_ktp }}">
        </div>

        <div class="p-row">
          <label>NPWP</label>
          <input class="form-control mono" type="text" name="no_npwp" id="no_npwp" value="{{ $karyawan->no_npwp }}">
        </div>

        <div class="p-row">
          <label>No. BPJS Kesehatan</label>
          <input class="form-control mono" type="text" name="no_bpjs" id="no_bpjs" value="{{ $karyawan->no_bpjs }}">
        </div>

        <div class="p-row">
          <label>No. BPJS Ketenagakerjaan</label>
          <input class="form-control mono" type="text" name="no_bpjstk" id="no_bpjstk" value="{{ $karyawan->no_bpjstk }}">
        </div>
      </div>

      <div class="p-card-foot">
        <span class="note">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
          Perubahan data pribadi akan diverifikasi oleh tim HR.
        </span>
        <button class="btn btn-primary" type="submit" style="padding: 0 24px;">Simpan Perubahan</button>
      </div>
    </form>

    {{-- Right: Informasi Struktural & Organisasi (Read-only) --}}
    <section class="p-card">
      <div class="p-card-head">
        <div>
          <h3>Penugasan &amp; Alur Persetujuan</h3>
          <p>Detail struktural organisasi dan kewenangan approval</p>
        </div>
        <span class="badge badge-pill badge-dot badge-blue">Terdaftar</span>
      </div>

      <div class="p-card-body">
        <div class="p-section">Detail Pekerjaan &amp; Organisasi</div>
        <div>
          <div class="kv-row"><span>Nomor Induk Pegawai (NIP):</span><span class="v"><span class="mono">{{ $karyawan->nik }}</span></span></div>
          <div class="kv-row"><span>Perusahaan:</span><span class="v">{{ $karyawan->_perusahaan?->nama ?? '-' }}</span></div>
          <div class="kv-row"><span>Direktorat:</span><span class="v">{{ $karyawan->_divisi?->nama ?? '-' }}</span></div>
          <div class="kv-row"><span>Divisi:</span><span class="v">{{ $karyawan->_departemen?->nama ?? '-' }}</span></div>
          <div class="kv-row"><span>Departemen:</span><span class="v">{{ $karyawan->_subdepartemen?->nama ?? '-' }}</span></div>
          <div class="kv-row"><span>Posisi / Jabatan:</span><span class="v">{{ $karyawan->_jabatan?->jabatan ?? '-' }}</span></div>
          <div class="kv-row"><span>Level:</span><span class="v">{{ $karyawan->_level?->level ?? '-' }}</span></div>
          <div class="kv-row"><span>Status Kepegawaian:</span><span class="v"><span class="badge badge-pill badge-amber">{{ $karyawan->_status?->status_karyawan ?? '-' }}</span></span></div>
          <div class="kv-row"><span>Jadwal / Working Group:</span><span class="v">{{ $karyawan->_wg?->wg_name ?? '-' }}</span></div>
          <div class="kv-row"><span>Email Perusahaan:</span><span class="v">{{ $karyawan->email }}</span></div>
        </div>

        <div class="p-section" style="margin-top: 14px;">Alur Persetujuan (Approval Layer)</div>
        <div>
          <div class="kv-row">
            <span>Approval Layer 1:</span>
            <span class="v">{{ $karyawan->_layer1?->nama ?? '-' }} <small class="muted-2">(Atasan Langsung)</small></span>
          </div>
          <div class="kv-row">
            <span>Approval Layer 2:</span>
            <span class="v">{{ $karyawan->_layer2?->nama ?? '-' }} <small class="muted-2">(Reviewer)</small></span>
          </div>
          <div class="kv-row">
            <span>Approval Layer 3:</span>
            <span class="v">{{ $karyawan->_layer3?->nama ?? '-' }} <small class="muted-2">(Final Approver)</small></span>
          </div>
        </div>
      </div>
    </section>
  </div>

  {{-- Bottom Tabs: Riwayat Anak, Pendidikan, Pelatihan, Sertifikasi --}}
  <section class="p-card" style="margin-top: 24px;">
    <div class="tabs-line" role="tablist" aria-label="Data riwayat">
      <button type="button" role="tab" aria-selected="true" aria-controls="panel-anak">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Nama Anak <span class="count">{{ count($anak) }}</span>
      </button>
      <button type="button" role="tab" aria-selected="false" aria-controls="panel-pendidikan">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        Pendidikan <span class="count">{{ count($pendidikan) }}</span>
      </button>
      <button type="button" role="tab" aria-selected="false" aria-controls="panel-pelatihan">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>
        Pelatihan <span class="count">{{ count($pelatihan) }}</span>
      </button>
      <button type="button" role="tab" aria-selected="false" aria-controls="panel-sertifikasi">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
        Sertifikasi <span class="count">{{ count($sertifikasi) }}</span>
      </button>
    </div>

    {{-- TAB 1: ANAK --}}
    <div id="panel-anak" style="padding: 20px;">
      <div style="margin-bottom: 14px;">
        <a href="{{ route('karyawananak.create', ['nik'=>(new App\Http\Controllers\OpenDataController)->encryptWithKey($karyawan->nik), 'karyawan_id'=>(new App\Http\Controllers\OpenDataController)->encryptWithKey($karyawan->id), 'src'=>'open'] ) }}" class="btn btn-primary btn-sm">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
          Tambah Data Anak
        </a>
      </div>
      <div class="table-wrap">
        <table class="dt" style="min-width: 560px;">
          <thead>
            <tr>
              <th class="num">No</th>
              <th>Urutan</th>
              <th>Nama Anak</th>
              <th>Tempat Lahir</th>
              <th>Tanggal Lahir</th>
              <th>Jenis Kelamin</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($anak as $item)
              <tr>
                <td class="num">{{ ++$no_anak }}</td>
                <td class="strong">{{ $item->_anakke?->anakke ?? '-' }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->tempat_lahir ?: '-' }}</td>
                <td>{{ $item->tanggal_lahir ? \Carbon\Carbon::parse($item->tanggal_lahir)->format('d-m-Y') : '-' }}</td>
                <td>{{ $item->_kelamin?->kelamin ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="center muted-2" style="padding: 24px;">Belum ada data anak.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- TAB 2: PENDIDIKAN --}}
    <div id="panel-pendidikan" class="hidden" style="padding: 20px;">
      <div class="table-wrap">
        <table class="dt" style="min-width: 560px;">
          <thead>
            <tr>
              <th class="num">No</th>
              <th>Jenjang</th>
              <th>Institusi / Universitas</th>
              <th>Jurusan</th>
              <th>Tahun Lulus</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($pendidikan as $item)
              <tr>
                <td class="num">{{ ++$no_pendidikan }}</td>
                <td class="strong">{{ $item->_jenjang?->jenjang ?? '-' }}</td>
                <td>{{ $item->nama_sekolah }}</td>
                <td>{{ $item->jurusan }}</td>
                <td>{{ $item->tahun_lulus }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="center muted-2" style="padding: 24px;">Belum ada data pendidikan.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- TAB 3: PELATIHAN --}}
    <div id="panel-pelatihan" class="hidden" style="padding: 20px;">
      <div class="table-wrap">
        <table class="dt" style="min-width: 560px;">
          <thead>
            <tr>
              <th class="num">No</th>
              <th>Nama Pelatihan</th>
              <th>Penyelenggara</th>
              <th>Tahun</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($pelatihan as $item)
              <tr>
                <td class="num">{{ ++$no_pelatihan }}</td>
                <td class="strong">{{ $item->nama }}</td>
                <td>{{ $item->penyelenggara }}</td>
                <td>{{ $item->tahun }}</td>
                <td>{{ $item->keterangan ?: '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="center muted-2" style="padding: 24px;">Belum ada data pelatihan.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- TAB 4: SERTIFIKASI --}}
    <div id="panel-sertifikasi" class="hidden" style="padding: 20px;">
      <div class="table-wrap">
        <table class="dt" style="min-width: 560px;">
          <thead>
            <tr>
              <th class="num">No</th>
              <th>Nama Sertifikasi</th>
              <th>Penerbit</th>
              <th>Berlaku Hingga</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($sertifikasi as $item)
              <tr>
                <td class="num">{{ ++$no_sertifikasi }}</td>
                <td class="strong">{{ $item->nama }}</td>
                <td>{{ $item->penerbit }}</td>
                <td>{{ $item->berlaku_sampai ? \Carbon\Carbon::parse($item->berlaku_sampai)->format('d-m-Y') : '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="center muted-2" style="padding: 24px;">Belum ada data sertifikasi.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
@endsection
