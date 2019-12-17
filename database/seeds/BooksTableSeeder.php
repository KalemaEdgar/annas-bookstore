<?php

use App\Author;
use App\Book;
use Illuminate\Database\Seeder;

class BooksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Here, we make a query for all the authors in our database, create two books for each author, 
        // and associate the books with the author, using the pluck method on the books collection to get the ids only.
        Author::all()->each(function (Author $author) {
            $books = factory(Book::class, 2)->create();
            $author->books()->sync($books->pluck('id'));
        });
    }
}
