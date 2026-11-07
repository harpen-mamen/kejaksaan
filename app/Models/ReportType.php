<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportType extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function subtypes(): HasMany { return $this->hasMany(ReportSubtype::class); }

    public function documents(): HasMany { return $this->hasMany(ArchiveDocument::class); }
}
