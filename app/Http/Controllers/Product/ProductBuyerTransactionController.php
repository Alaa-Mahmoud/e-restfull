<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Product;
use App\Transaction;
use App\Transformers\TransactionTransformer;
use App\User;
use Illuminate\Http\Request;
use DB;
class ProductBuyerTransactionController extends ApiController
{
    public function __construct()
    {
        $this->middleware('transform.input:'.TransactionTransformer::class)->only('store');
    }

    public function store(Request $request , Product $product , User $buyer)
    {
        $rules = [
            'quantity' => 'required|integer|min:1'
        ];

        $this->validate($request , $rules);

        if($buyer->id == $product->seller_id)
        {
           return $this->errorResponse('the buyer must be different from the seller',409);
        }
        if(!$buyer->isVerified())
        {
            return $this->errorResponse('you must be verified before performing a transaction ',409);
        }

        if(!$product->seller->isVerified())
        {
            return $this->errorResponse('the seller must be verified first',409);
        }

        if(!$product->isAvailable())
        {
            return $this->errorResponse('the product no available',409);
        }

        if($product->quantity < $request->quantity)
        {
            return $this->errorResponse('there is no enough quantity in the stock for your transaction',409);
        }

        return DB::transaction(function () use ($request , $product , $buyer){
               $product->quantity -= $request->quantity;
               $product->save();

               $transaction = Transaction::create([
                       'quantity' => $request->quantity,
                       'buyer_id' => $buyer->id,
                       'product_id' => $product->id,
               ]);

               return $this->showOne($transaction);
        });


    }

}
