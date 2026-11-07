<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderReminder extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'work_order_id', 'reminder_rule_id', 'user_id', 'end_date_snapshot', 'triggered_for',
        'read_at', 'invalidated_at', 'created_at',
    ];

    protected function casts(): array
    {
        return ['end_date_snapshot' => 'date', 'triggered_for' => 'date', 'read_at' => 'datetime', 'invalidated_at' => 'datetime', 'created_at' => 'datetime'];
    }

    public function workOrder(): BelongsTo { return $this->belongsTo(WorkOrder::class); }
    public function rule(): BelongsTo { return $this->belongsTo(ReminderRule::class, 'reminder_rule_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
