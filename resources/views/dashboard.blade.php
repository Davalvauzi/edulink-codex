<!-- ini blade baru -->

@extends('layouts.portal')

@section('styles')
@endsection

@section('content')
<div class="dashboard-wrap">

  {{-- ── GREETING BANNER ─────────────────────── --}}
  <div class="dashboard-greeting">
    @if ($role === 'siswa' && isset($user))
      <div class="dashboard-greeting__name">Halo, {{ $user->name }}! 👋</div>
      <div class="dashboard-greeting__sub">Selamat belajar hari ini. Semangat meraih nilai terbaik!</div>
      <div class="dashboard-greeting__pills">
        <span class="dashboard-greeting__pill">🎓 {{ \App\Models\User::kelasLabel($user->kelas) }}</span>
        <span class="dashboard-greeting__pill">📚 Bahasa Inggris</span>
        @if(isset($progressPercentage))
          <span class="dashboard-greeting__pill">⚡ Progres {{ $progressPercentage }}%</span>
        @endif
      </div>
    @elseif ($role === 'guru')
      <div class="dashboard-greeting__name">Halo, Guru! 👋</div>
      <div class="dashboard-greeting__sub">Pantau perkembangan materi, kuis, dan aktivitas siswa dari satu halaman.</div>
      <div class="dashboard-greeting__pills">
        <span class="dashboard-greeting__pill">👩‍🏫 Dashboard Guru</span>
      </div>
    @else
      <div class="dashboard-greeting__name">Dashboard Admin 🛡️</div>
      <div class="dashboard-greeting__sub">Pantau seluruh aktivitas portal dari satu tempat.</div>
      <div class="dashboard-greeting__pills">
        <span class="dashboard-greeting__pill">🔧 Mode Admin</span>
      </div>
    @endif
    <div class="dashboard-greeting__emoji">🚀</div>
  </div>

  {{-- ── PROGRESS (SISWA ONLY) ───────────────── --}}
  @if ($role === 'siswa' && isset($availableQuizzes))
    <div class="dashboard-progress">
      <div class="dashboard-progress__top">
        <div>
          <span class="dashboard-progress__eyebrow">Progres Kamu</span>
        </div>
        <div class="dashboard-progress__badge">
          <span class="dashboard-progress__badge-dot"></span>
          Sedang Berjalan
        </div>
      </div>

      <div>
        <div class="dashboard-progress__bar-info">
          <span class="dashboard-progress__bar-label">Total kuis diselesaikan</span>
          <span class="dashboard-progress__bar-pct">{{ $progressPercentage }}%</span>
        </div>
        <div class="dashboard-progress__track">
          <div class="dashboard-progress__fill" style="width:{{ $progressPercentage }}%"></div>
        </div>
      </div>

      {{-- ── STAT CARDS ──────── --}}
      @php
        $icons   = ['📚', '✅', '✏️', '🏆'];
        $statDef = $dashboardStats ?? [];
      @endphp
      <div class="dashboard-stat-grid">
        @foreach ($statDef as $i => $stat)
          <div class="dashboard-stat-card">
            <div class="dashboard-stat-card__icon">{{ $icons[$i] ?? '📊' }}</div>
            <div class="dashboard-stat-card__val">{{ $stat['value'] }}</div>
            <div class="dashboard-stat-card__lbl">{{ $stat['label'] }}</div>
            <div class="dashboard-stat-card__delta">{{ $stat['detail'] }}</div>
          </div>
        @endforeach
      </div>

    </div>
  @endif

  {{-- ── GURU / ADMIN: STAT CARDS ────────────── --}}
  @if ($role === 'guru' || $role === 'admin')
    @php
      $icons   = ['📚', '📖', '✏️', '🏆'];
      $statDef = $dashboardStats ?? [];
    @endphp
    <div class="dashboard-stat-grid">
      @foreach ($statDef as $i => $stat)
        <div class="dashboard-stat-card">
          <div class="dashboard-stat-card__icon">{{ $icons[$i] ?? '📊' }}</div>
          <div class="dashboard-stat-card__val">{{ $stat['value'] }}</div>
          <div class="dashboard-stat-card__lbl">{{ $stat['label'] }}</div>
          <div class="dashboard-stat-card__delta">{{ $stat['detail'] }}</div>
        </div>
      @endforeach
    </div>
  @endif

  {{-- ── SISWA: MATERI + RIWAYAT KUIS ────────── --}}
  @if (false && $role === 'siswa')
    <div class="dashboard-two-col">

      {{-- Daftar Mapel --}}
      <div class="dashboard-section-box">
        <div class="dashboard-section-box__title">📚 Materi</div>
        @php
          $thumbColors = ['green','blue','amber','green','blue'];
          $thumbIcons  = ['✍️','📖','🖊️','📐','🔬'];
        @endphp

        @if(($materials ?? collect())->isEmpty())
          <div class="dashboard-empty">Belum ada materi untuk kelas ini.</div>
        @else
          <div class="dashboard-materi-list">
            @foreach ($materials as $idx => $material)
              <a href="{{ route('materials.show', [$material->subject, $material]) }}"
                 class="dashboard-materi-item">
                <div class="dashboard-materi-item__thumb dashboard-materi-item__thumb--{{ $thumbColors[$idx % count($thumbColors)] }}">
                  {{ $thumbIcons[$idx % count($thumbIcons)] }}
                </div>
                <div class="dashboard-materi-item__info">
                  <div class="dashboard-materi-item__name">
                    {{ $material->title }}
                  </div>
                </div>
                <span class="dashboard-materi-item__arrow">→</span>
              </a>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Riwayat Latihan Soal --}}
      <div class="dashboard-section-box">
        <div class="dashboard-section-box__title" style="display:flex;align-items:center;justify-content:space-between;">
          <span>📊 Riwayat Latihan Soal</span>
          <a href="{{ route('siswa.quizzes') }}" class="dashboard-section-box__link">Lihat Semua →</a>
        </div>

        @php
          $recentAttempts = $recentQuizAttempts ?? collect();
        @endphp

        @if ($recentAttempts->isEmpty())
          <div class="dashboard-empty" style="padding:16px;font-size:.8rem;">
            Belum ada latihan soal yang dikerjakan. <a href="{{ route('siswa.quizzes') }}" style="color:var(--g600);font-weight:700;">Mulai sekarang →</a>
          </div>
        @else
          <div class="dashboard-recent-list">
            @foreach ($recentAttempts as $attempt)
              @php
                $score = $attempt->score;
                $scoreClass = $score >= 80 ? 'dashboard-recent-item__score--high' : ($score >= 60 ? 'dashboard-recent-item__score--mid' : 'dashboard-recent-item__score--low');
                $scoreIcon  = $score >= 80 ? '✍️' : ($score >= 60 ? '📝' : '📖');
                $diffLabel  = $attempt->quiz->questions_count . ' soal';
                $timeLabel  = $attempt->submitted_at?->diffForHumans() ?? '-';
              @endphp
              <a href="{{ route('quizzes.show', [$attempt->quiz->material->subject, $attempt->quiz->material, $attempt->quiz]) }}"
                 class="dashboard-recent-item" style="text-decoration:none;color:inherit;">
                <span class="dashboard-recent-item__icon">{{ $scoreIcon }}</span>
                <div style="flex:1;min-width:0;">
                  <div class="dashboard-recent-item__name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $attempt->quiz->title }}
                  </div>
                  <div class="dashboard-recent-item__meta">{{ $timeLabel }} · {{ $diffLabel }}</div>
                </div>
                <div class="dashboard-recent-item__score {{ $scoreClass }}">{{ $score }}</div>
              </a>
            @endforeach
          </div>
        @endif
      </div>

    </div>
  @endif

  {{-- ── GURU / ADMIN: AKTIVITAS + MAPEL ────── --}}
  @if ($role === 'guru' || $role === 'admin')
    {{-- Kuis Terbaru --}}
    @if (!empty($recentQuizzes) && $recentQuizzes->isNotEmpty())
      <div class="dashboard-section-box">
        <div class="dashboard-section-box__title" style="display:flex;align-items:center;justify-content:space-between;">
          <span>✏️ Kuis Terbaru</span>
          @if ($role === 'guru')
            <a href="{{ route('guru.quizzes') }}" class="dashboard-section-box__link">Lihat Semua →</a>
          @else
            <a href="{{ route('admin.quizzes') }}" class="dashboard-section-box__link">Lihat Semua →</a>
          @endif
        </div>
        <div class="dashboard-quiz-grid">
          @foreach ($recentQuizzes as $quiz)
            <a href="{{ route('quizzes.show', [$quiz->material->subject, $quiz->material, $quiz]) }}"
               class="dashboard-quiz-card">
              <div class="dashboard-quiz-card__badge">{{ $quiz->material->subject->name ?? 'Mapel' }}</div>
              <div class="dashboard-quiz-card__title">{{ $quiz->title }}</div>
              <div class="dashboard-quiz-card__meta">{{ $quiz->questions_count }} soal · {{ $quiz->attempts_count }} attempt siswa</div>
            </a>
          @endforeach
        </div>
      </div>
    @endif

  @endif

</div>
@endsection


