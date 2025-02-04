<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\UserSystemInfo;
use App\Http\Controllers\Controller;
use App\Models\ContentDetails;
use App\Models\Language;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Rules\PhoneLength;
use Facades\App\Services\Google\GoogleRecaptchaService;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */

    protected $maxAttempts = 3; // Change this to 4 if you want 4 tries
    protected $decayMinutes = 5; // Change this according to your
    protected $redirectTo = '/user/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->theme = template();
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        $basic = basicControl();
        if ($basic->registration == 0) {
            return redirect('/')->with('warning', 'Registration Has Been Disabled.');
        }
        $data['template'] = ContentDetails::whereHas('content', function ($query) {
            $query->whereIn('name', ['authentication']);
        })->get()->groupBy('content.name');
        return view(template() . 'auth.register', $data);
    }

    public function sponsor($sponsor)
    {
        $basic = basicControl();
        if ($basic->registration == 0) {
            return redirect('/')->with('warning', 'Registration Has Been Disabled.');
        }

        session()->put('sponsor', $sponsor);
        $info = json_decode(json_encode(getIpInfo()), true);
        $country_code = null;
        if (!empty($info['code'])) {
            $country_code = @$info['code'][0];
        }
        $countries = config('country');
        $data['template'] = ContentDetails::whereHas('content', function ($query) {
            $query->whereIn('name', ['authentication']);
        })->get()->groupBy('content.name');
        return view(template() . 'auth.register', compact('sponsor', 'countries', 'country_code'), $data);

    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $basicControl = basicControl();
        if ($basicControl->strong_password == 0) {
            $rules['password'] = ['required', 'min:6', 'confirmed'];
        } else {
            $rules['password'] = ["required", 'confirmed',
                Password::min(6)->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()];
        }

        if (basicControl()->reCaptcha_status_registration) {
            $rules['g-recaptcha-response'] = ['sometimes', 'required'];
        }

        $rules['firstname'] = ['required', 'string', 'max:91'];
        $rules['lastname'] = ['required', 'string', 'max:91'];
        $rules['username'] = ['required', 'alpha_dash', 'min:5', 'unique:users,username'];
        $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email'];
        $rules['phone'] = ['required', 'string', 'unique:users,phone'];
        $rules['phone_code'] = ['required', 'string', 'max:15'];

        // Recaptcha
        if ($basicControl->google_recaptcha && ($basicControl->google_reCaptcha_status_registration)) {
            $res = GoogleRecaptchaService::responseRecaptcha($_POST['g-recaptcha-response']);
            if (is_null($res)) {
                // Throw a validation error if $res is null
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => __('ReCAPTCHA validation is required.'),
                ]);
            }
        }

        // Manual Recaptcha
        if (($basicControl->manual_recaptcha == 1) && ($basicControl->reCaptcha_status_registration == 1)) {
            $rules['captcha'] = ['required',
                Rule::when((!empty(request()->captcha) && strcasecmp(session()->get('captcha'), $_POST['captcha']) != 0), ['confirmed']),
            ];
        }

        return Validator::make($data, $rules, [
            'firstname.required' => 'First Name Field is required',
            'lastname.required' => 'Last Name Field is required',
            'g-recaptcha-response.required' => 'The reCAPTCHA field is required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $basic = basicControl();

        $sponsor = session()->get('sponsor');
        if ($sponsor != null) {
            $sponsorId = User::where('username', $sponsor)->first();
        } else {
            $sponsorId = null;
        }

        return User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'username' => $data['username'],
            'email' => $data['email'],
            'referral_id' => ($sponsorId != null) ? $sponsorId->id : null,
            'password' => Hash::make($data['password']),
            'phone_code' => $data['phone_code'],
            'phone' => $data['phone'],
            'email_verification' => ($basic->email_verification) ? 0 : 1,
            'sms_verification' => ($basic->sms_verification) ? 0 : 1,
        ]);
    }

    public function register(Request $request)
    {

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        if ($request->ajax()) {
            return route('user.dashboard');
        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
    }

    protected function registered(Request $request, $user)
    {
        $user->language_id = Language::where('default_status', 1)->first()->id;
        $user->last_login = Carbon::now();
        $user->last_seen = Carbon::now();
        $user->two_fa_verify = ($user->two_fa == 1) ? 0 : 1;
        $user->save();

        $info = @json_decode(json_encode(getIpInfo()), true);
        $ul['user_id'] = $user->id;

        $ul['longitude'] = (!empty(@$info['long'])) ? implode(',', $info['long']) : null;
        $ul['latitude'] = (!empty(@$info['lat'])) ? implode(',', $info['lat']) : null;
        $ul['country_code'] = (!empty(@$info['code'])) ? implode(',', $info['code']) : null;
        $ul['location'] = (!empty(@$info['city'])) ? implode(',', $info['city']) . (" - " . @implode(',', @$info['area']) . "- ") . @implode(',', $info['country']) . (" - " . @implode(',', $info['code']) . " ") : null;
        $ul['country'] = (!empty(@$info['country'])) ? @implode(',', @$info['country']) : null;

        $ul['ip_address'] = UserSystemInfo::get_ip();
        $ul['browser'] = UserSystemInfo::get_browsers();
        $ul['os'] = UserSystemInfo::get_os();
        $ul['get_device'] = UserSystemInfo::get_device();

        UserLogin::create($ul);

    }

    protected function guard()
    {
        return Auth::guard();
    }

}
