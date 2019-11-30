<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    // Ensures we can add the name attribute when using Author::create() because it’s now mass assignable.
    protected $fillable = ['name'];
}
