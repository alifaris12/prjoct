@extends('layouts.matta.master')
@section('title', 'Formulir Kesehatan — MATTA')

@section('content')
<nav class="breadcrumb" aria-label="Breadcrumb">
  <span>Applications</span>
  <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
  <span class="current" aria-current="page">Health</span>
</nav>

<form action="{{ route('covid.update', $id) }}" method="post" enctype="multipart/form-data" class="form-card" id="form-health" onsubmit="return confirm('Apakah Anda yakin ingin mengirimkan data absensi kesehatan ini?');">
  @method('PATCH')
  @csrf

  <div class="form-card-head">
    <div class="dt-icon">
      <svg class="icon icon-md" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
    </div>
    <div>
      <h1 class="dt-title">Formulir Kesehatan Mandiri</h1>
      <p class="dt-desc" style="color:var(--slate-600)">Sesuai protokol kesehatan K3 &amp; pencegahan transmisi virus di lingkungan kerja</p>
    </div>
  </div>

  {{-- RING 1 --}}
  <div class="acc-item">
    <button type="button" class="acc-trigger" data-acc-trigger aria-expanded="true" aria-controls="ring-1">
      Ring 1 - Karyawan
      <svg class="icon chev" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <div class="acc-panel open" id="ring-1">
      <div>
        <div class="acc-inner">
          <label class="field">
            <span class="q" style="font-weight: 600; font-size: 14px; margin-bottom: 6px; display: block;">Apakah Anda dalam keadaan sehat hari ini?</span>
            <select class="form-control" id="ring1" name="ring1" required>
              <option value="" disabled selected>Pilih status...</option>
              <option value="Sehat">Ya, saya sehat</option>
              <option value="Sakit">Tidak, saya sedang kurang sehat / sakit</option>
            </select>
          </label>

          <div id="div_ring1" style="display: none; margin-top: 14px; padding: 14px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <label class="field">
                <span class="field-label">Sebutkan Keluhan / Sakit <span class="req">*</span></span>
                <input name="sakit_ring1" id="sakit_ring1" class="form-control" type="text" placeholder="Contoh: Demam, flu, pusing..." disabled required>
              </label>
              <label class="field">
                <span class="field-label">Sejak Tanggal <span class="req">*</span></span>
                <input class="form-control" name="tanggal_ring1" id="tanggal_ring1" type="date" disabled required>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- RING 2 --}}
  <div class="acc-item">
    <button type="button" class="acc-trigger" data-acc-trigger aria-expanded="false" aria-controls="ring-2">
      Ring 2 - Orang Serumah
      <svg class="icon chev" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <div class="acc-panel" id="ring-2">
      <div>
        <div class="acc-inner">
          <label class="field">
            <span class="q" style="font-weight: 600; font-size: 14px; margin-bottom: 6px; display: block;">Apakah ada orang serumah (Suami/Istri, Anak, Orang Tua, Saudara) yang sedang sakit?</span>
            <select class="form-control" id="ring2" name="ring2" required>
              <option value="" disabled selected>Pilih...</option>
              <option value="Tidak">Tidak ada</option>
              <option value="Ya">Ya, ada</option>
            </select>
          </label>

          <div id="div_ring2" style="display: none; margin-top: 14px; padding: 14px; background: #fffaf0; border: 1px solid #feebc8; border-radius: 8px;">
            <table class="table" style="width: 100%;" id="trx_ringdua">
              <tbody>
                <tr id="ringdua0">
                  <td style="padding: 10px 0; border: none;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                      <div>
                        <span class="field-label" style="font-size: 12px; font-weight: 600;">Siapa yang sakit?</span>
                        <select class="form-control" name="siapa_ring2[]" id="siapa_ring2[]" disabled required>
                          <option value="" disabled selected>Pilih hubungan...</option>
                          <option value="Suami/Istri">Suami/Istri</option>
                          <option value="Anak 1">Anak 1</option>
                          <option value="Anak 2">Anak 2</option>
                          <option value="Anak 3">Anak 3</option>
                          <option value="Orang Tua">Orang Tua</option>
                          <option value="Kakak/Adik">Kakak/Adik</option>
                          <option value="Pembantu">Pembantu</option>
                          <option value="Lainnya">Lainnya</option>
                        </select>
                      </div>
                      <div>
                        <span class="field-label" style="font-size: 12px; font-weight: 600;">Keluhan / Sakit</span>
                        <input name="sakit_ring2[]" id="sakit_ring2[]" class="form-control" type="text" placeholder="Keluhan sakit" disabled required>
                      </div>
                      <div>
                        <span class="field-label" style="font-size: 12px; font-weight: 600;">Sejak Tanggal</span>
                        <input class="form-control" name="tanggal_ring2[]" id="tanggal_ring2[]" type="date" disabled required>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr id="ringdua1"></tr>
              </tbody>
            </table>
            <div style="display: flex; gap: 8px; margin-top: 8px;">
              <button id="add_row_ring2" type="button" class="btn btn-outline btn-sm">+ Tambah Baris</button>
              <button id="delete_row_ring2" type="button" class="btn btn-outline btn-sm" style="color: #ef4444;">- Hapus Baris</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- RING 3 --}}
  <div class="acc-item">
    <button type="button" class="acc-trigger" data-acc-trigger aria-expanded="false" aria-controls="ring-3">
      Ring 3 - Lingkungan Tempat Tinggal
      <svg class="icon chev" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <div class="acc-panel" id="ring-3">
      <div>
        <div class="acc-inner">
          <label class="field">
            <span class="q" style="font-weight: 600; font-size: 14px; margin-bottom: 6px; display: block;">Apakah di lingkungan tempat tinggal Anda terdapat kasus penyakit menular / wabah?</span>
            <select class="form-control" id="ring3" name="ring3" required>
              <option value="" disabled selected>Pilih...</option>
              <option value="Tidak">Tidak ada</option>
              <option value="Ya">Ya, ada</option>
            </select>
          </label>

          <div id="div_ring3" style="display: none; margin-top: 14px; padding: 14px; background: #fffaf0; border: 1px solid #feebc8; border-radius: 8px;">
            <table class="table" style="width: 100%;" id="trx_ringtiga">
              <tbody>
                <tr id="ringtiga0">
                  <td style="padding: 10px 0; border: none;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                      <div>
                        <span class="field-label" style="font-size: 12px; font-weight: 600;">Keterangan / Hubungan</span>
                        <input name="siapa_ring3[]" id="siapa_ring3[]" class="form-control" type="text" placeholder="Contoh: Tetangga depan rumah" disabled required>
                      </div>
                      <div>
                        <span class="field-label" style="font-size: 12px; font-weight: 600;">Keluhan / Sakit</span>
                        <input name="sakit_ring3[]" id="sakit_ring3[]" class="form-control" type="text" placeholder="Keluhan sakit" disabled required>
                      </div>
                      <div>
                        <span class="field-label" style="font-size: 12px; font-weight: 600;">Sejak Tanggal</span>
                        <input class="form-control" name="tanggal_ring3[]" id="tanggal_ring3[]" type="date" disabled required>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr id="ringtiga1"></tr>
              </tbody>
            </table>
            <div style="display: flex; gap: 8px; margin-top: 8px;">
              <button id="add_row_ring3" type="button" class="btn btn-outline btn-sm">+ Tambah Baris</button>
              <button id="delete_row_ring3" type="button" class="btn btn-outline btn-sm" style="color: #ef4444;">- Hapus Baris</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- PANDUAN --}}
  <div class="acc-item">
    <button type="button" class="acc-trigger" data-acc-trigger aria-expanded="false" aria-controls="guide">
      Panduan &amp; Prosedur Kesehatan MATTA
      <svg class="icon chev" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <div class="acc-panel" id="guide">
      <div>
        <div class="acc-inner">
          <ol class="guide-list">
            <li>Isi formulir ini setiap hari kerja sebelum pukul 09:00 WIB.</li>
            <li>Jika merasa kurang sehat, segera komunikasikan ke atasan dan ajukan Sakit melalui menu Applications.</li>
            <li>Gunakan masker bila sedang batuk/flu dan senantiasa jaga kebersihan tangan.</li>
            <li>Data formulir ini bersifat rahasia dan hanya digunakan untuk kepentingan pemantauan K3 perusahaan.</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  {{-- KONTAK DARURAT --}}
  <div class="acc-item">
    <button type="button" class="acc-trigger" data-acc-trigger aria-expanded="false" aria-controls="contact">
      Kontak Darurat Medis &amp; Hotline K3
      <svg class="icon chev" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <div class="acc-panel" id="contact">
      <div>
        <div class="acc-inner">
          <div class="contact-list">
            <a class="contact" href="tel:112"><span class="ic"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span><span><b>Gawat Darurat</b><span>112</span></span></a>
            <a class="contact" href="tel:119"><span class="ic"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span><span><b>Ambulans / Layanan Medis</b><span>119</span></span></a>
            <a class="contact" href="mailto:k3@alita.id"><span class="ic"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg></span><span><b>Hotline K3 MATTA</b><span>k3@alita.id</span></span></a>
            <a class="contact" href="mailto:hr@alita.id"><span class="ic"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span><b>Tim HR</b><span>hr@alita.id</span></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="form-card-foot">
    <button class="btn btn-primary btn-md" type="submit" style="margin-left:auto">
      <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
      <span>Kirim Laporan Kesehatan</span>
    </button>
  </div>
