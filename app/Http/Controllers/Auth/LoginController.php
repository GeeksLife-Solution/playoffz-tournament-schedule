<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\UserSystemInfo;
use App\Http\Controllers\Controller;
use App\Models\ContentDetails;
use App\Models\User;
use App\Models\UserLogin;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Facades\App\Services\Google\GoogleRecaptchaService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Client\RequestException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    protected $maxAttempts = 3; // Change this to 4 if you want 4 tries
    protected $decayMinutes = 5; // Change this according to your

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->theme = template();
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm(Request $request)
    {
        $data['template'] = ContentDetails::whereHas('content', function ($query) {
            $query->whereIn('name', ['authentication']);
        })->get()->groupBy('content.name');

        return view($this->theme . 'auth/login', $data);
    }

    protected function validateLogin(Request $request)
    {
        //
    }

    public function loginModal(Request $request)
    {
        $this->validateLogin($request);
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // Check if the user exists locally
        $user = User::where($this->username(), $request->username)->first();

        if (!$user) {
            $data = [
                'email' => $request->username,
                'password' => $request->password,
                'type' => 'Orgnizer'
            ];

            try {
                $response = Http::withOptions([
                    'verify' => false,
                ])->withHeaders([
                    'Content-Type' => 'application/json'
                ])->post('https://app.playoffz.in/orag_api/u_login_user.php', $data);

                if ($response->successful()) {
                    $apiUserData = $response->json();

                    if (isset($apiUserData['OragnizerLogin'])) {
                        $organizer = $apiUserData['OragnizerLogin'];

                        User::create([
                            'username' => $organizer['title'],
                            'email' => $organizer['email'],
                            'phone' => $organizer['mobile'],
                            'password' => Hash::make($request->password)
                        ]);
                    } else {
                        return response()->json('These credentials do not match our records.' , 401);
                    }
                } else {
                    return response()->json('These credentials do not match our records.',401);
                }
            } catch (RequestException $e) {
                return response()->json('These credentials do not match our records.' . $e->getMessage(), 401);
            }
        }

        if ($this->guard()->validate($this->credentials($request))) {
            if (Auth::attempt([$this->username() => $request->username, 'password' => $request->password, 'status' => 1])) {
                $user = Auth::user();
                $this->authenticated($request, $user);
                $request->session()->regenerate();
                return route('user.dashboard');
            } else {
                return response()->json('You are banned from this application. Please contact with system Adminstrator.', 401);
            }
        }


        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    public function username()
    {
        $login = request()->input('username');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $login]);
        return $field;
    }

    public function login(Request $request)
    {
        $basicControl = basicControl();
        $rules[$this->username()] = 'required';
        $rules ['password'] = 'required';

        // Recaptcha
        if ($basicControl->google_recaptcha && ($basicControl->google_reCaptcha_status_login)) {
            $res = GoogleRecaptchaService::responseRecaptcha($_POST['g-recaptcha-response']);
            if (is_null($res)) {
                // Throw a validation error if $res is null
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => __('ReCAPTCHA validation is required.'),
                ]);
            }
        }

        // Manual Recaptcha
        if (($basicControl->manual_recaptcha == 1) && ($basicControl->reCaptcha_status_login == 1)) {
            $rules['captcha'] = ['required',
                Rule::when((!empty($request->captcha) && strcasecmp(session()->get('captcha'), $_POST['captcha']) != 0), ['confirmed']),
            ];
        }

        $message['captcha.confirmed'] = "The captcha does not match.";
        $message['g-recaptcha-response.required'] = 'The reCAPTCHA field is required.';

        $request->validate($rules, $message);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        if ($this->guard()->validate($this->credentials($request))) {
            if (Auth::attempt([$this->username() => $request->username, 'password' => $request->password])) {
                return $this->sendLoginResponse($request);
            } else {
                return back()->with('error', 'You are banned from this application. Please contact with system Administrator.');
            }
        }
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }


    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard('admin')->user())) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
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


}
