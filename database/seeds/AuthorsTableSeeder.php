<?php

use Illuminate\Database\Seeder;

class AuthorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Call our Factory to be able to insert the data
        factory(\App\Author::class, 5)->create();
    }
}
