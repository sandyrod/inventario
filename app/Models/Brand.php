<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['code','description'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
