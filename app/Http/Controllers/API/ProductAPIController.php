<?php

namespace App\Http\Controllers\API;

use App\Exports\ProductExcelExport;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Imports\ProductImport;
use App\Models\MainProduct;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\VariationProduct;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class ProductAPIController extends AppBaseController
{
    /** @var ProductRepository */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(Request $request): ProductCollection
    {
        $perPage = null; //getPageSize($request);
        $products = $this->productRepository;
        $user_details =Auth::user();

        if ($request->get('product_unit')) {
            $products->where('product_unit', $request->get('product_unit'));
        }


        // if($request->get('warehouse_id') && $request->get('warehouse_id') != 'null'){
        //     $warehouseId = $request->get('warehouse_id');
        //     $products->whereHas('stock', function ($q) use ($warehouseId) {
        //         $q->where('manage_stocks.warehouse_id', $warehouseId);
        //     })->with([
        //         'stock' => function (HasOne $query) use ($warehouseId) {
        //             $query->where('manage_stocks.warehouse_id', $warehouseId);
        //         },
        //     ]);
        // }

        $products = $products->with(['distributor','stock'])
        ->where('status', 1)
        ->get();
        ProductResource::usingWithCollection();

        return new ProductCollection($products);
    }



    // public function index(Request $request): ProductCollection
    // {
    //     $perPage = getPageSize($request);
    //     $products = $this->mainProductRepository;
    //     $user_details = Auth::user();

    //     Log::info('User Role ID: ' . $user_details->role_id);

    //     // Filter by product unit if provided
    //     if ($request->get('product_unit')) {
    //         $products->where('product_unit', $request->get('product_unit'));
    //     }

    //     // Role-based filtering
    //     if ($user_details->role_id == 1 || $user_details->role_id == 2) {
    //         // Admin roles: fetch all products
    //         $products = $products->with(['distributor', 'stock'])->paginate($perPage);
    //     } elseif ($user_details->role_id == 3 || $user_details->role_id == 4) {
    //         $products = $products->with(['distributor', 'stock'])
    //             ->where(function ($query) use ($user_details) {
    //                 $query->whereNull('user_id')
    //                       ->orWhere('user_id', $user_details->id);
    //             });

    //         // Check the product count before pagination
    //         $productCount = $products->count();
    //         Log::info('Pre-Paginate Products Count: ' . $productCount);

    //         // Now paginate
    //         $products = $products->paginate($perPage);
    //     } else {
    //         // Other roles: filter by user_id only
    //         $products = $products->with(['distributor', 'stock'])
    //             ->where('user_id', $user_details->id)
    //             ->paginate($perPage);
    //     }

    //     Log::info('Final Products Count: ' . $products->count());

    //     ProductResource::usingWithCollection();

    //     return new ProductCollection($products);
    // }







    /**
     * @return ProductResource|JsonResponse
     */
    public function store(CreateProductRequest $request)
    {

        $input = $request->all();

        $user_details=Auth::user();

        if ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $input['added_by'] = $user_details->id;
        } else if ($user_details->role_id == 3) {
            $input['distributor_id'] = $user_details->id;
            $input['added_by'] = $user_details->id;
        } else if($user_details->role_id == 4) {
             $input['warehouse_id'] = $user_details->id;
             $input['added_by'] = $user_details->id;
        }


        // if($user_details->role_id==1 || $user_details->role_id==2){
        //     $input['added_by']=$user_details->id;
        // }else{
        //     $input['user_id']=$user_details->id;
        //     $input['added_by']=$user_details->id;
        //  }

        if ($input['main_product_id']) {
            $mainProduct = MainProduct::find($input['main_product_id']);
            if ($mainProduct->product_type == MainProduct::SINGLE_PRODUCT) {
                return $this->sendError('You can add variations for single type product');
            }
        }

        if ($input['barcode_symbol'] == Product::EAN8 && strlen($input['code']) != 7) {
            return $this->sendError('Please enter 7 digit code');
        }

        if ($input['barcode_symbol'] == Product::UPC && strlen($input['code']) != 11) {
            return $this->sendError(' Please enter 11 digit code');
        }

        $product = $this->productRepository->storeProduct($input);

        // VariationProduct::create([
        //     'product_id' => $product->id,
        //     'variation_id' => $input['variation_id'],
        //     'variation_type_id' => $input['variation_type'],
        //     'main_product_id' => $input['main_product_id'],
        //     'added_by' => $input['added_by'],
        //     // 'user_id'=>$input['user_id'],
        //     'distributor_id' => isset($input['distributor_id']) ? $input['distributor_id'] : null,
        //    'warehouse_id' => isset($input['warehouse_id']) ? $input['warehouse_id'] : null,
        // ]);


        return new ProductResource($product);
    }


    // public function store(CreateProductRequest $request)
    // {
    //     $input = $request->all();
    //     $user_details = Auth::user();

    //     if ($user_details->role_id == 1 || $user_details->role_id == 2) {
    //         $input['added_by'] = $user_details->id;
    //     } elseif ($user_details->role_id == 3) {
    //         $input['distributor_id'] = $user_details->id;
    //         $input['added_by'] = $user_details->id;
    //     }

    //     // Log input data for debugging
    //     Log::debug('Input Data: ', $input);

    //     // Main product validation
    //     if ($input['main_product_id']) {
    //         $mainProduct = MainProduct::find($input['main_product_id']);
    //         if ($mainProduct && $mainProduct->product_type == MainProduct::SINGLE_PRODUCT) {
    //             return $this->sendError('You can add variations for single type product');
    //         }
    //     }

    //     // Barcode validation
    //     if ($input['barcode_symbol'] == Product::EAN8 && strlen($input['code']) != 7) {
    //         return $this->sendError('Please enter a 7-digit code');
    //     }

    //     if ($input['barcode_symbol'] == Product::UPC && strlen($input['code']) != 11) {
    //         return $this->sendError('Please enter an 11-digit code');
    //     }

    //     // Try saving the product
    //     try {
    //         $product = $this->productRepository->storeProduct($input);

    //         VariationProduct::create([
    //             'product_id' => $product->id,
    //             'variation_id' => $input['variation_id'],
    //             'variation_type_id' => $input['variation_type'],
    //             'main_product_id' => $input['main_product_id'],
    //             'added_by' => $input['added_by'],
    //             'distributor_id' => $input['distributor_id'] ?? null,
    //         ]);

    //         return new ProductResource($product);
    //     } catch (\Exception $e) {
    //         return $this->sendError('Product could not be saved: ' . $e->getMessage());
    //     }
    // }



    public function show($id): ProductResource
    {
        $product = $this->productRepository->find($id);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, $id): ProductResource
    {
        $input = $request->all();

        $product = $this->productRepository->updateProduct($input, $id);

        return new ProductResource($product);
    }

    public function destroy($id): JsonResponse
    {

        $purchaseItemModels = [
            PurchaseItem::class,
        ];
        $saleItemModels = [
            SaleItem::class,
        ];
        $purchaseResult = canDelete($purchaseItemModels, 'product_id', $id);
        $saleResult = canDelete($saleItemModels, 'product_id', $id);
        if ($purchaseResult || $saleResult) {
            return $this->sendError(__('messages.error.product_cant_deleted'));
        }

        if (File::exists(Storage::path('product_barcode/barcode-PR_' . $id . '.png'))) {
            File::delete(Storage::path('product_barcode/barcode-PR_' . $id . '.png'));
        }

        $product = $this->productRepository->find($id);
        $mainProduct = MainProduct::withCount('products')->find($product->main_product_id);

        if ($mainProduct->product_type == MainProduct::VARIATION_PRODUCT && $mainProduct->products_count <= 1) {
            return $this->sendError('You can not delete last variation product');
        }

        VariationProduct::where('product_id', $id)->delete();

        $this->productRepository->delete($id);

        return $this->sendSuccess('Product deleted successfully');
    }

    public function productImageDelete($mediaId): JsonResponse
    {
        $media = Media::where('id', $mediaId)->firstOrFail();
        $media->delete();

        return $this->sendSuccess('Product image deleted successfully');
    }


      public function getProductsDetails(Request $request,$id=null): JsonResponse
    {
        $product = $this->productRepository->find($id);
        $data = $product->prepareProducts();

        return $this->sendResponse($data, 'Product Detail Retrieved Successfully');
    }




    public function importProducts(Request $request): JsonResponse
    {
        Excel::import(new ProductImport, request()->file('file'));

        return $this->sendSuccess('Products imported successfully');
    }

    public function getProductExportExcel(Request $request): JsonResponse
    {
        if (Storage::exists('excel/product-excel-export.xlsx')) {
            Storage::delete('excel/product-excel-export.xlsx');
        }
        Excel::store(new ProductExcelExport, 'excel/product-excel-export.xlsx');

        $data['product_excel_url'] = Storage::url('excel/product-excel-export.xlsx');

        return $this->sendResponse($data, 'Product retrieved successfully');
    }

    public function getAllProducts()
    {
        $products = Product::all();
        $data = [];

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->id,
                'name' => $product->name,
            ];
        }

        return $this->sendResponse($data, 'Products retrieve successfully.');
    }

    public function showProduct(){
        $product = Product::all();

        return response()->json([
           'status'=>200,
           'product'=> $product
        ], 200);
    }

    public function productDetail($id){
        $product = Product::find($id);
        return response()->json([
            'status'=>200,
            'data'=>$product
        ]);
    }
}
