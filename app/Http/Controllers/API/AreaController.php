<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Area;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AreaController extends AppBaseController
{
//    public function index(Request $request){

//     $perPage = getPageSize($request);
//     $data=Area::with('region')->latest()->paginate($perPage);
//     return $this->sendResponse($data ,'Retrieved Successfully');
//     }

public function index(Request $request)
{
    $perPage = getPageSize($request);
    $search = $request->filter['search'] ?? '';
    $page=$request->get('page');
    $pageNumber=$page['number']??1;
    $userDetails = Auth::user();
    $country = $userDetails->country;

    try {
        $query = Area::with(['region.country'])
            ->whereHas('region', function ($q) use ($country) {
                $q->where('country', $country);
            })
            ->latest();


        $sort = null;
        $sort_name = ltrim($request->sort, '-');
        if ($request->sort == $sort_name) {
            $sort = 'asc';
        } else {
            $sort = 'desc';
        }

        $query = Area::with(['region.country']);

        if ($sort_name) {
            $query ->orderBy($sort_name, $sort);
        } else {
            $query->orderBy('id', 'desc');
        }

        if ($search) {
            $query->where('name', 'LIKE', "%$search%")
                ->orWhereHas('region', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                });
        }

        $data = $query->paginate($perPage,['*'], 'page',$pageNumber);

        return $this->sendResponse($data, 'Retrieved Successfully');
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error fetching areas.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    // public function AreaList(Request $request){

    //     $data=Area::with(['region', 'subAreas'])->latest()->get();
    //     return $this->sendResponse($data ,'Retrieved Successfully');
    // }


    public function AreaList(Request $request)
    {
        $data = Area::with(['region', 'subAreas' => function ($query) {
            $query->where('status', 1);
        }])
        ->where('status', 1)
        ->latest()
        ->get();

        return $this->sendResponse($data, 'Retrieved Successfully');
    }





    // public function fetchCountries()
    // {
    //         try {
    //             $countries = Country::where('status', 'active')->get();
    //             return response()->json(['status' => 'success', 'data' => $countries], 200);
    //         } catch (\Exception $e) {
    //             return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //         }
    // }


    public function fetchCountries(Request $request)
    {

       $search = $request->filter['search'] ?? '';
       try {

            $query = Country::where('status', 'active');

            if($search){
                 $query->where('name', 'LIKE', "%$search%")
                 ->orWhere('phone_code', 'LIKE', "%$search%")
                 ->orWhere('short_code', 'LIKE', "%$search%");
            }

            $countries = $query->get();

            return response()->json(['status' => 'success', 'data' => $countries], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }






        public function updateCountry(Request $request)
        {
            $validated = $request->validate([
                'country_id' => 'required|exists:countries,id',
            ]);

            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            $country = Country::find($validated['country_id']);

            if (!$country) {
                return response()->json(['message' => 'Country not found'], 404);
            }

            $user->country = $country->id;
            $user->save();

            return response()->json([
                'message' => 'Country updated successfully',
                'user' => $user,
            ]);
        }


    // function AddArea(Request $request){

    //     Area::create([
    //       "name"=>$request->name,
    //       "region_id"=>$request->region_id,
    //       "status" => $request->status,
    //     ]);
    //     return $this->sendSuccess('Area added successfully');

    // }


    public function AddArea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'region_id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages());
        }

        $existingArea = Area::where('name', $request->name)->first();

        if ($existingArea) {
            return $this->sendError('An area with the same name already exists.');
        }

        try {
            Area::create([
                "name" => $request->name,
                "region_id" => $request->region_id,
                "status" => $request->status,
            ]);

              return $this->sendSuccess('Area added successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }


    function fetchArea(Request $request,$id=null){
        $data= Area::find($id);
        return $this->sendResponse($data,'Retrieved Successfully');

    }


    public function EditArea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:areas,id',
            'name' => 'required',
            'region_id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages());
        }

        $existingArea = Area::where('name', $request->name)
                            ->where('id', '!=', $request->id)
                            ->first();

        if ($existingArea) {
            return $this->sendError('An area with the same name already exists.');
        }

        try {
            $data = Area::where('id', $request->id)
                        ->update([
                            'name' => $request->name,
                            'region_id' => $request->region_id,
                            'status' => $request->status,
                        ]);

           return $this->sendResponse($data, 'Updated Successfully');
        } catch (\Exception $e) {
           return $this->sendError($e->getMessage());
        }
    }



    function DeleteArea(Request $request,$id=null){
        $data= Area::where('id',$id)->delete();
        return $this->sendResponse($data,'deleted Successfully');

    }



}
