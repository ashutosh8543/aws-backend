<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubArea;

class SubAreaController extends Controller
{
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'sub_area_name' => 'required|string|max:255',
    //         'area_id' => 'required|exists:areas,id',
    //     ]);

    //     try {

    //         $subArea = SubArea::create([
    //             'sub_area_name' => $request->input('sub_area_name'),
    //             'area_id' => $request->input('area_id'),
    //         ]);

    //         return response()->json([
    //             'message' => 'Sub-Area added successfully!',
    //             'data' => $subArea
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'An error occurred while adding the sub-area.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function store(Request $request)
    // {

    //     $request->validate([
    //         'sub_area_name' => 'required|string|max:255',
    //         'area_id' => 'required|exists:areas,id',
    //         'status' => 'required|in:0,1',
    //     ]);

    //     try {
    //         $subArea = SubArea::create([
    //             'sub_area_name' => $request->input('sub_area_name'),
    //             'area_id' => $request->input('area_id'),
    //             'status' => $request->input('status'),
    //         ]);

    //         return response()->json([
    //             'message' => 'Sub-Area added successfully!',
    //             'data' => $subArea
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'An error occurred while adding the sub-area.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function store(Request $request)
    {
        $request->validate([
            'sub_area_name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
            'status' => 'required|in:0,1',
        ]);


        $existingSubArea = SubArea::where('sub_area_name', $request->sub_area_name)
                              ->where('area_id', $request->area_id)
                              ->first();

        if ($existingSubArea) {
            return response()->json([
                'message' => 'A sub-area with the same name already exists in this area.',
            ], 400);
        }

        try {
            $subArea = SubArea::create([
                'sub_area_name' => $request->input('sub_area_name'),
                'area_id' => $request->input('area_id'),
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'message' => 'Sub-Area added successfully!',
                'data' => $subArea
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while adding the sub-area.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function index(Request $request)
    {
        $perPage = getPageSize($request);
        $page=$request->get('page');
        $pageNumber=$page['number']??1;
        $search = $request->filter['search'] ?? '';
        try {
            $query = SubArea::with(['areaDetails', 'area.region.country'])->latest();


            $sort = null;
            $sort_name = ltrim($request->sort, '-');
            if ($request->sort == $sort_name) {
                $sort = 'asc';
            } else {
                $sort = 'desc';
            }

            $query = SubArea::with(['areaDetails', 'area.region.country']);

            if ($sort_name) {
                $query ->orderBy($sort_name, $sort);
            } else {
                $query->orderBy('id', 'desc');
            }

            if ($search) {
                $query->where('sub_area_name', 'LIKE', "%$search%")
                    ->orWhereHas('area', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })
                    ->orWhereHas('area.region', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    });
            }

            $subAreas = $query->paginate($perPage,['*'], 'page',$pageNumber);

            return response()->json([
                'success' => true,
                'data' => $subAreas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sub-areas: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function subAreaList()
    {
        try {
            $subAreas = SubArea::with(['areaDetails', 'area.region'])
            ->where('status', 1)
            ->latest()
            ->paginate();

            return response()->json([
                'success' => true,
                'data' => $subAreas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sub-areas: ' . $e->getMessage(),
            ], 500);
        }
    }




    public function show($id)
    {
       try {

            $subArea = SubArea::find($id);

            if (!$subArea) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sub-Area not found.',
                ], 404);
            }

            return response()->json([
                    'success' => true,
                    'data' => $subArea,
                ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sub-area: ' . $e->getMessage(),
            ], 500);
        }
    }


    // public function updateSubAreaName(Request $request, $id)
    // {
    //     $request->validate([
    //         'sub_area_name' => 'required|string|max:255',
    //     ]);

    //     try {
    //         $subArea = SubArea::findOrFail($id);

    //         $subArea->update([
    //         'sub_area_name' => $request->input('sub_area_name'),
    //         'area_id' => $request->input('area_id'),
    //         ]);

    //         return response()->json([
    //             'message' => 'Sub-Area updated successfully!',
    //             'data' => $subArea
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'An error occurred while updating the sub-area name.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function updateSubAreaName(Request $request, $id)
    {
        $request->validate([
            'sub_area_name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
            'status' => 'required|in:0,1',
        ]);

        try {
           $subArea = SubArea::findOrFail($id);

            $subArea->update([
                'sub_area_name' => $request->input('sub_area_name'),
                'area_id' => $request->input('area_id'),
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'message' => 'Sub-Area updated successfully!',
                'data' => $subArea
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the sub-area name.',
                'error' => $e->getMessage()
            ], 500);
        }
    }





}
