<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\orderHistoryProducts;

class OrderHistory extends Model
{
    protected $table = 'order_history';
    protected $guarded = [];

    public static function generateRefNum()
    {
        $param = str_shuffle("00000111112222233333444445555566666777778888899999");
        return substr($param, 0, 8);
    }

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

    public function orderHistoryProducts()
    {
        return $this->hasMany('App\OrderHistoryProducts', 'order_history_id');
    }

    public function usersAddresses()
    {
        return $this->belongsTo('App\UsersAddress', 'users_addresses_id');
    }

    public function getAmountTotalAttribute()
    {
        return OrderHistoryProducts::where([
            'order_history_id' => $this->attributes['id'],
        ])->sum('amount');
    }

    public function getFormattedCostAttribute()
    {
        return "£".number_format($this->attributes['cost'], 2);
    }
}
