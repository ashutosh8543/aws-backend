<?php

namespace App\Exports;

use App\Models\ProductInventory;
use Maatwebsite\Excel\Concerns\FromView;

class StockReportExport implements FromView
{
    public function view(): \Illuminate\Contracts\View\View
    {
        $warehouseId=request()->get('warehouse_id');  
     
        $productInventories = ProductInventory::with(['product', 'product.productUnit', 'warehouse'])
        ->whereNotNull('warehouse_id')
        ->whereHas('product');

        if(isset($warehouseId) && $warehouseId !='null'){
            $productInventories->where('warehouse_id',$warehouseId);
        } 

        return view('excel.stock-report-excel', ['stocks' =>$productInventories->get()]);
    }
}
