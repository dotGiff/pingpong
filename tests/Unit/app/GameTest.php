<?php

namespace Tests\Unit;

use App\Game;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @return void
     */
    public function getUsers()
    {
        $count = 2;
        $game = factory(Game::class)->create();
        factory(User::class, $count)->create()->each(function (User $user) use ($game) {
            $user->games()->attach($game);
        });

        $this->assertCount($count, $game->refresh()->users);
    }

    /** @test */
    public function getOpenGamesBasedOnTimestamps()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $game = factory(Game::class)->create([
            'started_at' => Carbon::now()->subMinute(),
            'ended_at' => null,
        ])->first();
        $game->users()->attach($user2);
        factory(Game::class, 3)->create();

        $this->assertEquals($game, $user->games()->openGames()->first());
    }

    /** @test */
    public function getOpenGamesBasedOnUsers()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $game = factory(Game::class)->create([
            'started_at' => Carbon::now()->subMinute(),
            'ended_at' => null,
        ])->first();
        $game->users()->attach($user);
        $game->users()->attach($user2);

        $this->assertEmpty($user->games()->openGames()->first());
    }
}
