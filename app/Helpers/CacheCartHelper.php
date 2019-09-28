<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Product;

class CacheCart 
{
    /**
     * Adds a product to the user's session cart.
     * 
     * @param  \App\Product  $product
     */
    public static function addProductToCacheCart(Product $product)
    {
        /** Cookie will expire in 120 minutes */
        $expiresAt = now()->addMinutes(120);

        /** Get existing session cookie if set  */
        $cacheCart = Cache::get('cc');

        /** Check is existing cookie is present */
        if($cacheCart !== NULL && is_array($cacheCart))
        {
            $productAlreadyAdded = FALSE;
            /** Check if this product already exists in cart */
            foreach($cacheCart as $cc)
            {
                if(is_array($cc) && in_array($product->id, $cc))
                {
                    $productAlreadyAdded = TRUE;
                }
            }
            /** Add new product to session cart if not already present */
            if($productAlreadyAdded == FALSE)
            {
                $newItem = array(
                    'product' => $product->id,
                    'amount'  => 1,
                );
                array_push($cacheCart, $newItem);
                Cache::put('cc', $cacheCart, $expiresAt);
            }
        }
        else
        {
            /** Set a new session cart cookie with product as it's first item */
            $cacheCart = array(
                array(
                    'product' => $product->id,
                    'amount'  => 1,
                )
            );
            Cache::put('cc', $cacheCart, $expiresAt);
        }
    }

    /**
     * Returns the user's session cart.
     * 
     * @return  array
     */
    public static function getCacheCart()
    {
        $cacheCart = Cache::get('cc');
        $array = array();

        if(isset($cacheCart))
        {
            foreach($cacheCart as $cc)
            {
                $item = Product::where('id', $cc['product'])->first();
                $amount = $cc['amount'];

                array_push($array, array(
                    'product' => $item,
                    'amount'  => $amount
                ));
            }
        }

        return $array;
    }

    /**
     * Updates the respective number of products in the user's session cart.
     * 
     * @param  \Illuminate\Http\Request  $request
     */
    public static function updateCacheCartAmount(Request $request)
    {
        /** Get existing session cart */
        $cacheCart = self::getCacheCart();
        $array     = array();

        foreach($cacheCart as $cc)
        {
            /** Check if an amount value for this product was given in the request */
            $product_id = $cc['product']->id;
            $amount = $request->get('amount-' . $product_id);

            if($amount !== NULL && $amount != 0)
            {
                /** Push to $array the product with new amount value */
                array_push($array, array(
                    'product' => $product_id,
                    'amount'  => (int) $amount,
                ));
            }
        }

        /** Set session cart equal to our updated products array */
        $expiresAt = now()->addMinutes(120);
        Cache::put('cc', $array, $expiresAt);
    }

    /**
     * Remove the session cart cookie.
     */
    public static function clearCacheCart()
    {
        Cache::forget('cc');
    }
}