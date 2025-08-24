<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as IntModel;

class Model extends IntModel
{
    use HasFactory;
    protected $fillable = [
        'name',
        'heading',
        'object',
        'body',
        'ending',
        'sender',
        'img_1',
        'img_2',
    ];
}
