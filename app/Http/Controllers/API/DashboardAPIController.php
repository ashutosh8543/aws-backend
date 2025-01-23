<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\SaleCollection;
use App\Http\Resources\SaleResource;
use App\Models\BaseUnit;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ManageStock;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\User;
use App\Models\Salesman;
use App\Models\Suppervisor;
use App\Models\SaleReturn;
use App\Models\SalesPayment;
use App\Models\Warehouse;
use App\Models\ProductInventory;
use App\Models\CreditCollection;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardAPIController extends AppBaseController
{
    public function getPurchaseSalesCounts(): JsonResponse
    {
        $data = [];
        $today = Carbon::today();
        $userDetails=Auth::user();
        $loginUserId = Auth::id();

        $data['today_sales'] = (float) Sale::where('date', $today)->where('country',$userDetails->country??'')->sum('grand_total');
        $data['today_purchases'] = (float) Purchase::where('date', $today)->sum('grand_total');
        $data['today_sale_return'] = (float) SaleReturn::where('date', $today)->sum('grand_total');
        $data['today_purchase_return'] = (float) PurchaseReturn::where('date', $today)->sum('grand_total');
        $data['today_sales_received_count'] = (float) SalesPayment::where('payment_date', $today)->sum('amount');
        $data['today_expense_count'] = (float) Expense::where('date', $today)->sum('amount');

        if ($userDetails->role_id == 3) {
            $distributor = User::where('id', $loginUserId)->first();
            if($distributor){
                $distributorId = $distributor -> id;
                $salesmanIds = Salesman::where('distributor_id', $distributorId )
                ->pluck('salesman_id');
                $data['today_sales'] = (float) Sale::where('date', $today)
               ->where('country', $userDetails->country ?? '')
               ->whereIn('salesman_id', $salesmanIds)
               ->sum('grand_total');
            }
        }

        if($userDetails->role_id == 4){
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if($warehouse){
                $warehouseId = $warehouse->id;
                $wareId = $warehouse->ware_id;
                $salesmanIds = Salesman::where('ware_id', $wareId )
                ->pluck('salesman_id');
                $data['today_sales'] = (float) Sale::where('date', $today)
                ->where('country', $userDetails->country ?? '')
                ->where('warehouse_id', $warehouseId)
                ->sum('grand_total');


            }

        }

        if ($userDetails->role_id == 5) {
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if( $supervisor){
                $wareId = $supervisor->ware_id;
                $salesmanIds = Salesman::where('ware_id', $wareId )
                ->pluck('salesman_id');
                $data['today_sales'] = (float) Sale::where('date', $today)
                ->where('country', $userDetails->country ?? '')
                ->whereIn('salesman_id', $salesmanIds)
                ->sum('grand_total');
            }
        }

        return $this->sendResponse($data, 'Sales Purchase Count Retrieved Successfully');
    }



    public function getAllPurchaseSalesCounts(): JsonResponse
    {

        $loginUserId = Auth::id();
        $userDetails = Auth::user();
        $today = Carbon::today();
        $data = [];

        $data['today_sales'] = (float) Sale::where('date', $today)->where('country',$userDetails->country??'')->sum('grand_total');
        $data['all_sales_count'] = (float) Sale::where('country',$userDetails->country??'')->sum('grand_total');
        $data['all_sale_return_count'] =  (float) SaleReturn::where('country',$userDetails->country??'')->sum('grand_total');
        $data['all_purchase_return_count'] = (float) PurchaseReturn::sum('grand_total');
        $data['all_purchases_count'] = (float) Purchase::sum('grand_total') - $data['all_purchase_return_count'];
        $data['all_sales_received_count'] = (float) SalesPayment::sum('amount');
        $data['all_expense_count'] = (float) Expense::sum('amount');

        if ($userDetails->role_id == 3) {

            $distributor = User::where('id', $loginUserId)->first();
            if($distributor){
                $distributorId = $distributor -> id;
                $salesmanIds = Salesman::where('distributor_id', $distributorId )
                ->pluck('salesman_id');
                $data['all_sales_count'] = (float) Sale::whereIn('salesman_id', $salesmanIds )
                ->sum('grand_total');
                $data['all_sale_return_count'] = (float) SaleReturn::whereIn('salesman_id', $salesmanIds )
               ->sum('grand_total');

            }
        }

        if($userDetails->role_id == 4){
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if($warehouse){
                $warehouseId = $warehouse->id;
                $wareId = $warehouse->ware_id;
                $salesmanIds = Salesman::where('ware_id', $wareId )
                ->pluck('salesman_id');
                $data['all_sales_count'] = (float) Sale::where('warehouse_id', $warehouseId )
                ->sum('grand_total');

                $data['all_sale_return_count'] = (float) SaleReturn::where('warehouse_id', $warehouseId )
                ->sum('grand_total');

            }
        }

        if ($userDetails->role_id == 5) {
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if ($supervisor) {
                $wareId = $supervisor->ware_id;

                $warehouseId = Warehouse::where('ware_id', $wareId)->pluck('id')->first();
                $salesmanIds = Salesman::where('ware_id', $wareId )
                ->pluck('salesman_id');
                $data['all_sales_count'] = (float) Sale::where('warehouse_id', $warehouseId )
                ->sum('grand_total');

                $data['all_sale_return_count'] = (float) SaleReturn::where('warehouse_id', $warehouseId )
                ->sum('grand_total');

            }
        }


        return $this->sendResponse($data, 'All Sales Purchase and returns Count Retrieved Successfully');
    }

    public function getRecentSales(): SaleCollection
    {
        $recentSales = Sale::latest()->take(5)->get();
        SaleResource::usingWithCollection();

        return new SaleCollection($recentSales);
    }

    // public function getTopSellingProducts(): JsonResponse
    // {
    //     $month = Carbon::now()->month;
    //     $year = Carbon::now()->year;
    //     $topSellings = Product::leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
    //         ->whereMonth('sale_items.created_at', $month)
    //         ->whereYear('sale_items.created_at', $year)
    //         ->selectRaw('products.*, COALESCE(sum(sale_items.sub_total),0) grand_total')
    //         ->selectRaw('products.*, COALESCE(sum(sale_items.quantity),0) total_quantity')
    //         ->groupBy('products.id')
    //         ->orderBy('total_quantity', 'desc')
    //         ->latest()
    //         ->take(5)
    //         ->get();
    //     $data = [];
    //     foreach ($topSellings as $topSelling) {
    //         $data[] = $topSelling->prepareTopSelling();
    //     }

    //     return $this->sendResponse($data, 'Top Selling Products Retrieved Successfully');
    // }


    public function getTopSellingProducts(): JsonResponse
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $userDetails = Auth::user();
        $loginUserId = Auth::id();
        $data = [];

        $query = Product::leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereMonth('sale_items.created_at', $month)
            ->whereYear('sale_items.created_at', $year)
            ->selectRaw('products.*, COALESCE(sum(sale_items.sub_total),0) as grand_total')
            ->selectRaw('COALESCE(sum(sale_items.quantity),0) as total_quantity')
            ->groupBy('products.id')
            ->orderBy('total_quantity', 'desc');

        if ($userDetails->role_id == 3) {
            $distributor = User::where('id', $loginUserId)->first();
            if ($distributor) {
                $salesmanIds = Salesman::where('distributor_id', $distributor->id)
                    ->pluck('salesman_id');
                $query->whereIn('sales.salesman_id', $salesmanIds);
            }
        } elseif ($userDetails->role_id == 4) {
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if ($warehouse) {
                $salesmanIds = Salesman::where('ware_id', $warehouse->ware_id)
                    ->pluck('salesman_id');
                $query->whereIn('sales.salesman_id', $salesmanIds);
            }
        } elseif ($userDetails->role_id == 5) {
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if ($supervisor) {
                $salesmanIds = Salesman::where('ware_id', $supervisor->ware_id)
                    ->pluck('salesman_id');
                $query->whereIn('sales.salesman_id', $salesmanIds);
            }
        }

        $topSellings = $query->latest()->take(5)->get();

        foreach ($topSellings as $topSelling) {
            $data[] = $topSelling->prepareTopSelling();
        }

        return $this->sendResponse($data, 'Top Selling Products Retrieved Successfully');
    }



    // public function getWeekSalePurchases(): JsonResponse
    // {
    //     $count = 7;
    //     $days = [];
    //     $date = Carbon::tomorrow();
    //     for ($i = 0; $i < $count; $i++) {
    //         $days[] = $date->subDay()->format('Y-m-d');
    //     }
    //     $day['days'] = array_reverse($days);
    //     $sales = Sale::whereBetween('date', [$day['days'][0], $day['days'][6]])
    //         ->orderBy('date', 'desc')
    //         ->groupBy('date')
    //         ->get([
    //             DB::raw('DATE_FORMAT(date,"%Y-%m-%d") as week'),
    //             DB::raw('SUM(grand_total) as grand_total'),
    //         ])->keyBy('week');
    //     $period = CarbonPeriod::create($day['days'][0], $day['days'][6]);
    //     $data['dates'] = array_map(function ($datePeriod) {
    //         return $datePeriod->format('Y-m-d');
    //     }, iterator_to_array($period));

    //     $data['sales'] = array_map(function ($datePeriod) use ($sales) {
    //         $week = $datePeriod->format('Y-m-d');

    //         return $sales->has($week) ? $sales->get($week)->grand_total : 0;
    //     }, iterator_to_array($period));

    //     $purchases = Purchase::whereBetween('date', [$day['days'][0], $day['days'][6]])
    //         ->orderBy('date', 'desc')
    //         ->groupBy('date')
    //         ->get([
    //             DB::raw('DATE_FORMAT(date,"%Y-%m-%d") as week'),
    //             DB::raw('SUM(grand_total) as grand_total'),
    //         ])->keyBy('week');
    //     $data['purchases'] = array_map(function ($datePeriod) use ($purchases) {
    //         $week = $datePeriod->format('Y-m-d');

    //         return $purchases->has($week) ? $purchases->get($week)->grand_total : 0;
    //     }, iterator_to_array($period));

    //     return $this->sendResponse($data, 'Week of Sales Purchase Retrieved Successfully');
    // }

    public function getWeekSalePurchases(): JsonResponse
    {
       $count = 7;
       $days = [];
       $date = Carbon::tomorrow();
       for ($i = 0; $i < $count; $i++) {
           $days[] = $date->subDay()->format('Y-m-d');
       }
       $day['days'] = array_reverse($days);
       $userDetails = Auth::user();
       $loginUserId = Auth::id();

       $salesQuery = Sale::whereBetween('date', [$day['days'][0], $day['days'][6]]);

        if ($userDetails->role_id == 3) {
            $distributor = User::where('id', $loginUserId)->first();
            if ($distributor) {
                $distributorId = $distributor->id;
                $salesmanIds = Salesman::where('distributor_id', $distributorId)->pluck('salesman_id');
                $salesQuery->whereIn('salesman_id', $salesmanIds);
            }
        } elseif ($userDetails->role_id == 4) {
           $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if ($warehouse) {
                $warehouseId = $warehouse->id;
                $wareId = $warehouse->ware_id;
                $salesmanIds = Salesman::where('ware_id', $wareId)->pluck('salesman_id');
                $salesQuery->where('warehouse_id', $warehouseId);
            }
        } elseif ($userDetails->role_id == 5) {
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if ($supervisor) {
                $wareId = $supervisor->ware_id;
                $salesmanIds = Salesman::where('ware_id', $wareId)->pluck('salesman_id');
                $salesQuery->whereIn('salesman_id', $salesmanIds);
            }
        }

        $salesQuery->where('country', $userDetails->country ?? '');

        $sales = $salesQuery->orderBy('date', 'desc')
        ->groupBy('date')
        ->get([
            DB::raw('DATE_FORMAT(date, "%Y-%m-%d") as week'),
            DB::raw('SUM(grand_total) as grand_total'),
        ])->keyBy('week');

        $period = CarbonPeriod::create($day['days'][0], $day['days'][6]);
        $data['dates'] = array_map(function ($datePeriod) {
            return $datePeriod->format('Y-m-d');
        }, iterator_to_array($period));

        $data['sales'] = array_map(function ($datePeriod) use ($sales) {
            $week = $datePeriod->format('Y-m-d');
            return $sales->has($week) ? $sales->get($week)->grand_total : 0;
        }, iterator_to_array($period));

        // Purchases
        $purchases = Purchase::whereBetween('date', [$day['days'][0], $day['days'][6]])
        ->orderBy('date', 'desc')
        ->groupBy('date')
        ->get([
            DB::raw('DATE_FORMAT(date, "%Y-%m-%d") as week'),
            DB::raw('SUM(grand_total) as grand_total'),
        ])->keyBy('week');

        $data['purchases'] = array_map(function ($datePeriod) use ($purchases) {
            $week = $datePeriod->format('Y-m-d');
            return $purchases->has($week) ? $purchases->get($week)->grand_total : 0;
        }, iterator_to_array($period));

        return $this->sendResponse($data, 'Week of Sales Purchase Retrieved Successfully');
    }


    public function getYearlyTopSelling(): JsonResponse
    {
        $year = Carbon::now()->year;
        $topSellings = Product::leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->whereYear('sale_items.created_at', $year)
            ->selectRaw('products.*, COALESCE(sum(sale_items.sub_total),0) grand_total')
            ->selectRaw('products.*, COALESCE(sum(sale_items.quantity),0) total_quantity')
            ->groupBy('products.id')
            ->orderBy('total_quantity', 'desc')
            ->take(5)
            ->get();
        $data = [];
        foreach ($topSellings as $topSelling) {
            $data['name'][] = $topSelling->name;
            $data['total_quantity'][] = $topSelling->total_quantity;
        }

        return $this->sendResponse($data, 'Yearly TopSelling Products Retrieved Successfully');
    }

    public function getTopCustomer(): JsonResponse
    {
        $month = Carbon::now()->month;
        $topCustomers = Customer::leftJoin('sales', 'customers.id', '=', 'sales.customer_id')
            ->whereMonth('date', $month)
            ->select('customers.*', DB::raw('sum(sales.grand_total) as grand_total'))
            ->groupBy('customers.id')
            ->orderBy('grand_total', 'desc')
            ->latest()
            ->take(5)
            ->get();
        $data = [];
        foreach ($topCustomers as $topCustomer) {
            $data['name'][] = $topCustomer->name;
            $data['grand_total'][] = (float) $topCustomer->grand_total;
        }

        return $this->sendResponse($data, 'Top Customers Retrieved Successfully');
    }

    public function stockAlerts(): JsonResponse
    {
        $manageStocks = ManageStock::with('warehouse')->where('alert', true)->limit(10)->latest()->get();
        $productResponse = [];
        foreach ($manageStocks as $stock) {
            $product = Product::where('id', $stock->product_id)->first();
            if (!empty($product)) {
                $productUnitName = BaseUnit::whereId($product->product_unit)->value('name');
                $stock['product_unit_name'] = $productUnitName;
                $product->stock = $stock;
                $productResponse[] = $product;
                $product = null;
                $stock = null;
            }
        }

        return $this->sendResponse($productResponse, 'Stocks retrieved successfully');
    }

    public function remainingStockAlert()
    {
        try {
            $productInventories = ProductInventory::with(['product', 'product.productUnit', 'warehouse'])
                ->whereNotNull('warehouse_id')
                ->whereHas('product', function ($query) {
                    $query->whereColumn('product_inventories.warehouse_quantities', '<', 'products.stock_alert');
                })
                ->get();

            if ($productInventories->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No product inventories found where warehouse quantities are less than stock alert.',
                    'data' => [],
                ], 404);
            }

            $data = [
                'success' => true,
                'message' => 'Product inventories retrieved successfully.',
                'data' => $productInventories,
            ];

            return response()->json($data, 200);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching product inventories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }





    // public function SalesmanCollectionList(Request $request)
    // {
    //     $perPage = getPageSize($request);

    //     $startDate = $request->get('start_date');
    //     $endDate = $request->get('end_date');

    //     $dataQuery = CreditCollection::with(['salesman', 'customer', 'customer.channelDetails', 'orderDetails'])
    //     ->latest();
    //     if ($startDate && $endDate) {
    //         $dataQuery->whereBetween('collected_date', [$startDate, $endDate]);
    //     }
    //     $data = $dataQuery->paginate($perPage);
    //     return $this->sendResponse($data, 'All Collection Retrieved Successfully');
    // }


    public function SalesmanCollectionList(Request $request)
    {
       $perPage = getPageSize($request);
       $startDate = $request->get('start_date');
       $endDate = $request->get('end_date');
       $search = $request->filter['search'] ?? '';
       $page=$request->get('page');
       $pageNumber=$page['number']??1;
       $collection_payment_type = $request->get('payment_type');

       $dataQuery = CreditCollection::with(['salesman', 'customer', 'customer.channelDetails', 'orderDetails'])
        ->latest();


        $sort = null;
        $sort_name = ltrim($request->sort, '-');
        if ($request->sort == $sort_name) {
            $sort = 'asc';
        } else {
            $sort = 'desc';
        }

        $dataQuery = CreditCollection::with(['salesman', 'customer', 'customer.channelDetails', 'orderDetails' ]);

        if ($sort_name) {
            $dataQuery->orderBy($sort_name, $sort);
        } else {
            $dataQuery->orderBy('id', 'desc');
        }

        if ($startDate && $endDate) {
          $dataQuery->whereBetween('collected_date', [$startDate, $endDate]);
        }


        if (!empty($search)) {
            $searchTerms = explode(' ', $search);
            $dataQuery->where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->where(function ($subQuery) use ($term) {
                        $subQuery->whereHas('salesman', function ($q) use ($term) {
                            $q->where('first_name', 'like', '%' . $term . '%')
                              ->orWhere('last_name', 'like', '%' . $term . '%');
                        })
                        ->orWhereHas('customer', function ($q) use ($term) {
                            $q->where('name', 'like', '%' . $term . '%');
                        });
                    });
                }
            });
        }
        if ($collection_payment_type){
             $type = $collection_payment_type == 1 ? 'Cheque' : ($collection_payment_type == 2 ?'Cash':'');
            $dataQuery->where('collection_payment_type', $type);
        }

         $data = $dataQuery->paginate($perPage,['*'], 'page',$pageNumber);

         return $this->sendResponse($data, 'All Collection Retrieved Successfully');
    }




}
