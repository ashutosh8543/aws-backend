<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateMainProductRequest;
use App\Http\Requests\UpdateMainProductRequest;
use App\Http\Resources\MainProductCollection;
use App\Http\Resources\MainProductResource;
use App\Models\MainProduct;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\VariationProduct;
use App\Repositories\MainProductRepository;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Auth;
class MainProductAPIController extends AppBaseController
{
    /** @var MainProductRepository */
    private $mainProductRepository;

    public function __construct(MainProductRepository $mainProductRepository)
    {
        $this->mainProductRepository = $mainProductRepository;
    }


    // public function index(Request $request)
    // {
    //     $user_details =Auth::user();

    //     $perPage = getPageSize($request);
    //     $products = $this->mainProductRepository;


    //     if ($request->get('product_unit')){
    //         $products->where('product_unit', $request->get('product_unit'));
    //     }

    //     if ($request->get('warehouse_id') && $request->get('warehouse_id') != 'null') {
    //         $warehouseId = $request->get('warehouse_id');
    //         $products->whereHas('stock', function ($q) use ($warehouseId) {
    //             $q->where('manage_stocks.warehouse_id', $warehouseId);
    //         })->with([
    //             'stock' => function (HasOne $query) use ($warehouseId) {
    //                 $query->where('manage_stocks.warehouse_id', $warehouseId);
    //             },
    //         ]);
    //     }

    //     $userCountryId = $user_details->country;

    //     if ($user_details->role_id == 1 || $user_details->role_id == 2) {
    //         $products = $products->where('country_id', $userCountryId )->
    //         paginate($perPage);
    //     } elseif ($user_details->role_id == 3 || $user_details->role_id == 4) {
    //         $products = $products->where(function($query) use ($user_details) {
    //             $query->whereNull('user_id')
    //                   ->orWhere('user_id', $user_details->id)
    //                   ->where('country_id', $userCountryId);
    //         })->paginate($perPage);
    //     } else {
    //         $products = $products->where('user_id', $user_details->id)->paginate($perPage);
    //     }

    //     MainProductResource::usingWithCollection();

    //     return new MainProductCollection($products);
    // }

    public function index(Request $request)
    {
        $user_details = Auth::guard('passport')->user();
        $perPage = getPageSize($request);
        $products = $this->mainProductRepository;


        if ($request->get('product_unit')) {
            $products->where('product_unit', $request->get('product_unit'));
        }

        if ($request->get('warehouse_id') && $request->get('warehouse_id') != 'null')     {
            $warehouseId = $request->get('warehouse_id');
            $products->whereHas('stock', function ($q) use ($warehouseId) {
                $q->where('manage_stocks.warehouse_id', $warehouseId);
            })->with([
                'stock' => function (HasOne $query) use ($warehouseId) {
                    $query->where('manage_stocks.warehouse_id', $warehouseId);
                },
            ]);
        }

        $userCountryId = $user_details->country;


        if ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $products = $products->where('country_id', $userCountryId)->paginate($perPage);
        }

        if ($user_details->role_id == 3) {
            $products = $products->where(function ($query) use ($user_details,     $userCountryId) {
                $query->whereNull('user_id')
                      ->orWhere('user_id', $user_details->id)
                      ->where('country_id', $userCountryId);
            })->paginate($perPage);
        }

        if ($user_details->role_id == 4) {
            $products = $products->where(function ($query) use ($user_details,     $userCountryId) {
                $query->whereNull('user_id')
                      ->orWhere('user_id', $user_details->id)
                      ->where('country_id', $userCountryId);
            })->paginate($perPage);
        }
        if($user_details->role_id == 5){
            $products = $products->where(function ($query) use ($user_details,     $userCountryId) {
                $query->whereNull('user_id')
                      ->orWhere('user_id', $user_details->id)
                      ->where('country_id', $userCountryId);
            })->paginate($perPage);
        }
        // else {
        //     $products = $products->where('user_id', $user_details->id)->paginate($perPage);
        // }

