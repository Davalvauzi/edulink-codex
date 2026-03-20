<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        :root {
            --bg: #f3f7f6;
            --surface: #ffffff;
            --sidebar: #18342f;
            --text: #10231f;
            --muted: #5a6f69;
            --line: #d7e4df;
            --accent: #d97706;
            --accent-soft: #fff1d6;
            --success-bg: #dcfce7;
            --success-text: #166534;
            --warn-bg: #fff7ed;
            --warn-text: #9a3412;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Trebuchet MS", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(217, 119, 6, 0.12), transparent 25%),
                linear-gradient(160deg, #eef6f3 0%, #f8fafc 45%, #eefbf6 100%);
            color: var(--text);
        }

        a {
            color: inherit;
        }

        .layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px 1fr;
        }

        .sidebar {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.03), transparent 24%),
                var(--sidebar);
            color: #eefbf6;
            padding: 28px;
        }

        .brand {
            margin-bottom: 28px;
        }

        .brand h2 {
            margin: 0 0 8px;
            font-size: 22px;
            letter-spacing: 0.04em;
        }

        .brand p,
        .nav p {
            margin: 0;
            color: rgba(238, 251, 246, 0.72);
            line-height: 1.5;
            font-size: 13px;
        }

        .nav {
            margin-top: 26px;
            display: grid;
            gap: 14px;
        }

        .nav a,
        .nav .static-item {
            display: block;
            padding: 11px 13px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .nav .static-item span,
        .nav a span {
            display: block;
            margin-top: 4px;
            color: rgba(238, 251, 246, 0.7);
            font-size: 12px;
            font-weight: 400;
        }

        .main {
            padding: 28px;
        }

        .alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
        }

        .alert.error {
            background: var(--warn-bg);
            color: var(--warn-text);
        }

        .alert.success {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 26px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: #9a3412;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 14px 0 10px;
            font-size: 32px;
            line-height: 1.1;
        }

        .subtitle {
            margin: 0;
            color: var(--muted);
            max-width: 760px;
            line-height: 1.6;
            font-size: 14px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn,
        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 14px;
            padding: 12px 16px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-section {
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            white-space: nowrap;
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, #d97706, #b45309);
        }

        .btn-dark {
            color: #fff;
            background: #10231f;
        }

        .btn-danger {
            color: #fff;
            background: linear-gradient(135deg, #dc2626, #991b1b);
        }

        .btn-soft {
            color: var(--text);
            background: var(--surface);
            border: 1px solid var(--line);
        }

        .cards,
        .subjects-grid,
        .meta-grid,
        .materials-grid,
        .compact-grid,
        .subsection-grid,
        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 18px;
        }

        .card,
        .subject-item,
        .meta-item,
        .material-item {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(215, 228, 223, 0.95);
            border-radius: 24px;
            padding: 22px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.05);
        }

        .card strong,
        .meta-item strong {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .card p,
        .subject-item p,
        .meta-item p,
        .material-item p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
            font-size: 14px;
        }

        .meta {
            margin-top: 24px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 24px;
        }

        .meta.compact {
            padding: 18px 20px;
        }

        .info-strip {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .mini-info {
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.9);
        }

        .mini-info span {
            display: block;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .mini-info strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .mini-info p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }

        .section-title p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .filter-form,
        .subject-form,
        .material-form,
        .profile-form {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
        }

        .profile-form {
            align-items: stretch;
        }

        .field {
            min-width: 180px;
            flex: 1;
        }

        .field-full {
            width: 100%;
            flex-basis: 100%;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fff;
            font-size: 14px;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        .subject-item,
        .material-item {
            display: block;
            text-decoration: none;
        }

        .subject-item:hover,
        .material-item:hover {
            border-color: #c8dad4;
            transform: translateY(-2px);
            transition: 0.2s ease;
        }

        .subject-badge {
            display: inline-block;
            margin-bottom: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: #9a3412;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        .subject-item h3,
        .material-item h3 {
            margin: 0 0 10px;
            font-size: 18px;
        }

        .empty-state {
            margin-top: 18px;
            padding: 22px;
            border-radius: 20px;
            background: #f8fbfa;
            border: 1px dashed var(--line);
            color: var(--muted);
        }

        .meta-item span,
        .material-meta span {
            display: block;
            color: var(--muted);
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .material-meta {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .link-inline {
            color: #b45309;
            font-weight: 700;
            text-decoration: none;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .toolbar button,
        .toolbar select {
            width: auto;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--text);
        }

        .rich-editor {
            min-height: 220px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #fff;
            line-height: 1.7;
            outline: none;
        }

        .rich-editor:empty::before {
            content: attr(data-placeholder);
            color: #90a4ae;
        }

        .prose {
            color: var(--text);
            line-height: 1.8;
        }

        .prose h1,
        .prose h2,
        .prose h3 {
            margin: 0 0 14px;
            line-height: 1.25;
        }

        .prose p,
        .prose ul,
        .prose ol,
        .prose blockquote {
            margin: 0 0 16px;
        }

        .prose blockquote {
            padding-left: 16px;
            border-left: 4px solid #f59e0b;
            color: var(--muted);
        }

        .prose ul,
        .prose ol {
            padding-left: 24px;
        }

        .stack {
            display: grid;
            gap: 16px;
        }

        .progress-panel {
            display: grid;
            gap: 12px;
        }

        .progress-track {
            width: 100%;
            height: 14px;
            border-radius: 999px;
            background: #e5ece9;
            overflow: hidden;
        }

        .progress-track.compact {
            height: 10px;
            margin-bottom: 8px;
        }

        .progress-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #d97706, #f59e0b);
        }

        .progress-value {
            font-size: 14px;
            font-weight: 700;
            color: #9a3412;
        }

        .inline-progress {
            margin-top: 16px;
        }

        .subsection-list {
            display: grid;
            gap: 16px;
        }

        .subsection-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .subsection-item {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            padding: 20px;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.82);
        }

        .subsection-item.card-mode {
            min-height: 100%;
            flex-direction: column;
        }

        .subsection-item.card-mode .subsection-actions {
            min-width: 0;
            justify-content: flex-start;
        }

        .subsection-item h3 {
            margin: 0 0 10px;
            font-size: 18px;
        }

        .question-list {
            display: grid;
            gap: 18px;
        }

        .question-card {
            padding: 20px;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.86);
        }

        .question-card.compact {
            padding: 16px;
        }

        .question-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .question-card-header p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .question-text {
            margin: 0 0 16px;
            color: var(--text);
            line-height: 1.6;
        }

        .option-grid,
        .choice-list,
        .radio-list {
            display: grid;
            gap: 12px;
        }

        .option-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .question-form-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(280px, 0.7fr);
            gap: 16px;
            align-items: start;
        }

        .question-aside {
            display: grid;
            gap: 12px;
        }

        .choice-item,
        .radio-option,
        .explanation-card,
        .result-panel {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: #fffdfa;
        }

        .choice-item.correct {
            border-color: #bbf7d0;
            background: #f0fdf4;
        }

        .radio-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
        }

        .radio-option input {
            width: auto;
            margin-top: 3px;
        }

        .question-media {
            margin: 0 0 16px;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: #f8fbfa;
        }

        .question-media img {
            display: block;
            width: 100%;
            max-height: 280px;
            object-fit: cover;
        }

        .question-media-caption {
            padding: 10px 14px;
            color: var(--muted);
            font-size: 12px;
            border-top: 1px solid var(--line);
        }

        .answer-pill {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: #ecfccb;
            color: #3f6212;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .answer-pill.wrong {
            background: #fee2e2;
            color: #991b1b;
        }

        .explanation-card strong,
        .result-panel strong {
            display: block;
            margin-bottom: 8px;
        }

        .explanation-card p,
        .result-panel p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .result-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            background: linear-gradient(135deg, #fff7ed, #ffffff);
        }

        .result-score {
            min-width: 86px;
            height: 86px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: linear-gradient(135deg, #d97706, #b45309);
            color: #fff;
            font-size: 28px;
            font-weight: 800;
        }

        .subsection-content {
            flex: 1;
        }

        .subsection-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-start;
            justify-content: flex-end;
            min-width: 180px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            margin-top: 14px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .status-badge.done {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.pending {
            background: #fff7ed;
            color: #9a3412;
        }

        .material-summary {
            margin-top: 14px !important;
        }

        .muted-note {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .error-list {
            margin: 0 0 18px;
            padding: 14px 16px 14px 32px;
            border-radius: 16px;
            background: var(--warn-bg);
            color: var(--warn-text);
        }

        @media (max-width: 980px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .cards,
            .subjects-grid,
            .meta-grid,
            .materials-grid,
            .compact-grid,
            .subsection-grid,
            .quiz-grid,
            .info-strip,
            .option-grid,
            .question-form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .main,
            .sidebar {
                padding: 20px;
            }

            .topbar,
            .section-title,
            .filter-form,
            .subject-form,
            .material-form,
            .profile-form,
            .subsection-item,
            .question-card-header,
            .result-panel {
                flex-direction: column;
                align-items: stretch;
            }

            .actions {
                width: 100%;
                justify-content: flex-start;
            }

            .btn-section {
                width: 100%;
            }

            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <h2>EduLink</h2>
                <p>Portal sekolah dengan akses berbasis peran untuk admin, guru, dan siswa.</p>
            </div>

            <div class="nav">
                @yield('sidebar')
            </div>
        </aside>

        <main class="main">
            @if (session('error'))
                <div class="alert error">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <div class="topbar">
                <div>
                    <span class="eyebrow">{{ $role }}</span>
                    <h1>@yield('heading')</h1>
                    @hasSection('subtitle')
                        <p class="subtitle">@yield('subtitle')</p>
                    @endif
                </div>

                <div class="actions">
                    @yield('actions')

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-dark" type="submit">Logout</button>
                    </form>
                </div>
            </div>

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
