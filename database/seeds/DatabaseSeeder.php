<?php

use App\User\Model as User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        factory(User::class, 3)->create();
    }
}
