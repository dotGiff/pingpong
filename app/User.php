<?php

namespace App;

use App\Jobs\EndGame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /**
     * @var string|null
     */
    protected $message;

    /**
     * @return BelongsToMany
     */
    public function games()
    {
        return $this->belongsToMany(Game::class);
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return $this
     */
    public function looking(): User
    {
        if ($game = Game::openGames()->first()) {
            $username = $game->users()->first()->username;
            $game->users()->attach($this);
            dispatch(new EndGame($game))->delay(30);

            $this->setMessage("You will be playing against {$username}");
        } elseif(Game::gameInProgress()->count()) {
            $this->setMessage("There is a game in progress, try again later.");
        } else {
            $game = new Game();
            $game->save();
            $game->users()->attach($this);

            $this->setMessage("Game created, waiting for challenger.");
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function join(): User
    {
        if ($game = Game::openGames()->first()) {
            $username = $game->users()->first()->username;
            $game->users()->attach($this);
            $game->save();
            dispatch(new EndGame($game))->delay(30);

            $this->setMessage("You will be playing against {$username}");
        } else {
            $this->setMessage('No available games.');
        }

        return $this;
    }
}
