<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['date', 'name', 'description', 'is_active', 'created_by'];

    protected function casts(): array { return ['date' => 'date', 'is_active' => 'boolean']; }
}
