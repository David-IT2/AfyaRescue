<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyEventLog extends Model
{
    protected $fillable = ['emergency_id', 'event_type', 'payload', 'user_id'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
