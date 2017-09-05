<?php

use Faker\Generator as Faker;
use T1k3\LaravelCalendarEvent\Tests\Fixtures\Models\User;

$factory = app()->make(\Illuminate\Database\Eloquent\Factory::class);

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, function (Faker $faker) {
    return [
        'name'  => $faker->name(),
        'email' => $faker->email
    ];
});