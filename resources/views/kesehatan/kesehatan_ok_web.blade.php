@extends('layouts.matta.master')
@section('title', 'Absensi Kesehatan Terkirim — MATTA')

@section('content')
<nav class="breadcrumb" aria-label="Breadcrumb">
  <span>Applications</span>
  <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
  <span class="current" aria-current="page">Health Confirmation</span>
</nav>

<div style="max-width: 640px; margin: 40px auto;">
  <div class="dt-card" style="text-align: center; padding: 40px 24px;">
    <div style="width: 72px; height: 72px; border-radius: 50%; background: #ecfdf5; color: #10b981; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
      <svg class="icon icon-lg" style="width: 36px; height: 36px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"></polyline>
      </svg>
    </div>

    <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 8px; color: #0f172a;">Terima Kasih!</h2>
    <p style="font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 24px;">
      Terima kasih atas partisipasi dan kerja samanya dalam melakukan pengisian Absensi Kesehatan mandiri hari ini. Data Anda telah berhasil tersimpan dalam sistem K3.
    </p>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 24px; font-size: 13px; color: #475569;">
      Tetap jaga kesehatan, terapkan pola hidup bersih dan sehat, serta patuhi protokol keselamatan kerja.
    </div>

    <div>
      <a href="{{ route('todolist.index') }}" class="btn btn-primary btn-md">
        <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Kembali ke Beranda
      </a>
    </div>
  </div>
</div>
@endsection
