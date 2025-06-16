<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Output extends Model
{
    protected $fillable = ['user_id','amount','type','description'];

    public function items()
    {
        return $this->hasMany(OutputItem::class);
    }
    
    // Relación con el usuario (opcional pero recomendado)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
