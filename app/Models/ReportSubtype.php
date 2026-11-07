<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ReportSubtype extends Model
{
    protected $fillable = ['report_type_id', 'name', 'code', 'description', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function reportType(): BelongsTo { return $this->belongsTo(ReportType::class); }

    public function documents(): HasMany { return $this->hasMany(ArchiveDocument::class); }

    public static function activeOptionsForType(?int $reportTypeId): Collection
    {
        if (! $reportTypeId) {
            return collect();
        }

        return static::query()
            ->where('is_active', true)
            ->where('report_type_id', $reportTypeId)
            ->orderBy('name')
            ->pluck('name', 'id');
    }
}
