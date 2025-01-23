<?php

namespace App\Http\Controllers\API\M1;

use App\Http\Controllers\AppBaseController;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Language;
use App\Models\Currency;
use App\Models\Warehouse;
use App\Models\Area;
use App\Models\Country;
use App\Models\Suppervisor;
use App\Models\UserLogin;


class AuthController extends AppBaseController
{
    public function login(Request $request): JsonResponse
    {
        $email = $request->get('email');
        $password = $request->get('password');

        if (empty($email) or empty($password)) {
            return $this->sendError('username and password required', 422);
        }
        $user = User::with(['salesman'])->whereRaw('lower(email) = ?', [$email])->first();

        if(empty($user)) {
            return $this->sendError('Invalid username or password', 422);
        }

        if (! Hash::check($password, $user->password)) {
            return $this->sendError('Invalid username or password', 422);
        }

        if($user->role_id!=6){
            return $this->sendError('You can not proceed further contact to administrator', 422);
        }
        if(!$user->status){
            return $this->sendError('You account is not active. Please contact your supervisor.', 422);
        }
        $user->update(['fcm_tocken'=>$request->get('fcm_tocken'),'language'=>$request->get('language')]);


        $token = $user->createToken('token')->accessToken;
        $user->last_name = $user->last_name ?? '';
        $language_get = Language::where('iso_code',$user->language)->first();
        $currency=Currency::select(['id','code','name','symbol'])->where('country_id',$user->country)->first();
        $user->language_name=$language_get->name;
        $user->currencies=$currency;
        $warehouse=Warehouse::where('ware_id',$user->salesman[0]['ware_id']??'')->first();
        // $supervisor=Suppervisor::where('ware_id',$user->salesman[0]['ware_id']??'')->first()??'';
        $supervisorDetails=User::where('id',$user->supervisor_id??'')->first();
        $area=Area::where('id',$warehouse->area??'')->first();
        $countryDetails=Country::find($user->country??'');

        $user->warehouse=$warehouse;
        $user->suppervisor=$supervisorDetails;
        $user->area=$area;
        $user->country_name=$countryDetails->name??'';

        $userlogin=$request->except(['email','password','language','fcm_tocken']);
        $userlogin['user_id']=$user->id;
        // dd($userlogin);
        UserLogin::insert($userlogin);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
            'message' => 'Logged in successfully.',
        ]);
    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->where('id', Auth::user()->currentAccessToken()->id)->delete();

        return $this->sendSuccess('Logout Successfully');
    }

    /**
     * @throws ValidationException
     */
    public function sendPasswordResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );
        $user = User::whereEmail($request->email)->first();
        if (! $user) {
            return $this->sendError('We can\'t find a user with that e-mail address.');
        }

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['success' => true, 'message' => __($status)], 200);
        } else {
            throw ValidationException::withMessages([
                'email' => 'Please Wait Before Trying',
            ]);
        }

        return $this->sendSuccess('Password Reset link sent successfully.');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['success' => true, 'message' => __($status)], 200);
        } else {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }
    }
    public function updateLatitudeAndLongitude(Request $request): JsonResponse
    {
        $request->validate([
            'salesman_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        $status = User::where('id',$request->salesman_id    )->update(['latitude'=>$request->latitude,'longitude'=>$request->longitude]);
        if($status){
            return response()->json(['success'=>true,'message' => __("Updated successfully")], 200);
        }else{
            throw ValidationException::withMessages([
                'message' => __($status),
            ]);
        }
    }

}
