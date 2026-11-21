@extends('layouts.matta.master')
@section('title', ($status == 'edit' ? 'Detail Lembur' : 'Pengajuan Lembur') . ' — MATTA')
@section('ui-version', 'v1')
@section('ui-kind', 'overtime')

@section('content')
<div class="page-top">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('todolist.index') }}">Home</a>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <span>Overtime</span>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <a href="{{ route('overtime.index') }}">Propose</a>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <span class="current" aria-current="page">{{ $status == 'edit' ? 'Detail Lembur' : 'Form Pengajuan Lembur' }}</span>
  </nav>
</div>

@if(Session::has('error'))
<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px;">
  <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; color: #b91c1c; font-weight: 700;">
    <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Pengajuan Tidak Dapat Diproses:</span>
  </div>
  <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #991b1b; line-height: 1.6;">
    @foreach(session('error')['message'] ?? [] as $err)
      <li>
        <strong>{{ $err['message'] ?? '-' }}</strong>: 
        <span style="font-weight: 600; color: {{ ($err['status'] ?? false) ? '#16a34a' : '#dc2626' }};">
          {{ ($err['status'] ?? false) ? 'OK' : 'TIDAK LOLOS' }}
        </span>
        @if(isset($err['quota']))
          <br><small style="color: #64748b;">(Sisa kuota: {{ $err['quota'] }})</small>
        @endif
      </li>
    @endforeach
  </ul>
</div>
@endif

