<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_user_id', 'second_user_id', 'started_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function ScopeOpenGames()
    {
        return $this->whereNull('ended_at')
            ->whereNull('started_at')
            ->orderBy('created_at', 'desc')
            ->has('users', '=', 1);
    }

    public function ScopeGameInProgress()
    {
        return $this->whereNull('ended_at')
            ->whereNotNull('started_at')
            ->orderBy('created_at', 'desc')
            ->has('users', 2)
            ->orderBy('created_at', 'desc');
    }
}
