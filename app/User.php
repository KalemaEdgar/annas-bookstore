<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Set the default value for string columns in Laravel.
     * The default values for strings cannot be set in the migrations
     */
    protected $attributes = [
        'role' => 'user'
    ];
    
    public function comments()
    {
        // Relationship. User has many comments
        return $this->hasMany(Comment::class);
    }

    /**
     * We want to provide the UUID to insert as the id for the user since we told laravel not to use autoIncrementing ids
     * We need to hook into the model's creation event and set the UUID string to the id of the model.
     *
     * @return void
     */
    protected static function boot() 
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    // Add the methods from the AbstractAPIModel class since the Users Model doesnot extend it
    public function type()
    {
        return 'users';
    }
    public function allowedAttributes() 
    {
        return collect($this->attributes)->filter(function($item, $key) {
            return !collect($this->hidden)->contains($key) && $key !== 'id';
        })->merge([
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
