@php
    $currentRoute = Route::currentRouteName();
    $isFormsActive = in_array($currentRoute, [
        'overtime.index', 'overtime.create', 'overtime.edit',
        'penilaian.index', 'penilaian.create',
        'cuti.index', 'cuti.create',
        'izin.index', 'izin.create',
        'nonaktif.index', 'nonaktif.create',
        'reimburse.index', 'reimburse.create',
        'sakit.index', 'sakit.create',
        'covid.index'
    ]);
@endphp
<aside class="sidebar" id="sidebar" aria-label="Navigasi utama">
  <div class="sidebar-brand">
    <a href="{{ route('todolist.index') }}" class="brand" aria-label="MATTA beranda">
      <img class="brand-logo" src="{{ asset('matta-frontend/assets/img/logo-matta.png') }}" alt="MATTA" width="36" height="36">
    </a>
    <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-controls="sidebar" aria-expanded="true" aria-label="Buka/tutup menu">
      <svg class="icon icon-md" viewBox="0 0 24 24" aria-hidden="true">
        <line x1="21" x2="3" y1="6" y2="6"/>
        <line x1="15" x2="3" y1="12" y2="12"/>
        <line x1="17" x2="3" y1="18" y2="18"/>
      </svg>
    </button>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-group">
      <span class="nav-label" data-i18n="nav.home_group">HOME PAGE</span>
      <ul class="nav-list">
        <li>
          <a class="nav-link {{ request()->routeIs('todolist.index') ? 'active' : '' }}" aria-current="{{ request()->routeIs('todolist.index') ? 'page' : 'false' }}" href="{{ route('todolist.index') }}">
            <svg class="icon icon-fill" viewBox="0 0 24 24" aria-hidden="true">
              <rect width="7" height="7" x="3" y="3" rx="1.5"/>
              <rect width="7" height="7" x="14" y="3" rx="1.5"/>
              <rect width="7" height="7" x="14" y="14" rx="1.5"/>
              <rect width="7" height="7" x="3" y="14" rx="1.5"/>
            </svg>
            <span data-i18n="nav.home">Home</span>
          </a>
        </li>
        <li>
          <a class="nav-link {{ request()->routeIs('todolist.tasks') ? 'active' : '' }}" href="{{ route('todolist.tasks') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M9 11l3 3L22 4"/>
              <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            <span>To-Do List</span>
          </a>
        </li>
        <li>
          <a class="nav-link {{ request()->routeIs('feeds.*') ? 'active' : '' }}" href="{{ route('feeds.view') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 11a9 9 0 0 1 9 9"/>
              <path d="M4 4a16 16 0 0 1 16 16"/>
              <circle cx="5" cy="19" r="1"/>
            </svg>
            <span data-i18n="nav.feeds">Feeds</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="nav-group">
      <span class="nav-label" data-i18n="nav.apps">APPLICATIONS</span>
      <ul class="nav-list">
        <li>
          <button type="button" class="nav-link {{ $isFormsActive ? 'parent-active' : '' }}" data-submenu-toggle aria-controls="sub-forms" aria-expanded="{{ $isFormsActive ? 'true' : 'false' }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
              <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
              <path d="M8 18v-2"/>
              <path d="M12 18v-6"/>
              <path d="M16 18v-4"/>
            </svg>
            <span data-i18n="nav.forms">Forms</span>
            <svg class="icon chev" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
          </button>
          <ul class="nav-sub {{ $isFormsActive ? 'open' : '' }}" id="sub-forms">
            <div>
              <li><a href="{{ route('overtime.index') }}" class="{{ request()->routeIs('overtime.*') ? 'active' : '' }}">Overtime</a></li>
              <li><a href="{{ route('penilaian.index') }}" class="{{ request()->routeIs('penilaian.*') ? 'active' : '' }}">Evaluation</a></li>
              <li><a href="{{ route('cuti.index') }}" class="{{ request()->routeIs('cuti.*') ? 'active' : '' }}">Leave</a></li>
              <li><a href="{{ route('izin.index') }}" class="{{ request()->routeIs('izin.*') ? 'active' : '' }}">Permit</a></li>
              <li><a href="{{ route('nonaktif.index') }}" class="{{ request()->routeIs('nonaktif.*') ? 'active' : '' }}">Exit Clearance</a></li>
              <li><a href="{{ route('reimburse.index') }}" class="{{ request()->routeIs('reimburse.*') ? 'active' : '' }}">Reimbursement</a></li>
              <li><a href="{{ route('sakit.index') }}" class="{{ request()->routeIs('sakit.*') ? 'active' : '' }}">Sick Leave</a></li>
              <li><a href="{{ route('covid.index') }}" class="{{ request()->routeIs('covid.*') ? 'active' : '' }}">Health Form</a></li>
            </div>
          </ul>
        </li>
        @can('hc')
        <li>
          <a class="nav-link" href="{{ route('karyawan.index') }}" style="border-left: 2px solid #2563eb;">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" style="color:#2563eb;">
              <rect width="18" height="18" x="3" y="3" rx="2"/>
              <path d="M9 3v18"/>
              <path d="m14 9 3 3-3 3"/>
            </svg>
            <span style="color:#2563eb;font-weight:600;">HC Portal</span>
          </a>
        </li>
        @endcan
      </ul>
    </div>

    <div class="nav-group">
      <span class="nav-label" data-i18n="nav.payroll_group">PAYROLL</span>
      <ul class="nav-list">
        <li>
          <a class="nav-link {{ request()->routeIs('payroll.slip*') ? 'active' : '' }}" href="{{ route('payroll.slip', (new App\Http\Controllers\OpenDataController)->encryptWithKey(auth()->user()->nik)) }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
              <rect width="20" height="14" x="2" y="5" rx="2"/>
              <line x1="2" x2="22" y1="10" y2="10"/>
            </svg>
            <span data-i18n="nav.payroll">Payroll Slip</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="nav-group">
      <span class="nav-label" data-i18n="nav.att_group">ATTENDANCE</span>
      <ul class="nav-list">
        <li>
          <a class="nav-link {{ request()->routeIs('attendance.index') ? 'active' : '' }}" href="{{ route('attendance.index') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
            <span data-i18n="nav.att_log">Attendance Log</span>
          </a>
        </li>
        <li>
          <a class="nav-link {{ request()->routeIs('recap_attendance.*') ? 'active' : '' }}" href="{{ route('recap_attendance.index') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
            <span data-i18n="nav.att_recap">Attendance Recap</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="nav-group">
      <span class="nav-label" data-i18n="nav.ot_group">OVERTIME</span>
      <ul class="nav-list">
        <li>
          <a class="nav-link {{ request()->routeIs('overtime.create') ? 'active' : '' }}" href="{{ route('overtime.create') }}">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
            <span data-i18n="nav.propose">Propose</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="sidebar-footer">
    <img src="{{ asset('matta-frontend/assets/img/logo-trust.png') }}" alt="TRUST - Truthful Resilient Unwavering Sound Teamwork" class="sidebar-footer-logo" width="176" height="53">
  </div>
</aside>
