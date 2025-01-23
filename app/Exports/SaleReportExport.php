<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromView;

class SaleReportExport implements FromView
{   
     public $customer_id;
     
     public function __construct($id=null) {
       $this->customer_id=$id;
    }


    public function view(): \Illuminate\Contracts\View\View
    {   
        

        $startDate = request()->get('start_date');
        $endDate = request()->get('end_date');
        $warehouse_id=request()->get('warehouse_id');
        $query=Sale::with(['countryDetails.currencyDetails','saleItems', 'warehouse', 'customer', 'payments']);
         
        if(request()->has('warehouse_id') && $warehouse_id!='null'){
            $query->where('warehouse_id', $warehouse_id);
        }          
        if($startDate != 'null' && $endDate != 'null' && $startDate && $endDate){
            $query->whereDate('created_at', '>=',
            $startDate)
            ->whereDate('created_at', '<=', $endDate);
            
        }          
        if($this->customer_id){
            $query->where('customer_id',$this->customer_id);
        }        
        $sales = $query->get();

        return view('excel.all-sale-report-excel', ['sales' => $sales]);
    }
}
