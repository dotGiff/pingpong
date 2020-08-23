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

    /** @test */
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
    public function getOpenGamesSuccessful()
    {
        $user = factory(User::class)->create();
        $game = factory(Game::class)->create([
            'started_at' => null,
            'ended_at' => null,
        ])->first();
        $game->users()->attach($user);
        factory(Game::class, 3)->create();

        $this->assertEquals($game->refresh(), Game::openGames()->first());
        $this->assertCount(4, Game::all());
        $this->assertCount(1, Game::openGames()->get());
    }

    /** @test */
    public function getOpenGamesFailStillInProgress()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $game = factory(Game::class)->create([
            'started_at' => Carbon::now()->subMinute(),
            'ended_at' => null,
        ])->first();
        $game->users()->attach($user2);
        factory(Game::class, 3)->create();

        $this->assertEmpty($user->games()->openGames()->first());
    }

    /** @test */
    public function getGamesInProgress()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $game = factory(Game::class)->create([
            'started_at' => Carbon::now()->subMinute(),
            'ended_at' => null,
        ])->first();
        $game->users()->attach($user);
        $game->users()->attach($user2);
        factory(Game::class, 3)->create();

        $this->assertNotEmpty(Game::gameInProgress()->first());
    }
}
