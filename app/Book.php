<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// class Book extends Model
class Book extends AbstractAPIModel
{
    protected $fillable = [
        'title',
        'description',
        'publication_year',
    ];

    public function authors() 
    {
        return $this->belongsToMany(Author::class);
    }

    public function comments()
    {
        // This book has many comments
        return $this->hasMany(Comment::class);
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'books';
    }

}
