<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Output extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'amount', 'type', 'description', 'paymentterm_id', 'paymentform_id', 'status_id'];

    public function items()
    {
        return $this->hasMany(OutputItem::class);
    }
    
    // Relación con el usuario (opcional pero recomendado)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentterm()
    {
        return $this->belongsTo(Paymentterm::class, 'paymentterm_id');
    }

    public function paymentform()
    {
        return $this->belongsTo(Paymentform::class, 'paymentform_id');
    }
}
