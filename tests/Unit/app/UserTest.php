<?php

namespace Tests\Unit;

use App\Game;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @return void
     */
    public function getGames()
    {
        $count = rand(2, 6);
        $user = factory(User::class)->create();
        factory(Game::class, $count)->create()->each(function (Game $game) use ($user) {
            $game->users()->attach($user);
        });

        $this->assertCount($count, $user->refresh()->games);
    }

    /** @test */
    public function lookingCreateNewGame()
    {
        $this->assertCount(0, Game::all());

        $user = factory(User::class)->create();
        $this->assertEquals("Game created, waiting for challenger.", $user->looking());

        $this->assertCount(1, Game::all());
    }

    /** @test */
    public function lookingDefaultToJoin()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $game = factory(Game::class)->create([
            'started_at' => null,
            'ended_at' => null,
        ]);
        $user2->games()->attach($game);

        $this->assertCount(1, Game::open()->get());
        $this->assertCount(0, $user1->games);
        $this->assertEquals("You will be playing against {$user2->username}", $user1->looking());
        $this->assertCount(1, $user1->refresh()->games);
    }

    /** @test */
    public function joinAnOpenGame()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $game = factory(Game::class)->create([
            'started_at' => null,
            'ended_at' => null,
        ]);
        $user2->games()->attach($game);

        $this->assertCount(1, Game::open()->get());
        $this->assertCount(0, $user1->games);
        $this->assertEquals("You will be playing against {$user2->username}", $user1->join());
        $this->assertCount(1, $user1->refresh()->games);
    }

    /** @test */
    public function joinNoGamesOpen()
    {
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $user3 = factory(User::class)->create();
        $game = factory(Game::class)->create([
            'started_at' => null,
            'ended_at' => null,
        ]);
        $user2->games()->attach($game);
        $user3->games()->attach($game);

        $this->assertCount(0, Game::open()->get());
        $this->assertCount(0, $user1->games);
        $this->assertEquals("No available games.", $user1->join());
        $this->assertCount(0, $user1->refresh()->games);
    }
}
