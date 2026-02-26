<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentLog extends Model
{
    protected $fillable = [
        'emergency_id',
        'ambulance_id',
        'distance_km',
        'eta_minutes',
        'assignment_reason',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'assigned_at' => 'datetime',
        ];
    }

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class);
    }

    public function ambulance(): BelongsTo
    {
        return $this->belongsTo(Ambulance::class);
    }
}
