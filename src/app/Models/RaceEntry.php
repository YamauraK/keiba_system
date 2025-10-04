<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_id',
        'frame_number',
        'horse_number',
        'horse_name',
        'sex',
        'running_style',
        'popularity',
        'finish_position',
        'win_odds',
        'win_payout',
        'place_payout',
    ];

    protected $casts = [
        'win_odds' => 'float',
        'frame_number' => 'integer',
        'horse_number' => 'integer',
        'popularity' => 'integer',
        'finish_position' => 'integer',
        'win_payout' => 'integer',
        'place_payout' => 'integer',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}
