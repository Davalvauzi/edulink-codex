<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login EduLink </title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --panel: #ffffff;
            --primary: #1d4ed8;
            --text: #0f172a;
            --muted: #475569;
            --danger: #b91c1c;
            --border: #dbe3f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, #dbeafe, transparent 28%),
                radial-gradient(circle at bottom right, #bfdbfe, transparent 22%),
                var(--bg);
            color: var(--text);
            padding: 24px;
        }

        .card {
            width: min(100%, 460px);
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        p {
            margin: 0 0 24px;
            color: var(--muted);
            line-height: 1.6;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 15px;
            margin-bottom: 18px;
        }

        input:focus {
            outline: 2px solid #93c5fd;
            border-color: #60a5fa;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            color: var(--muted);
        }

        .checkbox input {
            width: auto;
            margin: 0;
        }

        button {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            cursor: pointer;
        }

        .error {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #fee2e2;
            color: var(--danger);
        }

        .demo {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
            font-size: 14px;
            color: var(--muted);
        }

        .demo strong {
            color: var(--text);
        }

        .register-link {
            margin-top: 18px;
            text-align: center;
            color: var(--muted);
        }

        .register-link a {
            color: #1e40af;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>EduLink </h1>
        <p>Masuk ke website edulink menggunakan akun yang sudah terdaftar.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label class="checkbox" for="remember">
                <input id="remember" type="checkbox" name="remember">
                Ingat saya
            </label>

            <button type="submit">Masuk</button>
        </form>

        {{-- <div class="demo">
            <strong>Akun demo</strong><br>
            admin@edulink.test / password<br>
            guru@edulink.test / password<br>
            siswa@edulink.test / password
        </div> --}}

        <div class="register-link">
            Belum punya akun siswa? <a href="{{ route('register') }}">Daftar di sini</a>
        </div>
    </div>
</body>

</html>
