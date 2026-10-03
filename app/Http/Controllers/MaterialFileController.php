<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MaterialFileController extends Controller
{
    private const TYPES = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    public function show(Request $request, Material $material)
    {
        $user = $request->user();
        $room = $material->room;

        // Guru pemilik room, atau siswa anggota room yang aktif
        $allowed = $user->isGuru() ? Gate::allows('manage', $room) : Gate::allows('access', $room);
        abort_unless($allowed, 403);

        $disk = Storage::disk('local');
        abort_unless($material->hasFile() && $disk->exists($material->file_path), 404);

        $headers = [
            'Content-Type'           => self::TYPES[$material->file_type] ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ];

        // Hanya PDF yang boleh tampil langsung di browser; lainnya selalu diunduh
        if ($material->isPdf() && ! $request->boolean('download')) {
            return $disk->response($material->file_path, $material->file_name, $headers, 'inline');
        }

        return $disk->download($material->file_path, $material->file_name, $headers);
    }
}