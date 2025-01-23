<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GiftInventory;
use App\Models\Gift;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class GiftInvetoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $giftId = $request->query('gift_id');
        $warehouse_id = $request->query('warehouse_id');
        if ($giftId){
            $giftInventories = GiftInventory::where('gift_id', $giftId)->get();
        } else {
            $giftInventories = GiftInventory::with(['gift','warehouse'])->where('warehouse_id',$warehouse_id)->get();
        }
        return response()->json($giftInventories);
    }




    /**
     * Store a newly created resource in storage.
     */



    // public function updateQuantity(Request $request)
    // {

    //     $validated = $request->validate([
    //         'gift_id' => 'required|exists:gifts,id',
    //         'distributor_id' => 'nullable|exists:users,id',
    //         'warehouse_id' => 'nullable|exists:warehouses,id',
    //         'distributor_quantities' => 'nullable|integer|min:0',
    //         'warehouse_quantities' => 'nullable|integer|min:0',
    //     ]);

    //     $userId = Auth::id();
    //     $country = Auth::user()->country;


    //     if (!isset($validated['distributor_id']) && !isset($validated['warehouse_id'])) {
    //         return response()->json(['message' => 'Either distributor_id or warehouse_id must be provided'], 400);
    //     }

    //     if (isset($validated['distributor_id']) && isset($validated['warehouse_id'])) {
    //         return response()->json(['message' => 'You cannot update both distributor and warehouse at the same time'], 400);
    //     }

    //     if (isset($validated['distributor_id']) && isset($validated['warehouse_id'])) {
    //         return response()->json(['message' => 'You cannot update both distributor and warehouse at the same time'], 400);
    //     }

    //     if (isset($validated['distributor_id']) && (!isset($validated['distributor_quantities']) || $validated['distributor_quantities'] < 1)) {
    //         return response()->json(['message' => 'Quantity must be at least 1 for distributor'], 400);
    //     }

    //     $gift = Gift::find($validated['gift_id']);
    //     if (!$gift) {
    //         return response()->json(['message' => 'Gift not found'], 404);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         if (isset($validated['distributor_id'])) {
    //             $distributorId = $validated['distributor_id'];
    //             $distributorQuantities = $validated['distributor_quantities'] ?? 0;

    //             if ($gift->quantity < $distributorQuantities) {
    //                 return response()->json(['message' => 'Insufficient gift quantity'], 400);
    //             }

    //             $distributorInventory = GiftInventory::where('gift_id', $gift->id)
    //             ->where('distributor_id', $distributorId)
    //             ->whereNull('warehouse_id')
    //             ->first();

    //             if (!$distributorInventory) {
    //                 $distributorInventory = GiftInventory::create([
    //                     'gift_id' => $gift->id,
    //                     'distributor_id' => $distributorId,
    //                     'user_id' => $userId,
    //                     'distributor_quantities' => $distributorQuantities,
    //                     'warehouse_quantities' => 0,
    //                     'country' => $country,
    //                 ]);
    //             } else {
    //                 $distributorInventory->distributor_quantities += $distributorQuantities;
    //                 $distributorInventory->save();
    //             }

    //             $gift->quantity -= $distributorQuantities;
    //             $gift->save();
    //         }

    //         if (isset($validated['warehouse_id'])) {
    //             $warehouseId = $validated['warehouse_id'];
    //             $requestedWarehouseQuantities = $validated['warehouse_quantities'] ?? 0;


    //             $distributorId = Warehouse::where('id', $warehouseId)->value('user_id');
    //             if (!$distributorId) {
    //                 return response()->json(['message' => 'No distributor associated with this warehouse'], 400);
    //             }

    //             $distributorInventory = GiftInventory::where('gift_id', $gift->id)
    //                 ->where('distributor_id', $distributorId)
    //                 ->whereNull('warehouse_id')
    //                 ->first();


    //             if (!$distributorInventory) {
    //                 return response()->json(['message' => 'Distributor inventory not found'], 400);
    //             }

    //             $warehouseInventory = GiftInventory::where('gift_id', $gift->id)
    //                 ->where('warehouse_id', $warehouseId)
    //                 ->first();

    //             if ($warehouseInventory) {
    //                 $previousWarehouseQuantities = $warehouseInventory->warehouse_quantities;
    //                 $quantityDifference = $requestedWarehouseQuantities - $previousWarehouseQuantities;

    //                 if ($quantityDifference > $distributorInventory->distributor_quantities) {
    //                     return response()->json([
    //                         'message' => 'Insufficient distributor quantity for warehouse allocation. Remaining quantity: ' . $distributorInventory->distributor_quantities
    //                     ], 400);
    //                 }

    //                 $warehouseInventory->warehouse_quantities = $requestedWarehouseQuantities;
    //                 $warehouseInventory->save();

    //                 $distributorInventory->distributor_quantities -= $quantityDifference;
    //             } else {
    //                 if ($requestedWarehouseQuantities > $distributorInventory->distributor_quantities) {
    //                     return response()->json([
    //                         'message' => 'Insufficient distributor quantity for warehouse allocation. Remaining quantity: ' . $distributorInventory->distributor_quantities
    //                     ], 400);
    //                 }

    //                 GiftInventory::create([
    //                     'gift_id' => $gift->id,
    //                     'warehouse_id' => $warehouseId,
    //                     'user_id' => $userId,
    //                     'distributor_id' => $distributorId,
    //                     'warehouse_quantities' => $requestedWarehouseQuantities,
    //                     'distributor_quantities' => $distributorInventory->distributor_quantities - $requestedWarehouseQuantities,
    //                     'country' => $country,
    //                 ]);

    //                 $distributorInventory->distributor_quantities -= $requestedWarehouseQuantities;
    //             }

    //             $distributorInventory->save();
    //         }


    //         DB::commit();
    //         return response()->json(['message' => 'Quantity updated successfully'], 200);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['message' => 'Failed to update quantity', 'error' => $e->getMessage()], 500);
    //     }
    // }


    // public function updateQuantity(Request $request)
    // {
    //     $validated = $request->validate([
    //         'gift_id' => 'required|exists:gifts,id',
    //         'distributor_id' => 'nullable|exists:users,id',
    //         'warehouse_id' => 'nullable|exists:warehouses,id',
    //         'distributor_quantities' => 'nullable|integer',
    //         'warehouse_quantities' => 'nullable|integer|min:0',
    //     ]);

    //     $userId = Auth::id();
    //     $country = Auth::user()->country;

    //     if (!isset($validated['distributor_id']) && !isset($validated['warehouse_id'])) {
    //         return response()->json(['message' => 'Either distributor_id or warehouse_id must be provided'], 400);
    //     }

    //     if (isset($validated['distributor_id']) && isset($validated['warehouse_id'])) {
    //         return response()->json(['message' => 'You cannot update both distributor and warehouse at the same time'], 400);
    //     }

    //     if (isset($validated['distributor_id']) && isset($validated['warehouse_id'])) {
    //         return response()->json(['message' => 'You cannot update both distributor and warehouse at the same time'], 400);
    //     }

    //     if (isset($validated['distributor_id']) && (!isset($validated['distributor_quantities']) || $validated['distributor_quantities'] < 1)) {
    //     return response()->json(['message' => 'Quantity must be at least 1 for distributor'], 400);
    //     }

    //     if (isset($validated['warehouse_id']) && (!isset($validated['warehouse_quantities']) || $validated    ['warehouse_quantities'] < 1)) {
    //     return response()->json(['message' => 'Quantity must be at least 1 for warehouse'], 400);
    //     }

    //     $gift = Gift::find($validated['gift_id']);
    //     if (!$gift) {
    //         return response()->json(['message' => 'Gift not found'], 404);
    //     }

    //     DB::beginTransaction();
    //     try {
    //        if (isset($validated['distributor_id'])) {
    //             $distributorId = $validated['distributor_id'];
    //             $distributorQuantities = $validated['distributor_quantities'] ?? 0;

    //             if ($distributorQuantities < 0) {
    //                 $returnedQuantity = abs($distributorQuantities);

    //                 $distributorInventory = GiftInventory::where('gift_id', $gift->id)
    //                     ->where('distributor_id', $distributorId)
    //                     ->whereNull('warehouse_id')
    //                     ->first();

    //                 if (!$distributorInventory || $distributorInventory->distributor_quantities <     $returnedQuantity) {
    //                     return response()->json(['message' => 'Insufficient quantity to return from distributor'], 400);
    //                 }

    //                 $distributorInventory->distributor_quantities -= $returnedQuantity;
    //                 $distributorInventory->save();

    //                 $gift->quantity += $returnedQuantity;
    //                 $gift->save();
    //             } else {
    //                 if ($gift->quantity < $distributorQuantities) {
    //                     return response()->json(['message' => 'Insufficient Gift quantity'], 400);
    //                 }

    //                 $distributorInventory = GiftInventory::where('gift_id', $gift->id)
    //                     ->where('distributor_id', $distributorId)
    //                     ->whereNull('warehouse_id')
    //                     ->first();

    //                 if (!$distributorInventory) {
    //                     $distributorInventory = GiftInventory::create([
    //                         'gift_id' => $gift->id,
    //                         'distributor_id' => $distributorId,
    //                         'user_id' => $userId,
    //                         'distributor_quantities' => $distributorQuantities,
    //                         'warehouse_quantities' => 0,
    //                         'country' => $country,
    //                     ]);
    //                 } else {
    //                     $distributorInventory->distributor_quantities += $distributorQuantities;
    //                     $distributorInventory->save();
    //                 }

    //                 $gift->quantity -= $distributorQuantities;
    //                 $gift->save();
    //             }
    //         }

    //         if (isset($validated['warehouse_id'])) {
    //             $warehouseId = $validated['warehouse_id'];
    //             $requestedWarehouseQuantities = $validated['warehouse_quantities'] ?? 0;

    //             $distributorId = Warehouse::where('id', $warehouseId)->value('user_id');
    //             if (!$distributorId) {
    //                 return response()->json(['message' => 'No distributor associated with this warehouse'], 400);
    //             }

    //             $distributorInventory = GiftInventory::where('gift_id', $gift->id)
    //                 ->where('distributor_id', $distributorId)
    //                 ->whereNull('warehouse_id')
    //                 ->first();

    //             if (!$distributorInventory) {
    //                 return response()->json(['message' => 'Distributor inventory not found'], 400);
    //             }

    //             $warehouseInventory = GiftInventory::where('gift_id', $gift->id)
    //                 ->where('warehouse_id', $warehouseId)
    //                 ->first();

    //             if ($warehouseInventory) {
    //                 $previousWarehouseQuantities = $warehouseInventory->warehouse_quantities;
    //                 $quantityDifference = $requestedWarehouseQuantities - $previousWarehouseQuantities;

    //                 if ($quantityDifference > $distributorInventory->distributor_quantities) {
    //                     return response()->json([
    //                         'message' => 'Insufficient distributor quantity for warehouse allocation.     Remaining quantity: ' . $distributorInventory->distributor_quantities,
    //                     ], 400);
    //                 }

    //                 $warehouseInventory->warehouse_quantities = $requestedWarehouseQuantities;
    //                 $warehouseInventory->save();

    //                 $distributorInventory->distributor_quantities -= $quantityDifference;
    //             } else {
    //                 if ($requestedWarehouseQuantities > $distributorInventory->distributor_quantities) {
    //                    return response()->json([
    //                        'message' => 'Insufficient distributor quantity for warehouse allocation.    Remaining quantity: ' . $distributorInventory->distributor_quantities,
    //                    ], 400);
    //                 }

    //                 GiftInventory::create([
    //                     'gift_id' => $gift->id,
    //                     'warehouse_id' => $warehouseId,
    //                     'user_id' => $userId,
    //                     'distributor_id' => $distributorId,
    //                     'warehouse_quantities' => $requestedWarehouseQuantities,
    //                     'distributor_quantities' => $distributorInventory->distributor_quantities - $requestedWarehouseQuantities,
    //                     'country' => $country,
    //                 ]);

    //                 $distributorInventory->distributor_quantities -= $requestedWarehouseQuantities;
    //             }

    //            $distributorInventory->save();
    //         }

    //         DB::commit();
    //         return response()->json(['message' => 'Quantity updated successfully'], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['message' => 'Failed to update quantity', 'error' => $e->getMessage()], 500);
    //     }
    // }


    public function updateQuantity(Request $request)
{
    $validated = $request->validate([
        'gift_id' => 'required|exists:gifts,id',
        'distributor_id' => 'nullable|exists:users,id',
        'warehouse_id' => 'nullable|exists:warehouses,id',
        'distributor_quantities' => 'nullable|integer',
        'warehouse_quantities' => 'nullable|integer|min:0',
    ]);

    if (!isset($validated['distributor_quantities']) && !isset($validated['warehouse_quantities'])) {
        return response()->json(['message' => 'Entere Qunatity first!'], 400);
    }

    $userId = Auth::id();
    $country = Auth::user()->country;

    // Validation: Ensure only one of distributor_id or warehouse_id is provided
    if (isset($validated['distributor_id']) && isset($validated['warehouse_id'])) {
        return response()->json(['message' => 'You cannot update both distributor and warehouse at the same time'], 400);
    }

    $gift = Gift::find($validated['gift_id']);
    if (!$gift) {
        return response()->json(['message' => 'Gift not found'], 404);
    }

    DB::beginTransaction();
    try {
        if (isset($validated['distributor_id'])) {
            $distributorId = $validated['distributor_id'];
            $distributorQuantities = $validated['distributor_quantities'] ?? 0;

            if ($distributorQuantities < 0) {
                $returnedQuantity = abs($distributorQuantities);

                $distributorInventory = GiftInventory::where('gift_id', $gift->id)
                    ->where('distributor_id', $distributorId)
                    ->whereNull('warehouse_id')
                    ->first();

                if (!$distributorInventory || $distributorInventory->distributor_quantities < $returnedQuantity) {
                    return response()->json(['message' => 'Insufficient quantity to return from distributor'], 400);
                }

                $distributorInventory->distributor_quantities -= $returnedQuantity;
                $distributorInventory->save();

                $gift->quantity += $returnedQuantity;
                $gift->save();
            } else {
                if ($distributorQuantities < 1) {
                    return response()->json(['message' => 'At least 1 quantity is required for distributor update'], 400);
                }

                if ($gift->quantity < $distributorQuantities) {
                    return response()->json(['message' => 'Insufficient Gift quantity'], 400);
                }

                $distributorInventory = GiftInventory::where('gift_id', $gift->id)
                    ->where('distributor_id', $distributorId)
                    ->whereNull('warehouse_id')
                    ->first();

                if (!$distributorInventory) {
                    $distributorInventory = GiftInventory::create([
                        'gift_id' => $gift->id,
                        'distributor_id' => $distributorId,
                        'user_id' => $userId,
                        'distributor_quantities' => $distributorQuantities,
                        'warehouse_quantities' => 0,
                        'country' => $country,
                    ]);
                } else {
                    $distributorInventory->distributor_quantities += $distributorQuantities;
                    $distributorInventory->save();
                }

                $gift->quantity -= $distributorQuantities;
                $gift->save();
            }
        }

        if (isset($validated['warehouse_id'])) {
            $warehouseId = $validated['warehouse_id'];
            $requestedWarehouseQuantities = $validated['warehouse_quantities'] ?? 0;

            $distributorId = Warehouse::where('id', $warehouseId)->value('user_id');
            if (!$distributorId) {
                return response()->json(['message' => 'No distributor associated with this warehouse'], 400);
            }

            $distributorInventory = GiftInventory::where('gift_id', $gift->id)
                ->where('distributor_id', $distributorId)
                ->whereNull('warehouse_id')
                ->first();

            if (!$distributorInventory) {
                return response()->json(['message' => 'Distributor has no inventory of this Gift'], 400);
            }

            $warehouseInventory = GiftInventory::where('gift_id', $gift->id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($warehouseInventory) {
                $previousWarehouseQuantities = $warehouseInventory->warehouse_quantities;
                $quantityDifference = $requestedWarehouseQuantities - $previousWarehouseQuantities;

                if ($quantityDifference > $distributorInventory->distributor_quantities) {
                    return response()->json([
                        'message' => 'Insufficient distributor quantity for warehouse allocation. Remaining quantity: ' . $distributorInventory->distributor_quantities,
                    ], 400);
                }

                $warehouseInventory->warehouse_quantities = $requestedWarehouseQuantities;
                $warehouseInventory->save();

                $distributorInventory->distributor_quantities -= $quantityDifference;
            } else {
                if ($requestedWarehouseQuantities > $distributorInventory->distributor_quantities) {
                    return response()->json([
                        'message' => 'Insufficient distributor quantity for warehouse allocation. Remaining quantity: ' . $distributorInventory->distributor_quantities,
                    ], 400);
                }

                GiftInventory::create([
                    'gift_id' => $gift->id,
                    'warehouse_id' => $warehouseId,
                    'user_id' => $userId,
                    'distributor_id' => $distributorId,
                    'warehouse_quantities' => $requestedWarehouseQuantities,
                    'distributor_quantities' => $distributorInventory->distributor_quantities - $requestedWarehouseQuantities,
                    'country' => $country,
                ]);

                $distributorInventory->distributor_quantities -= $requestedWarehouseQuantities;
            }

            $distributorInventory->save();
        }

        DB::commit();
        return response()->json(['message' => 'Quantity updated successfully'], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Failed to update quantity', 'error' => $e->getMessage()], 500);
    }
}







    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
