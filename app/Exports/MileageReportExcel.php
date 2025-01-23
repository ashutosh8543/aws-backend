<?php

namespace App\Exports;

use App\Models\GiftSubmit;
use App\Models\Mileage;
use App\Models\Warehouse;
use App\Models\Salesman;
use Maatwebsite\Excel\Concerns\FromView;
class MileageReportExcel implements FromView
{
    public function view(): \Illuminate\Contracts\View\View
    {   
        $startDate = request()->get('start_date');
        $endDate = request()->get('end_date');
        $warehouse_id = request()->get('warehouse_id');
        $mileageQuery = Mileage::with(['sales_man' => function($query) {
            $query->where('role_id', 6);
        }]);
        if($warehouse_id){
            $warehouse = Warehouse::where('id',$warehouse_id)->first();
                if ($warehouse) {
                    $ware_id = $warehouse->ware_id;
                    $country = $warehouse->country;
                    $salesmanIds = Salesman::where('ware_id', $ware_id)
                       ->where('country', $country)
                       ->pluck('salesman_id');
                    $mileageQuery->whereIn('sales_man_id', $salesmanIds);
                }
        }
        if($startDate != 'null' && $endDate != 'null' && $startDate && $endDate){
            $mileageQuery->whereDate('created_at', '>=',
            $startDate)
            ->whereDate('created_at', '<=', $endDate);
            
        }


        return view('excel.mileage-report-excel', ['saleReturns' => $mileageQuery->get()]);
    }
}
