<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutputItem extends Model
{
    protected $fillable = [
        'output_id',
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
