<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BetInvest;
use App\Models\BetInvestLog;
use App\Models\GameOption;
use App\Models\GameQuestions;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Facades\App\Services\BasicService;
use Yajra\DataTables\Facades\DataTables;

class ManageResultController extends Controller
{
    public function resultList(Request $request)
    {
        $data['gameQuestions'] = collect(GameQuestions::selectRaw('COUNT(id) AS totalQuestion')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeQuestion')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeQuestionPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingQuestion')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingQuestionPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS closedQuestion')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS closedQuestionPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayQuestion')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayQuestionPercentage')
            ->get()
            ->toArray())->collapse();

        return view('admin.result_history.list', $data);
    }

    public function resultSearch(Request $request)
    {
        $previousUrl = url()->previous();
        $lastSegment = basename(parse_url($previousUrl, PHP_URL_PATH));
        $resultQuery = ($lastSegment == 'pending') ? 0 : 1;

        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $questions = GameQuestions::orderBy('id', 'DESC')->where('result', $resultQuery)->withCount('betInvestLog')
            ->with(['gameMatch.gameTeam1', 'gameMatch.gameTeam2', 'betInvestLog.betInvest'])
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('name', 'LIKE', '%' . $filterName . '%');
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
                    $subquery->where('name', 'LIKE', "%{$search}%");
                });
            });
        return DataTables::of($questions)
            ->addColumn('question', function ($item) {
                return $item->name;
            })
            ->addColumn('match', function ($item) {
                $team1Img = getFile(optional(optional($item->gameMatch)->gameTeam1)->driver, optional(optional($item->gameMatch)->gameTeam1)->image);
                $team2Img = getFile(optional(optional($item->gameMatch)->gameTeam2)->driver, optional(optional($item->gameMatch)->gameTeam2)->image);

                return '<div class="d-lg-flex d-block align-items-center ">
                                    <div class="me-3 cursor-pointer" title="' . optional(optional($item->gameMatch)->gameTeam1)->name . '">
                                        <small class="text-dark font-weight-bold">' . shortName(optional(optional($item->gameMatch)->gameTeam1)->name) . '</small>
                                    </div>
                                    <div class="me-2 cursor-pointer" title="' . optional(optional($item->gameMatch)->gameTeam1)->name . '">
                                        <img src="' . $team1Img . '" alt="user" class="rounded-circle" width="25" height="25">
                                    </div>
                                    <small class="font-italic mb-0 font-16 ">vs</small>

                                    <div class="me-3 ms-2 cursor-pointer" title="' . optional(optional($item->gameMatch)->gameTeam2)->name . '">
                                        <img src="' . $team2Img . '" alt="user" class="rounded-circle" width="25" height="25">
                                    </div>
                                    <div class="cursor-pointer" title="' . optional(optional($item->gameMatch)->gameTeam2)->name . '">
                                        <small class="text-dark font-weight-bold">' . shortName(optional(optional($item->gameMatch)->gameTeam2)->name) . '</small>
                                    </div>
                                </div>';
            })
            ->addColumn('end_time', function ($item) {
                return dateTime($item->end_time, 'd M Y h:i A');
            })
            ->addColumn('predictions', function ($item) {
                return '<span class="badge bg-soft-primary text-secondary">' . number_format($item->bet_invest_log_count) . '</span>';
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('admin.resultWinner', $item->id);
                $editRoute = route('admin.updateQuestion', $item->id);
                $refundRoute = route('admin.refundQuestion', $item->id);
                $logs = route('admin.betUser', $item->id);
                $disClass = ($item->result == 1) ? 'disabled' : '';

                $html = "<div class='btn-group' role='group'>
                      <a href='" . $viewRoute . "' class='btn btn-white btn-sm'>
                        <i class='fal fa-eye me-1'></i> View
                      </a>";

                $html .= '<div class="btn-group">
                      <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                        <a class="dropdown-item editBtn ' . $disClass . '" href="javascript:void(0)" data-bs-target="#editModal"
                           data-bs-toggle="modal" data-action="' . $editRoute . '" data-id="' . $item->id . '" data-name="' . $item->name . '" data-status="' . $item->status . '"
                           data-endtime="' . $item->end_time . '">
                          <i class="fal fa-edit dropdown-item-icon"></i> ' . trans("Edit") . '
                       </a>
                       <a class="dropdown-item editBtn ' . $disClass . '" href="javascript:void(0)" data-bs-target="#refundQuestionModal"
                           data-bs-toggle="modal" data-action="' . $refundRoute . '">
                          <i class="fal fa-paper-plane dropdown-item-icon"></i> ' . trans("Refund Bet") . '
                       </a>
                        <a class="dropdown-item" href="' . $logs . '">
                          <i class="fal fa-chart-line dropdown-item-icon"></i> ' . trans("Bet Logs") . '
                       </a>
                      </div>
                    </div>';

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['question', 'match', 'end_time', 'predictions', 'action'])
            ->make(true);
    }

    public function resultWinner($id)
    {
        $data['gameQuestion'] = GameQuestions::with('gameOptions')->findOrFail($id);
        return view('admin.result_history.optionList', $data);
    }

    public function makeWinner(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'optionId' => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }
        DB::beginTransaction();
        $betOptionId = $request->optionId;
        $betOption = GameOption::with('gameMatch', 'gameQuestion')->findOrFail($betOptionId);

        $question = $betOption->gameQuestion;

        if ($question->result == 1) {
            return back()->with('error', 'Invalid Request, Reload the page');
        }
        $question->betInvestLog();

        //Winner Declare
        $question->betInvestLog()->where('bet_option_id', $betOptionId)->where('status', 0)->get()->map(function ($winner) use ($betOption, $question) {
            $winner->status = 2;
            $winner->update();

        });

        //Loser Declare
        $question->betInvestLog()->where('bet_option_id', '!=', $betOptionId)->where('status', 0)->get()->map(function ($loser) use ($betOption, $question) {
            $loser->status = -2;
            $loser->update();
        });

        $question->result = 1; // Question Result Declare
        $question->save();

        $betOption->status = 2;  // Option Result Declare
        $betOption->update();

        $question->gameOptions()->where('id', '!=', $betOptionId)->get()->map(function ($item) {
            $item->status = -2;
            $item->update();
        });

        DB::commit();
        return back()->with('success', 'Winner Make Successfully');

    }

    public function refundQuestion(Request $request, $id)
    {
        $basic = basicControl();
        $question = GameQuestions::with(['gameMatch.gameTeam1', 'gameMatch.gameTeam2', 'gameOptions', 'betInvestLog.betInvest', 'betInvestLog.user'])
            ->whereResult(0)
            ->withCount('betInvestLog')
            ->findOrFail($id);

        $question->result = 1;
        $question->save();
        $detail = $question->betInvestLog;

        $question->gameOptions->map(function ($item) {
            $item->status = 3;
            $item->save();
            return $item;
        });

        foreach ($detail as $value) {
            if ($value->status == 3) {
                continue;
            } else {
                $rootBetInvest = $value->betInvest;

                $value->status = 3;  // refunded
                $value->save();

                if ($rootBetInvest->isMultiBet == 1) {
                    $newRatio = $rootBetInvest->ratio / $value->ratio;
                    $rootBetInvest->ratio = $newRatio;
                    $rootBetInvest->return_amount = $rootBetInvest->invest_amount * $newRatio;
                    $rootBetInvest->save();
                } else {
                    $user = $value->user;
                    $charge = ($rootBetInvest->invest_amount * $basic->refund_charge) / 100;
                    $user->balance += ($rootBetInvest->invest_amount - $charge);
                    $user->save();

                    $rootBetInvest->status = 2;
                    $rootBetInvest->save();

                    $title = $rootBetInvest->invest_amount . ' ' . $basic->currency . " refunded by admin policy on " . $rootBetInvest->transaction_id;
                    $trx = strRandom();
                    BasicService::makeTransaction($user, getAmount($rootBetInvest->invest_amount - $charge), getAmount($charge), '+', $trx, $title, $rootBetInvest->id, BetInvest::class);
                }

            }

        }
        return back()->with('success', 'Investor Amount has been refunded');
    }

    public function betUser($questionId)
    {

        $question = GameQuestions::with(['gameMatch.gameTeam1:id,name', 'gameMatch.gameTeam2:id,name'])->findOrFail($questionId);
        $data['betInvestLogs'] = BetInvestLog::with(['user', 'gameQuestion.winOption', 'gameOption'])->where('question_id', $questionId)->orderBy('id', 'desc')->paginate(config('basic.paginate'));

        $data['question'] = $question;
        $data['matchName'] = @$question->gameMatch->gameTeam1->name . ' VS ' . @$question->gameMatch->gameTeam2->name;
        return view('admin.result_history.userList', $data);
    }
}
