<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/layouts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/material.css') }}">
@stack('styles')
<title>{{ $title ?? 'EduLink' }}</title>
</head>
<body>

{{-- ── MOBILE OVERLAY ── --}}
<div class="l-overlay" id="l-overlay" onclick="closeSidebar()"></div>

<div class="l-shell">

  {{-- ════════════════════════════════════
       SIDEBAR
  ════════════════════════════════════ --}}
  <aside class="l-sidebar" id="l-sidebar">

    {{-- Logo --}}
    <a href="{{ route($role . '.dashboard') }}" class="l-sidebar__logo">
      <div class="l-sidebar__logo-mark">E</div>
      <span class="l-sidebar__logo-name">Edu<span>Link</span></span>
    </a>

    {{-- Navigasi utama --}}
    @if(in_array($role, ['admin', 'guru', 'siswa'], true))
      <div class="l-sidebar__section">Menu Utama</div>

      <ul class="l-sidebar__nav">

        <li class="l-sidebar__nav-item">
          <a href="{{ route($role . '.dashboard') }}"
             class="l-sidebar__nav-link {{ request()->routeIs($role . '.dashboard') ? 'active' : '' }}">
            <span class="l-sidebar__nav-icon">🏠</span>
            <span>
              Dashboard
              <span class="l-sidebar__nav-sub">Ringkasan & progress</span>
            </span>
          </a>
        </li>

        <li class="l-sidebar__nav-item">
          <a href="{{ route($role . '.materials') }}"
             class="l-sidebar__nav-link {{
               (
                 request()->routeIs($role . '.materials') ||
                 request()->routeIs('subjects.show') ||
                 request()->routeIs('materials.*') ||
                 request()->routeIs('guru.subjects.materials.*') ||
                 request()->routeIs('guru.materials.subsections.*')
               ) ? 'active' : ''
             }}">
            <span class="l-sidebar__nav-icon">📚</span>
            <span>
              Materi
              <span class="l-sidebar__nav-sub">Mapel, bab, dan sub bab</span>
            </span>
          </a>
        </li>

        <li class="l-sidebar__nav-item">
          <a href="{{ route($role . '.quizzes') }}"
             class="l-sidebar__nav-link {{
               (
                 request()->routeIs($role . '.quizzes') ||
                 request()->routeIs('quizzes.*') ||
                 request()->routeIs('guru.materials.quizzes.*')
               ) ? 'active' : ''
             }}">
            <span class="l-sidebar__nav-icon">✏️</span>
            <span>
              Kuis
              <span class="l-sidebar__nav-sub">Latihan soal & hasil</span>
            </span>
          </a>
        </li>

        @if($role === 'siswa')
          <li class="l-sidebar__nav-item">
            <a href="{{ route('siswa.ai.index') }}"
               class="l-sidebar__nav-link {{ request()->routeIs('siswa.ai.*') ? 'active' : '' }}">
              <span class="l-sidebar__nav-icon">🤖</span>
              <span>
                Tanya AI
                <span class="l-sidebar__nav-sub">Konsultasi materi & kuis</span>
              </span>
            </a>
          </li>
        @endif

      </ul>
    @endif

    {{-- Profile bottom --}}
    <div class="l-sidebar__bottom">

      <div class="l-sidebar__profile">
        <div class="l-sidebar__avatar">🎓</div>

        <div class="l-sidebar__profile-info">
          <div class="l-sidebar__profile-name">
            {{ auth()->user()->name ?? 'Pengguna' }}
          </div>

          <div class="l-sidebar__profile-role">
            {{ ucfirst($role) }}
          </div>
        </div>
      </div>

    </div>

  </aside>

  {{-- ════════════════════════════════════
       MAIN
  ════════════════════════════════════ --}}
  <div class="l-main">

    {{-- TOPBAR --}}
    <header class="l-topbar">

      <button class="l-topbar__hamburger"
              onclick="toggleSidebar()"
              aria-label="Toggle menu">
        ☰
      </button>

      <div class="l-topbar__title">
        @yield('topbar_title', '🏠 Dashboard')
      </div>

      <div class="l-topbar__right">

        <div class="l-topbar__btn" title="Notifikasi">
          🔔
          <span class="l-topbar__notif-dot"></span>
        </div>

        <div class="l-topbar__avatar" title="{{ auth()->user()->name ?? '' }}">
          🎓
        </div>

        <form method="POST"
              action="{{ route('logout') }}"
              class="l-logout-form">
          @csrf

          <button type="submit"
                  class="btn btn-soft"
                  style="height:36px;padding:0 14px;font-size:.8rem;border-radius:10px">
            Keluar
          </button>
        </form>

      </div>

    </header>

    {{-- KONTEN --}}
    <div class="l-content">

      {{-- Flash alerts --}}
      @if(session('error'))
        <div class="l-alert l-alert--error">
          ⚠️ {{ session('error') }}
        </div>
      @endif

      @if(session('success'))
        <div class="l-alert l-alert--success">
          ✅ {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <ul class="l-error-list">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      @endif

      {{-- Page heading + actions --}}
      <div class="l-page-topbar">

        <div>
          <h1 class="l-page-heading">
            @yield('heading')
          </h1>

          @hasSection('subtitle')
            <p class="l-page-subtitle">
              @yield('subtitle')
            </p>
          @endif
        </div>

        <div class="l-page-actions">

          @if(
            in_array($role, ['admin', 'guru', 'siswa'], true) &&
            !request()->routeIs($role . '.dashboard')
          )
            <a class="btn btn-soft"
               href="{{ route($role . '.dashboard') }}">
              🏠 Home
            </a>
          @endif

          @yield('actions')

        </div>

      </div>

      {{-- Page content --}}
      @yield('content')

    </div>
  </div>

</div>

<script>
function toggleSidebar(){
  document.getElementById('l-sidebar').classList.toggle('open');
  document.getElementById('l-overlay').classList.toggle('open');
}

function closeSidebar(){
  document.getElementById('l-sidebar').classList.remove('open');
  document.getElementById('l-overlay').classList.remove('open');
}
</script>

@stack('scripts')
</body>
</html>