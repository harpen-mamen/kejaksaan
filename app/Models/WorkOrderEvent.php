<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'work_order_id', 'user_id', 'event_type', 'previous_end_date', 'new_end_date',
        'previous_duration_days', 'new_duration_days', 'approval_reference', 'reason', 'created_at',
    ];

    protected function casts(): array
    {
        return ['previous_end_date' => 'date', 'new_end_date' => 'date', 'created_at' => 'datetime'];
    }

    public function workOrder(): BelongsTo { return $this->belongsTo(WorkOrder::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
