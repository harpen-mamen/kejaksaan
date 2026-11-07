<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchiveDocument extends Model
{
    use HasFactory, SoftDeletes;

    public const VERIFICATION_STATUSES = [
        'belum_diverifikasi' => 'Belum diverifikasi',
        'terverifikasi' => 'Terverifikasi',
        'perlu_perbaikan' => 'Perlu perbaikan',
    ];

    protected $fillable = [
        'archive_code', 'report_type_id', 'report_subtype_id', 'activity_id', 'title', 'document_number', 'report_date',
        'report_year', 'storage_path', 'original_file_name', 'mime_type',
        'file_size', 'sha256', 'uploaded_by', 'uploaded_at', 'administrative_notes',
        'sending_status', 'sent_to_kejati_at', 'verification_status', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'sent_to_kejati_at' => 'date',
            'report_year' => 'integer',
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function reportSubtype(): BelongsTo
    {
        return $this->belongsTo(ReportSubtype::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ArchiveDocumentVersion::class)->orderByDesc('version_number');
    }

    public function currentVersion(): HasMany
    {
        return $this->hasMany(ArchiveDocumentVersion::class)->orderByDesc('version_number')->limit(1);
    }
}
