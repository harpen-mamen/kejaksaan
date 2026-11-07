<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchiveDocumentVersion extends Model
{
    protected $fillable = [
        'archive_document_id', 'version_number', 'storage_path', 'original_file_name',
        'mime_type', 'file_size', 'sha256', 'uploaded_by', 'uploaded_at',
    ];

    protected function casts(): array
    {
        return ['file_size' => 'integer', 'uploaded_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ArchiveDocument::class, 'archive_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
