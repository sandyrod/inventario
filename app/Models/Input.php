<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Input extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id','amount','type','description','dateinput','datepaid','statuspaid'];

    public function items()
    {
        return $this->hasMany(InputItem::class);
    }
    
    // Relación con el usuario (opcional pero recomendado)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
