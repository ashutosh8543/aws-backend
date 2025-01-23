<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use App\Models\ProductInventory;
use App\Models\Salesman;class ProdcutStockReportExport implements FromView
{   

    public function view(): \Illuminate\Contracts\View\View
    {   
        $warehouseId=request()->get('warehouse_id');  
     
            $productInventories = ProductInventory::with(['product', 'product.productUnit', 'warehouse'])
                ->whereNotNull('warehouse_id')
                ->whereHas('product', function ($query) {
                    $query->whereColumn('product_inventories.warehouse_quantities', '<', 'products.stock_alert');
                });

            if(isset($warehouseId) && $warehouseId !='null'){
                $productInventories->where('warehouse_id',$warehouseId);
            } 
            
        return view('excel.product-stock-report-excel', ['stocks' => $productInventories->get()]);
    }
  
}
