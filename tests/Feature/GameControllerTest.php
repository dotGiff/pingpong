<?php

namespace Tests\Feature;

use App\Game;
use App\Jobs\EndGame;
use App\Jobs\SendSlackMessage;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    /** @test */
    public function lookingCreateGameCreateUser()
    {
        $this->assertCount(0, User::all());

        $response = $this->post('/api/looking', [
            'username' => 'art.garfunkel',
        ]);
        $response->assertStatus(200);

        $this->assertCount(1, User::all());
        $this->assertCount(1, Game::all());

        Queue::assertPushed(function (SendSlackMessage $job) {
            return $job->channel = 'pongbot' && $job->message = "Game created, waiting for challenger.";
        });
        Queue::assertNotPushed(EndGame::class);
    }

    /** @test */
    public function lookingCreateGameExistingUser()
    {
        $user1 = factory(User::class)->create([
            'username' => 'art.garfunkel'
        ]);
        $this->assertCount(1, User::all());

        $response = $this->post('/api/looking', [
            'username' => 'art.garfunkel',
        ]);
        $response->assertStatus(200);

        $this->assertCount(1, User::all());
        $this->assertCount(1, Game::all());

        Queue::assertPushed(function (SendSlackMessage $job) {
            return $job->channel = 'pongbot' && $job->message = "Game created, waiting for challenger.";
        });
        Queue::assertNotPushed(EndGame::class);
    }

    /** @test */
    public function lookingJoinExistingGame()
    {
        // Setup existing user and open game
        $user1 = factory(User::class)->create([
            'username' => 'paul.simon'
        ]);
        $game = factory(Game::class)->create([
            'started_at' => null,
            'ended_at' => null,
        ]);
        $user1->games()->attach($game);

        // Assert open game
        $this->assertCount(1, User::all());
        $this->assertCount(1, Game::all());
        $this->assertNotEmpty(Game::openGames()->first());

        // Make request and assert 200 response
        $response = $this->post('/api/looking', [
            'username' => 'art.garfunkel',
        ]);
        $response->assertStatus(200);

        // Assert new user joined game and there there are no more open games
        $this->assertCount(2, User::all());
        $this->assertCount(1, Game::all());
        $this->assertCount(2, $game->refresh()->users);
        $this->assertEmpty(Game::openGames()->first());

        Queue::assertPushed(function (SendSlackMessage $job) use ($user1) {
            return $job->channel = 'pongbot' && $job->message = "You will be playing against {$user1->username}";
        });
        Queue::assertPushed(function (EndGame $job) use ($game) {
            return $job->game = $game;
        });
    }

    /** @test */
    public function lookingGameAlreadyInProgress()
    {
        // Setup existing user and open game
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $game = factory(Game::class)->create([
            'started_at' => Carbon::now()->subMinute(),
            'ended_at' => null,
        ]);
        $user1->games()->attach($game);
        $user2->games()->attach($game);

        // Assert open game
        $this->assertCount(2, User::all());
        $this->assertCount(1, Game::all());
        $this->assertEmpty(Game::openGames()->first());

        // Make request and assert 200 response
        $response = $this->post('/api/looking', [
            'username' => 'art.garfunkel',
        ]);
        $response->assertStatus(200);

        // Assert new user joined game and there there are no more open games
        $this->assertCount(3, User::all());
        $this->assertCount(1, Game::all());
        $this->assertCount(2, $game->refresh()->users);
        $this->assertEmpty(Game::openGames()->first());

        Queue::assertPushed(function (SendSlackMessage $job) use ($user1) {
            return $job->channel = 'pongbot' && $job->message = "There is a game in progress, try again later.";
        });
        Queue::assertNotPushed(EndGame::class);
    }

    /** @test */
    public function joinExistingGameCreateUser()
    {
        // Setup existing user and open game
        $user1 = factory(User::class)->create([
            'username' => 'paul.simon'
        ]);
        $game = factory(Game::class)->create([
            'started_at' => null,
            'ended_at' => null,
        ]);
        $user1->games()->attach($user1);

        // Assert open game
        $this->assertCount(1, User::all());
        $this->assertCount(1, Game::all());
        $this->assertNotEmpty(Game::openGames()->first());

        // Make request and assert 200 response
        $response = $this->post('/api/join', [
            'username' => 'art.garfunkel',
        ]);
        $response->assertStatus(200);

        // Assert new user joined game and there there are no more open games
        $this->assertCount(2, User::all());
        $this->assertCount(1, Game::all());
        $this->assertCount(2, $game->refresh()->users);
        $this->assertEmpty(Game::openGames()->first());

        Queue::assertPushed(function (SendSlackMessage $job) use ($user1) {
            return $job->channel = 'pongbot' && $job->message = "You will be playing against {$user1->username}";
        });
        Queue::assertPushed(function (EndGame $job) use ($game) {
            return $job->game = $game;
        });
    }

    /** @test */
    public function joinNoOpenGames()
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

        // Assert no open game
        $this->assertCount(1, User::all());
        $this->assertCount(1, Game::all());
        $this->assertEmpty(Game::openGames()->first());

        // Make request and assert 200 response
        $response = $this->post('/api/join', [
            'username' => 'art.garfunkel',
        ]);
        $response->assertStatus(200);

        // Assert new user joined game and there there are no more open games
        $this->assertCount(2, User::all());
        $this->assertCount(1, Game::all());
        $this->assertEmpty(Game::openGames()->first());

        Queue::assertPushed(function (SendSlackMessage $job) {
            return $job->channel = 'pongbot' && $job->message = "No available games.";
        });
        Queue::assertNotPushed(EndGame::class);
    }
}
