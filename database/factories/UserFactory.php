<?php

use App\User\Model;
use ArtisanSdk\SRP\Client;
use ArtisanSdk\SRP\Config;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$srp = new Client(Config::fromArray(config('srp')));

$factory->define(Model::class, function (Faker $faker) use ($srp) {
    $email = $faker->unique()->safeEmail;
    $salt = $srp->salt($email, Str::random(16));

    return [
        'name'     => $faker->name,
        'email'    => $email,
        'salt'     => $salt,
        'verifier' => $srp->enroll($email, 'secret', $salt),
    ];
});
