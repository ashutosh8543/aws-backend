<?php

namespace App\Http\Controllers\API\M1;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Chanel;

class ChanelController extends AppBaseController
{
   


    public function ChannelList(Request $request)
    {
            $data=chanel::where('status','Active')->latest()->get();
            return $this->sendResponse($data ,'Retrieved Successfully');

    }

    

    // function AddChanels(Request $request){

    //     Chanel::create([
    //       "name"=>$request->name,
    //       "status"=>$request->status,
    //     ]);
    //     return $this->sendSuccess('Channel added successfully');
        
    // }

    // function fetchChanel(Request $request,$id=null){              
    //     $data= Chanel::find($id);
    //     return $this->sendResponse($data,'retrieved Successfully');       
        
    // }
    // function EditChanel(Request $request){
       
    //     $data= Chanel::where('id',$request->id)->update(['name'=>$request->name,'status'=>$request->status]);
    //     return $this->sendResponse($data,'Updated Successfully');       
        
    // }

    
    // function DeleteChanel(Request $request,$id=null){              
    //     $data= Chanel::where('id',$id)->delete();
    //     return $this->sendResponse($data,'deleted Successfully');       
        
    // }



 

}
