@php
    $editorId = $editorId ?? 'description-editor';
    $inputId = $inputId ?? 'description';
    $descriptionValue = old('description', $material->description ?? '<p></p>');
@endphp

<div class="field">
    <label for="title">Nama Materi</label>
    <input id="title" type="text" name="title" value="{{ old('title', $material->title ?? '') }}" placeholder="Contoh: Bab 1" required>
</div>

<div class="field field-full">
    <label for="{{ $inputId }}">Deskripsi</label>
    <div class="toolbar" data-editor-toolbar>
        <select data-editor-block>
            <option value="P">Paragraf</option>
            <option value="H1">Heading 1</option>
            <option value="H2">Heading 2</option>
            <option value="H3">Heading 3</option>
        </select>
        <button type="button" data-command="bold"><strong>B</strong></button>
        <button type="button" data-command="italic"><em>I</em></button>
        <button type="button" data-command="underline"><u>U</u></button>
        <button type="button" data-command="insertUnorderedList">Bullet</button>
        <button type="button" data-command="insertOrderedList">Number</button>
        <button type="button" data-command="formatBlock" data-value="blockquote">Quote</button>
    </div>
    <div
        id="{{ $editorId }}"
        class="rich-editor"
        contenteditable="true"
        data-editor
        data-input="{{ $inputId }}"
        data-placeholder="Tulis deskripsi materi di sini"
    >{!! $descriptionValue !!}</div>
    <input id="{{ $inputId }}" type="hidden" name="description" value="{{ e($descriptionValue) }}">
</div>

<div class="field">
    <label for="file">File PDF</label>
    <input id="file" type="file" name="file" accept="application/pdf">
    @if (! empty($material?->file_name))
        <p>File saat ini: {{ $material->file_name }}</p>
    @endif
</div>

@once
    @push('scripts')
    <script>
        document.querySelectorAll('[data-editor]').forEach((editor) => {
            const input = document.getElementById(editor.dataset.input);
            const toolbar = editor.parentElement.querySelector('[data-editor-toolbar]');
            const blockSelect = toolbar.querySelector('[data-editor-block]');

            const syncValue = () => {
                input.value = editor.innerHTML.trim() || '<p></p>';
            };

            toolbar.querySelectorAll('button[data-command]').forEach((button) => {
                button.addEventListener('click', () => {
                    const value = button.dataset.value ?? null;
                    document.execCommand(button.dataset.command, false, value);
                    editor.focus();
                    syncValue();
                });
            });

            blockSelect.addEventListener('change', () => {
                document.execCommand('formatBlock', false, blockSelect.value);
                editor.focus();
                syncValue();
            });

            editor.addEventListener('input', syncValue);
            syncValue();
        });
    </script>
    @endpush
@endonce
