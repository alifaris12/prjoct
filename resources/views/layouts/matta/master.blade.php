<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'MATTA — Enterprise Workplace')</title>
  <link rel="icon" type="image/png" href="{{ asset('matta-frontend/assets/img/favicon.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('matta-frontend/assets/css/style.css') }}">
  <style>
    /* Global helpers for MATTA Laravel Integration */
    .btn-hc-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 9999px;
      background: #eff6ff;
      color: #2563eb;
      border: 1px solid #bfdbfe;
      text-decoration: none;
      transition: all .2s;
    }
    .btn-hc-pill:hover {
      background: #2563eb;
      color: #fff;
    }
  </style>
  @yield('css')
  @yield('style')
</head>
<body data-ui="@yield('ui-version', 'v1')" data-ui-kind="@yield('ui-kind', 'home')" @yield('body-attrs')>
  <div class="page-loader" aria-hidden="true"></div>
  <div class="app">
    @include('layouts.matta.sidebar')
    <div class="sidebar-overlay" aria-hidden="true"></div>
    <div class="app-main">
      @include('layouts.matta.header')
      <main class="main @yield('main-class', 'main-dashboard')" id="main">
        @yield('content')
      </main>
    </div>
  </div>

  @include('layouts.matta.modals')

  <script src="{{ asset('matta-frontend/assets/js/i18n.js') }}"></script>
  <script src="{{ asset('matta-frontend/assets/js/app.js') }}"></script>
  @yield('script')
</body>
</html>
