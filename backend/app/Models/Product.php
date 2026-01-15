<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'url',
        'price',
        'currency',
        'source',
        'image_url'
    ];
}
