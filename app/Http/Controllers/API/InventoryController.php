<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gift;
use App\Models\GiftSubmit;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Chanel;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PriceInventory;
use App\Models\Distributor;
use App\Models\ManageStock;
use App\Models\Product;

class InventoryController extends AppBaseController
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chanel_id' => 'required|integer|exists:chanels,id',
            'product_id' => 'required|integer|exists:products,id',
            'price' => 'required|numeric|min:0',
            'distributor_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }
        $distributor = User::find($request->distributor_id);
        if ($distributor->role_id != 3) {
            return response()->json([
                'status' => 403,
                'message' => 'User is not authorized as a distributor.',
            ], 403);
        }

        $priceInventory = PriceInventory::updateOrCreate(
            [
                'chanel_id' => $request->chanel_id,
                'user_id' => $request->distributor_id,
                'product_id' => $request->product_id,
            ],
            [
                'price' => $request->price,
            ]
        );

        return response()->json([
            'status' => 200,
            'message' => 'Price inventory saved successfully',
            'data' => $priceInventory,
        ], 200);
    }


    public function show(Request $request)
    {
        $chanel = Chanel::all();

        return response()->json([
            'status' => 200,
            'message' => 'channels retrieved successfully',
            'chanel' => $chanel,
        ]);
    }


    public function update(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'distributor_id' => 'required|integer',
        'product_id' => 'required|integer|exists:products,id',
        'quantity' => 'required|integer',
       ]);

       if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'errors' => $validator->errors(),
        ]);
        }
         $distributorProduct = Distributor::where('distributor_id', $request->distributor_id)
         ->where('product_id', $request->product_id)->first();

    if ($distributorProduct) {
        $distributorProduct->quantity = $request->quantity;
        $distributorProduct->save();

        return response()->json([
            'status' => 200,
            'message' => 'Distributor quantity updated successfully',
            'data' => $distributorProduct
        ]);
    } else {
        $distributorProduct = Distributor::create([
            'distributor_id' => $request->distributor_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Distributor created successfully',
            'data' => $distributorProduct
        ]);
    }
   }



     public function quantityUpdate(Request $request)
     {
            $validator = Validator::make($request->all(), [
              'warehouse_id' => 'required|integer|exists:warehouses,id',
              'product_id' => 'required|integer|exists:products,id',
              'quantity' => 'required|integer|min:0',
           ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->errors(),
                ]);
            }

                  $warehouseEntry = ManageStock::updateOrCreate(
                 [
                     'warehouse_id' => $request->warehouse_id,
                     'product_id' => $request->product_id,
                 ],
                 [
                     'quantity' => $request->quantity,
                 ]
        );

         return response()->json([
             'status' => 200,
             'message' => 'Warehouse quantity updated successfully',
             'data' => $warehouseEntry
         ]);
     }




    public function getDistributorsByProductId($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $distributors = $product->distributor;

        if (!$distributors) {
            return response()->json(['message' => 'No distributor found for this product'], 404);
        }

        return response()->json([
            'id' => $distributors->id,
            'name' => $distributors->name,
            'role_id' => $distributors->role_id,
        ]);
    }


    public function getDistributorPrices($distributorId, $productId)
    {
      $prices = PriceInventory::where('user_id', $distributorId)
        ->where('product_id', $productId)
        ->with('channel')
        ->get(['chanel_id as channel_id', 'price']);

       return response()->json([
        'data' => $prices,
       ]);
   }















}
