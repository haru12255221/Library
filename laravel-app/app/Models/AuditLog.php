<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'details',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 監査ログを記録する
     */
    public static function log(string $action, ?Model $model = null, ?string $details = null): self
    {
        $request = request();

        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? $model->getMorphClass() : null,
            'model_id' => $model?->getKey(),
            'details' => $details,
            'ip_address' => $request?->ip(),
            'created_at' => now(),
        ]);
    }
}
