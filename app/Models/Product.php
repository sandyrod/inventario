<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['productcode','reference','description','family_id','brand_id','unit_id', 'cost', 'price', 'stock', 'stockmin'];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
