<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasRoles;

    //To instruct spatie roles, to use guard name api, instead of default Web
    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    // protected $fillable = [
    //     'name', 'email', 'password',
    // ];
    protected $guarded=[];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','reset_password_code','suspend'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dates = ['suspend'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the leaderboard associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function leaderboard()
    {
        return $this->hasOne(GamesLeaderboard::class);
    }

    /**
     * Get all of the cartItems for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get all of the eventUserTickets for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventUserTickets()
    {
        return $this->hasMany(EventUserTicket::class);
    }

    /**
     * Get all of the purchasedItems for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchasedItems()
    {
        return $this->hasMany(UserShopItems::class);
    }

    /**
     * Get all of the kids for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kids()
    {
        return $this->hasMany(Kid::class, 'parent_id', 'id');
    }

    /**
     * Get all of the gameNights for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gameNights()
    {
        return $this->hasMany(GameNight::class);
    }
}
