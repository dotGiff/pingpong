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
}
