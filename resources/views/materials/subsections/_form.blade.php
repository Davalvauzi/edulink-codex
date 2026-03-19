@php
    $editorId = $editorId ?? 'subsection-description-editor';
    $inputId = $inputId ?? 'subsection-description';
    $descriptionValue = old('description', $subsection->description ?? '<p></p>');
@endphp

<div class="field">
    <label for="title">Nama Sub Bab</label>
    <input id="title" type="text" name="title" value="{{ old('title', $subsection->title ?? '') }}" placeholder="Contoh: Bentuk Aljabar" required>
</div>

<div class="field">
    <label for="position">Urutan Sub Bab</label>
    <input id="position" type="number" min="1" name="position" value="{{ old('position', $subsection->position ?? 1) }}" required>
</div>

<div class="field field-full">
    <label for="{{ $inputId }}">Isi Sub Bab</label>
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
        data-placeholder="Tulis isi sub bab di sini"
    >{!! $descriptionValue !!}</div>
    <input id="{{ $inputId }}" type="hidden" name="description" value="{{ e($descriptionValue) }}">
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
