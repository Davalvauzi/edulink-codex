<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function create(Request $request, Subject $subject): View
    {
        abort_if($request->user()->role !== 'guru', 403);

        return view('materials.create', [
            'title' => 'Tambah Materi',
            'role' => $request->user()->role,
            'user' => $request->user(),
            'subject' => $subject,
        ]);
    }

    public function store(Request $request, Subject $subject): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);

        $data = $this->validateMaterial($request);
        [$filePath, $fileName] = $this->storeUploadedFile($request);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => $data['title'],
            'description' => $this->sanitizeDescription($data['description']),
            'file_path' => $filePath,
            'file_name' => $fileName,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('materials.show', [$subject, $material])
            ->with('success', 'Materi berhasil ditambahkan ke mata pelajaran.');
    }

    public function show(Request $request, Subject $subject, Material $material): View
    {
        $this->ensureMaterialBelongsToSubject($subject, $material);

        $user = $request->user();
        abort_if(! in_array($user->role, ['guru', 'siswa'], true), 403);

        $material->load(['creator', 'subject.creator']);

        return view('materials.show', [
            'title' => $material->title,
            'role' => $user->role,
            'user' => $user,
            'subject' => $subject,
            'material' => $material,
        ]);
    }

    public function edit(Request $request, Subject $subject, Material $material): View
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);

        return view('materials.edit', [
            'title' => 'Edit Materi',
            'role' => $request->user()->role,
            'user' => $request->user(),
            'subject' => $subject,
            'material' => $material,
        ]);
    }

    public function update(Request $request, Subject $subject, Material $material): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);

        $data = $this->validateMaterial($request);

        [$filePath, $fileName] = $this->replaceUploadedFile($request, $material);

        $material->update([
            'title' => $data['title'],
            'description' => $this->sanitizeDescription($data['description']),
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]);

        return redirect()
            ->route('materials.show', [$subject, $material])
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Request $request, Subject $subject, Material $material): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()
            ->route('subjects.show', $subject)
            ->with('success', 'Materi berhasil dihapus.');
    }

    private function validateMaterial(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);
    }

    private function storeUploadedFile(Request $request): array
    {
        if (! $request->hasFile('file')) {
            return [null, null];
        }

        $storedFile = $request->file('file');

        return [
            $storedFile->store('materials', 'public'),
            $storedFile->getClientOriginalName(),
        ];
    }

    private function replaceUploadedFile(Request $request, Material $material): array
    {
        if (! $request->hasFile('file')) {
            return [$material->file_path, $material->file_name];
        }

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        return $this->storeUploadedFile($request);
    }

    private function ensureMaterialBelongsToSubject(Subject $subject, Material $material): void
    {
        abort_if($material->subject_id !== $subject->id, 404);
    }

    private function sanitizeDescription(string $html): string
    {
        $allowed = '<p><div><br><strong><b><em><i><u><h1><h2><h3><ul><ol><li><blockquote>';
        $sanitized = strip_tags($html, $allowed);
        $sanitized = preg_replace('/<(\/?)div>/', '<$1p>', $sanitized) ?? $sanitized;

        return preg_replace('/<([a-z0-9]+)(?:\s[^>]*)?>/i', '<$1>', $sanitized) ?? $sanitized;
    }
}
