<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'latitude',
        'longitude',
        'phone',
        'is_active',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
            'level' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'hospital_id');
    }

    public function ambulances(): HasMany
    {
        return $this->hasMany(Ambulance::class);
    }

    public function emergencies(): HasMany
    {
        return $this->hasMany(Emergency::class);
    }
}
