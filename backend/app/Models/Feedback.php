<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'rating',
        'admin_reply',
        'replied_at',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'replied_at' => 'datetime',
        'rating' => 'integer',
    ];
}
