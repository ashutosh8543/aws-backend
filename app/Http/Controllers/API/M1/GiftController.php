<?php

namespace App\Http\Controllers\API\M1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gift;
use App\Models\GiftSubmit;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use DB;
use Carbon\Carbon;
use App\Models\AssignGift;
use App\Models\GiftItems;
use App\Models\Salesman;


class GiftController extends AppBaseController
{
    
    

   public  function  index(Request $request):JsonResponse
   {       $perPage = getPageSize($request);
        $gift=Gift::latest()->get();
        return $this->sendResponse($gift,'Gift list retrieved Successfully');
   }

   public  function  getDetails(Request $request,$id=null):JsonResponse
   {      
        $gift=Gift::where('id',$id)->first();
        return $this->sendResponse($gift,'Gift details retrieved Successfully');
   }
  
   
 public function submitGift(Request $request){

     $input = $request->all();  
    
     $validator = Validator::make($request->all(), [
         'sales_man_id'=>'required|exists:users,id',
         'outlet_id'=>'required|exists:customers,id',
         'gift_id'=>'required|array|min:1',
         'quantity'=>'required|array|min:1',
        //  'discription'=>"required",
         'location'=>'required',
         'uploaded_date'=>"required",
         'image'=>'required',         
     ]);
     
 
     if ($validator->fails()) {
         return $this->SendError($validator->messages());
     }
         try {
            
            if ($request->hasFile('image')){
                    $image = $request->image;              
                     $image_name_with_ext = rand().$image->getClientOriginalName();
                     $destinationPath = 'gift';
                     $image->move(public_path($destinationPath), $image_name_with_ext);
                     $vehicle_image=url("public/gift/")."/".$image_name_with_ext;
                     $giftsubmited['image']=$vehicle_image;
             }  
             $giftQuantities=array_combine($request->gift_id,$request->quantity);

             $total=0;
             foreach($giftQuantities as $key=>$value){                        
                $total=$total+$value;     
             }

             $saleman = Salesman::where('salesman_id',$request->sales_man_id)->first(); 
            
            $giftsubmited['sales_man_id']=$request->sales_man_id;
            $giftsubmited['outlet_id']=$request->outlet_id;
            $giftsubmited['discription']=$request->discription;
            $giftsubmited['uploaded_date']=$request->uploaded_date;
            $giftsubmited['location']=$request->location;
            $giftsubmited['country']=$saleman->country??'';
            $giftsubmited['total_quantity']=$total;
            $giftsubmited['gift_type']=$request->gift_type;
            $giftsubmitedId=DB::table('gift_submits')->insertGetId($giftsubmited);             
              
             if($giftsubmitedId){
                 DB::table('gift_submits')->where('id',$giftsubmitedId)->update(['unique_id'=>"GTF_".$giftsubmitedId]);
                 foreach($giftQuantities as $key=>$value){
                        
                    $giftDetails=Gift::whereId($key)->first();
                    $inputItem['gift_details']=$giftDetails;
                    $inputItem['sales_man_id']=$request->sales_man_id;
                    $inputItem['customer_id']=$request->outlet_id;
                    $inputItem['gift_id']=$key;
                    $inputItem['quantity']=$value;
                    $inputItem['submited_gift_id']=$giftsubmitedId;
                    DB::table('gift_items')->insert($inputItem); 
                    $existingAssignment = AssignGift::where([
                        'salesman_id' => $request->sales_man_id,
                        'gift_id' => $key,
                        "assign_for_date"=>date('y-m-d',strtotime($request->uploaded_date))
                    ])->first();
        
                    if ($existingAssignment) {
                        $existingAssignment->quantity -= $value;
                        $existingAssignment->save();
                    } 
                 }
             } 
             $data=DB::table('gift_submits')->where('id',$giftsubmitedId)->first();              
             return $this->sendResponse($data,'Gift submitted successfully');
            
         } catch (\Exception $e) {
             return $this->sendError($e->getMessage());
         }

 }

   
   public  function  submitGiftHistory(Request $request,$id=null):JsonResponse
   {       $perPage = getPageSize($request);
        $giftHistory=GiftSubmit::with(['outlets','outlets.channelDetails'=>function($query){
         $query->select(['id','name']);
        },'giftItem'])
        ->select(['id','unique_id','total_quantity','sales_man_id','outlet_id','discription','image','location','uploaded_date','created_at'])
        ->where('sales_man_id',$id)->orderBy('id','desc')->paginate($perPage);
        return $this->sendResponse($giftHistory,'Gift history retrieved Successfully');
   }


   public function TodayLoadedGiftList(Request $request,$id=null){        
      
    $today=Carbon::today()->format('Y-m-d');
    $data=AssignGift::with('giftdetails')
    ->where('salesman_id',$id)->whereDate('assign_for_date',Carbon::today())->latest()->get();   
    return $this->sendResponse($data, 'Retrieved Successfully.');

}
public function AllLoadedGiftList(Request $request,$id=null){       
 
  $data=AssignGift::with('giftdetails')->where('salesman_id',$id)->latest()->get();   
      return $this->sendResponse($data, 'Retrieved Successfully.');
  }
}
