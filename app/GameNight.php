<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameNight extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the category that owns the GameNight
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(GameNightCategory::class);
    }

    /**
     * Get all of the users for the GameNight
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, UserGameNight::class);
    }

    /**
     * Get all of the trivia for the GameNight
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function triviaGames()
    {
        return $this->hasMany(Trivia::class);
    }

    /**
     * Get all of the twoPicGames for the GameNight
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function twoPicsGames()
    {
        return $this->hasMany(TwoPicsGame::class);
    }

    /**
     * Get all of the spotDifferences for the GameNight
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function spotDifferencesGames()
    {
        return $this->hasMany(SpotDifference::class);
    }

    public function userCanPlay()
    {
        $has_paid = UserGameNight::where('user_id', auth()->id())->where('game_night_id', $this->id)->exists();
        if ($has_paid) {
            return true;
        }

        return false;
    }
}
