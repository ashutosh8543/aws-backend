<?php

namespace App\Exports;

use App\Models\GiftSubmit;
use App\Models\Warehouse;
use App\Models\Salesman;
use Maatwebsite\Excel\Concerns\FromView;

class GiftSubmitedReportExcel implements FromView
{
    public function view(): \Illuminate\Contracts\View\View
    {   
        $startDate = request()->get('start_date');
        $endDate = request()->get('end_date');
        $warehouseId = request()->get('warehouse_id');
        $customer_id = request()->get('customer_id');
        $query = GiftSubmit::with(['outlets','salesman_details']);
        if (isset($warehouseId) && $warehouseId != 'null'){
            $warehouse = Warehouse::where('id', $warehouseId)->first();
            $salesmanIds = Salesman::where('ware_id',$warehouse->ware_id)
            ->pluck('salesman_id');
            $query->whereIn('sales_man_id', $salesmanIds);        } 
        
        if($startDate != 'null' && $endDate != 'null' && $startDate && $endDate){
            $query->whereDate('created_at', '>=',
            $startDate)
            ->whereDate('created_at', '<=', $endDate);
            
        }
        if($customer_id != 'null' && $customer_id){
            $query->where('outlet_id',$customer_id);            
        }
        $giftHistory=$query->get();

        return view('excel.gift-report-excel', ['saleReturns' => $giftHistory]);
    }
}
