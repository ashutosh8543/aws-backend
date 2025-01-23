<?php

namespace App\Exports;

use App\Models\OpeningAndClosing;
use App\Models\Warehouse;
use App\Models\Salesman;
use Maatwebsite\Excel\Concerns\FromView;
class CashAssignReportExcel implements FromView
{
    public function view(): \Illuminate\Contracts\View\View
    {   
        $startDate = request()->get('start_date');
        $endDate = request()->get('end_date');
        $warehouse_id = request()->get('warehouse_id');
        $openingCashQuery = OpeningAndClosing::with(['sales_man']);
        if($warehouse_id){
            $warehouse = Warehouse::where('id',$warehouse_id)->first();
                if ($warehouse) {
                    $ware_id = $warehouse->ware_id;
                    $country = $warehouse->country;
                    $salesmanIds = Salesman::where('ware_id', $ware_id)
                       ->where('country', $country)
                       ->pluck('salesman_id');
                    $openingCashQuery->whereIn('sales_man_id', $salesmanIds);
                }
        }

        if($startDate != 'null' && $endDate != 'null' && $startDate && $endDate){
            $openingCashQuery->whereDate('created_at', '>=',
            $startDate)
            ->whereDate('created_at', '<=', $endDate);
            
        }

        return view('excel.cash-report-excel', ['saleReturns' => $openingCashQuery->get()]);
    }
}
