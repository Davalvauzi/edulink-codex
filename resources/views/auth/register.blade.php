<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Siswa EduLink Codex</title>
    <style>
        :root {
            --bg: #f8fafc;
            --panel: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --accent: #0f766e;
            --accent-2: #115e59;
            --border: #dbe3ea;
            --danger: #b91c1c;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--text);
            background:
                radial-gradient(circle at top right, rgba(45, 212, 191, 0.25), transparent 30%),
                linear-gradient(135deg, #e2e8f0, #f8fafc 45%, #ecfeff);
        }

        .card {
            width: min(100%, 560px);
            padding: 32px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.12);
        }

        h1 { margin: 0 0 8px; font-size: 34px; }
        p { margin: 0 0 24px; color: var(--muted); line-height: 1.6; }
        label { display: block; margin-bottom: 8px; font-weight: 700; }
        input, select {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid var(--border);
            font-size: 15px;
            margin-bottom: 18px;
            background: #fff;
        }
        .error {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #fee2e2;
            color: var(--danger);
        }
        button {
            width: 100%;
            padding: 14px 16px;
            border: 0;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            font-weight: 700;
            cursor: pointer;
        }
        .footer {
            margin-top: 18px;
            text-align: center;
            color: var(--muted);
        }
        .footer a { color: var(--accent-2); text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Daftar Sebagai Siswa</h1>
        <p>Buat akun siswa baru. Role akan otomatis disimpan sebagai <strong>siswa</strong>.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <label for="name">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>

            <label for="kelas">Kelas</label>
            <select id="kelas" name="kelas" required>
                <option value="">Pilih kelas</option>
                @foreach (\App\Models\User::kelasOptions() as $kelasValue => $kelasLabel)
                    <option value="{{ $kelasValue }}" @selected(old('kelas') === $kelasValue)>{{ $kelasLabel }}</option>
                @endforeach
            </select>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label for="password_confirmation">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>

            <button type="submit">Buat Akun Siswa</button>
        </form>

        <div class="footer">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
    </div>
</body>
</html>
