<?php

namespace App;

use App\Jobs\EndGame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
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
        if ($game = Game::openGames()->first()) {
            $username = $game->users()->first()->username;
            $game->users()->attach($this);
            dispatch(new EndGame($game))->delay(30);

            return "You will be playing against {$username}";
        } else {
            $game = new Game();
            $game->save();
            $game->users()->attach($this);

            return "Game created, waiting for challenger.";
        }
    }

    public function join()
    {
        if ($game = Game::openGames()->first()) {
            $username = $game->users()->first()->username;
            $game->users()->attach($this);
            $game->save();
            dispatch(new EndGame($game))->delay(30);

            return "You will be playing against {$username}";
        } else {
            return 'No available games.';
        }
    }
}
