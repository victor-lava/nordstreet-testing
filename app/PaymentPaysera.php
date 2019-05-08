<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentPaysera extends Model
{
    protected $table = 'payment_paysera';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function Project()
    {
        return $this->belongsTo('App\Project');
    }
}
