@extends('layouts.matta.master')
@section('title', 'Feeds — MATTA')
@section('ui-version', 'v1')
@section('ui-kind', 'feeds')

@php
    $activeCount = $feed->where('is_active', 1)->count();
    $inactiveCount = $feed->where('is_active', 0)->count();
    $feedImages = [
        'batik-nasional.jpg',
        'happy-new-year.jpg',
        'libur-nasional.jpg',
        'maulid-nabi.jpg',
        'pengumuman-penting.jpg',
        'ulang-tahun-alita.jpg'
    ];
@endphp

@section('content')
<div class="page-top">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('todolist.index') }}">Home</a>
    <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    <span class="current" aria-current="page" data-crumb-current>Feeds</span>
  </nav>
  <div class="version-switch">
    <span>Tampilan</span>
    <div class="seg">
      <a href="javascript:void(0)" class="active" data-ui-set="v1" aria-current="page">Versi 1</a>
    </div>
  </div>
</div>

<div data-page="feeds" style="display:contents">
  <div class="feeds-head">
    <div>
      <h1 class="page-title" data-feeds-title>Feeds Aktif</h1>
      <p class="page-sub" data-feeds-sub>Pengumuman yang sedang berlaku dan tampil untuk seluruh karyawan.</p>
    </div>
    <div class="tabs-pill" role="tablist" aria-label="Status feed">
      <button type="button" role="tab" aria-selected="true" data-status="active">
        <span class="dot-live" aria-hidden="true"></span>Feed Aktif <span class="tab-count">{{ $activeCount }}</span>
      </button>
      <button type="button" role="tab" aria-selected="false" data-status="inactive">
        <svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></svg>Feed Non Aktif <span class="tab-count">{{ $inactiveCount }}</span>
      </button>
    </div>
  </div>

  <div class="archive-note" data-archive-note hidden>
    <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Feed non aktif sudah tidak berlaku dan tidak lagi tampil di beranda karyawan. Ditampilkan sebagai arsip.</span>
  </div>

  <div class="feed-grid">
    @foreach ($feed as $index => $data)
      @php
          $imgFile = $feedImages[$index % count($feedImages)];
          $isActive = ($data->is_active == 1);
          $excerpt = Str::limit(strip_tags($data->content), 120);
      @endphp
      <article class="feed-card {{ $isActive ? '' : 'is-inactive' }}" data-status="{{ $isActive ? 'active' : 'inactive' }}" style="--i:{{ $index % 6 }}">
        <div class="feed-body">
          <a class="feed-media" href="javascript:void(0)" onclick="openFeedModal({{ $data->id }})" tabindex="-1" aria-hidden="true">
            <img class="feed-img" src="{{ asset('matta-frontend/assets/img/feeds/' . $imgFile) }}" alt="{{ $data->title }}" loading="lazy">
            @if($isActive)
              <span class="feed-flag on"><span class="dot-live"></span>Aktif</span>
            @else
              <span class="feed-flag off"><svg class="icon icon-xs" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/></svg>Non Aktif</span>
            @endif
          </a>
          <span class="feed-tag">{{ $isActive ? 'INFORMASI' : 'ARSIP' }}</span>
          <h2 class="feed-title">
            <a href="javascript:void(0)" onclick="openFeedModal({{ $data->id }})">{{ $data->title }}</a>
          </h2>
          <p class="feed-excerpt">{{ $excerpt ?: 'Klik lihat detail untuk membaca isi pengumuman selengkapnya.' }}</p>
          <div class="feed-date">
            <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true">
              <rect width="18" height="18" x="3" y="4" rx="2"/>
              <path d="M16 2v4"/>
              <path d="M8 2v4"/>
              <path d="M3 10h18"/>
            </svg>
            <span>{{ $data->created_at }}</span>
          </div>
        </div>
        <div class="feed-foot">
          <a href="javascript:void(0)" onclick="openFeedModal({{ $data->id }})" class="link-more">
            <span>{{ $isActive ? 'Lihat detail' : 'Lihat arsip' }}</span>
            <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
          </a>
        </div>
      </article>

      <!-- Feed Detail Modal -->
      <div class="modal-backdrop" id="feedModal{{ $data->id }}" style="display:none;" aria-hidden="true">
        <div class="modal modal-lg" role="dialog" aria-modal="true" style="max-width: 720px;">
          <div class="modal-header">
            <div>
              <div class="modal-title" style="font-size: 1.1rem; font-weight: 700;">{{ $data->title }}</div>
              <div class="modal-sub">{{ $data->created_at }}</div>
            </div>
            <button class="icon-btn" type="button" onclick="closeFeedModal({{ $data->id }})" aria-label="Tutup">
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
          </div>
          <div class="modal-body" style="padding: 24px; max-height: 65vh; overflow-y: auto; line-height: 1.7; font-size: 15px;">
            <div style="margin-bottom: 20px;">
              <img src="{{ asset('matta-frontend/assets/img/feeds/' . $imgFile) }}" alt="{{ $data->title }}" style="width: 100%; border-radius: 12px; max-height: 280px; object-fit: cover;">
            </div>
            <div class="feed-content-body">
              {!! $data->content !!}
            </div>
          </div>
          <div class="modal-footer" style="padding: 12px 24px;">
            <button class="btn btn-outline" type="button" onclick="closeFeedModal({{ $data->id }})">Tutup</button>
          </div>
        </div>
      </div>
    @endforeach

    <div class="empty-state" data-feed-empty hidden>Belum ada feed.</div>
  </div>
</div>
@endsection

@section('script')
<script>
function openFeedModal(id) {
  const modal = document.getElementById('feedModal' + id);
  if (modal) {
    modal.style.display = 'flex';
    modal.classList.add('show');
  }
}
function closeFeedModal(id) {
  const modal = document.getElementById('feedModal' + id);
  if (modal) {
    modal.style.display = 'none';
    modal.classList.remove('show');
  }
}
</script>
@endsection
