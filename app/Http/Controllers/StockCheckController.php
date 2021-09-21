<?php

namespace App\Http\Controllers;

use App\Services\Contracts\StockCheckerInterface;
use Illuminate\Http\Request;

class StockCheckController extends Controller
{
    protected $service;

    public function __construct(StockCheckerInterface $stockChecker)
    {
        $this->service = $stockChecker;
    }

    public function index(Request $request)
    {
        $sku = $request->input('sku');
        $stock = $this->service->getStock($sku);

        return response()->json($stock);
    }
}
