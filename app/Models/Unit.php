<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;
    protected $fillable = ['unitname'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
