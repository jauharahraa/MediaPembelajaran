<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'room_id', 'title', 'content', 'image_path',
        'file_path', 'file_name', 'file_type', 'file_size',
    ];

    public function room() { return $this->belongsTo(Room::class); }

    public function hasFile(): bool { return ! empty($this->file_path); }

    public function isPdf(): bool { return $this->file_type === 'pdf'; }

    public function fileIcon(): string
    {
        return match ($this->file_type) {
            'pdf'         => 'bi-file-earmark-pdf text-danger',
            'doc', 'docx' => 'bi-file-earmark-word text-primary',
            'ppt', 'pptx' => 'bi-file-earmark-ppt text-warning',
            default       => 'bi-file-earmark',
        };
    }

    public function fileTypeLabel(): string
    {
        return match ($this->file_type) {
            'pdf'         => 'PDF',
            'doc', 'docx' => 'Word',
            'ppt', 'pptx' => 'PowerPoint',
            default       => 'File',
        };
    }

    public function fileSizeLabel(): string
    {
        $kb = ($this->file_size ?? 0) / 1024;

        return $kb >= 1024
            ? number_format($kb / 1024, 1, ',', '.') . ' MB'
            : number_format($kb, 0, ',', '.') . ' KB';
    }
}