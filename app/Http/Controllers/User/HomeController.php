<?php

namespace App\Http\Controllers\User;


use App\Helpers\GoogleAuthenticator;
use App\Http\Controllers\Controller;
use App\Models\BetInvest;
use App\Models\BetInvestLog;
use App\Models\Deposit;
use App\Models\GameOption;
use App\Models\Gateway;
use App\Models\Kyc;
use App\Models\Language;
use App\Models\Payout;
use App\Models\Transaction;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Stevebauman\Purify\Facades\Purify;
use Facades\App\Services\BasicService;


class HomeController extends Controller
{
    use Upload;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function saveToken(Request $request)
    {
        try {
            Auth::user()
                ->fireBaseToken()
                ->create([
                    'token' => $request->token,
                ]);
            return response()->json([
                'msg' => 'token saved successfully.',
            ]);
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }


    public function index()
    {
        $data['betInvests'] = BetInvest::with('betInvestLog')->where('user_id', $this->user->id)->orderBy('id', 'desc')->limit(6)->get();
        $data['userBet'] = collect(BetInvest::with('betInvestLog')->where('user_id', $this->user->id)
            ->selectRaw('SUM(CASE WHEN status != 2 THEN invest_amount END) as totalInvest')
            ->selectRaw('SUM(CASE WHEN status = 1 THEN return_amount END) as totalReturn')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS win')
            ->selectRaw('COUNT(CASE WHEN status != 2 THEN id END) AS totalBet')
            ->orderBy('id', 'desc')
            ->get()->toArray())->collapse();

        $dailyPayout = $this->dayList();

        BetInvest::where('user_id', $this->user->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->select(
                DB::raw('SUM(invest_amount) as totalInvest'),
                DB::raw('DATE_FORMAT(created_at,"Day %d") as date')
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()->map(function ($item) use ($dailyPayout) {
                $dailyPayout->put($item['date'], round($item['totalInvest'], 2));
            });

        $data['dailyPayout'] = $dailyPayout;
        $data['user'] = Auth::user();
        $data['firebaseNotify'] = config('firebase');
        return view(template() . 'user.dashboard', $data);
    }

    public function dayList()
    {
        $totalDays = $this->days_in_month(date('m'), date('Y'));
        $daysByMonth = [];
        for ($i = 1; $i <= $totalDays; $i++) {
            array_push($daysByMonth, ['Day ' . sprintf("%02d", $i) => 0]);
        }
        return collect($daysByMonth)->collapse();
    }

    public function days_in_month($month, $year)
    {
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }

    public function profile(Request $request)
    {
        $data['user'] = $this->user;
        $data['languages'] = Language::where('status', 1)->get();
        $data['kycs'] = Kyc::where('status', 1)->get();

        return view($this->theme . 'user.profile.myprofile', $data);
    }

    public function updateProfile(Request $request)
    {
        $allowedExtensions = array('jpg', 'png', 'jpeg');
        $image = $request->image;
        $this->validate($request, [
            'image' => [
                'required',
                'max:4096',
                function ($fail) use ($image, $allowedExtensions) {
                    $ext = strtolower($image->getClientOriginalExtension());
                    if (($image->getSize() / 1000000) > 2) {
                        throw ValidationException::withMessages(['image' => "Images MAX  2MB ALLOW!"]);
                    }
                    if (!in_array($ext, $allowedExtensions)) {
                        throw ValidationException::withMessages(['image' => "Only png, jpg, jpeg images are allowed"]);
                    }
                }
            ]
        ]);
        $user = Auth::user();
        if ($request->hasFile('image')) {
            $image = $this->fileUpload($request->image, config('filelocation.userProfile.path'), null, config('filelocation.userProfile.size'), 'webp', null, $user->image, $user->image_driver);
            if ($image) {
                $profileImage = $image['path'];
                $ImageDriver = $image['driver'];
            }
        }
        $user->image = $profileImage ?? $user->image;
        $user->image_driver = $ImageDriver ?? $user->image_driver;
        $user->save();
        return back()->with('success', 'Updated Successfully.');
    }

    public function updateInformation(Request $request)
    {
        try {
            $languages = Language::all()->map(function ($item) {
                return $item->id;
            });

            $req = $request->all();
            $user = $this->user;
            $rules = [
                'firstname' => 'required',
                'lastname' => 'required',
                'username' => "sometimes|required|alpha_dash|min:5|unique:users,username," . $user->id,
                'address' => 'required',
                'language_id' => Rule::in($languages),
            ];
            $message = [
                'firstname.required' => 'First Name field is required',
                'lastname.required' => 'Last Name field is required',
            ];

            $validator = Validator::make($req, $rules, $message);
            if ($validator->fails()) {
                $validator->errors()->add('profile', '1');
                return back()->withErrors($validator)->withInput();
            }
            $user->language_id = $req['language_id'];
            $user->firstname = $req['firstname'];
            $user->lastname = $req['lastname'];
            $user->username = $req['username'];
            $user->address_one = $req['address'];
            $user->save();
            return back()->with('success', 'Updated Successfully.');
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }


    public function updatePassword(Request $request)
    {
        if ($request->method() == 'GET') {
            $data['kycs'] = Kyc::where('status', 1)->get();
            return view($this->theme . 'user.profile.change', $data);
        } elseif ($request->method() == 'POST') {
            $rules = [
                'current_password' => "required",
                'password' => "required|min:5|confirmed",
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $user = Auth::user();
            try {
                if (Hash::check($request->current_password, $user->password)) {
                    $user->password = bcrypt($request->password);
                    $user->save();
                    return back()->with('success', 'Password Changes successfully.');
                } else {
                    throw new \Exception('Current password did not match');
                }
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }
    }


    public function addFund()
    {
        $data['basic'] = basicControl();
        $data['gateways'] = Gateway::where('status', 1)->orderBy('sort_by', 'ASC')->get();
        return view(template() . 'user.fund.add_fund', $data);
    }

    public function fund(Request $request)
    {
        $basic = basicControl();
        $search = $request->all();
        $dateSearch = $request->datetrx;
        $date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);
        $userId = Auth::id();
        $funds = Deposit::with(['depositable', 'gateway'])
            ->where('user_id', $userId)
            ->where('status', '!=', 0)
            ->when(@$search['name'], function ($query) use ($search) {
                return $query->where('trx_id', 'LIKE', "%{$search['name']}%");
            })
            ->when(@$search['status'], function ($query) use ($search) {
                return $query->where('status', $search['status']);
            })
            ->when($date == 1, function ($query) use ($dateSearch) {
                return $query->whereDate("created_at", $dateSearch);
            })
            ->orderBy('id', 'desc')
            ->latest()->paginate($basic->paginate);
        return view($this->theme . 'user.transaction.fundHistory', compact('funds'));
    }

    public function referral()
    {
        $title = "Invite Friends";
        //$referrals = getLevelUser($this->user->id);
        $data['directReferralUsers'] = getDirectReferralUsers($this->user->id);
        return view($this->theme . 'user.referral', compact('title'), $data);
    }

    public function getReferralUser(Request $request)
    {
        $data = getDirectReferralUsers($request->userId);
        $directReferralUsers = $data->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'count_direct_referral' => count(getDirectReferralUsers($user->id)),
                'joined_at' => dateTime($user->created_at),
            ];
        });

        return response()->json(['data' => $directReferralUsers]);
    }

    public function referralBonus()
    {
        $title = "Referral Bonus";
        $transactions = $this->user->referralBonusLog()->latest()->with('bonusBy:id,firstname,lastname')->paginate(basicControl()->paginate);
        return view($this->theme . 'user.transaction.referral-bonus', compact('title', 'transactions'));
    }

    public function referralBonusSearch(Request $request)
    {
        $title = "Referral Bonus";
        $search = $request->all();
        $dateSearch = $request->datetrx;
        $date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);

        $transaction = $this->user->referralBonusLog()->latest()
            ->with('bonusBy:id,firstname,lastname')
            ->when(isset($search['search_user']), function ($query) use ($search) {
                return $query->whereHas('bonusBy', function ($q) use ($search) {
                    $q->where(DB::raw('concat(firstname, " ", lastname)'), 'LIKE', "%{$search['search_user']}%")
                        ->orWhere('firstname', 'LIKE', '%' . $search['search_user'] . '%')
                        ->orWhere('lastname', 'LIKE', '%' . $search['search_user'] . '%')
                        ->orWhere('username', 'LIKE', '%' . $search['search_user'] . '%');
                });
            })
            ->when($date == 1, function ($query) use ($dateSearch) {
                return $query->whereDate("created_at", $dateSearch);
            })
            ->paginate(config('basic.paginate'));
        $transactions = $transaction->appends($search);

        return view($this->theme . 'user.transaction.referral-bonus', compact('title', 'transactions'));
    }

    /*
     * User payout Operation
     */

    public function payoutHistory()
    {
        $user = $this->user;
        $data['payoutLog'] = Payout::whereUser_id($user->id)->where('status', '!=', 0)->latest()->with('user', 'method')->paginate(config('basic.paginate'));
        $data['title'] = "Payout Log";
        return view($this->theme . 'user.payout.log', $data);
    }


    public function payoutHistorySearch(Request $request)
    {
        $search = $request->all();

        $dateSearch = $request->date_time;
        $date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);

        $payoutLog = Payout::orderBy('id', 'DESC')->where('user_id', $this->user->id)->where('status', '!=', 0)
            ->when(isset($search['name']), function ($query) use ($search) {
                return $query->where('trx_id', 'LIKE', $search['name']);
            })
            ->when($date == 1, function ($query) use ($dateSearch) {
                return $query->whereDate("created_at", $dateSearch);
            })
            ->when(isset($search['status']), function ($query) use ($search) {
                return $query->where('status', $search['status']);
            })
            ->with('user', 'method')->paginate(config('basic.paginate'));
        $payoutLog->appends($search);

        $title = "Payout Log";
        return view($this->theme . 'user.payout.log', compact('title', 'payoutLog'));
    }


