<?php

namespace App\Services;

use App\Models\Product;

class DatabaseStockCheck
{
    public function getStock($sku)
    {
        $product = Product::whereSku($sku)->first();

        return $product->qty;
    }
}
