<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Emergency extends Model
{
    use HasFactory;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_ENROUTE = 'enroute';
    public const STATUS_ARRIVED = 'arrived';
    public const STATUS_CLOSED = 'closed';

    public const STATUS_FLOW = [
        self::STATUS_REQUESTED,
        self::STATUS_ASSIGNED,
        self::STATUS_ENROUTE,
        self::STATUS_ARRIVED,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'patient_id',
        'hospital_id',
        'ambulance_id',
        'status',
        'latitude',
        'longitude',
        'address_text',
        'severity_score',
        'severity_label',
        'requested_at',
        'assigned_at',
        'enroute_at',
        'arrived_at',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'severity_score' => 'integer',
            'requested_at' => 'datetime',
            'assigned_at' => 'datetime',
            'enroute_at' => 'datetime',
            'arrived_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function ambulance(): BelongsTo
    {
        return $this->belongsTo(Ambulance::class);
    }

    public function triageResponse(): HasOne
    {
        return $this->hasOne(TriageResponse::class);
    }
}