    public function transaction(Request $request)
    {
        $search = $request->all();
        $dateSearch = $request->datetrx;
        $date = preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch);
        $userId = Auth::id();
        $transactions = Transaction::where('user_id', $userId)
            ->when(@$search['transaction_id'], function ($query) use ($search) {
                return $query->where('trx_id', 'LIKE', "%{$search['transaction_id']}%");
            })
            ->when(@$search['remark'], function ($query) use ($search) {
                return $query->where('remarks', 'LIKE', "%{$search['remark']}%");
            })
            ->when($date == 1, function ($query) use ($dateSearch) {
                return $query->whereDate("created_at", $dateSearch);
            })
            ->orderBy('id', 'desc')
            ->paginate(basicControl()->paginate);
        return view($this->theme . 'user.transaction.index', compact('transactions'));
    }

    public function betSlip(Request $request)
    {
        $basic = basicControl();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'activeSlip' => 'required',
            'activeSlip.*' => 'required',
        ], [
            'amount.required' => "Amount Field is required",
            'activeSlip.required' => "Please make prediction slip or list",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }
        $user = $this->user;


        $idCollection = collect($request->activeSlip)->pluck('id');

        $requestAmount = $request->amount;
        if ($user->balance < $requestAmount) {
            return response()->json(['errors' => ['amount' => ['Insufficient Balance']]]);
        }
        if ($basic->minimum_bet > $requestAmount) {
            return response()->json(['errors' => ['amount' => ["Minimum  Amount $basic->minimum_bet $basic->currency_symbol need!"]]]);
        }


        $lastbetIn = BetInvest::where('user_id', $this->user->id)->latest()->first();
        if ($lastbetIn && Carbon::parse($lastbetIn->created_at)->addSeconds(20) > Carbon::now()) {
            $time = Carbon::parse($lastbetIn->created_at)->addSeconds(20);
            $delay = $time->diffInSeconds(Carbon::now());
            $delay = gmdate('i:s', $delay);
            return response()->json(['errors' => ['amount' => ['You can next bet after ' . $delay . ' seconds']]]);
        }


        $predictionList = GameOption::query();

        $predictionActiveList = $predictionList->where('status', 1)
            ->whereHas('gameMatch', function ($q) {
                $q->where('status', 1)->where('is_unlock', 0);
            })
            ->whereHas('gameQuestion', function ($q) {
                $q->where('status', 1)->where('end_time', '>', Carbon::now())
                    ->where('is_unlock', 0);
            })
            ->orderBy('id', 'desc')
            ->whereIn('id', $idCollection)
            ->with([
                'gameMatch:id,name,team1_id,team2_id,tournament_id,category_id,is_unlock,status',
                'gameMatch.gameCategory:id,name,icon',
                'gameMatch.gameTeam1:id,name',
                'gameMatch.gameTeam2:id,name',
                'gameMatch.gameTournament:id,name',
                'gameQuestion:id,name,status,end_time,result,is_unlock'
            ])
            ->get();


        $getRatio = 1;
        $newSlip = $predictionActiveList->map(function ($item) use (&$getRatio) {
            $getRatio *= $item->ratio;
            return [
                "id" => $item->id,
                'status' => $item->status,
                "category_icon" => optional(optional($item->gameMatch)->gameCategory)->icon,
                "category_name" => optional(optional($item->gameMatch)->gameCategory)->name,
                "is_unlock_match" => optional($item->gameMatch)->is_unlock,
                "is_unlock_question" => optional($item->gameQuestion)->is_unlock,
                "match_id" => $item->match_id,
                "match_name" => optional(optional($item->gameMatch)->gameTeam1)->name . ' vs ' . optional(optional($item->gameMatch)->gameTeam2)->name,
                "option_name" => $item->option_name,
                "question_id" => $item->question_id,
                "question_name" => optional($item->gameQuestion)->name,
                "ratio" => (float)$item->ratio,
                "tournament_name" => optional(optional($item->gameMatch)->gameTournament)->name,
            ];
        });

        if (collect($request->activeSlip)->count() != count($predictionActiveList)) {
            return response()->json([
                'newSlip' => $newSlip,
                'newSlipMessage' => 'Please remove expired marking point',
            ]);
        }

        DB::beginTransaction();

        $user->balance -= round($requestAmount, 2);
        $user->save();


        $finalRatioReturnAmo = round(($requestAmount * $getRatio), 2);

        $invest = new BetInvest();
        $invest->transaction_id = strRandom();
        $invest->user_id = $user->id;
        $invest->creator_id = null;
        $invest->invest_amount = $requestAmount;
        $invest->return_amount = $finalRatioReturnAmo;
        $invest->charge = 0;
        $invest->remaining_balance = $user->balance;
        $invest->ratio = round($getRatio, 3);
        $invest->status = 0;
        $invest->isMultiBet = (count($newSlip) == 1) ? 0 : 1;
        $invest->save();


        $newSlip->map(function ($item) use ($invest, $user) {
            $betInv = new BetInvestLog();
            $betInv->bet_invest_id = $invest->id;
            $betInv->user_id = $user->id;
            $betInv->match_id = $item['match_id'];
            $betInv->question_id = $item['question_id'];
            $betInv->bet_option_id = $item['id'];
            $betInv->ratio = $item['ratio'];
            $betInv->category_icon = $item['category_icon'];
            $betInv->category_name = $item['category_name'];
            $betInv->tournament_name = $item['tournament_name'];
            $betInv->match_name = $item['match_name'];
            $betInv->question_name = $item['question_name'];
            $betInv->option_name = $item['option_name'];
            $betInv->status = 0;
            $betInv->save();
        });

        $title = 'Bet in ' . count($newSlip) . ' Matches By Bet slip';
        $trx = strRandom();
        BasicService::makeTransaction($user, $requestAmount, 0, '-',
            $trx, $title, $invest->id, BetInvest::class);

        if ($basic->bet_commission == 1) {
            BasicService::setBonus($user, getAmount($requestAmount), 'bet_invest');
        }

        DB::commit();
        return response()->json(['success' => true]);


    }
}
