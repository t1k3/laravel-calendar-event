<?php

use Faker\Generator as Faker;
use T1k3\LaravelCalendarEvent\Tests\Fixtures\Models\Place;

$factory = app()->make(\Illuminate\Database\Eloquent\Factory::class);

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Place::class, function (Faker $faker) {
    return [
        'name'    => $faker->name(),
        'address' => $faker->address,
    ];
});