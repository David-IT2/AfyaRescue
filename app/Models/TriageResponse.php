<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TriageResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'emergency_id',
        'responses',
        'calculated_score',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'calculated_score' => 'integer',
        ];
    }

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class);
    }
}
