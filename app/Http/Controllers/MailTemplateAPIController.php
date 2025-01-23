<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailTemplateUpdateRequest;
use App\Http\Resources\MailCollection;
use App\Http\Resources\MailResource;
use App\Models\MailTemplate;
use App\Models\AdminEmailTemplate;
use App\Repositories\MailRepository;
use Illuminate\Http\Request;

class MailTemplateAPIController extends AppBaseController
{
    /** @var mailRepository */
    private $mailRepository;

    public function __construct(MailRepository $mailRepository)
    {
        $this->mailRepository = $mailRepository;
    }

    public function index(Request $request): MailCollection
    {
        $perPage = getPageSize($request);

        $mailTemplates = $this->mailRepository;

        $mailTemplates = $mailTemplates->paginate($perPage);

        MailResource::usingWithCollection();

        return new MailCollection($mailTemplates);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'template_name' => 'required|string|max:255',
            'content' => 'required|string',
            'template_type'=> 'required|string'
        ]);

        try {
            $template = new MailTemplate();
            $template->template_name = $validatedData['template_name'];
            $template->content = $validatedData['content'];
            $template->template_type = $validatedData['template_type'];
            $template->save();

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function admintemplate(Request $request)
    {
        $validatedData = $request->validate([
            'template_name' => 'required|string|max:255',
            'content' => 'required|string',
            'template_type'=> 'required|string'
        ]);

        try {
            $template = new AdminEmailTemplate();
            $template->template_name = $validatedData['template_name'];
            $template->content = $validatedData['content'];
            $template->template_type = $validatedData['template_type'];
            $template->save();

            return response()->json([
                'success' => true,
                'message' => ' Admin Template created successfully',
                'data' => $template,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchAdminEmailTemplate(Request $request){
        $perPage = getPageSize($request);
        $page=$request->get('page');
        $pageNumber=$page['number']??1;
        $search = $request->filter['search'] ?? '';

        $sort_name = ltrim($request->sort, '-');
        $sort = $request->sort == $sort_name ? 'asc' : 'desc';

        try {
            $query = AdminEmailTemplate::latest();

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('template_name', 'like', '%' . $search . '%')
                          ->orWhere('template_type', 'like', '%' . $search . '%');
                });
            }

            if ($sort_name) {
                $query->orderBy($sort_name, $sort);
            } else {
                $query->orderBy('id', 'desc');
            }

            $adminEmailTemplates = $query->paginate($perPage,['*'], 'page',$pageNumber);

            return response()->json([
                'success' => true,
                'data' => $adminEmailTemplates,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sub-areas: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fetchAdminEmailTemplateDetails($id)
    {
       try {

            $adminEmailTemplate = AdminEmailTemplate::find($id);

            if (!$adminEmailTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin Email Template not found.',
                ], 404);
            }

            return response()->json([
                    'success' => true,
                    'data' => $adminEmailTemplate,
                ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admin email template details: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function updateAdminTemplate(Request $request, $id)
{
    $validatedData = $request->validate([
        'template_name' => 'required|string|max:255',
        'content' => 'required|string',
        'template_type' => 'required|string',
        'status' => 'required|boolean',
    ]);

    try {
        $template = AdminEmailTemplate::find($id);

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
            ], 404);
        }

        $template->template_name = $validatedData['template_name'];
        $template->content = $validatedData['content'];
        $template->template_type = $validatedData['template_type'];
        $template->status = $validatedData['status'];
        $template->save();

        return response()->json([
            'success' => true,
            'message' => 'Admin Template updated successfully',
            'data' => $template,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update template',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
    }

    public function edit(MailTemplate $mailTemplate): MailResource
    {
        return new MailResource($mailTemplate);
    }

    public function update(MailTemplateUpdateRequest $request, $id): MailResource
    {
        $input = $request->all();

        $mailTemplate = $this->mailRepository->updateMailTemplate($input, $id);

        return new MailResource($mailTemplate);
    }

    public function changeActiveStatus($id): MailResource
    {
        $mailTemplate = MailTemplate::findOrFail($id);
        $status = ! $mailTemplate->status;
        $mailTemplate->update(['status' => $status]);

        return new MailResource($mailTemplate);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }
}
