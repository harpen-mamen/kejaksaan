<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderRule extends Model
{
    protected $fillable = ['name', 'working_days_before', 'is_active'];

    protected function casts(): array { return ['working_days_before' => 'integer', 'is_active' => 'boolean']; }
}
