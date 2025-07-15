<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model
{
    use SoftDeletes;
    protected $fillable = ['familycode','familyname','UA','matrix','stockmin'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
