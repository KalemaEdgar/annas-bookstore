<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends AbstractAPIModel
{
    // Ensures we can add the name attribute when using Author::create() because it’s now mass assignable.
    protected $fillable = ['name'];

    public function books() 
    {
        return $this->belongsToMany(Book::class);
    }

    public function type()
    {
        return 'authors';
    }

}
