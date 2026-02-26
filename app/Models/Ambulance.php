<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ambulance extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BUSY = 'busy';
    public const STATUS_MAINTENANCE = 'maintenance';

    public const TYPE_BASIC = 'basic';
    public const TYPE_ADVANCED = 'advanced';
    public const TYPE_ICU = 'icu';

    protected $fillable = [
        'hospital_id',
        'driver_id',
        'plate_number',
        'type',
        'status',
        'latitude',
        'longitude',
        'location_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'location_updated_at' => 'datetime',
        ];
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function emergencies(): HasMany
    {
        return $this->hasMany(Emergency::class);
    }
}
