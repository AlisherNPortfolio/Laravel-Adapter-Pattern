<?php

namespace App\Services\Contracts;

interface StockCheckerInterface
{
    public function getStock($sku);
}
