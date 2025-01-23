<?php

namespace App\Exports;

use App\Models\GiftInventory;
use Maatwebsite\Excel\Concerns\FromView;

class GiftStockReportExport implements FromView
{
    public function view(): \Illuminate\Contracts\View\View
    {
        $giftId = request()->query('gift_id');
        $warehouse_id = request()->query('warehouse_id');
        if (isset($giftId) && $giftId){
            $giftInventories = GiftInventory::where('gift_id', $giftId)->get();
        } else {
            $giftInventories = GiftInventory::with(['gift','warehouse'])->where('warehouse_id',$warehouse_id)->get();
        }

        return view('excel.gift-stock-report-excel', ['stocks' => $giftInventories]);
    }
}
