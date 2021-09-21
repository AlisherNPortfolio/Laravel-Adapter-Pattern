<?php

namespace App\Services\Adapters;

use App\Models\Product;
use App\Services\Contracts\StockCheckerInterface;

class DatabaseStockCheckAdapter implements StockCheckerInterface
{
    public function getStock($sku)
    {
        $product = Product::whereSku($sku)->first();

        return $product->qty;
    }
}
