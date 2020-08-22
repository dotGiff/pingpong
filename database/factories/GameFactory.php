<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Game;
use Faker\Generator as Faker;

$factory->define(Game::class, function (Faker $faker) {
    $startedAt = $faker->dateTimeBetween('-30 days', '-1 day');
    return [
        'started_at' => $startedAt,
        'ended_at' => $faker->dateTimeInInterval($startedAt, '+30 minutes'),
    ];
});
