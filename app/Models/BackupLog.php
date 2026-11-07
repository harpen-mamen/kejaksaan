<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'archive_name', 'sha256', 'size_bytes', 'status', 'failure_code', 'created_at'];

    protected function casts(): array
    {
        return ['size_bytes' => 'integer', 'created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
