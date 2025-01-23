<?php

namespace App\Http\Controllers\API\M1;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\UpdateChangePasswordRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\Language;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;
use App\Models\LanguageContent;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Models\OpeningAndClosing;
use App\Http\Resources\UserResource;
use App\Models\Currency;
use App\Models\Warehouse;
use App\Models\Area;
use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Suppervisor;
use App\Models\Salesman;
use App\Models\AssignCustomersList;

class UserAPIController extends AppBaseController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function editProfile(): JsonResponse
    {
        $user = Auth::user();
        $userData = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'image' => $user->image_url,
        ];

        return $this->sendResponse($userData, 'User data retrieved successfully');
    }

    public function updateProfile(UpdateUserProfileRequest $request)
    {
        $input = $request->all();
        $user = $this->userRepository->updateUserProfile($input);
        $userData = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'image' => $user->image_url,
        ];

        return $this->sendResponse($userData, 'User data retrieved successfully');
    }

    public function changePassword(UpdateChangePasswordRequest $request): JsonResponse
    {
        $input = $request->all();
        try {
            $this->userRepository->updatePassword($input);
            return $this->sendSuccess('Password updated successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function languages(): JsonResponse
    {   
        $languages = Language::get(['id', 'name', 'iso_code', 'is_default','image']);

        return $this->sendResponse($languages, 'Languages retrieved Successfully');
    }
    
    
    public function countries(): JsonResponse
    {
        $countries = Country::where('status','Active')->get(['id', 'name', 'short_code', 'phone_code']);

        return $this->sendResponse($countries ,'Countries retrieved Successfully');
    }



    public function updateLanguage(Request $request): JsonResponse
    {
        $language = $request->get('language');
        $user = Auth::user();
        $user->update([
            'language' => $language,
        ]);

        return $this->sendResponse($user->language, 'Language Updated Successfully');
    }   
     
    public function OpeningCashList(Request $request): JsonResponse
    {   
        $input=$request->all();
        $validator = Validator::make($input, [
            'sales_man_id'=>'required|numeric',       
        ]);
 
        if ($validator->fails()){
            return $this->SendError($validator->messages());
        } 
        $sales_man_id=$request->sales_man_id;
        $OpeningCashList = OpeningAndClosing::with(['sales_man','sales_man.salesman','sales_man.salesman.warehouse'])->where('sales_man_id',$sales_man_id)->where('type','opening')->latest()->get();
        return $this->sendResponse($OpeningCashList ,'Opening cash list retrieved Successfully');
    }
  
    public function closingCashList(Request $request): JsonResponse
    {   
        $input=$request->all();
        $validator = Validator::make($input, [
            'sales_man_id'=>'required|numeric',       
        ]); 
        if ($validator->fails()) {
            return $this->SendError($validator->messages());
        } 
        $sales_man_id=$request->sales_man_id;
        $closingCashList = OpeningAndClosing::with(['sales_man','sales_man.salesman','sales_man.salesman.warehouse'])->where('sales_man_id',$sales_man_id)->where('type','closing')->latest()->get();

        return $this->sendResponse($closingCashList ,'closing cash list retrieved Successfully');
    }
    
    public function openingClosingCashList(Request $request){
           
        $input=$request->all();
        $validator = Validator::make($input, [
            'sales_man_id'=>'required|numeric',       
        ]);
 
        if ($validator->fails()) {
            return $this->SendError($validator->messages());
        } 

        $sales_man_id=$request->sales_man_id;
        $AllOpeningCashList = OpeningAndClosing::with(['sales_man','sales_man.salesman','sales_man.salesman.warehouse'])->where('sales_man_id',$sales_man_id)->latest()->get();

        return $this->sendResponse($AllOpeningCashList ,'closing cash list retrieved Successfully');

    }
   public function UploadMileage(Request $request){

    $input = $request->all();
    
    $validator = Validator::make($input, [
        'sales_man_id'=>'required|numeric', 
        'type'=>'required',
        'mileage'=>'required',
        'vehicle_image'=>'required',
        'mileage_image'=>'required',
        'location'=>'required',
        'uploaded_date'=>'required'
    ]);
    $input['country']=Salesman::where('salesman_id',$request->sales_man_id)->first()->country??'';
    if ($validator->fails()) {
        return $this->SendError($validator->messages());
    }   
        try {

           if ($request->hasFile('vehicle_image')){
                   $image = $request->vehicle_image;              
                    $image_name_with_ext = rand().$image->getClientOriginalName();
                    $destinationPath = 'mileagerecord';
                    $image->move(public_path($destinationPath), $image_name_with_ext);
                    $vehicle_image=url("public/mileagerecord/")."/".$image_name_with_ext;
                    $input['vehicle_image']=$vehicle_image;
            }

            if($request->hasFile('mileage_image')){
                $image = $request->mileage_image;
                $image_name_with_ext = rand().$image->getClientOriginalName();
                $destinationPath = 'mileagerecord';
                $image->move(public_path($destinationPath), $image_name_with_ext);
                $mileage_image=url("public/mileagerecord/")."/".$image_name_with_ext;
                $input['mileage_image']=$mileage_image;
            }

            DB::table('mileage_record')->insert($input);
            return $this->sendSuccess('Mileage Store successfully');
           
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    } 
    
   
    public function MileageRecords(Request $request){

        $input = $request->all();
        
        $input=$request->all();
        $validator = Validator::make($input, [
            'sales_man_id'=>'required|numeric', 
        ]);
    
        if ($validator->fails()) {
            return $this->SendError($validator->messages());
        }   
       
       try {             
    
                $data=DB::table('mileage_record')->where('sales_man_id',$input['sales_man_id'])->get();
                return $this->sendResponse( $data,'Mileage Record Fetch successfully');
               
            } catch (\Exception $e) {
                return $this->sendError($e->getMessage());
            }
        }


  
    public function GetMileageRecord(Request $request): JsonResponse
    {   
        $input=$request->all();
        $validator = Validator::make($input, [
            'sales_man_id'=>'required|numeric',       
        ]);
 
        if ($validator->fails()) {
            return $this->SendError($validator->messages());
        } 

        $sales_man_id=$request->sales_man_id;
        $closingCashList =  DB::table('closing_cash_table')->where('sales_man_id',$sales_man_id)->get();

        return $this->sendResponse($closingCashList ,'closing cash list retrieved Successfully');
    }
       
        //Language Change Starts here
        public function change_language(Request $request)
        {
            $language_check = $request->language;
            $input=$request->all();
            $validator = Validator::make($input, [
                'language'=>'required', 
                // 'user_id'=> 'required'    
            ]);
     
            if ($validator->fails()) {
                return $this->SendError($validator->messages());
            } 
            
            if (is_numeric($language_check)){
                $language_get = Language::where('id', $language_check)->first();
                if(!$language_get){
                    return $this->SendError("No language Found");
                 }
                $language = $language_get->iso_code;
            }else{
                $language_get = Language::where('iso_code', $language_check)->first();
                if(!$language_get){
                    return $this->SendError("No language Found");
                 }
                $language = $language_get->iso_code;
            }        
           
            if($language == $language){
                $query = LanguageContent::select($language, 'string')->where(['active' => '1'])->get();
                foreach($query as $queryes)
                {
                    $sting[] = $queryes->string;
                    $value[] = $queryes->$language;
                }
                $languagenew=  array_combine($sting,$value);
            }
            
            if($request->user_id){                
                $user = User::where('id',$request->user_id)->update(['language' =>$language]);
            }

            $data['language_data']=$languagenew;
            $data['language']=$language;
            $data['language_name']=$language_get->name;
            return $this->sendResponse($data, 'All Language List');            
            
        }


        public function DistributorList(Request $request)
        {
            $data = User::where('role_id',3)->get();
            return $this->sendResponse($data, 'All distributors List');  
        }

        public function getProfile($id)
        {
            $user =User::with(['salesman','countryDetails'])->find($id);
            $language_get = Language::where('iso_code',$user->language)->first();
            $currency=Currency::select(['id','code','name','symbol'])->where('country_id',$user->country)->first();
            $countryDetails=Country::find($user->country??'');            
            $user->language_name=$language_get->name;
            $user->currencies=$currency;
            $user->country_name=$countryDetails->name??'';    
            $warehouse=Warehouse::where('ware_id',$user->salesman[0]['ware_id']??'')->first();

            $supervisor=Suppervisor::where('ware_id',$user->salesman[0]['ware_id']??'')->first()??'';
            $supervisorDetails=User::where('id',$supervisor->supervisor_id??'')->first();
            $area=Area::where('id',$warehouse->area??'')->first();
            
            $user->warehouse=$warehouse;
            $user->area=$area;
            $user->suppervisor=$supervisorDetails;


            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                ],
                'message' => 'Profile details',
            ]);
        }
     

  
        
   public function CheckIn(Request $request){

    $input = $request->all();
    
    $validator = Validator::make($input, [
        'salesman_id'=>'required|numeric', 
        'image'=>'required',
        'location'=>'required',
        'customer_id'=>'required',
        'uploaded_date'=>'required',
    ]);

    if ($validator->fails()) {
        return $this->SendError($validator->messages());
    }   
        try {

           if ($request->hasFile('image')){
                   $image = $request->image;              
                    $image_name_with_ext = rand().$image->getClientOriginalName();
                    $destinationPath = 'mileagerecord';
                    $image->move(public_path($destinationPath), $image_name_with_ext);
                    $vehicle_image=url("mileagerecord/")."/".$image_name_with_ext;
                    $input['image']=$vehicle_image;
            }
           $checkIn= CheckIn::create($input);

           if($checkIn){
              $checkIn->update(['unique_code'=>'CHKIN_'.$checkIn->id]);
           }


            return $this->sendSuccess('Check in Store successfully');
           
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }


    public function Checkout(Request $request){

        $input = $request->all();
        
        $validator = Validator::make($input, [
            'salesman_id'=>'required|numeric', 
            'image'=>'required',
            'location'=>'required',
            'customer_id'=>'required',
            'uploaded_date'=>'required',

        ]);
    
        if ($validator->fails()) {
            return $this->SendError($validator->messages());
        }   
            try {
    
               if ($request->hasFile('image')){
                       $image = $request->image;              
                        $image_name_with_ext = rand().$image->getClientOriginalName();
                        $destinationPath = 'mileagerecord';
                        $image->move(public_path($destinationPath), $image_name_with_ext);
                        $vehicle_image=url("mileagerecord/")."/".$image_name_with_ext;
                        $input['image']=$vehicle_image;
                }
                 $checkOut=CheckOut::create($input);
                if($checkOut){
                    $checkOut->update(['unique_code'=>'CHKOUT_'.$checkOut->id]);
                 }
                return $this->sendSuccess('Check Out Store successfully');
               
            } catch (\Exception $e) {
                return $this->sendError($e->getMessage());
            }
        }
      
        public function CheckInList(Request $request,$salesman_id=null){           
            
            try {       
                 $data=CheckIn::with('customer','customer.channelDetails')->where('salesman_id',$salesman_id)->latest()->get();
                return $this->sendResponse($data,'CheckIn List Fetch successfully');
                   
                } catch (\Exception $e) {
                    return $this->sendError($e->getMessage());
                }
            }

            public function CheckOutList(Request $request,$salesman_id=null){           
            
                try {       
                     $data=CheckOut::with('customer','customer.channelDetails')->where('salesman_id',$salesman_id)->latest()->get();
                     return $this->sendResponse($data,'CheckOut List Fetch successfully');
                       
                    } catch (\Exception $e) {
                        return $this->sendError($e->getMessage());
                    }
                }



                public function CheckInOutList(Request $request,$salesman_id=null){           
            
                    try {       
                        $checkIn=CheckIn::with('customer','customer.channelDetails')->where('salesman_id',$salesman_id);      
                        $checkOut=CheckOut::with('customer','customer.channelDetails')->where('salesman_id',$salesman_id);
                        $allData=$checkIn->union($checkOut)->latest()->get();
                        return $this->sendResponse($allData,'Check In/Out List Fetch successfully'); 
                           
                        } catch (\Exception $e) {
                            return $this->sendError($e->getMessage());
                        }
                    }   
    
                    public function UpdateSalesmanTrip(Request $request){

                        $input = $request->all();
                        
                        $validator = Validator::make($input, [
                            'assign_customer_id'=>'required|numeric', 
                            'salesman_id'=>'required|numeric', 
                            'customer_id'=>'required',
                            'status'=>'required',
                        ]);
                    
                        if ($validator->fails()) {
                            return $this->SendError($validator->messages());
                        }   
                            try {
                    
                               $checkIn= AssignCustomersList::where('assign_customer_id',$input['assign_customer_id'])
                                ->where('salesman_id',$input['salesman_id'])
                                ->where('customer_id',$input['customer_id'])
                                ->update(['status'=>$input['status']]);                   
                                       
                                return $this->sendSuccess('Trip status Updated successfully');
                               
                            } catch (\Exception $e) {
                                return $this->sendError($e->getMessage());
                            }
                        }                
                    




}
