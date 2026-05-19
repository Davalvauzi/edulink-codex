{{--
    Partial: quizzes/_question_panel.blade.php
    Variables: $index (int), $question (array), $isActive (bool)
--}}
<div class="cq-qpanel {{ $isActive ? 'active' : '' }}" data-qidx="{{ $index }}">

    {{-- ============================================================
         PREVIEW (hidden until toggled)
         ============================================================ --}}
    <div class="cq-preview-section" id="pv-section-{{ $index }}">
        <div class="cq-preview-label">👁️ Preview Soal {{ $index + 1 }}</div>
        <div class="cq-preview-card">
            <div class="cq-pv-num">{{ $index + 1 }}</div>
            <img class="cq-pv-img" id="pv-img-{{ $index }}" src="" alt=""/>
            <div class="cq-pv-q" id="pv-q-{{ $index }}">—</div>
            <div class="cq-pv-options" id="pv-opts-{{ $index }}"></div>
            <div class="cq-pv-exp" id="pv-exp-{{ $index }}" style="display:none">
                <div class="cq-pv-exp-label">💡 Pembahasan</div>
                <div class="cq-pv-exp-text" id="pv-exp-text-{{ $index }}"></div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         CARD: PERTANYAAN + GAMBAR
         ============================================================ --}}
    <div class="cq-card">
        <div class="cq-card-title">
            <div class="cq-card-title-icon">❓</div>
            Soal {{ $index + 1 }}
        </div>

        <div class="cq-question-media-grid">
        {{-- Link Gambar --}}
        <div class="cq-field">
            <label class="cq-label">
                Link Gambar
                <span style="font-weight:400;color:var(--ink3)">(opsional)</span>
            </label>
            <input
                class="cq-input @error("questions.{$index}.image_url") is-invalid @enderror"
                type="url"
                name="questions[{{ $index }}][image_url]"
                value="{{ old("questions.{$index}.image_url", $question['image_url'] ?? '') }}"
                placeholder="https://contoh.com/gambar.jpg"
                oninput="syncPreviewIfOpen({{ $index }})"
            />
            <div class="cq-input-hint">Atau upload file di bawah — upload akan diprioritaskan.</div>
            @error("questions.{$index}.image_url")
                <div class="cq-error-msg">{{ $message }}</div>
            @enderror
        </div>

        {{-- Upload Gambar --}}
        <div class="cq-field">
            <label class="cq-label">
                Upload Gambar
                <span style="font-weight:400;color:var(--ink3)">(opsional)</span>
            </label>
            <div class="cq-img-preview-wrap" id="img-preview-wrap-{{ $index }}">
                <img class="cq-img-preview" id="img-preview-{{ $index }}" src="" alt="Preview"/>
                <button type="button" class="cq-img-remove" onclick="removeImage({{ $index }})">✕</button>
                <div class="cq-img-filename" id="img-filename-{{ $index }}"></div>
            </div>
            <div class="cq-img-upload-area" id="img-upload-area-{{ $index }}"
                ondragover="handleDragOver(event,{{ $index }})"
                ondragleave="handleDragLeave(event,{{ $index }})"
                ondrop="handleDrop(event,{{ $index }})">
                <input
                    class="cq-img-upload-input"
                    type="file"
                    name="questions[{{ $index }}][image_file]"
                    accept="image/*"
                    onchange="handleImageUpload(event,{{ $index }})"
                />
                <div class="cq-img-upload-placeholder">
                    <div class="cq-img-upload-icon">🖼️</div>
                    <div class="cq-img-upload-text">Klik atau seret gambar ke sini</div>
                    <div class="cq-img-upload-hint">JPG, PNG, GIF, WebP · Maks. 4 MB</div>
                </div>
            </div>
            @error("questions.{$index}.image_file")
                <div class="cq-error-msg">{{ $message }}</div>
            @enderror
        </div>
        </div>

        {{-- Teks Pertanyaan --}}
        <div class="cq-field">
            <label class="cq-label">
                Teks Pertanyaan <span class="cq-label-req">*</span>
            </label>
            <textarea
                class="cq-textarea @error("questions.{$index}.question") is-invalid @enderror"
                name="questions[{{ $index }}][question]"
                rows="3"
                placeholder="Tulis pertanyaan di sini…"
                required
                oninput="syncPreviewIfOpen({{ $index }})"
            >{{ old("questions.{$index}.question", $question['question'] ?? '') }}</textarea>
            @error("questions.{$index}.question")
                <div class="cq-error-msg">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- ============================================================
         CARD: PILIHAN JAWABAN
         ============================================================ --}}
    <div class="cq-card">
        <div class="cq-card-title">
            <div class="cq-card-title-icon">🔤</div>
            Pilihan Jawaban
            <span style="font-size:.72rem;font-weight:600;color:var(--ink3);margin-left:auto">
                Radio = jawaban benar
            </span>
        </div>

        @php
            $correctOption = old("questions.{$index}.correct_option", $question['correct_option'] ?? 'a');
            $optionMap = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'];
        @endphp

        <div class="cq-options-list" id="opts-list-{{ $index }}">
            @foreach ($optionMap as $key => $letter)
                @php
                    $fieldKey  = "option_{$key}";
                    $isCorrect = ($correctOption === $key);
                    $value     = old("questions.{$index}.{$fieldKey}", $question[$fieldKey] ?? '');
                @endphp
                <div class="cq-option-row {{ $isCorrect ? 'cq-correct-row' : '' }}"
                     id="opt-row-{{ $index }}-{{ $loop->index }}">
                    <div class="cq-option-letter"
                         id="opt-letter-{{ $index }}-{{ $loop->index }}">
                        {{ $letter }}
                    </div>
                    <input
                        class="cq-option-input @error("questions.{$index}.{$fieldKey}") is-invalid @enderror"
                        type="text"
                        name="questions[{{ $index }}][{{ $fieldKey }}]"
                        placeholder="Pilihan {{ $letter }}…"
                        value="{{ $value }}"
                        required
                        oninput="syncPreviewIfOpen({{ $index }})"
                    />
                    <label class="cq-correct-radio">
                        <input
                            type="radio"
                            name="cq-radio-{{ $index }}"
                            value="{{ $key }}"
                            {{ $isCorrect ? 'checked' : '' }}
                            onchange="setCorrect({{ $index }},'{{ $key }}',{{ $loop->index }})"
                        />
                        Benar
                    </label>
                </div>
                @error("questions.{$index}.{$fieldKey}")
                    <div class="cq-error-msg" style="margin-left:50px">{{ $message }}</div>
                @enderror
            @endforeach
        </div>

        {{-- Hidden input untuk correct_option yang dikirim ke controller --}}
        <input
            type="hidden"
            name="questions[{{ $index }}][correct_option]"
            id="correct-hidden-{{ $index }}"
            value="{{ $correctOption }}"
        />

        @error("questions.{$index}.correct_option")
            <div class="cq-error-msg" style="margin-top:8px">{{ $message }}</div>
        @enderror
    </div>

    {{-- ============================================================
         CARD: PEMBAHASAN
         ============================================================ --}}
    <div class="cq-card">
        <div class="cq-card-title">
            <div class="cq-card-title-icon">💡</div>
            Pembahasan Jika Salah
        </div>
        <div class="cq-exp-wrap">
            <div class="cq-exp-label">💡 Pembahasan</div>
            <textarea
                class="cq-textarea @error("questions.{$index}.explanation") is-invalid @enderror"
                name="questions[{{ $index }}][explanation]"
                rows="3"
                placeholder="Jelaskan konsep atau langkah jawaban yang benar (opsional)"
                oninput="syncPreviewIfOpen({{ $index }})"
            >{{ old("questions.{$index}.explanation", $question['explanation'] ?? '') }}</textarea>
            @error("questions.{$index}.explanation")
                <div class="cq-error-msg">{{ $message }}</div>
            @enderror
        </div>
    </div>

</div>{{-- end .cq-qpanel --}}
