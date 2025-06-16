<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $fillable = ['familycode','familyname','UA','matrix','stockmin'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
