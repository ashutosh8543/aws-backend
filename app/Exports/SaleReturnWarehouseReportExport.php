<?php

namespace App\Exports;

use App\Models\SaleReturn;
use Maatwebsite\Excel\Concerns\FromView;

class SaleReturnWarehouseReportExport implements FromView
{    
    public $customer_id;
     
    public function __construct($id=null) {
      $this->customer_id=$id;
   }
    public function view(): \Illuminate\Contracts\View\View
    {     

        $startDate = request()->get('start_date');
        $endDate = request()->get('end_date');
        $warehouseId = request()->get('warehouse_id');
        $query= SaleReturn::with('countryDetails.currencyDetails','sale','warehouse', 'customer');
        if (isset($warehouseId) && $warehouseId != 'null'){
                $query->where('warehouse_id',$warehouseId);
        } 
        if($startDate != 'null' && $endDate != 'null' && $startDate && $endDate){
            $query->whereDate('created_at', '>=',
            $startDate)
            ->whereDate('created_at', '<=', $endDate);
        }         
        if($this->customer_id){
            $query->where('customer_id',$this->customer_id);
        }
        
        $saleReturns = $query->get();
        return view('excel.sale-return-report-excel', ['saleReturns' => $saleReturns]);
    }
}
