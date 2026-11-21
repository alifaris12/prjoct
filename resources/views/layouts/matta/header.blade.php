<header class="app-header">
  <div class="header-left">
    <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-controls="sidebar" aria-expanded="true" aria-label="Buka/tutup menu">
      <svg class="icon icon-md" viewBox="0 0 24 24" aria-hidden="true">
        <line x1="21" x2="3" y1="6" y2="6"/>
        <line x1="15" x2="3" y1="12" y2="12"/>
        <line x1="17" x2="3" y1="18" y2="18"/>
      </svg>
    </button>
    <span class="header-brand">
      <a href="{{ route('todolist.index') }}" class="brand" aria-label="MATTA beranda">
        <img class="brand-logo" src="{{ asset('matta-frontend/assets/img/logo-matta.png') }}" alt="MATTA" width="36" height="36">
      </a>
    </span>
    @can('hc')
    <a href="{{ route('switch-ui', 'cuba') }}" class="btn-hc-pill d-none d-md-inline-flex" title="Beralih ke Portal HC / Admin (Cuba)">
      <svg class="icon icon-xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect width="18" height="18" x="3" y="3" rx="2"/>
        <path d="M9 3v18"/>
        <path d="m14 9 3 3-3 3"/>
      </svg>
      <span>HC Admin (Cuba)</span>
    </a>
    @endcan
  </div>
  <div class="header-actions">
    <div class="lang-pill" role="group" aria-label="Bahasa">
      <button type="button" data-lang="en" aria-pressed="true">EN</button>
      <button type="button" data-lang="id" aria-pressed="false">ID</button>
    </div>
    <div class="profile">
      <button type="button" class="profile-badge" data-dropdown="menu-profile" aria-haspopup="true" aria-expanded="false">
        <div>
          <div class="profile-name">{{ auth()->user()->name ?? 'User' }}</div>
          <div class="profile-nip">{{ auth()->user()->nik ?? '-' }}</div>
        </div>
        <span class="avatar-btn">
          @if(file_exists(public_path('assets/images/matta/karyawan/' . auth()->user()->nik . '/' . auth()->user()->nik . '.jpg')))
            <img src="{{ asset('assets/images/matta/karyawan/' . auth()->user()->nik . '/' . auth()->user()->nik . '.jpg') }}" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
          @else
            <svg class="icon icon-md" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          @endif
        </span>
      </button>
      <div class="dropdown" id="menu-profile" role="menu">
        <a href="{{ route('karyawan.show', (new App\Http\Controllers\OpenDataController)->encryptWithKey(auth()->user()->nik)) }}" role="menuitem">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span data-i18n="menu.profile">My Profile</span>
        </a>
        <a href="{{ route('attendance.index') }}" role="menuitem">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span data-i18n="nav.att_log">Attendance Log</span>
        </a>
        @can('hc')
        <a href="{{ route('switch-ui', 'cuba') }}" role="menuitem" style="color: #2563eb;">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="m14 9 3 3-3 3"/></svg>
          <span>HC Admin Portal (Cuba)</span>
        </a>
        @endcan
        <hr>
        <form action="{{ route('logoutapp') }}" method="post" id="logout-matta-form" style="margin:0;">
          @csrf
          <button type="submit" class="danger" role="menuitem" style="width:100%;background:none;border:none;display:flex;align-items:center;gap:8px;padding:8px 12px;font:inherit;color:#ef4444;cursor:pointer;text-align:left;">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            <span data-i18n="menu.logout">Log out</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</header>
