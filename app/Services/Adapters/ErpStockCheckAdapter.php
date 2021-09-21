<?php

namespace App\Services\Adapters;

use App\Services\Contracts\StockCheckerInterface;
use App\Classes\ThirdParty\Erp;

class ErpStockCheckAdapter implements StockCheckerInterface
{
    public function getStock($sku)
    {
        $erp = new Erp($sku);

        $result = $erp->checkStock();

        return $result['qty'];
    }
}