        MainProductResource::usingWithCollection();
        return new MainProductCollection($products);
    }


    public function mainProductsAll(Request $request)
    {
        $user_details = Auth::guard('passport')->user();
        $perPage = getPageSize($request);
        $products = $this->mainProductRepository;


        $products = $products->where('status', 1);


        if ($request->get('product_unit')) {
            $products->where('product_unit', $request->get('product_unit'));
        }

        if ($request->get('warehouse_id') && $request->get('warehouse_id') != 'null')     {
            $warehouseId = $request->get('warehouse_id');
            $products->whereHas('stock', function ($q) use ($warehouseId) {
                $q->where('manage_stocks.warehouse_id', $warehouseId);
            })->with([
                'stock' => function (HasOne $query) use ($warehouseId) {
                    $query->where('manage_stocks.warehouse_id', $warehouseId);
                },
            ]);
        }

        $userCountryId = $user_details->country;


        if ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $products = $products->where('country_id', $userCountryId)->paginate($perPage);
        }

        elseif ($user_details->role_id == 3) {
            $products = $products->where(function ($query) use ($user_details,     $userCountryId) {
                $query->whereNull('user_id')
                      ->orWhere('user_id', $user_details->id)
                      ->where('country_id', $userCountryId);
            })->paginate($perPage);
        }

        elseif ($user_details->role_id == 4) {
            $products = $products->where(function ($query) use ($user_details,     $userCountryId) {
                $query->whereNull('user_id')
                      ->orWhere('user_id', $user_details->id)
                      ->where('country_id', $userCountryId);
            })->paginate($perPage);
        }
        elseif($user_details->role_id == 5){
            $products = $products->where(function ($query) use ($user_details,     $userCountryId) {
                $query->whereNull('user_id')
                      ->orWhere('user_id', $user_details->id)
                      ->where('country_id', $userCountryId);
            })->paginate($perPage);
        }
        else {
            $products = $products->where('user_id', $user_details->id)->paginate($perPage);
        }

        MainProductResource::usingWithCollection();
        return new MainProductCollection($products);
}



    // public function index(Request $request)
    // {
    //     $user_details = Auth::user();
    //     $perPage = getPageSize($request);
    //     $products = $this->mainProductRepository;

    //     if ($request->get('product_unit')) {
    //         $products->where('product_unit', $request->get('product_unit'));
    //     }

    //     if ($request->get('warehouse_id') && $request->get('warehouse_id') != 'null') {
    //         $warehouseId = $request->get('warehouse_id');
    //         $products->whereHas('stock', function ($q) use ($warehouseId) {
    //             $q->where('manage_stocks.warehouse_id', $warehouseId);
    //         })->with([
    //             'stock' => function (HasOne $query) use ($warehouseId) {
    //                 $query->where('manage_stocks.warehouse_id', $warehouseId);
    //             },
    //         ]);
    //     }

    //     $userCountryId = $user_details->country;

    //     // Handling different roles
    // if ($user_details->role_id == 1 || $user_details->role_id == 2) {
    //     $products = $products->where(function($query) use ($userCountryId) {
    //         $query->where('country_id', $userCountryId)
    //               ->orWhereNull('country_id');
    //     })->paginate($perPage);

    //     } elseif ($user_details->role_id == 3) {
    //         $products = $products->where(function($query) use ($userCountryId,     $user_details) {
    //         $query->whereNull('user_id')
    //               ->orWhere('user_id', $user_details->id);
    //              })->where('country_id', $userCountryId)->paginate($perPage);
    //     } elseif ($user_details->role_id == 4) {
    //         $products = $products->where(function($query) use ($userCountryId,     $user_details) {
    //             $query->whereNull('user_id')
    //                   ->orWhere('user_id', $user_details->id);
    //         })->where('country_id', $userCountryId)->paginate($perPage);
    //     }elseif ($user_details->role_id == 5){
    //         $products = $products->where(function($query) use ($userCountryId,     $user_details) {
    //             $query->whereNull('user_id')
    //                   ->orWhere('user_id', $user_details->id);
    //         })->where('country_id', $userCountryId)->paginate($perPage);
    //     }
    //      else {
    //         $products = $products->where('user_id', $user_details->id)->paginate    ($perPage);
    //     }

    //     MainProductResource::usingWithCollection();

    //     return new MainProductCollection($products);
    // }




    public function show($id): MainProductResource
    {
        /** @var MainProduct $mainProduct */
        $mainProduct = $this->mainProductRepository->find($id);

        return new MainProductResource($mainProduct);
    }

    public function store(CreateMainProductRequest $request)
    {
        $input = $request->all();

        $existingProduct = MainProduct::where('code', $input['product_code'])->first();

        if ($existingProduct) {
            return response()->json([
                'success' => false,
                'message' => 'A product with the same code already exists. Please use a different code.'
            ], 422);
        }


        $user_details = Auth::guard('passport')->user();
        $user_id = $user_details->id;

        if ($user_details->role_id == 3) {
            $input['distributor_id'] = $user_details->id;
            $input['added_by'] = $user_details->id;
            $input['user_id'] = $user_id;
        } elseif ($user_details->role_id == 4) {
            $input['added_by'] = $user_details->id;
            $input['user_id'] = $user_id;
        } elseif ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $input['added_by'] = $user_details->id;
        } else {
            $input['user_id'] = $user_id;
            $input['added_by'] = $user_details->id;
        }

        try {
            DB::beginTransaction();
            $productRepo = app(ProductRepository::class);
            $mainProduct = MainProduct::create([
                'name' => $input['name'],
                'code' => $input['product_code'],
                'status'=>$input['status'],
                'product_unit' => $input['product_unit'],
                'user_id' => isset($input['user_id']) ? $input['user_id'] : null,
                'added_by' => $input['added_by'],
                'country_id' => $input['country_id'],
                'product_type' => 1, //$input['product_type'],
            ]);

            if (isset($input['images']) && !empty($input['images'])) {
                foreach ($input['images'] as $image) {
                    $product['image_url'] = $mainProduct->addMedia($image)->toMediaCollection(
                        MainProduct::PATH,
                        config('app.media_disc')
                    );
                }
            }

            $input['main_product_id'] = $mainProduct->id;

            $product = $productRepo->storeProduct($input);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e);
        }

        return new MainProductResource($product);
    }






    public function update(UpdateMainProductRequest $request, $id): MainProductResource
    {
        $input = $request->all();

        $mainProduct = MainProduct::find($id);

        $mainProduct->update([
            // 'country_id' => $input['country_id'],
            'status'=>$input['status'],
            'name' => $input['name'],
            'code' => $input['product_code'],
            'product_unit' => $input['product_unit'],
        ]);


        if (isset($input['images']) && !empty($input['images'])) {
            foreach ($input['images'] as $image) {
                $product['image_url'] = $mainProduct->addMedia($image)->toMediaCollection(
                    MainProduct::PATH,
                    config('app.media_disc')
                );
            }
        }

        $products = Product::with('variationType')->where('main_product_id', $id)->get();

        foreach ($products as $product) {
            if ($mainProduct->product_type == MainProduct::VARIATION_PRODUCT) {
                $input['code'] = $input['product_code'] . '-' . strtoupper($product->variationType->name);
            } else {
                $input['code'] = $input['product_code'];
            }
            $productRepo = app(ProductRepository::class);
            $product = $productRepo->updateProduct($input, $product->id);
        }

        return new MainProductResource($product);
    }

    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $products = Product::where('main_product_id', $id)->get();

            foreach ($products as $product) {

                // $purchaseItemModels = [
                //     PurchaseItem::class,
                // ];
                // $saleItemModels = [
                //     SaleItem::class,
                // ];

                // $purchaseResult = canDelete($purchaseItemModels, 'product_id', $product->id);
                // $saleResult = canDelete($saleItemModels, 'product_id', $product->id);

                // if ($purchaseResult || $saleResult) {
                //     return $this->sendError(__('messages.error.product_cant_deleted'));
                // }

                // if (File::exists(Storage::path('product_barcode/barcode-PR_' . $product->id . '.png'))) {
                //     File::delete(Storage::path('product_barcode/barcode-PR_' . $product->id . '.png'));
                // }
                $product->delete();
            }

            // VariationProduct::where('main_product_id', $id)->delete();

            $this->mainProductRepository->delete($id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage());
        }

        return $this->sendSuccess('Product deleted successfully');
    }
}
