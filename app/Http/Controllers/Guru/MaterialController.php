<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stevebauman\Purify\Facades\Purify;

class MaterialController extends Controller
{
    // Jenis isi file yang diizinkan. docx/pptx kadang terbaca sebagai zip, file lama sebagai OLE
    private const DOC_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip',
        'application/vnd.ms-office',
        'application/x-ole-storage',
        'application/CDFV2',
    ];

    public function create(Room $room)
    {
        Gate::authorize('manage', $room);

        return view('guru.materials.form', ['room' => $room, 'material' => new Material]);
    }

    public function store(Request $request, Room $room)
    {
        Gate::authorize('manage', $room);

        $data = $this->validated($request, null);
        $data += $this->imageAttributes($request, null);
        $data += $this->fileAttributes($request);

        $room->materials()->create($data);

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Room $room, Material $material)
    {
        $this->authorizeMaterial($room, $material);

        return view('guru.materials.form', compact('room', 'material'));
    }

    public function update(Request $request, Room $room, Material $material)
    {
        $this->authorizeMaterial($room, $material);

        $data = $this->validated($request, $material);
        $data += $this->imageAttributes($request, $material);

        // File baru menggantikan yang lama; centang "hapus file" mengosongkan kolomnya
        $fileChange = $this->fileAttributes($request);
        if ($fileChange === [] && $request->boolean('remove_file')) {
            $fileChange = ['file_path' => null, 'file_name' => null, 'file_type' => null, 'file_size' => null];
        }

        $oldPath = $material->file_path;
        $material->update($data + $fileChange);

        // File lama dihapus dari disk setelah data berhasil disimpan
        if ($oldPath && $fileChange !== []) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Room $room, Material $material)
    {
        $this->authorizeMaterial($room, $material);

        if ($material->image_path) {
            Storage::disk('public')->delete($material->image_path);
        }
        if ($material->file_path) {
            Storage::disk('local')->delete($material->file_path);
        }
        $material->delete();

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Materi berhasil dihapus.');
    }

    private function authorizeMaterial(Room $room, Material $material): void
    {
        Gate::authorize('manage', $room);
        abort_unless($material->room_id === $room->id, 404); // materi harus milik room di URL
    }

    private function validated(Request $request, ?Material $material): array
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'content'     => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'file'        => ['nullable', 'file', 'max:20480', 'extensions:pdf,doc,docx,ppt,pptx', 'mimetypes:' . implode(',', self::DOC_MIMES)],
            'remove_file' => ['nullable', 'boolean'],
        ], [
            'file.extensions' => 'File harus berformat PDF, Word (.doc/.docx), atau PowerPoint (.ppt/.pptx).',
            'file.mimetypes'  => 'Isi file tidak dikenali sebagai PDF, Word, atau PowerPoint yang valid.',
            'file.max'        => 'Ukuran file maksimal 20 MB.',
            'file.uploaded'   => 'File gagal diunggah. Ukuran mungkin melebihi batas server.',
        ]);

        $content = filled($data['content'] ?? null) ? trim(Purify::clean($data['content'])) : null;

        // Editor kosong bisa menghasilkan tag tanpa isi, misalnya <p><br></p>
        if ($content !== null && $this->isBlankHtml($content)) {
            $content = null;
        }

        $keepsFile = $material?->hasFile() && ! $request->boolean('remove_file');

        if ($content === null && ! $request->hasFile('file') && ! $keepsFile) {
            throw ValidationException::withMessages([
                'content' => 'Isi materi dengan mengetik, mengunggah file, atau keduanya.',
            ]);
        }

        return ['title' => $data['title'], 'content' => $content];
    }

    private function isBlankHtml(string $html): bool
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

        return preg_replace('/[\s\x{00A0}]+/u', '', $text) === '';
    }

    private function imageAttributes(Request $request, ?Material $material): array
    {
        if (! $request->hasFile('image')) {
            return [];
        }

        if ($material?->image_path) {
            Storage::disk('public')->delete($material->image_path);
        }

        return ['image_path' => $request->file('image')->store('materials', 'public')];
    }

    private function fileAttributes(Request $request): array
    {
        if (! $request->hasFile('file')) {
            return [];
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension()); // sudah dibatasi aturan 'extensions'

        return [
            // Nama di disk acak; nama asli hanya disimpan untuk ditampilkan dan saat diunduh
            'file_path' => $file->storeAs('materials/files', Str::random(40) . '.' . $extension, 'local'),
            'file_name' => Str::limit($file->getClientOriginalName(), 200, ''),
            'file_type' => $extension,
            'file_size' => $file->getSize(),
        ];
    }
}