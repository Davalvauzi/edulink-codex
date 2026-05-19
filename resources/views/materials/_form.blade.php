@php
    $descriptionValue = old('description', strip_tags($material->description ?? ''));
@endphp

{{-- CARD 1 – Informasi Dasar --}}
<div class="cm-form-card">

    <div class="cm-form-card-title">
        <div class="cm-form-card-title-icon">📋</div>
        Informasi Dasar Materi
    </div>

    <div class="cm-field-row cm-full">
        <div class="cm-field">
            <label class="cm-field-label" for="title">
                Judul Materi <span class="cm-req">*</span>
            </label>
            <input id="title" type="text" name="title"
                class="cm-field-input {{ $errors->has('title') ? 'cm-is-invalid' : '' }}"
                value="{{ old('title', $material->title ?? '') }}"
                placeholder="Contoh: Bab 1 – Aljabar Linear" required>
            @error('title')<span class="cm-field-error">{{ $message }}</span>@enderror
            <div class="cm-field-hint">Maksimal 255 karakter.</div>
        </div>
    </div>

    <div class="cm-field-row cm-three">
        <div class="cm-field">
            <label class="cm-field-label" for="topic">Topik</label>
            <input id="topic" type="text" name="topic"
                class="cm-field-input {{ $errors->has('topic') ? 'cm-is-invalid' : '' }}"
                value="{{ old('topic', $material->topic ?? '') }}"
                placeholder="Contoh: Grammar">
            @error('topic')<span class="cm-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="cm-field">
            <label class="cm-field-label" for="duration">Durasi</label>
            <input id="duration" type="text" name="duration"
                class="cm-field-input {{ $errors->has('duration') ? 'cm-is-invalid' : '' }}"
                value="{{ old('duration', $material->duration ?? '') }}"
                placeholder="Contoh: 90 menit">
            @error('duration')<span class="cm-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="cm-field"></div>
    </div>

    <div class="cm-field-row cm-full">
        <div class="cm-field">
            <label class="cm-field-label" for="description">
                Deskripsi Materi <span class="cm-req">*</span>
            </label>
            <textarea
                id="description"
                name="description"
                class="cm-field-textarea {{ $errors->has('description') ? 'cm-is-invalid' : '' }}"
                placeholder="Tulis ringkasan atau pengantar materi di sini…"
                required
            >{{ $descriptionValue }}</textarea>
            @error('description')<span class="cm-field-error">{{ $message }}</span>@enderror
        </div>
    </div>

</div>{{-- /card 1 --}}


{{-- CARD 2 – Thumbnail --}}
<div class="cm-form-card">

    <div class="cm-form-card-title">
        <div class="cm-form-card-title-icon">🖼️</div>
        Thumbnail Materi
    </div>

    <div class="cm-field-row cm-full">
        <div class="cm-field">
            <label class="cm-field-label">Gambar Sampul</label>

            <input type="file" id="cm-thumb-input" name="thumbnail"
                accept="image/jpeg,image/png,image/webp"
                style="display:none"
                onchange="cmPreviewThumbnail(event)">

            <div class="cm-upload-zone" role="button" tabindex="0"
                onclick="document.getElementById('cm-thumb-input').click()"
                onkeydown="if(event.key==='Enter'||event.key===' ')document.getElementById('cm-thumb-input').click()">
                <div class="cm-upload-icon">🖼️</div>
                <div class="cm-upload-title">Upload Thumbnail</div>
                <div class="cm-upload-sub">PNG, JPG, WEBP — Maks. 5 MB</div>
            </div>

            @error('thumbnail')<span class="cm-field-error">{{ $message }}</span>@enderror

            <div class="cm-thumb-preview" id="cm-thumb-preview"
                @if(! empty($material?->thumbnail_url)) style="display:block" @endif>
                <img id="cm-thumb-img"
                    src="{{ $material?->thumbnail_url ?? '' }}" alt="Thumbnail">
                <button type="button" class="cm-thumb-remove" onclick="cmRemoveThumbnail()">
                    ✕ Hapus Gambar
                </button>
            </div>
        </div>
    </div>

</div>{{-- /card 2 --}}


{{-- CARD 3 – File PDF --}}
<div class="cm-form-card">

    <div class="cm-form-card-title">
        <div class="cm-form-card-title-icon">📄</div>
        File Materi
    </div>

    <input type="file" name="file" id="cm-file-input"
        accept="application/pdf"
        style="display:none"
        onchange="cmPreviewFile(this)">

    <div class="cm-upload-zone" role="button" tabindex="0"
        onclick="document.getElementById('cm-file-input').click()"
        onkeydown="if(event.key==='Enter'||event.key===' ')document.getElementById('cm-file-input').click()">
        <div class="cm-upload-icon">📤</div>
        <div class="cm-upload-title">Upload File PDF</div>
        <div class="cm-upload-sub">Format PDF — Maks. 5 MB</div>
    </div>

    @error('file')
        <span class="cm-field-error" style="margin-top:8px;display:block">{{ $message }}</span>
    @enderror

    @if (! empty($material?->file_name))
        <div class="cm-existing-file">
            <div class="cm-existing-file-icon">📄</div>
            <div>
                <div class="cm-existing-file-name">{{ $material->file_name }}</div>
                <div class="cm-existing-file-hint">File saat ini. Upload baru untuk mengganti.</div>
            </div>
        </div>
    @endif

    <div class="cm-existing-file" id="cm-file-preview" style="display:none">
        <div class="cm-existing-file-icon">📄</div>
        <div>
            <div class="cm-existing-file-name" id="cm-file-name">—</div>
            <div class="cm-existing-file-hint" id="cm-file-size">—</div>
        </div>
    </div>

</div>{{-- /card 3 --}}

<script>
function cmPreviewThumbnail(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran gambar maksimal 5 MB.');
        event.target.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('cm-thumb-img').src = e.target.result;
        document.getElementById('cm-thumb-preview').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function cmRemoveThumbnail() {
    document.getElementById('cm-thumb-input').value = '';
    document.getElementById('cm-thumb-preview').style.display = 'none';
}

function cmPreviewFile(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('cm-file-name').textContent = file.name;
    document.getElementById('cm-file-size').textContent =
        (file.size / 1024).toFixed(0) + ' KB';
    document.getElementById('cm-file-preview').style.display = 'flex';
}
</script>