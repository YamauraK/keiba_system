<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_date',
        'race_name',
        'racecourse',
        'course_type',
        'weather',
        'track_condition',
        'distance',
        'direction',
        'number_of_turns',
        'number_of_runners',
    ];

    protected $casts = [
        'race_date' => 'date',
        'distance' => 'integer',
        'number_of_turns' => 'integer',
        'number_of_runners' => 'integer',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(RacePayout::class);
    }
}
