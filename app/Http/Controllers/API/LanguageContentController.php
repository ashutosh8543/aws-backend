<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LanguageContent;
use Illuminate\Support\Facades\Validator;

class LanguageContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */


     public function index(Request $request)
     {
         $perPage = getPageSize($request);
         $search = $request->filter['search'] ?? '';
         $page=$request->get('page');
         $pageNumber=$page['number']??1;

         $sort_name = ltrim($request->sort, '-');
         $sort = $request->sort == $sort_name ? 'asc' : 'desc';

         $query = LanguageContent::query();

         if ($search) {
             $query->where(function($q) use ($search) {
                 $q->where('string', 'like', '%' . $search . '%')
                   ->orWhere('en', 'like', '%' . $search . '%')
                   ->orWhere('cn', 'like', '%' . $search . '%')
                   ->orWhere('bn', 'like', '%' . $search . '%');
             });
         }


        if ($sort_name) {
            $query->orderBy($sort_name, $sort);
        } else {
            $query->orderBy('id', 'desc');
        }

         $languageContents = $query->orderBy('id','desc')->paginate($perPage,['*'], 'page',$pageNumber);
         return response()->json([
             'message' => 'Language Contents fetched successfully.',
             'data' => $languageContents,
         ], 200);
     }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'string' => 'required|string|max:255|unique:language_contents,string',
            'en' => 'required|string|max:255',
            'bn' => 'required|string|max:255',
            'cn' => 'required|string|max:255',
            'active' => 'required|boolean',
        ]);


        $activeStatus = $validated['active'] ? 1 : 0;

        $languageContent = LanguageContent::create([
            'string' => $validated['string'],
            'en' => $validated['en'],
            'bn' => $validated['bn'],
            'cn' => $validated['cn'],
            'active' => $activeStatus,
        ]);

        return response()->json([
            'message' => 'Language content created successfully!',
            'data' => $languageContent
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $languageContent = LanguageContent::find($id);

        if ($languageContent) {
            return response()->json([
                'success' => true,
                'data' => $languageContent
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Language content not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $languageContent = LanguageContent::find($id);

        if (!$languageContent) {
            return response()->json(['message' => 'Language content not found'], 404);
        }


        $validator = Validator::make($request->all(), [
            'string' => 'required|string',
            'en' => 'required|string',
            'bn' => 'required|string',
            'cn' => 'required|string',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $languageContent->string = $request->input('string');
        $languageContent->en = $request->input('en');
        $languageContent->bn = $request->input('bn');
        $languageContent->cn = $request->input('cn');
        $languageContent->active = $request->input('active', true);


        $languageContent->save();

        return response()->json([
            'message' => 'Language content updated successfully',
            'data' => $languageContent
        ], 200);
    }


    public function destroy(string $id)
    {
        //
    }
}
