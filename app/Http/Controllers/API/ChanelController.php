<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Chanel;
use Illuminate\Support\Facades\Validator;

class ChanelController extends AppBaseController
{

    public function index(Request $request)
    {
          
       try{ 
       $perPage = getPageSize($request);
       $search = $request->filter['search'] ?? '';
       $page=$request->get('page');
       $pageNumber=$page['number']??1;

       $sort_name = ltrim($request->sort, '-');
       $sort = $request->sort == $sort_name ? 'asc' : 'desc';

       $query = chanel::orderBy($sort_name,$sort);
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }
        // if ($sort_name) {
        //     // dd($sort_name,$sort);
        //     $query->orderBy($sort_name, $sort);
        // } else {
        //     $query->orderBy('id', 'desc');
        // }

        $data = $query->paginate($perPage,['*'], 'page',$pageNumber);

        return $this->sendResponse($data, 'Retrieved Successfully');
    }catch(\Exception $e){
        
    }
    }



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

    public function AddChanels(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages());
        }

        $existingChannel = Chanel::where('name', $request->name)->first();

        if ($existingChannel) {
            return $this->sendError('A channel with the same name already exists.');
        }

        try {
            Chanel::create([
                "name" => $request->name,
                "status" => $request->status,
            ]);

            return $this->sendSuccess('Channel added successfully');
        } catch (\Exception $e) {
           return $this->sendError($e->getMessage());
        }
    }


    function fetchChanel(Request $request,$id=null){
        $data= Chanel::find($id);
        return $this->sendResponse($data,'retrieved Successfully');

    }

    public function EditChanel(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
            'id' => 'required|exists:chanels,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages());
        }


        $existingChannel = Chanel::where('name', $request->name)
                             ->where('id', '!=', $request->id)
                             ->first();

        if ($existingChannel) {
            return $this->sendError('A channel with the same name already exists.');
        }

        try {
            $data = Chanel::where('id', $request->id)
                          ->update(['name' => $request->name, 'status' => $request->status]);

            return $this->sendResponse($data, 'Updated Successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }



    function DeleteChanel(Request $request,$id=null){
        $data= Chanel::where('id',$id)->delete();
        return $this->sendResponse($data,'deleted Successfully');

    }





}
