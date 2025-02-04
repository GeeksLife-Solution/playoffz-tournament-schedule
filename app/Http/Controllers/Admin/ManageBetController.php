<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BetInvest;
use App\Models\Transaction;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Facades\App\Services\BasicService;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ManageBetController extends Controller
{
    use Upload, Notify;

    public function betList()
    {
        $data['betInvests'] = collect(BetInvest::selectRaw('COUNT(id) AS totalBet')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS winBet')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS winBetPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingBet')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingBetPercentage')
            ->selectRaw('COUNT(CASE WHEN status = -1 THEN id END) AS loseBet')
            ->selectRaw('(COUNT(CASE WHEN status = -1 THEN id END) / COUNT(id)) * 100 AS loseBetPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS refundBet')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS refundBetPercentage')
            ->get()
            ->toArray())->collapse();

        return view('admin.bet_history.list', $data);
    }

    public function betSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStatus = $request->filterStatus;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $betInvests = BetInvest::orderBy('id', 'DESC')
            ->with('betInvestLog', 'user')
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('transaction_id', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(!empty($request->filterDate) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filterDate) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('transaction_id', 'LIKE', "%{$search}%");
                });
            });
        return DataTables::of($betInvests)
            ->addColumn('trx', function ($item) {
                return $item->transaction_id;
            })
            ->addColumn('user', function ($item) {
                $url = route("admin.user.edit", $item->user_id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                                <div class="flex-shrink-0">
                                  ' . optional($item->user)->profilePicture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->user)->firstname . ' ' . optional($item->user)->lastname . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->user)->username . '</span>
                                </div>
                              </a>';
            })
            ->addColumn('pre_amount', function ($item) {
                return currencyPosition($item->invest_amount);
            })
            ->addColumn('re_amount', function ($item) {
                return currencyPosition($item->return_amount);
            })
            ->addColumn('charge', function ($item) {
                return currencyPosition($item->charge);
            })
            ->addColumn('ratio', function ($item) {
                return $item->ratio;
            })
            ->addColumn('time', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Win') . '
                  </span>';

                } elseif ($item->status == 0) {
                    return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Processing') . '
                  </span>';
                } elseif ($item->status == -1) {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Loss') . '
                  </span>';
                } elseif ($item->status == 2) {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Refund') . '
                  </span>';
                }
            })
            ->addColumn('action', function ($item) {
                $refundRoute = route('admin.refundBet');
                $disClass = ($item->status != 0) ? 'disabled' : '';

                $html = "<div class='btn-group' role='group'>
                      <a href='javascript:void(0)' class='btn btn-white btn-sm investLogList' data-resource='" . $item->betInvestLog . "'
                       data-bs-target='#investLogList' data-bs-toggle='modal'>
                        <i class='fal fa-chart-line me-1'></i> Invest Log
                      </a>";

                $html .= '<div class="btn-group">
                      <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                        <a class="dropdown-item refundBet ' . $disClass . '" href="javascript:void(0)" data-bs-target="#refund"
                           data-bs-toggle="modal" data-route="' . $refundRoute . '" data-id="' . $item->id . '">
                          <i class="fal fa-paper-plane dropdown-item-icon"></i> ' . trans("Refund") . '
                       </a>
                      </div>
                    </div>';

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['trx', 'user', 'pre_amount', 're_amount', 'charge', 'ratio', 'time', 'status', 'action'])
            ->make(true);
    }

    public function betRefund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'betInvestId' => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }


        $betInvestId = $request->betInvestId;
        $invest = BetInvest::with('user')->find($betInvestId);

        $basic = basicControl();

        if (!$invest || $invest->status != 0) {
            return back()->with('error', 'Invalid Request');
        }

        if ($invest->isMultiBet == 1) {
            $detail = $invest->betInvestLog;
            $detail->map(function ($sBet) {
                $sBet->status = 3;
                $sBet->update();
            });
        }

        DB::beginTransaction();
        $charge = ($invest->invest_amount * $basic->refund_charge) / 100;
        $user = $invest->user;
        $user->balance += ($invest->invest_amount - $charge);
        $user->save();


        $invest->charge = getAmount($charge);
        $invest->status = 2;
        $invest->creator_id = Auth()->guard('admin')->id();
        $invest->save();

        $amount = getAmount($invest->invest_amount - $charge);
        $remark = $amount . ' ' . $basic->currency . " refunded by admin policy.";

        BasicService::makeTransaction($user, $amount, $charge, '+',
            $invest->transaction_id, $remark, $invest->id, BetInvest::class);

        DB::commit();

        $this->sendMailSms($user, 'USER_REFUND', [
            'amount' => currencyPosition($amount)
        ]);

        $msg = [
            'amount' => currencyPosition($amount)
        ];
        $action = [
            "link" => '#',
            "icon" => "fas fa-file-alt text-white"
        ];
        $this->userPushNotification($user, 'USER_REFUND', $msg, $action);

        return back()->with('success', 'Refund Successfully');
    }
}
