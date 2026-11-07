<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityCategory extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function isInUse(): bool
    {
        return $this->activities()->withTrashed()->exists();
    }
}
