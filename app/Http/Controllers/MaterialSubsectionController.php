<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialSubsection;
use App\Models\MaterialSubsectionProgress;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MaterialSubsectionController extends Controller
{
    public function create(Request $request, Subject $subject, Material $material): View
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);

        return view('materials.subsections.create', [
            'title' => 'Tambah Sub Bab',
            'role' => $request->user()->role,
            'user' => $request->user(),
            'subject' => $subject,
            'material' => $material,
        ]);
    }

    public function store(Request $request, Subject $subject, Material $material): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);

        $data = $this->validateSubsection($request);
        [$imagePath, $imageName] = $this->storeUploadedImage($request);

        $material->subsections()->create([
            'title' => $data['title'],
            'description' => $this->sanitizeDescription($data['description']),
            'image_path' => $imagePath,
            'image_name' => $imageName,
            'image_url' => $data['image_url'] ?? null,
            'position' => $data['position'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('materials.show', [$subject, $material])
            ->with('success', 'Sub bab berhasil ditambahkan.');
    }

    public function show(Request $request, Subject $subject, Material $material, MaterialSubsection $subsection): View
    {
        $this->ensureMaterialBelongsToSubject($subject, $material);
        $this->ensureSubsectionBelongsToMaterial($material, $subsection);

        $user = $request->user();
        abort_if(! in_array($user->role, ['guru', 'siswa'], true), 403);

        if ($user->role === 'siswa') {
            $progress = MaterialSubsectionProgress::query()->firstOrCreate(
                [
                    'material_subsection_id' => $subsection->id,
                    'user_id' => $user->id,
                ],
                [
                    'completed_at' => now(),
                ],
            );

            if (! $progress->completed_at) {
                $progress->update(['completed_at' => now()]);
            }
        }

        $subsection->load(['creator', 'material.subject']);
        $material->load([
            'subsections' => fn ($query) => $query->with(
                $user->role === 'siswa'
                    ? ['progressRecords' => fn ($progressQuery) => $progressQuery->where('user_id', $user->id)]
                    : ['progressRecords']
            ),
        ]);

        $totalSubsections = $material->subsections->count();
        $completedSubsections = $user->role === 'siswa'
            ? $material->subsections->filter(fn (MaterialSubsection $item) => $item->progressRecords->isNotEmpty())->count()
            : $material->subsections->filter(fn (MaterialSubsection $item) => $item->progressRecords->isNotEmpty())->count();

        $nextSubsection = $material->subsections
            ->first(fn (MaterialSubsection $item) => $item->position > $subsection->position || ($item->position === $subsection->position && $item->id > $subsection->id));

        return view('materials.subsections.show', [
            'title' => $subsection->title,
            'role' => $user->role,
            'user' => $user,
            'subject' => $subject,
            'material' => $material,
            'subsection' => $subsection,
            'nextSubsection' => $nextSubsection,
            'totalSubsections' => $totalSubsections,
            'completedSubsections' => $completedSubsections,
            'progressPercentage' => $totalSubsections > 0 ? (int) round(($completedSubsections / $totalSubsections) * 100) : 0,
        ]);
    }

    public function edit(Request $request, Subject $subject, Material $material, MaterialSubsection $subsection): View
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);
        $this->ensureSubsectionBelongsToMaterial($material, $subsection);

        return view('materials.subsections.edit', [
            'title' => 'Edit Sub Bab',
            'role' => $request->user()->role,
            'user' => $request->user(),
            'subject' => $subject,
            'material' => $material,
            'subsection' => $subsection,
        ]);
    }

    public function update(Request $request, Subject $subject, Material $material, MaterialSubsection $subsection): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);
        $this->ensureSubsectionBelongsToMaterial($material, $subsection);

        $data = $this->validateSubsection($request);
        [$imagePath, $imageName, $imageUrl] = $this->resolveUpdatedImage($request, $subsection, $data);

        $subsection->update([
            'title' => $data['title'],
            'description' => $this->sanitizeDescription($data['description']),
            'image_path' => $imagePath,
            'image_name' => $imageName,
            'image_url' => $imageUrl,
            'position' => $data['position'],
        ]);

        return redirect()
            ->route('materials.subsections.show', [$subject, $material, $subsection])
            ->with('success', 'Sub bab berhasil diperbarui.');
    }

    public function destroy(Request $request, Subject $subject, Material $material, MaterialSubsection $subsection): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);
        $this->ensureSubsectionBelongsToMaterial($material, $subsection);

        if ($subsection->image_path) {
            Storage::disk('public')->delete($subsection->image_path);
        }

        $subsection->delete();

        return redirect()
            ->route('materials.show', [$subject, $material])
            ->with('success', 'Sub bab berhasil dihapus.');
    }

    private function validateSubsection(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'position' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string'],
            'image_url' => ['nullable', 'url'],
            'image_file' => ['nullable', 'file', 'image', 'max:4096'],
        ]);
    }

    private function storeUploadedImage(Request $request): array
    {
        if (! $request->hasFile('image_file')) {
            return [null, null];
        }

        $uploadedFile = $request->file('image_file');

        return [
            $uploadedFile->store('material-subsections', 'public'),
            $uploadedFile->getClientOriginalName(),
        ];
    }

    private function resolveUpdatedImage(Request $request, MaterialSubsection $subsection, array $data): array
    {
        if ($request->hasFile('image_file')) {
            if ($subsection->image_path) {
                Storage::disk('public')->delete($subsection->image_path);
            }

            [$imagePath, $imageName] = $this->storeUploadedImage($request);

            return [$imagePath, $imageName, $data['image_url'] ?? null];
        }

        return [
            $subsection->image_path,
            $subsection->image_name,
            $data['image_url'] ?? $subsection->image_url,
        ];
    }

    private function ensureMaterialBelongsToSubject(Subject $subject, Material $material): void
    {
        abort_if($material->subject_id !== $subject->id, 404);
    }

    private function ensureSubsectionBelongsToMaterial(Material $material, MaterialSubsection $subsection): void
    {
        abort_if($subsection->material_id !== $material->id, 404);
    }

    private function sanitizeDescription(string $html): string
    {
        $allowed = '<p><div><br><strong><b><em><i><u><h1><h2><h3><ul><ol><li><blockquote>';
        $sanitized = strip_tags($html, $allowed);
        $sanitized = preg_replace('/<(\/?)div>/', '<$1p>', $sanitized) ?? $sanitized;

        return preg_replace('/<([a-z0-9]+)(?:\s[^>]*)?>/i', '<$1>', $sanitized) ?? $sanitized;
    }
}
