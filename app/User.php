<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function games()
    {
        return $this->belongsToMany(Game::class);
    }

    public function looking()
    {
        $return = '';
        if ($game = Game::open()->first()) {
            $return = "You will be playing against {$game->users()->first()->username}";
        } else {
            $game = new Game();
            $game->save();
            $return = "Game created, waiting for challenger.";
        }

        $game->users()->attach($this);

        return $return;
    }

    public function join()
    {
        if ($game = Game::open()->first()) {
            $username = $game->users()->first()->username;
            $game->users()->attach($this);
            $game->save();

            return "You will be playing against {$username}";
        } else {
            return 'No available games.';
        }
    }
}