<div style="display: grid; grid-template-columns: {{ $status == 'edit' ? '1fr 360px' : '1fr' }}; gap: 24px; align-items: start;">

  {{-- CARD FORM PENGAJUAN / DETAIL --}}
  <div class="dt-card" style="margin: 0; border-radius: 16px;">
    
    {{-- Card Header --}}
    <div class="dt-head" style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9;">
      <div class="dt-title-wrap">
        <div class="dt-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563eb; border-radius: 12px; width: 44px; height: 44px;">
          <svg class="icon icon-md" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
        <div>
          <h1 class="dt-title" style="font-size: 17px; font-weight: 700; color: #0f172a;">
            {{ $status == 'edit' ? 'Detail Permohonan Lembur' : 'Form Pengajuan Lembur (Overtime Propose)' }}
          </h1>
          <p class="dt-desc" style="font-size: 13px; color: #64748b;">
            {{ $status == 'edit' ? 'Informasi data dan riwayat status persetujuan pengajuan lembur.' : 'Isi formulir pengajuan lembur kerja sesuai dengan instruksi atasan dan kebijakan perusahaan.' }}
          </p>
        </div>
      </div>
    </div>

    {{-- Form Content --}}
    <div style="padding: 24px;">
      @if ($status == 'edit')
        <form action="{{ route('overtime.update', $overtime->id) }}" method="post" enctype="multipart/form-data" onsubmit="return confirm('Apakah Anda yakin ingin memperbarui data lembur ini?');">
          @method('PATCH')
      @else
        <form action="{{ route('overtime.store') }}" method="post" enctype="multipart/form-data" onsubmit="return confirm('Apakah Anda yakin ingin mengajukan lembur ini?');">
      @endif
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
          
          {{-- Pemohon / Maker --}}
          <div>
            <label class="field" style="margin: 0;">
              <span class="field-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">Pemohon Lembur (Maker)</span>
              @if ($status == 'edit')
                <input name="ot_maker_by" id="ot_maker_by" class="form-control" type="text" value="{{ $overtime->ot_maker_by }}" readonly style="background: #f8fafc; font-weight: 600; color: #334155; height: 42px;">
              @else
                <input type="hidden" id="ot_maker_by" name="ot_maker_by" value="{{ $ot_maker_by }}">
                <div style="display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 14px; min-height: 42px;">
                  <div style="width: 28px; height: 28px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">
                    {{ substr(trim($pengaju), 0, 1) }}
                  </div>
                  <div>
                    <div style="font-size: 13px; font-weight: 700; color: #1e293b;">{{ $pengaju }}</div>
                  </div>
                </div>
              @endif
            </label>
          </div>

          {{-- Approver / Atasan Langsung --}}
          <div>
            <label class="field" style="margin: 0;">
              <span class="field-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                Approver (Atasan Langsung)
              </span>
              <div style="display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 14px; min-height: 42px;">
                <div style="width: 28px; height: 28px; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">
                  <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                </div>
                <div>
                  <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                    {{ $approver ? $approver->nama : 'Atasan Langsung (Layer 1)' }}
                  </div>
                  @if($approver)
                    <div style="font-size: 11px; color: #64748b;">NIP: {{ $approver->nik }}</div>
                  @endif
                </div>
              </div>
            </label>
          </div>

          {{-- Judul Lembur (Title) --}}
          <div>
            <label class="field" style="margin: 0;">
              <span class="field-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                Judul Lembur (Title) <span style="color: #ef4444;">*</span>
              </span>
              <input name="ot_title" id="ot_title" class="form-control" type="text" placeholder="Contoh: Pekerjaan Maintenance Server Bulanan" required @if ($status=='edit') value="{{ $overtime->ot_title }}" @endif style="height: 42px;">
            </label>
          </div>

          {{-- Tipe Lembur (Type) --}}
          <div>
            <label class="field" style="margin: 0;">
              <span class="field-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                Tipe Lembur (Type) <span style="color: #ef4444;">*</span>
              </span>
              <select class="form-control" id="ot_type_code" name="ot_type_code" required style="height: 42px; background: #ffffff;">
                <option selected disabled value="">Pilih Tipe Lembur...</option>
                @foreach ($type_code as $data)
                  <option value="{{ $data->ot_type }}" @if ($status=='edit' && $data->id == $overtime->ot_type_code) selected @endif>
                    {{ $data->ot_type }}
                  </option>
                @endforeach
              </select>
            </label>
          </div>

          {{-- Waktu Mulai (Start) --}}
          <div>
            <label class="field" style="margin: 0;">
              <span class="field-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                Tanggal & Jam Mulai (Start) <span style="color: #ef4444;">*</span>
              </span>
              <input class="form-control" name="ot_start_at" id="ot_start_at" type="datetime-local" required @if ($status=='edit') value="{{ \Carbon\Carbon::parse($overtime->ot_start_at)->format('Y-m-d\TH:i') }}" @endif style="height: 42px;">
            </label>
          </div>

          {{-- Waktu Selesai (Finish) --}}
          <div>
            <label class="field" style="margin: 0;">
              <span class="field-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                Tanggal & Jam Selesai (Finish) <span style="color: #ef4444;">*</span>
              </span>
              <input class="form-control" name="ot_end_at" id="ot_end_at" type="datetime-local" required @if ($status=='edit') value="{{ \Carbon\Carbon::parse($overtime->ot_end_at)->format('Y-m-d\TH:i') }}" @endif style="height: 42px;">
            </label>
          </div>

          {{-- Live Duration Indicator --}}
          <div id="duration-badge" style="grid-column: 1 / -1; display: none; align-items: center; gap: 8px; padding: 10px 14px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 13px; font-weight: 600; color: #1d4ed8;">
            <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span id="duration-text">Total Durasi Lembur: -</span>
          </div>

          {{-- Keterangan / Rincian Pekerjaan (Description) --}}
          <div style="grid-column: 1 / -1;">
            <label class="field" style="margin: 0;">
              <span class="field-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                Alasan / Rincian Pekerjaan Lembur (Description) <span style="color: #ef4444;">*</span>
              </span>
              <textarea class="form-control" id="ot_desc" name="ot_desc" rows="4" placeholder="Jelaskan secara rinci tugas atau pekerjaan yang dikerjakan saat lembur (contoh: deploy rilis sistem, maintenance server, closing buku akhir bulan, dll.)..." required style="padding: 10px 14px; line-height: 1.5;">@if ($status == 'edit'){{ $overtime->ot_desc }}@endif</textarea>
            </label>
          </div>

        </div>

        {{-- Action Buttons --}}
        <div style="display: flex; align-items: center; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
          @if ($status == 'create')
            <button class="btn btn-primary btn-md" type="submit" style="padding: 0 24px; height: 42px; font-size: 13px; font-weight: 600; border-radius: 8px;">
              <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
              Kirim Pengajuan Lembur
            </button>
          @endif
          <a href="{{ route('overtime.index') }}" class="btn btn-outline btn-md" style="padding: 0 20px; height: 42px; font-size: 13px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center;">
            Kembali ke Propose
          </a>
        </div>

      </form>
    </div>

  </div>

  {{-- PANEL KANAN: STATUS & HISTORY (HANYA MUNCUL DI MODE EDIT/DETAIL) --}}
  @if ($status == 'edit')
  <div class="dt-card" style="margin: 0; border-radius: 16px;">
    <div class="dt-head" style="padding: 18px 20px; border-bottom: 1px solid #f1f5f9;">
      <div class="dt-title-wrap">
        <div class="dt-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 10px; width: 38px; height: 38px;">
          <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
        </div>
        <div>
          <h2 class="dt-title" style="font-size: 15px; font-weight: 700; color: #0f172a;">Riwayat Persetujuan</h2>
          <p class="dt-desc" style="font-size: 11px; color: #64748b;">History status permohonan</p>
        </div>
      </div>
    </div>
    <div style="padding: 20px;">
      @if(isset($history) && count($history) > 0)
        <div style="position: relative; padding-left: 20px; border-left: 2px solid #e2e8f0; display: flex; flex-direction: column; gap: 18px;">
          @foreach ($history as $_history)
            <div style="position: relative;">
              <span style="position: absolute; left: -27px; top: 3px; width: 12px; height: 12px; border-radius: 50%; background: {{ $_history->ot_approver_status == 'Approve' ? '#10b981' : ($_history->ot_approver_status == 'Reject' ? '#ef4444' : '#f59e0b') }}; border: 2px solid #ffffff; box-shadow: 0 0 0 2px #e2e8f0;"></span>
              <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">
                {{ \Carbon\Carbon::parse($_history->created_at)->format('d-m-Y H:i') }}
              </div>
              <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                {{ $_history->ot_approver_status }}
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div style="text-align: center; color: #94a3b8; font-size: 12px; padding: 20px 0;">
          Belum ada riwayat aktivitas persetujuan.
        </div>
      @endif
    </div>
  </div>
  @endif

