<?php

namespace App\Models;

use App\Services\WorkOrderCalendarService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'work_order_type_id', 'start_date', 'duration_days', 'duration_basis',
        'start_date_is_day_one', 'end_date', 'status', 'responsible_user_id', 'closed_at',
        'administrative_notes', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'closed_at' => 'date',
            'duration_days' => 'integer',
            'start_date_is_day_one' => 'boolean',
        ];
    }

    public function type(): BelongsTo { return $this->belongsTo(WorkOrderType::class, 'work_order_type_id'); }
    public function responsible(): BelongsTo { return $this->belongsTo(User::class, 'responsible_user_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function events(): HasMany { return $this->hasMany(WorkOrderEvent::class); }
    public function reminders(): HasMany { return $this->hasMany(WorkOrderReminder::class); }

    public function calculateEndDate(): void
    {
        $this->end_date = app(WorkOrderCalendarService::class)->calculateEndDate(
            $this->start_date,
            $this->duration_days,
            $this->duration_basis,
            $this->start_date_is_day_one,
        )->toDateString();
    }
}
