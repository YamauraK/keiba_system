<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RacePayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'bet_type',
        'combination',
        'odds',
        'payout',
    ];

    protected $casts = [
        'odds' => 'float',
        'payout' => 'integer',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}
