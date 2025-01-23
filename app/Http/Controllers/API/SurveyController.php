<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Survey;
use App\Models\QuestionOption;
use App\Models\CheckIn;
use App\Models\CheckOut;
use Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyController extends AppBaseController
{



    public function SurveyList(Request $request)
{
    $perPage = getPageSize($request);
    $page = $request->get('page');
    $pageNumber = $page['number'] ?? 1;
    $startDate = $request->get('start_date');
    $endDate = $request->get('end_date');
    $search = $request->filter['search'] ?? '';

    try {
        $query = Survey::with([
            'salesmanDetails' => function ($query) {
                $query->select(['id', 'first_name', 'last_name']);
            },
            'surveyHistory',
            'customerDetails' => function ($query) {
                $query->select(['id', 'name', 'email', 'phone']);
            }
        ])->latest();

            $sort = null;
            $sort_name = ltrim($request->sort, '-');
            if ($request->sort == $sort_name) {
                $sort = 'asc';
            } else {
                $sort = 'desc';
            }

            $query = Survey::with(['salesmanDetails', 'surveyHistory', 'customerDetails' ]);

            if ($sort_name) {
                $query ->orderBy($sort_name, $sort);
            } else {
                $query->orderBy('id', 'desc');
            }


        if ($search) {
            $searchTerms = explode(' ', $search);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQuery) use ($term) {
                        $subQuery->orWhereHas('salesmanDetails', function ($salesmanQuery) use ($term) {
                            $salesmanQuery->where('first_name', 'like', '%' . $term . '%')
                                          ->orWhere('last_name', 'like', '%' . $term . '%');
                        })
                        ->orWhereHas('customerDetails', function ($customerQuery) use ($term) {
                            $customerQuery->where('name', 'like', '%' . $term . '%');
                        });
                    });
                }
            });
        }


        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $surveys = $query->paginate($perPage, ['*'], 'page', $pageNumber);

        return $this->sendResponse($surveys, 'Surveys retrieved successfully.');
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch surveys: ' . $e->getMessage(),
        ], 500);
    }
}



    public function SurveyDetails(Request $request,$id=null){

        $survey = Survey::with(['salesmanDetails'=>function($query){
            $query->select(['id','first_name','last_name']);
         },'surveyHistory'])->where('id',$id)->first();
         return $this->sendResponse($survey, 'Question retrieved Successfully');
    }

    // public function QuestionList(Request $request){

    //     $perPage = getPageSize($request);
    //     $question = Question::with(['options'])->latest()->paginate($perPage);
    //     return $this->sendResponse($question, 'Question retrieved Successfully');

    // }


    public function QuestionList(Request $request)
    {
        $perPage = getPageSize($request);
        $search = $request->filter['search'] ?? '';
        $page=$request->get('page');
        $pageNumber=$page['number']??1;
        try {
            $query = Question::with(['options', 'warehouse'])->latest();


            $sort = null;
            $sort_name = ltrim($request->sort, '-');
            if ($request->sort == $sort_name) {
                $sort = 'asc';
            } else {
                $sort = 'desc';
            }

            $query = Question::with(['options', 'warehouse']);

            if ($sort_name) {
                $query ->orderBy($sort_name, $sort);
            } else {
                $query->orderBy('id', 'desc');
            }

            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('question', 'like', '%' . $search . '%')
                          ->orWhere('type', 'like', '%' . $search . '%');
                });
            }

            $questions = $query->paginate($perPage,['*'], 'page',$pageNumber);

            return $this->sendResponse($questions, 'Questions retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch questions: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function getQuestionById($id)
    {
         $question = Question::with(['options'])->find($id);

         if (!$question) {
             return $this->sendError('Question not found', 404);
         }

         return $this->sendResponse($question, 'Question retrieved successfully');
    }





    // public function addQuestionOption(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'question' => 'required',
    //         'option' => 'required|array|min:1',
    //     ]);

    //     if($validator->fails()){
    //         return response()->json([
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }
    //     $question = Question::create([
    //         'question' => $request->question,
    //         'status' => "Active",
    //     ]);

    //     foreach ($request->option as $opt) {
    //         QuestionOption::create([
    //             'question_id'=>$question->id,
    //             'option' => $opt['option'],
    //         ]);
    //     }
    //     return response()->json([
    //         'message' => 'Question Added successfully.',
    //     ], 201);
    // }


    // public function addQuestionOption(Request $request) {
    //     $validator = Validator::make($request->all(), [
    //         'question' => 'required',
    //         'option' => 'required|array|min:1',
    //         'status' => 'required|in:Active,Inactive',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $question = Question::create([
    //         'question' => $request->question,
    //         'status' => $request->status,
    //     ]);

    //     foreach ($request->option as $opt) {
    //         QuestionOption::create([
    //             'question_id' => $question->id,
    //             'option' => $opt['option'],
    //         ]);
    //     }

    //     return response()->json([
    //         'message' => 'Question added successfully.',
    //     ], 201);
    // }

    public function addQuestionOption(Request $request) {
        // dd($request->distributor['value']);
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'type' => 'required|string',
            'option' => 'array|min:1',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $question = Question::create([
            'question' => $request->question,
            'type' => $request->type,
            'status' => $request->status,
            'distributor_id'=>$request->distributor['value'],
            'warehouse_id'=>$request->warehouse['value']
        ]);

           foreach ($request->option as $opt) {
           QuestionOption::create([
                'question_id'=>$question->id,
                'option' => $opt['option'],
                 ]);
            }

        return response()->json([
            'message' => 'Question added successfully.',
        ], 201);
    }




    // public function CheckInList(Request $request)
    // {
    //     $perPage = getPageSize($request);
    //     $startDate = $request->get('start_date');
    //     $endDate = $request->get('end_date');

    //     $checkInQuery = CheckIn::with([
    //         'salesman' => function ($query) {
    //             $query->select(['id', 'first_name', 'last_name']);
    //          },
    //          'customer'
    //         ])->latest();


    //      if ($startDate && $endDate) {
    //          $checkInQuery->whereBetween('created_at', [$startDate, $endDate]);
    //      }

    //       $survey = $checkInQuery->paginate($perPage);
    //       return $this->sendResponse($survey, 'Check-in retrieved successfully');
    // }


    public function CheckInList(Request $request)
    {
        $perPage = getPageSize($request);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->filter['search'] ?? '';
        $page=$request->get('page');
        $pageNumber=$page['number']??1;

        $checkInQuery = CheckIn::with([
            'salesman' => function ($query) {
                $query->select(['id', 'first_name', 'last_name']);
            },
            'customer' => function ($query) {
                $query->select(['id', 'name']);
            }
        ])->latest();


        $sort = null;
        $sort_name = ltrim($request->sort, '-');
        if ($request->sort == $sort_name) {
            $sort = 'asc';
        } else {
            $sort = 'desc';
        }

        $checkInQuery = CheckIn::with(['salesman', 'customer']);

        if ($sort_name) {
            $checkInQuery->orderBy($sort_name, $sort);
        } else {
            $checkInQuery->orderBy('id', 'desc');
        }

        if ($startDate && $endDate) {
            $checkInQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        if (!empty($search)) {
            $searchTerms = explode(' ', $search);
            $checkInQuery->where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->where(function ($subQuery) use ($term) {
                        $subQuery->whereHas('salesman', function ($q) use ($term) {
                            $q->where('first_name', 'like', '%' . $term . '%')
                              ->orWhere('last_name', 'like', '%' . $term . '%');
                        })
                        ->orWhereHas('customer', function ($q) use ($term) {
                            $q->where('name', 'like', '%' . $term . '%');
                        });
                    });
                }
            });
        }

        $survey = $checkInQuery->paginate($perPage,['*'], 'page',$pageNumber);
        return $this->sendResponse($survey, 'Check-in retrieved successfully');
    }





    public function checkinDetails($id)
    {
        $checkIn = CheckIn::with(['salesman' => function($query) {
            $query->select(['id', 'first_name', 'last_name']);
        }, 'customer'])->findOrFail($id);

        return $this->sendResponse($checkIn, 'Checkin details retrieved successfully');
    }


    public function checkoutDetails($id){

        $checkOut = CheckOut::with(['salesman'=>function($query){
            $query->select(['id','first_name','last_name']);
         },'customer'])->findOrFail($id);

         return $this->sendResponse($checkOut, 'Check Out  details retrieved successfully');
    }


    public function CheckOutList(Request $request){

        $perPage = getPageSize($request);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->filter['search'] ?? '';
        $page=$request->get('page');
        $pageNumber=$page['number']??1;

        $checkOutQuery = CheckOut::with(['salesman'=>function($query){
           $query->select(['id','first_name','last_name']);
        },
        'customer' => function ($query) {
            $query->select(['id', 'name']);
        }
        ])->latest();

        $sort = null;
        $sort_name = ltrim($request->sort, '-');
        if ($request->sort == $sort_name) {
            $sort = 'asc';
        } else {
            $sort = 'desc';
        }

        $checkOutQuery = CheckOut::with(['salesman', 'customer']);

        if ($sort_name) {
            $checkOutQuery->orderBy($sort_name, $sort);
        } else {
            $checkOutQuery->orderBy('id', 'desc');
        }

        if ($startDate && $endDate) {
            $checkOutQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        if (!empty($search)) {
            $searchTerms = explode(' ', $search);
            $checkOutQuery->where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->where(function ($subQuery) use ($term) {
                        $subQuery->whereHas('salesman', function ($q) use ($term) {
                            $q->where('first_name', 'like', '%' . $term . '%')
                              ->orWhere('last_name', 'like', '%' . $term . '%');
                        })
                        ->orWhereHas('customer', function ($q) use ($term) {
                            $q->where('name', 'like', '%' . $term . '%');
                        });
                    });
                }
            });
        }
        $survey = $checkOutQuery ->paginate($perPage,['*'], 'page',$pageNumber);
        return $this->sendResponse($survey, 'Checkin retrieved Successfully');

    }


    public function deleteQuestion($id)
    {
        $question = Question::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully'], 200);
    }




    public function updateQuestionOption(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'question' => 'required',
            'option' => 'nullable|array',
            'status' => 'required|in:Active,Inactive',
            'type'=> 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $question = Question::find($id);
        if (!$question) {
            return response()->json(['message' => 'Question not found.'], 404);
        }

        // Update the question details
        $question->update([
            'question' => $request->question,
            'status' => $request->status,
            'type'=>$request->type,
            'distributor_id'=>$request->distributor_id,
            'warehouse_id'=>$request->warehouse_id
        ]);

        if (isset($request->option) && is_array($request->option)) {
            $existingOptionIds = $question->options()->pluck('id')->toArray();
            $newOptionIds = [];

            foreach ($request->option as $opt) {
                \Log::info('Processing option:', $opt);

                if (isset($opt['id'])) {
                    $newOptionIds[] = $opt['id'];
                    $option = QuestionOption::withTrashed()->find($opt['id']);
                    if ($option) {
                        if ($option->trashed()) {
                            \Log::info('Creating new option for soft-deleted ID ' . $opt['id']);
                            $newOption = QuestionOption::create([
                                'question_id' => $question->id,
                                'option' => $opt['option'],
                            ]);
                            \Log::info('Created new option:', $newOption->toArray());
                        } else {
                            $option->update(['option' => $opt['option']]);
                            \Log::info('Updated existing option ID ' . $opt['id'] . ':', $option->toArray());
                        }
                    } else {
                        \Log::info('Option ID ' . $opt['id'] . ' not found, creating new option.');
                        $newOption = QuestionOption::create([
                            'question_id' => $question->id,
                            'option' => $opt['option'],
                        ]);
                        \Log::info('Created new option:', $newOption->toArray());
                    }
                } else {
                    if (isset($opt['option']) && !empty($opt['option'])) {
                        \Log::info('Creating new option without ID for question ID ' . $question->id);
                        $newOption = QuestionOption::create([
                            'question_id' => $question->id,
                            'option' => $opt['option'],
                        ]);
                        \Log::info('Created new option:', $newOption->toArray());
                    } else {
                        \Log::warning('Option data is missing or invalid.');
                    }
                }
            }

            $optionsToDelete = array_diff($existingOptionIds, $newOptionIds);
            foreach ($optionsToDelete as $idToDelete) {
                $optionToDelete = QuestionOption::withTrashed()->find($idToDelete);
                if ($optionToDelete && !$optionToDelete->trashed()) {
                    $optionToDelete->delete();
                    \Log::info("Soft deleted option with ID {$idToDelete}");
                }
            }
        }

        return response()->json(['message' => 'Question and options updated successfully.'], 200);
    }


















}
