<?php

namespace Tests\Unit;

use App\Game;
use App\User;
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
    public function getGames()
    {
        $count = 2;
        $game = factory(Game::class)->create();
        factory(User::class, $count)->create()->each(function (User $user) use ($game) {
            $user->games()->attach($game);
        });

        $this->assertCount($count, $game->refresh()->users);
    }
}
