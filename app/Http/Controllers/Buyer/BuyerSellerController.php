<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BuyerSellerController extends ApiController
{
    public function index(Buyer $buyer)
    {
      $sellers = $buyer->transactions()->with('product.seller')->get()
               ->pluck('product.seller')->union('id')->values();

      return $this->showAll($sellers);
    }
}