</form>
@endsection

@section('script')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ring1 = document.getElementById("ring1");
    const divRing1 = document.getElementById("div_ring1");
    const sakit1 = document.getElementById("sakit_ring1");
    const tgl1 = document.getElementById("tanggal_ring1");

    const ring2 = document.getElementById("ring2");
    const divRing2 = document.getElementById("div_ring2");

    const ring3 = document.getElementById("ring3");
    const divRing3 = document.getElementById("div_ring3");

    function setRing1(show) {
        divRing1.style.display = show ? "block" : "none";
        sakit1.disabled = !show;
        tgl1.disabled = !show;
    }

    function setRing2(show) {
        divRing2.style.display = show ? "block" : "none";
        divRing2.querySelectorAll('select, input').forEach(el => el.disabled = !show);
    }

    function setRing3(show) {
        divRing3.style.display = show ? "block" : "none";
        divRing3.querySelectorAll('input').forEach(el => el.disabled = !show);
    }

    ring1.addEventListener("change", function() {
        setRing1(this.value === "Sakit");
    });
    ring2.addEventListener("change", function() {
        setRing2(this.value === "Ya");
    });
    ring3.addEventListener("change", function() {
        setRing3(this.value === "Ya");
    });

    // Row repeater Ring 2
    let rowRing2 = 1;
    document.getElementById("add_row_ring2").addEventListener("click", function(e) {
        e.preventDefault();
        const prev = document.getElementById("ringdua" + (rowRing2 - 1));
        const curr = document.getElementById("ringdua" + rowRing2);
        if (prev && curr) {
            curr.innerHTML = prev.innerHTML;
            const next = document.createElement("tr");
            next.id = "ringdua" + (rowRing2 + 1);
            document.querySelector("#trx_ringdua tbody").appendChild(next);
            rowRing2++;
        }
    });
    document.getElementById("delete_row_ring2").addEventListener("click", function(e) {
        e.preventDefault();
        if (rowRing2 > 1) {
            const row = document.getElementById("ringdua" + (rowRing2 - 1));
            if (row) row.innerHTML = "";
            rowRing2--;
        }
    });

    // Row repeater Ring 3
    let rowRing3 = 1;
    document.getElementById("add_row_ring3").addEventListener("click", function(e) {
        e.preventDefault();
        const prev = document.getElementById("ringtiga" + (rowRing3 - 1));
        const curr = document.getElementById("ringtiga" + rowRing3);
        if (prev && curr) {
            curr.innerHTML = prev.innerHTML;
            const next = document.createElement("tr");
            next.id = "ringtiga" + (rowRing3 + 1);
            document.querySelector("#trx_ringtiga tbody").appendChild(next);
            rowRing3++;
        }
    });
    document.getElementById("delete_row_ring3").addEventListener("click", function(e) {
        e.preventDefault();
        if (rowRing3 > 1) {
            const row = document.getElementById("ringtiga" + (rowRing3 - 1));
            if (row) row.innerHTML = "";
            rowRing3--;
        }
    });
});
</script>
@endsection
