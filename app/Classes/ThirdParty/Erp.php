<?php

/**
 * bu klasni vendor papkada joylashgan
 * tashqi API uchun paket deb faraz qilamiz
 */

namespace App\Classes\ThirdParty;

class Erp
{
    protected $sku;

    public function __construct($sku)
    {
        $this->sku = $sku;
    }

    public function checkStock()
    {
        return [
            'sku' => $this->sku,
            'status' => true,
            'qty' => 101
        ];
    }
}
