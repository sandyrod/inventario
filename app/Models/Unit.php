<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['unitname'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
