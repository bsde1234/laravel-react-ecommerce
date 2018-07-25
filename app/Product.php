<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    public function getPathAttribute()
    {
        return url('/products/'.$this->attributes['id']);
    }

    public function getImagePathAttribute()
    {
        return $this->attributes['image_path'] ?? '/image/products/default/not-found.jpg';
    }

    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }

    public function productReview()
    {
        return $this->hasMany('App\ProductReview', 'product_id');
    }

    public function orderHistoryProducts()
    {
        return $this->hasMany('App\OrderHistoryProducts', 'product_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function getFormattedCostAttribute()
    {
        return "£".number_format($this->attributes['cost'], 2);
    }

    public function getCostAttribute()
    {
        return $this->attributes['cost'];
    }

    public static function getProducts($request)
    {
        $query   = $request->input('query');
        $min     = $request->input('min_price');
        $max     = $request->input('max_price');
        $sort_by = $request->input('sort_by');

        $products = Self::select('products.id', 'products.name', 'products.user_id', 'products.company_id', 'products.short_description', 'products.long_description', 'products.product_details', 'products.image_path', 'products.cost', 'products.shippable', 'products.free_delivery', 'products.created_at', 'products.updated_at');
        $whereClause = array();

        if(isset($query))
        {
            $query = filter_var($query, FILTER_SANITIZE_STRING);
            array_push($whereClause, [
                'products.name', 'LIKE', "$query%"
            ]);
        }
        if(isset($min))
        {
            $min = filter_var($min, FILTER_SANITIZE_NUMBER_FLOAT);
            array_push($whereClause, [
                'products.cost', '>', $min
            ]);
        }
        if(isset($max))
        {
            $max = filter_var($max, FILTER_SANITIZE_NUMBER_FLOAT);
            array_push($whereClause, [
                'products.cost', '<', $max
            ]);
        }

        if(isset($whereClause)) $products = $products->where($whereClause);

        if(isset($sort_by))
        {
            if($sort_by == 'pop')
            {
                $products = $products->leftJoin('order_history_products', 'products.id', '=', 'order_history_products.product_id')
                    ->groupBy('order_history_products.product_id');
            }
            // else
            // {
            //     if($sort_by == 'top')
            //     {
            //         // update to get average reviews
            //         $products = $products->leftJoin('product_reviews', 'products.id', '=', 'product_reviews.product_id')
            //             ->addSelect(DB::raw('AVG(product_reviews.score) as average_score'))
            //             ->orderBy('average_score', 'desc')
            //             ->groupBy('average_score', 'desc');
            //     }
            // }
        }

        $products = $products->orderBy('products.id', 'DESC')->paginate(7);

        return $products;
    }

    public function didUserPurchaseProduct($user_id)
    {
        foreach($this->orderHistoryProducts()->get() as $product){
            $orderHistory = $product->orderHistory()->get();

            foreach($orderHistory as $order)
            {
                if($order->user_id == $user_id)
                {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    public function didUserReviewProduct($user_id)
    {
        foreach($this->productReview()->get() as $review){

            if($review->user_id == $user_id)
            {
                return TRUE;
            }
        }

        return FALSE;
    }
}
