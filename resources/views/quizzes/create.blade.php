@extends('layouts.portal')

@php
    $oldQuestions = old('questions', [
        ['question' => '', 'option_a' => '', 'option_b' => '', 'option_c' => '', 'option_d' => '', 'correct_option' => 'a', 'explanation' => ''],
    ]);
@endphp

@section('sidebar')
    <a href="{{ route('materials.show', [$subject, $material]) }}">
        Kembali ke Detail Materi
        <span>{{ $material->title }}</span>
    </a>
    <div class="static-item">
        Mata Pelajaran
        <span>{{ $subject->name }}</span>
    </div>
    <div class="static-item">
        Mode
        <span>Buat kuis pilihan ganda</span>
    </div>
@endsection

@section('heading', 'Buat Kuis Baru')
@section('subtitle', 'Tambahkan latihan soal pilihan ganda setelah materi tersedia. Setiap soal dapat diberi pembahasan yang akan tampil saat siswa salah menjawab.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('materials.show', [$subject, $material]) }}">Kembali</a>
@endsection

@section('content')
    <section class="meta stack">
        <div>
            <strong>Materi Induk</strong>
            <p>Kuis ini akan muncul pada materi <strong>{{ $material->title }}</strong> di mapel {{ $subject->name }}.</p>
        </div>

        <form class="material-form stack" method="POST" action="{{ route('guru.materials.quizzes.store', [$subject, $material]) }}" enctype="multipart/form-data">
            @csrf

            <div class="question-form-grid">
                <div class="field field-full">
                    <label for="title">Judul Kuis</label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Latihan Bab 1" required>
                </div>

                <div class="field field-full">
                    <label for="description">Instruksi Kuis</label>
                    <textarea id="description" name="description" placeholder="Tulis petunjuk singkat untuk siswa">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="section-title">
                <div>
                    <strong>Daftar Soal</strong>
                    <p>Satu kuis dapat memuat banyak soal. Tiap soal bisa memiliki gambar dari link atau upload file.</p>
                </div>
                <button class="btn btn-primary btn-section" type="button" id="add-question">Tambah Soal</button>
            </div>

            <div id="question-list" class="question-list">
                @foreach ($oldQuestions as $index => $question)
                    <article class="question-card compact">
                        <div class="question-card-header">
                            <div>
                                <strong>Soal {{ $index + 1 }}</strong>
                                <p>Isikan pertanyaan, opsi jawaban, media pendukung, dan pembahasan.</p>
                            </div>
                            <div class="subsection-actions">
                                @if ($index > 0)
                                    <button class="btn btn-soft btn-section remove-question" type="button">Hapus Soal</button>
                                @endif
                            </div>
                        </div>

                        <div class="question-form-grid">
                            <div class="stack">
                                <div class="field field-full">
                                    <label>Pertanyaan</label>
                                    <textarea name="questions[{{ $index }}][question]" required>{{ $question['question'] ?? '' }}</textarea>
                                </div>

                                <div class="option-grid">
                                    <div class="field">
                                        <label>Opsi A</label>
                                        <input type="text" name="questions[{{ $index }}][option_a]" value="{{ $question['option_a'] ?? '' }}" required>
                                    </div>
                                    <div class="field">
                                        <label>Opsi B</label>
                                        <input type="text" name="questions[{{ $index }}][option_b]" value="{{ $question['option_b'] ?? '' }}" required>
                                    </div>
                                    <div class="field">
                                        <label>Opsi C</label>
                                        <input type="text" name="questions[{{ $index }}][option_c]" value="{{ $question['option_c'] ?? '' }}" required>
                                    </div>
                                    <div class="field">
                                        <label>Opsi D</label>
                                        <input type="text" name="questions[{{ $index }}][option_d]" value="{{ $question['option_d'] ?? '' }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="question-aside">
                                <div class="field">
                                    <label>Kunci Jawaban</label>
                                    <select name="questions[{{ $index }}][correct_option]" required>
                                        @foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $value => $label)
                                            <option value="{{ $value }}" @selected(($question['correct_option'] ?? 'a') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field">
                                    <label>Link Gambar</label>
                                    <input type="url" name="questions[{{ $index }}][image_url]" value="{{ $question['image_url'] ?? '' }}" placeholder="https://contoh.com/gambar-soal.jpg">
                                </div>

                                <div class="field">
                                    <label>Upload Gambar</label>
                                    <input type="file" name="questions[{{ $index }}][image_file]" accept="image/*">
                                    <p class="muted-note">Upload akan diprioritaskan jika keduanya diisi.</p>
                                </div>

                                <div class="field field-full">
                                    <label>Pembahasan Jika Salah</label>
                                    <textarea name="questions[{{ $index }}][explanation]" placeholder="Jelaskan konsep atau langkah jawaban yang benar">{{ $question['explanation'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <button class="btn btn-primary" type="submit">Simpan Kuis</button>
        </form>
    </section>

    <template id="question-template">
        <article class="question-card compact">
            <div class="question-card-header">
                <div>
                    <strong>Soal __NUMBER__</strong>
                    <p>Isikan pertanyaan, opsi jawaban, media pendukung, dan pembahasan.</p>
                </div>
                <div class="subsection-actions">
                    <button class="btn btn-soft btn-section remove-question" type="button">Hapus Soal</button>
                </div>
            </div>

            <div class="question-form-grid">
                <div class="stack">
                    <div class="field field-full">
                        <label>Pertanyaan</label>
                        <textarea name="questions[__INDEX__][question]" required></textarea>
                    </div>

                    <div class="option-grid">
                        <div class="field">
                            <label>Opsi A</label>
                            <input type="text" name="questions[__INDEX__][option_a]" required>
                        </div>
                        <div class="field">
                            <label>Opsi B</label>
                            <input type="text" name="questions[__INDEX__][option_b]" required>
                        </div>
                        <div class="field">
                            <label>Opsi C</label>
                            <input type="text" name="questions[__INDEX__][option_c]" required>
                        </div>
                        <div class="field">
                            <label>Opsi D</label>
                            <input type="text" name="questions[__INDEX__][option_d]" required>
                        </div>
                    </div>
                </div>

                <div class="question-aside">
                    <div class="field">
                        <label>Kunci Jawaban</label>
                        <select name="questions[__INDEX__][correct_option]" required>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Link Gambar</label>
                        <input type="url" name="questions[__INDEX__][image_url]" placeholder="https://contoh.com/gambar-soal.jpg">
                    </div>

                    <div class="field">
                        <label>Upload Gambar</label>
                        <input type="file" name="questions[__INDEX__][image_file]" accept="image/*">
                        <p class="muted-note">Upload akan diprioritaskan jika keduanya diisi.</p>
                    </div>

                    <div class="field field-full">
                        <label>Pembahasan Jika Salah</label>
                        <textarea name="questions[__INDEX__][explanation]" placeholder="Jelaskan konsep atau langkah jawaban yang benar"></textarea>
                    </div>
                </div>
            </div>
        </article>
    </template>
@endsection

@push('scripts')
    <script>
        const questionList = document.getElementById('question-list');
        const addQuestionButton = document.getElementById('add-question');
        const questionTemplate = document.getElementById('question-template');

        const refreshQuestionNumbers = () => {
            questionList.querySelectorAll('.question-card').forEach((card, index) => {
                const title = card.querySelector('.question-card-header strong');
                if (title) {
                    title.textContent = `Soal ${index + 1}`;
                }
            });
        };

        questionList.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-question');
            if (!removeButton) {
                return;
            }

            removeButton.closest('.question-card')?.remove();
            refreshQuestionNumbers();
        });

        addQuestionButton.addEventListener('click', () => {
            const index = questionList.querySelectorAll('.question-card').length;
            const html = questionTemplate.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(index + 1));

            questionList.insertAdjacentHTML('beforeend', html);
            refreshQuestionNumbers();
        });
    </script>
@endpush
