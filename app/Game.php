<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'first_user_id', 'second_user_id', 'started_at',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'started_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return mixed
     */
    public function ScopeOpenGames()
    {
        return $this->whereNull('ended_at')
            ->whereNull('started_at')
            ->orderBy('created_at', 'desc')
            ->has('users', '=', 1);
    }

    /**
     * @return mixed
     */
    public function ScopeGameInProgress()
    {
        return $this->whereNull('ended_at')
            ->whereNotNull('started_at')
            ->orderBy('created_at', 'desc')
            ->has('users', 2)
            ->orderBy('created_at', 'desc');
    }
}
