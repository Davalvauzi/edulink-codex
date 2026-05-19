<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Kuis {{ $quiz->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 32px; }
        h1, h2, h3, p { margin-top: 0; }
        .header, .question { margin-bottom: 24px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 24px; }
        .box { border: 1px solid #d1d5db; border-radius: 12px; padding: 12px 14px; }
        .status { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .status.correct { background: #dcfce7; color: #166534; }
        .status.wrong { background: #fee2e2; color: #991b1b; }
        .question img { max-width: 100%; border-radius: 10px; margin: 10px 0; }
        .muted { color: #6b7280; }
        @media print {
            .print-actions { display: none; }
            body { margin: 16px; }
        }
    </style>
</head>
<body>
    <div class="print-actions" style="margin-bottom: 20px;">
        <button onclick="window.print()">Print / Simpan PDF</button>
    </div>

    <div class="header">
        <h1>Hasil Kuis</h1>
        <p class="muted">{{ $quiz->title }} - {{ $subject->name }} - {{ $material->title }}</p>
    </div>

    <div class="grid">
        <div class="box">
            <strong>Nama Siswa</strong>
            <p>{{ $attempt->user->name }}</p>
        </div>
        <div class="box">
            <strong>Email</strong>
            <p>{{ $attempt->user->email }}</p>
        </div>
        <div class="box">
            <strong>Kelas</strong>
            <p>{{ $attempt->user?->kelas ? \App\Models\User::kelasLabel($attempt->user->kelas) : '-' }}</p>
        </div>
        <div class="box">
            <strong>Skor</strong>
            <p>{{ $attempt->score }} ({{ $attempt->correct_answers }}/{{ $attempt->total_questions }})</p>
        </div>
    </div>

    @foreach ($attempt->answers->sortBy(fn ($answer) => $answer->question->position) as $answer)
        <div class="question">
            <h3>Soal {{ $answer->question->position }}</h3>
            <p>{{ $answer->question->question }}</p>
            @if ($answer->question->image_source)
                <img src="{{ $answer->question->image_source }}" alt="Gambar soal {{ $answer->question->position }}">
            @endif
            <p>
                <span class="status {{ $answer->is_correct ? 'correct' : 'wrong' }}">
                    {{ $answer->is_correct ? 'Benar' : 'Salah' }}
                </span>
            </p>
            <p>Jawaban siswa: {{ strtoupper($answer->selected_option) }}</p>
            <p>Jawaban benar: {{ strtoupper($answer->question->correct_option) }}</p>
            <p>Penjelasan: {{ $answer->question->explanation ?: 'Tidak ada penjelasan tambahan.' }}</p>
        </div>
    @endforeach

    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>
