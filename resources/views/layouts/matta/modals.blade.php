<!-- Modal Popup Kartu Dashboard (Menu More / Layanan Lainnya) -->
<div class="modal-backdrop" id="modal-card-popup" aria-hidden="true">
  <div class="modal modal-lg" role="dialog" aria-modal="true" aria-labelledby="card-pop-title">
    <div class="modal-header">
      <div class="card-pop-header">
        <div class="card-pop-icon" data-card-pop-icon>
          <img data-card-pop-img src="{{ asset('matta-frontend/assets/img/modules/confirmation.png') }}" alt="More Applications">
        </div>
        <div>
          <div class="modal-title" id="card-pop-title" data-card-pop-title>Modul Konfirmasi</div>
          <div class="modal-sub" data-card-pop-sub>Klik kartu di bawah ini untuk menampilkan tabel riwayat konfirmasi di dashboard</div>
        </div>
      </div>
      <button class="icon-btn" type="button" data-modal-close aria-label="Tutup"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
    </div>
    <div class="modal-body" data-card-pop-body style="padding: 22px 24px;">
      <div class="pop-single-wrap">
        <button type="button" class="pop-module-card" data-pop-select="confirmation" aria-label="Modul Konfirmasi">
          <span class="m-head"><span class="m-name">KONFIRMASI</span></span>
          <span class="m-corner-badge badge-plus">6</span>
          <svg class="m-art-svg" viewBox="0 0 52 52" fill="none" stroke="#f05a00" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="11" y="9" width="30" height="36" rx="4" fill="#fff9f1"/>
            <rect x="19" y="5" width="14" height="6" rx="2" fill="#fff9f1"/>
            <line x1="18" y1="19" x2="34" y2="19"/>
            <line x1="18" y1="25" x2="30" y2="25"/>
            <circle cx="33" cy="34" r="8" fill="#fff9f1"/>
            <polyline points="29 34 32 37 38 31"/>
          </svg>
        </button>
      </div>
    </div>
    <div class="card-pop-foot" style="justify-content: flex-end;">
      <button class="btn btn-outline btn-md" type="button" data-modal-close>Tutup</button>
    </div>
  </div>
</div>

<!-- Drawer Detail Row / Riwayat -->
<div class="modal-backdrop" id="drawer-detail" aria-hidden="true">
  <div class="modal modal-lg" role="dialog" aria-modal="true" aria-labelledby="dr-title" style="max-width: 640px;">
    <div class="dr-head modal-header">
      <span class="dr-ico" data-dr-ico></span>
      <div class="dr-hgroup">
        <div class="dr-eyebrow" data-dr-eyebrow>Detail</div>
        <h2 class="dr-title" id="dr-title" data-dr-title>Detail</h2>
      </div>
      <button class="icon-btn" type="button" data-modal-close aria-label="Tutup"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
    </div>
    <div class="dr-body modal-body" data-dr-body></div>
    <div class="dr-foot modal-footer">
      <button class="btn btn-outline" type="button" data-modal-close>Tutup</button>
      <button class="btn btn-primary" type="button" data-dr-print>
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>Cetak
      </button>
    </div>
  </div>
</div>

<!-- Modal Quick Apply -->
<div class="modal-backdrop" id="modal-apply" aria-hidden="true">
  <form class="modal" id="form-apply" role="dialog" aria-modal="true" aria-labelledby="apply-title" novalidate>
    @csrf
    <div class="modal-header">
      <div>
        <div class="modal-title" id="apply-title" data-apply-title>Ajukan</div>
        <div class="modal-sub">Isi data pengajuan, lalu kirim untuk persetujuan atasan.</div>
      </div>
      <button class="icon-btn" type="button" data-modal-close aria-label="Tutup"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
    </div>
    <div class="modal-body">
      <div class="form-grid" data-fieldset="default">
        <label class="field">
          <span class="field-label">Tanggal mulai <span class="req">*</span></span>
          <input class="form-control" type="date" name="start" required>
          <span class="field-error">Tanggal mulai wajib diisi.</span>
        </label>
        <label class="field">
          <span class="field-label">Tanggal selesai</span>
          <input class="form-control" type="date" name="end">
        </label>
        <label class="field span-2">
          <span class="field-label">Keterangan <span class="req">*</span></span>
          <textarea class="form-control" name="note" placeholder="Tuliskan alasan / keterangan pengajuan" required></textarea>
          <span class="field-error">Keterangan wajib diisi.</span>
        </label>
        <label class="field span-2">
          <span class="field-label">Lampiran (PDF/JPG, opsional)</span>
          <input class="form-control" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" style="padding:6px 10px;height:auto">
        </label>
      </div>
      <div class="form-grid sick-grid" data-fieldset="sick-leave" hidden>
        <label class="field">
          <span class="field-label">Pengaju</span>
          <input class="form-control" value="{{ auth()->user()->name }} - {{ auth()->user()->nik }}" readonly tabindex="-1" data-no-i18n>
        </label>
        <span class="field-spacer" aria-hidden="true"></span>
        <label class="field">
          <span class="field-label">Tanggal Mulai <span class="req">*</span></span>
          <input class="form-control" type="date" name="start" required>
          <span class="field-error">Tanggal mulai wajib diisi.</span>
        </label>
        <label class="field">
          <span class="field-label">Tanggal Akhir <span class="req">*</span></span>
          <input class="form-control" type="date" name="end" required data-after="start">
          <span class="field-error">Tanggal akhir wajib diisi dan tidak boleh sebelum tanggal mulai.</span>
        </label>
        <label class="field">
          <span class="field-label">Telepon Pengaju <span class="req">*</span></span>
          <input class="form-control" type="tel" name="phone" placeholder="08xxxxxxxxxx" inputmode="tel" required>
          <span class="field-error">Telepon wajib diisi.</span>
        </label>
        <label class="field">
          <span class="field-label">Alasan <span class="req">*</span></span>
          <textarea class="form-control" name="reason" rows="4" placeholder="Tuliskan alasan sakit" required></textarea>
          <span class="field-error">Alasan wajib diisi.</span>
        </label>
        <label class="field">
          <span class="field-label">Alamat</span>
          <textarea class="form-control" name="address" rows="4" placeholder="Alamat selama izin sakit"></textarea>
        </label>
        <label class="field">
          <span class="field-label">Keterangan</span>
          <textarea class="form-control" name="note" rows="4" placeholder="Catatan tambahan (opsional)"></textarea>
        </label>
        <label class="field">
          <span class="field-label">Lampiran</span>
          <span class="field-hint">*Maksimal 2MB dengan format pdf atau gambar (jpg, jpeg dan png)</span>
          <input class="form-control" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" data-max-mb="2" style="padding:6px 10px;height:auto">
          <span class="field-error">Ukuran maksimal 2MB dengan format PDF, JPG, JPEG atau PNG.</span>
        </label>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" type="button" data-modal-close>Batal</button>
      <button class="btn btn-primary" type="submit">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Kirim Pengajuan
      </button>
    </div>
  </form>
</div>
