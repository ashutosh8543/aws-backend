<?php

namespace App\Http\Controllers\Api\M1;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Survey;
use App\Models\SurveyHistory;
use Validator;
use DB;
use App\Models\MainProduct;
use App\Models\SalesmanStock;
use App\Models\Salesman;

class SurveyController extends AppBaseController
{
   

    public function QuestionList(Request $request){  
         $warehouse_id=$request->get('warehouse_id');     
        $question = Question::with(['options'])->where('warehouse_id',$warehouse_id)->where('status','Active')->get();
        return $this->sendResponse($question, 'Question retrieved Successfully');      
    }
         
    public function CreateSurvey(Request $request){

        $input = $request->all();
       
        $validator = Validator::make($input, [
            'salesman_id'=>'required|exists:salesmen,salesman_id',
            'customer_id'=>'required|exists:customers,id',
            // 'warehouse_id'=>'required',
            'survey'=>"required|array|min:1",
        ]);
    
        if ($validator->fails()) {
            return $this->SendError($validator->messages());
        }
            try{  
                DB::beginTransaction();
                $survey= Survey::create([
                    'salesman_id'=>$request->salesman_id,
                    'order_id'=>$request->order_id,
                    'warehouse_id'=>$request->warehouse_id,
                    'customer_id'=>$request->customer_id,
                    'survey_date'=>$request->survey_date,
                  ]);
               $survey->update(['unique_code'=>'SRV_'.$survey->id]);                
               foreach($request->survey as $val){
                $question_image="";
                
                if($val['question_image']){  
                    // dd($val['question_image']);
                    $extension = explode('/', mime_content_type($val['question_image']))[1];        
            
                    $image =$val['question_image'];  // your base64 encoded
                    $image = str_replace('data:image/'.$extension.';base64,', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imageName = rand().'.'.$extension;
                    $destinationPath = '/uploads/question_image';    
                    $question_image=url('/uploads/question_image'.'/'. $imageName);            
                    \File::put(public_path($destinationPath).'/' . $imageName, base64_decode($image));               
            
                 }
                SurveyHistory::create([
                  'survey_id'=>$survey->id,
                  'question'=>$val['question'],
                  'option'=>$val['option'],
                  'question_image'=>$question_image
                ]);
            }
              DB::commit();
              return $this->sendSuccess('Survey created successfully');               
            }catch (\Exception $e) {
                DB::rollBack();
                return $this->sendError($e->getMessage());
            }
   
    }


    public function SurveyHistoryList(Request $request,$id=null){
       
        $perPage = getPageSize($request);

        $survey = Survey::with(['salesmanDetails'=>function($query){
           $query->select(['id','first_name','last_name']);
        },'customerDetails','surveyHistory'])->where('salesman_id',$id)->latest()->get();
        return $this->sendResponse($survey, 'Survey History retrieved Successfully');      
      
    }

    public function salesManStocks(Request $request){
        
         $salesman_id= $request->salesman_id;
         $customer_id=$request->customer_id;

         $distributor=Salesman::where('salesman_id',$salesman_id)->first();

         $distributor_id=$distributor->distributor_id;

        $question = MainProduct::with(['salesmanStock'=>function($query) use($salesman_id,$customer_id){
          $query->where('salesman_id',$salesman_id)
           ->where('customer_id',$customer_id);
          }])
          ->select(['id','name','code','added_by','user_id'])
          ->whereNull('user_id')->orWhere('user_id',$distributor_id)
        ->get();
        return $this->sendResponse($question, 'List retrieved Successfully');        
    }

    public function salesManStocksUpdate(Request $request){    
          
        $validator = Validator::make($request->all(), [
            'salesman_id'=>'required|exists:salesmen,salesman_id',
            'customer_id'=>'required|exists:customers,id',
            'product'=>'required|array|min:1',
            // 'quantity'=>"required",
        ]);
       
      
        if ($validator->fails()) {
            return $this->SendError($validator->messages());
        }
           $salesman_id= $request->salesman_id;
            $customer_id=$request->customer_id;
            $data=$request->product;  
          foreach($data as $value){
        //    $salesman_id= $value['salesman_id'];
        //    $customer_id=$value['customer_id'];
           $product_id=$value['product_id'];
           $quantity=$value['quantity'];

           $check = SalesmanStock::where('salesman_id',$salesman_id)->where('customer_id',$customer_id)->where('product_id',$product_id)->first();
          
            if(!empty($check)){
                $check->update(['quantity'=>$quantity]);
            }else{
                SalesmanStock::create([
                   'salesman_id'=>$salesman_id,
                   'product_id'=>$product_id,
                   'customer_id'=>$customer_id,
                   'quantity'=>$quantity
                ]);  
            } 
        }            
        return $this->sendSuccess('Updated Successfully');        
    }
    

}