</div>
@endsection

@section('script')
<script>
function updateDuration() {
  const startEl = document.getElementById('ot_start_at');
  const endEl = document.getElementById('ot_end_at');
  const badge = document.getElementById('duration-badge');
  const text = document.getElementById('duration-text');
  if (!badge || !text || !startEl || !endEl) return;

  const startVal = startEl.value;
  const endVal = endEl.value;

  if (startVal && endVal) {
    const start = new Date(startVal);
    const end = new Date(endVal);
    const diffMs = end - start;

    if (diffMs > 0) {
      const diffMins = Math.floor(diffMs / 60000);
      const hours = Math.floor(diffMins / 60);
      const mins = diffMins % 60;
      text.textContent = `Total Durasi Lembur: ${hours} Jam ${mins > 0 ? mins + ' Menit' : ''}`;
      badge.style.display = 'flex';
      badge.style.background = '#eff6ff';
      badge.style.borderColor = '#bfdbfe';
      badge.style.color = '#1d4ed8';
    } else {
      text.textContent = 'Perhatian: Waktu selesai harus lebih besar dari waktu mulai.';
      badge.style.display = 'flex';
      badge.style.background = '#fef2f2';
      badge.style.borderColor = '#fecaca';
      badge.style.color = '#dc2626';
    }
  } else {
    badge.style.display = 'none';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const startEl = document.getElementById('ot_start_at');
  const endEl = document.getElementById('ot_end_at');
  if (startEl) startEl.addEventListener('change', updateDuration);
  if (endEl) endEl.addEventListener('change', updateDuration);
  updateDuration();
});
</script>
@endsection
