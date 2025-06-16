<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputItem extends Model
{

    protected $fillable = [
        'input_id',
        'product_id',
        'quantity',
        'unit_price',
        'notes'
    ];

    public function input()
    {
        return $this->belongsTo(Input::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
