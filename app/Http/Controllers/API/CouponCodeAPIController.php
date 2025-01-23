<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\StoreCouponCodeRequest;
use App\Http\Requests\UpdateCouponCodeRequest;
use App\Http\Resources\CouponCodeCollection;
use App\Http\Resources\CouponCodeResource;
use App\Models\CouponCode;
use App\Repositories\CouponCodeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use Illuminate\Support\Carbon;

class CouponCodeAPIController extends AppBaseController
{
    /** @var CouponCodeRepository */
    private $couponCodeRepository;

    public function __construct(CouponCodeRepository $couponCodeRepository)
    {
        $this->couponCodeRepository = $couponCodeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = getPageSize($request);

        $sort = null;
        $sort_name = ltrim($request->sort, '-');
        if ($request->sort == $sort_name) {
            $sort = 'asc';
        } else {
            $sort = 'desc';
        }
        $couponCodes = $this->couponCodeRepository;

        $couponCodes = $couponCodes->paginate($perPage);
        CouponCodeResource::usingWithCollection();

        return new CouponCodeCollection($couponCodes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCouponCodeRequest $request)
    {
        $input = $request->all();
        $start= date('Y-m-d',strtotime($input['start_date']));
        $end= date('Y-m-d',strtotime($input['end_date']));
        $input['start_date']=$start;
        $input['end_date']=$end;
        $input['discount_type']=1;
        $couponCode = $this->couponCodeRepository->create($input);
        // $couponCode->products()->sync($input['products']);

        return new CouponCodeResource($couponCode);
    }


    public function show($id): CouponCodeResource
    {
        $brand = CouponCode::findOrFail($id);
        return new CouponCodeResource($brand);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(CouponCode $couponCode)
    {
        return new CouponCodeResource($couponCode);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCouponCodeRequest $request, CouponCode $couponCode)
    {
        $input = Arr::except($request->all(), 'products');
        $start= date('Y-m-d',strtotime($input['start_date']));
        $end= date('Y-m-d',strtotime($input['end_date']));
        $input['start_date']=$start;
        $input['end_date']=$end;
        $input['discount_type']=1;

        $this->couponCodeRepository->where('id', $couponCode->id)->update($input);
        // $couponCode->products()->sync($request->products);

        return new CouponCodeResource($couponCode);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(CouponCode $couponCode)
    {
        $couponCode->delete();

        return $this->sendSuccess('Coupon code deleted successfully.');
    }
}
