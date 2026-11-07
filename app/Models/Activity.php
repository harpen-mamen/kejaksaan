<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    public const REPORT_STATUSES = [
        'belum_dikirim' => 'Belum dikirim',
        'dikirim' => 'Dikirim',
        'perlu_perbaikan' => 'Perlu perbaikan',
    ];

    protected $fillable = [
        'reference_code', 'title', 'activity_category_id', 'activity_date', 'activity_year',
        'unit', 'report_status', 'report_submitted_at', 'administrative_notes', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'report_submitted_at' => 'date',
            'activity_year' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class, 'activity_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function archives(): HasMany
    {
        return $this->hasMany(ArchiveDocument::class);
    }
}
