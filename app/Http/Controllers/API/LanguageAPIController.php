<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateLanguageRequest;
use App\Http\Requests\UpdateLanguageRequest;
use App\Http\Resources\LanguageCollection;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use App\Models\User;
use App\Repositories\LanguageRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Illuminate\Support\Facades\Validator;
use App\Models\Region;
use Illuminate\Support\Facades\Auth;

class LanguageAPIController extends AppBaseController
{
    /** @var languageRepository */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function index(Request $request): LanguageCollection
    {
        $perPage = getPageSize($request);

        $languages = $this->languageRepository;

        $languages = $languages->paginate($perPage);

        LanguageResource::usingWithCollection();

        return new LanguageCollection($languages);
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

    public function store(CreateLanguageRequest $request): LanguageResource
    {
        $input = $request->all();

        $language = $this->languageRepository->storeLanguage($input);

        return new LanguageResource($language);
    }

    public function show(Language $language): LanguageResource
    {
        return new LanguageResource($language);
    }

    public function edit(Language $language): LanguageResource
    {
        return new LanguageResource($language);
    }

    public function update(UpdateLanguageRequest $request, Language $language): LanguageResource
    {
        if ($language->is_default == true) {
            return $this->sendError('Default Language can\'t be change.');
        }

        $input = $request->all();

        $language = $this->languageRepository->updateLanguage($input, $language);

        return new LanguageResource($language);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Language $language)
    {
        try {

            DB::beginTransaction();

            if ($language->is_default == true) {
                return $this->sendError('Default Language can\'t be deleted.');
            }

            $usesLang = User::pluck('language')->toArray();
            if (in_array($language->iso_code, $usesLang)) {
                return $this->sendError('Uses Language can\'t be deleted.');
            }
            if ($language->iso_code == getSettingValue('default_language')) {
                return $this->sendError('Default Setting Language can\'t be deleted.');
            }

            // json file delete
            $path = resource_path('pos/src/locales/'.$language->iso_code.'.json');
            if (\File::exists($path)) {
                \File::delete($path);
            }

            // php directory delete
            $directoryPath = base_path('lang/').$language->iso_code;
            if (\File::exists($directoryPath)) {
                \File::deleteDirectory($directoryPath);
            }

            $language->delete();

            DB::commit();

            return $this->sendSuccess('Language Deleted successfully');

        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function showTranslation(Language $language)
    {
        $selectedLang = $language->iso_code;
        $langExists = $this->languageRepository->checkLanguageExistOrNot($selectedLang);
        if (! $langExists) {
            throw new UnprocessableEntityHttpException($selectedLang.' language file not found.');
        }

        $data['id'] = $language->id;
        $data['iso_code'] = $language->iso_code;
        $data['lang_json_array'] = $this->languageRepository->getFileData($selectedLang);
        $data['lang_php_array'] = $this->languageRepository->getPhpFileData($selectedLang);

        return $this->sendResponse($data, 'Language retrieved successfully');

    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateTranslation(Language $language, Request $request)
    {
        try {
            $isoCode = $language->iso_code;

            if (! empty($request->get('lang_json_array'))) {
                $fileExists = $this->languageRepository->checkLanguageExistOrNot($isoCode);

                if (! $fileExists) {
                    return $this->sendError('Json File not found.');
                }

                if (! empty($isoCode)) {
                    $langJson = json_encode($request->lang_json_array, JSON_PRETTY_PRINT);

                    File::put(resource_path('pos/src/locales/').$isoCode.'.json', $langJson);
                }
            }

            if (! empty($request->get('lang_php_array'))) {
                $fileExists = $this->languageRepository->checkPhpDirectoryExistOrNot($isoCode);

                if (! $fileExists) {
                    return $this->sendError('Directory not found.');
                }

                if (! empty($isoCode)) {
                    $result = $request->get('lang_php_array');
                    File::put(base_path('lang/').$isoCode.'/messages.php', '<?php return '.var_export($result, true).'?>');
                }
            }

            return $this->sendSuccess('Language updated successfully');
        } catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    // public function addRegion(Request $request){
    //     $input = $request->all();

    //     $validator = Validator::make($input, [
    //         'country'=>'required',
    //         'name'=>'required',
    //         'status'=>'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->SendError($validator->messages());
    //     }
    //     try{
    //             DB::table('regions')->insert($input);
    //             return $this->sendSuccess('region Store successfully');

    //         } catch (\Exception $e) {
    //             return $this->sendError($e->getMessage());
    //         }
    // }


    public function addRegion(Request $request)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
            'country' => 'required',
            'name' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages());
        }

        try {

            $existingRegion = DB::table('regions')->where('name', $input['name'])->first();

            if ($existingRegion) {
                return $this->sendError('A region with the same name already exists.');
            }

            DB::table('regions')->insert($input);

            return $this->sendSuccess('Region stored successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }


    public function fetchRegions(Request $request)
    {
        $perPage = getPageSize($request);
        $search = $request->filter['search'] ?? '';
        $userDetails = Auth::user();
        $country = $userDetails->country;

        try {
            $query = Region::with('country')
            ->where('country', $country)
            ->orderBy('id', 'desc');

            $sort = null;
            $sort_name = ltrim($request->sort, '-');
            if ($request->sort == $sort_name) {
                $sort = 'asc';
            } else {
                $sort = 'desc';
            }

            $query = Region::with(['country']);

            if ($sort_name) {
                $query ->orderBy($sort_name, $sort);
            } else {
                $query->orderBy('id', 'desc');
            }

            if (!empty($search)) {
                $query->where('name', 'LIKE', "%$search%");
            }

            $region = $query
            ->orderBy('id', 'desc')
            ->paginate($perPage);

            return $this->sendResponse($region, 'Regions list retrieved successfully');
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function fetchRegionsList(Request $request)
    {
        $search = $request->filter['search'] ?? '';
        $userDetails = Auth::guard('passport')->user();
        $country = $userDetails->country;

        try {
           $sort = null;
           $sort_name = ltrim($request->sort, '-');
            if ($request->sort == $sort_name) {
                $sort = 'asc';
            } else {
                $sort = 'desc';
            }

            $query = Region::with('country')
                ->where('country', $country)
                ->where('status', 'Active');

            if ($sort_name) {
                $query->orderBy($sort_name, $sort);
            } else {
                $query->orderBy('id', 'desc');
            }

            if (!empty($search)) {
                $query->where('name', 'LIKE', "%$search%");
            }

            $regions = $query->get();

            return $this->sendResponse($regions, 'Regions list retrieved successfully');
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }



    public function deleteRegions(Request $request,$id=null){
        $region = Region::find($id)->delete();
        return $this->sendSuccess($region ,'Region deleted  Successfully');
    }


    public function editRegion(Request $request, $id = null)
    {
       $input = $request->all();

        $validator = Validator::make($input, [
            'country' => 'required',
            'name' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages());
        }

        try {
            $existingRegion = Region::where('name', $input['name'])
                                    ->where('id', '!=', $id)
                                    ->first();

            if ($existingRegion) {
                return $this->sendError('A region with the same name already exists.');
            }

            Region::where('id', $id)->update($input);

            $perPage = getPageSize($request);
            $region = Region::latest()->paginate($perPage);

            return $this->sendResponse($region, 'Region updated successfully');
        } catch (\Exception $e) {
           return $this->sendError($e->getMessage());
        }
    }


    public function fetchRegion(Request $request,$id=null){
        $input = $request->all();
        try{
                $region =  DB::table('regions')->where('id',$id)->get();
                return $this->sendResponse($region,'Region fetched successfully');

            } catch (\Exception $e) {
                return $this->sendError($e->getMessage());
            }
    }





}
