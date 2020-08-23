<?php

namespace Tests\Unit;

use App\Game;
use App\Jobs\EndGame;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndGameTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function endGame()
    {
        $game = factory(Game::class)->create([
            'ended_at' => null
        ]);

        $this->assertCount(1, Game::all());
        $this->assertDatabaseHas('games', ['ended_at' => null]);
        dispatch_now(new EndGame($game));
        $this->assertDatabaseMissing('games', ['ended_at' => null]);
    }
}
