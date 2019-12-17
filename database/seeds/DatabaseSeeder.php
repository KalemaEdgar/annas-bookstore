<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(AuthorsTableSeeder::class); // To be able to run the database seeder utilising the factory AuthorsFactory
        $this->call(BooksTableSeeder::class);
    }
}
