<?php

namespace Tests\Feature;

use App\Game;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @return void
     */
    public function lookingCreateGameCreateUser()
    {
        $this->assertCount(0, User::all());

        $response = $this->post('/api/looking', [
            'username' => 'art.garfunkel',
        ]);
        $response->assertStatus(200);

        $this->assertCount(1, User::all());
    }

    /**
     * @test
     *
     * @return void
     */
    public function joinExistingGameCreateUser()
    {
        // Setup existing user and open game
        $user1 = factory(User::class)->create([
            'username' => 'paul.simon'
        ]);
        $game = factory(Game::class)->create([
            'started_at' => Carbon::now()->subMinute(),
            'ended_at' => null,
        ]);
        $user1->games()->attach($user1);

        // Assert open game
        $this->assertCount(1, User::all());
        $this->assertCount(1, Game::all());
        $this->assertNotEmpty(Game::open()->first());

        // Make request and assert 200 response
        $response = $this->post('/api/join', [
            'username' => 'art.garfunkel',
        ]);
        $response->assertStatus(200);

        // Assert new user joined game and there there are no more open games
        $this->assertCount(2, User::all());
        $this->assertCount(1, Game::all());
        $this->assertCount(2, $game->refresh()->users);
        $this->assertEmpty(Game::open()->first());
    }
}
