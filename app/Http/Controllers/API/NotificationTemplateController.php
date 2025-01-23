<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\NotificationTemplate;

class NotificationTemplateController extends Controller
{

   public function index(Request $request){

    $perPage = getPageSize($request);
    $page=$request->get('page');
    $pageNumber=$page['number']??1;
    $search = $request->filter['search'] ?? '';
    $query= NotificationTemplate:: query();

    $sort_name = ltrim($request->sort, '-');
    $sort = $request->sort == $sort_name ? 'asc' : 'desc';

    if ($search) {
        $query->where(function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                  ->orWhere('type', 'like', '%' . $search . '%');
        });
    }

    if ($sort_name) {
        $query->orderBy($sort_name, $sort);
    } else {
        $query->orderBy('id', 'desc');
    }

    $notificationTemplates = $query->latest()->paginate($perPage,['*'], 'page',$pageNumber);

    return response()->json([
        'meassage'=> 'Notification templates retrieved successfully',
        'data' => $notificationTemplates
    ]);

   }

   public function show($id)
   {
       try {
           $template = NotificationTemplate::findOrFail($id);

           return response()->json([
               'success' => true,
               'data' => $template,
           ], 200);
       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Resource not found',
           ], 404);
       }
   }





    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'cn_title' => 'required|string|max:255',
            'bn_title' => 'required|string|max:255',
            'content' => 'required|string',
            'cn_content' => 'required|string',
            'bn_content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $template = NotificationTemplate::create($request->all());

        return response()->json([
            'message' => 'Notification Template created successfully!',
            'data' => $template,
        ], 201);
    }


    public function update(Request $request, $id)
{
    $template = NotificationTemplate::find($id);

    if (!$template) {
        return response()->json([
            'message' => 'Notification Template not found.',
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'type' => 'required|string|max:255',
        'cn_title' => 'required|string|max:255',
        'bn_title' => 'required|string|max:255',
        'content' => 'required|string',
        'cn_content' => 'required|string',
        'bn_content' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Update the template with the validated data
    $template->update($request->all());

    return response()->json([
        'message' => 'Notification Template updated successfully!',
        'data' => $template,
    ], 200);
}



}
