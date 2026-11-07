<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderType extends Model
{
    protected $fillable = ['name', 'code', 'default_duration_days', 'duration_basis', 'start_date_is_day_one', 'is_active', 'calculation_note'];

    protected function casts(): array
    {
        return ['default_duration_days' => 'integer', 'start_date_is_day_one' => 'boolean', 'is_active' => 'boolean'];
    }

    public function workOrders(): HasMany { return $this->hasMany(WorkOrder::class); }
}
