<?php

namespace App\Http\Controllers\API\M1;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Area;
use App\Models\SubArea;


class AreaController extends AppBaseController
{
   public function index(Request $request){
       
    $perPage = getPageSize($request);     
    $data=Area::with('region')->latest()->get();
    return $this->sendResponse($data ,'Retrieved Successfully');      
    }
  
        public function AreaList(Request $request){
       
        $data=Area::with('region')->latest()->get();
        return $this->sendResponse($data ,'Retrieved Successfully');      
        }

        public function SubAreaList(Request $request){
            $area_id=$request->get('area_id');
            $data=SubArea::with(['area','area.region'])->where('area_id',$area_id)->latest()->get();
            return $this->sendResponse($data ,'Retrieved Successfully');      
        }

    // function AddArea(Request $request){

    //     Area::create([
    //       "name"=>$request->name,
    //       "region_id"=>$request->region_id,
    //     ]);
    //     return $this->sendSuccess('Area added successfully');
        
    // }

    // function fetchArea(Request $request,$id=null){              
    //     $data= Area::find($id);
    //     return $this->sendResponse($data,'Retrieved Successfully');       
        
    // }
    // function EditArea(Request $request){
       
    //     $data= Area::where('id',$request->id)->update(['name'=>$request->name,'region_id'=>$request->region_id]);
    //     return $this->sendResponse($data,'Updated Successfully');       
        
    // }

    
    // function DeleteArea(Request $request,$id=null){              
    //     $data= Area::where('id',$id)->delete();
    //     return $this->sendResponse($data,'deleted Successfully');       
        
    // }



}
