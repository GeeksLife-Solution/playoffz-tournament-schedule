<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BasicControl;
use App\Models\Referral;
use App\Models\ReferralBonus;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Stevebauman\Purify\Facades\Purify;

class ReferralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function referralCommission()
    {
        $data['referrals'] = Referral::get();
        return view('admin.referral.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function referralCommissionStore(Request $request)
    {
        $request->validate([
            'level*' => 'required|integer|min:1',
            'percent*' => 'required|numeric',
            'commission_type' => 'required',
        ]);

        if (!isset($request->percent) || !isset($request->level)) {
            return back()->with('error', 'Please fill all the required fields.');
        }
        Referral::where('commission_type', $request->commission_type)->delete();

        for ($i = 0; $i < count($request->level); $i++) {
            $referral = new Referral();
            $referral->commission_type = $request->commission_type;
            $referral->level = $request->level[$i];
            $referral->percent = $request->percent[$i];
            $referral->save();
        }

        return back()->with('success', 'Level Bonus Has been Updated.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function referralCommissionAction(Request $request)
    {
        $basic = BasicControl::firstOrNew();
        $basic->deposit_commission = $request->deposit_commission;
        $basic->bet_commission = $request->bet_commission;
        $basic->bet_win_commission = $request->bet_win_commission;
        $basic->save();
        return back()->with('success', 'Update Successfully.');
    }

    public function commission()
    {
        return view('admin.referral.commission');
    }

    public function commissionList(Request $request)
    {
        $commission = ReferralBonus::query()->with('user','bonusBy')
            ->whereHas('user')
            ->whereHas('bonusBy')
            ->when(!empty($request->search['value']),function ($query) use ($request){
                $query->whereHas('bonusBy',function ($query) use ($request){
                    $query->where('firstname', 'LIKE', '%'.$request->search['value'].'%')
                        ->orWhere('lastname', 'LIKE', '%'.$request->search['value'].'%')
                        ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", [$request->search['value']])
                        ->orWhere('username','LIKE','%'.$request->search['value'].'%');
                })
                    ->orWhereHas('user',function ($query) use ($request){
                        $query->where('firstname', 'LIKE', '%'.$request->search['value'].'%')
                            ->orWhere('lastname', 'LIKE', '%'.$request->search['value'].'%')
                            ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", [$request->search['value']])
                            ->orWhere('username','LIKE','%'.$request->search['value'].'%');
                    });
            })
            ->orderBy('created_at', 'desc');


        return DataTables::of($commission)
            ->addColumn('sl', function ($commission) {
                static $counter = 0;
                return ++$counter;
            })
            ->addColumn('username', function ($commission) {
                return $commission->getUser();

            })
            ->addColumn('bonus_from', function ($commission) {
                return optional($commission->bonusBy)->fullname;
            })
            ->addColumn('amount', function ($commission) {

                return '<h5>'.currencyPosition($commission->amount+0).'</h5>';
            })
            ->addColumn('remarks', function ($commission) {
                return $commission->remarks;
            })
            ->addColumn('time', function ($commission) {
                return dateTime($commission->created_at);
            })
            ->rawColumns(['sl', 'username', 'bonus_from', 'amount', 'remarks', 'time'])
            ->make(true);
    }

}
