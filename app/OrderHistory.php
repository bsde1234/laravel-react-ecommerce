<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $table = 'order_history';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function product()
    {
        return $this->belongs('App\Product');
    }

    public function userPaymentConfig()
    {
        return $this->belongsTo('App\UserPaymentConfig', 'user_payment_config_id');
    }
}
