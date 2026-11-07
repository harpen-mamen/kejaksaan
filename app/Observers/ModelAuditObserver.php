<?php

namespace App\Observers;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ModelAuditObserver
{
    private function write(string $verb, Model $model): void
    {
        $entity = Str::snake(class_basename($model));
        app(AuditLogService::class)->record("{$entity}.{$verb}", Auth::user(), $model);
    }

    public function created(Model $model): void { $this->write('created', $model); }
    public function updated(Model $model): void { $this->write('updated', $model); }
    public function deleted(Model $model): void { $this->write('deleted', $model); }
    public function restored(Model $model): void { $this->write('restored', $model); }
}
