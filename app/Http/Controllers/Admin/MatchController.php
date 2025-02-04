<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameCategory;
use App\Models\GameMatch;
use App\Models\GameOption;
use App\Models\GameQuestions;
use App\Models\GameTeam;
use App\Models\GameTournament;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MatchController extends Controller
{
    use Upload, Notify;

    public function listMatch()
    {
        $data['matches'] = collect(GameMatch::selectRaw('COUNT(id) AS totalMatch')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeMatch')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeMatchPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS inActiveMatch')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS inActiveMatchPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayMatch')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayMatchPercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthMatch')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthMatchPercentage')
            ->get()
            ->toArray())->collapse();

        $data['tournaments'] = GameTournament::with('gameCategory')->whereStatus(1)->orderBy('name', 'asc')->get();
        $data['categories'] = GameCategory::whereStatus(1)->orderBy('name', 'asc')->get();
        return view('admin.match.list', $data);
    }


    public function listMatchSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStatus = $request->filterStatus;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $matches = GameMatch::orderBy('id', 'DESC')->withCount('activeQuestions')
            ->with(['gameCategory', 'gameTournament', 'gameTeam1', 'gameTeam2'])
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where(function ($subquery) use ($filterName) {
                    $subquery->where('name', 'LIKE', "%{$filterName}%")
                        ->orWhereHas('gameCategory', function ($categoryQuery) use ($filterName) {
                            $categoryQuery->where('name', 'LIKE', "%{$filterName}%");
                        })
                        ->orWhereHas('gameTournament', function ($categoryQuery) use ($filterName) {
                            $categoryQuery->where('name', 'LIKE', "%{$filterName}%");
                        })
                        ->orWhereHas('gameTeam1', function ($categoryQuery) use ($filterName) {
                            $categoryQuery->where('name', 'LIKE', "%{$filterName}%");
                        })
                        ->orWhereHas('gameTeam2', function ($categoryQuery) use ($filterName) {
                            $categoryQuery->where('name', 'LIKE', "%{$filterName}%");
                        });
                });
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
                    $subquery->where('name', 'LIKE', "%{$search}%")
                        ->orWhereHas('gameCategory', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('gameTournament', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('gameTeam1', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('gameTeam2', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'LIKE', "%{$search}%");
                        });
                });
            });
        return DataTables::of($matches)
            ->addColumn('checkbox', function ($item) {
                return '<input type="checkbox" id="chk-' . $item->id . '"
                                       class="form-check-input row-tic tic-check" name="check" value="' . $item->id . '"
                                       data-id="' . $item->id . '">';

            })
            ->addColumn('match', function ($item) {
                $team1Img = getFile(optional($item->gameTeam1)->driver, optional($item->gameTeam1)->image);
                $team2Img = getFile(optional($item->gameTeam2)->driver, optional($item->gameTeam2)->image);

                return '<div class="d-lg-flex d-block align-items-center ">
                                    <div class="me-3 cursor-pointer" title="' . optional($item->gameTeam1)->name . '">
                                        <small class="text-dark font-weight-bold">' . shortName(optional($item->gameTeam1)->name) . '</small>
                                    </div>
                                    <div class="me-2 cursor-pointer" title="' . optional($item->gameTeam1)->name . '">
                                        <img src="' . $team1Img . '" alt="user" class="rounded-circle" width="25" height="25">
                                    </div>
                                    <small class="font-italic mb-0 font-16 ">vs</small>

                                    <div class="me-3 ms-2 cursor-pointer" title="' . optional($item->gameTeam2)->name . '">
                                        <img src="' . $team2Img . '" alt="user" class="rounded-circle" width="25" height="25">
                                    </div>
                                    <div class="cursor-pointer" title="' . optional($item->gameTeam2)->name . '">
                                        <small class="text-dark font-weight-bold">' . shortName(optional($item->gameTeam2)->name) . '</small>
                                    </div>
                                </div>';
            })
            ->addColumn('active_question', function ($item) {
                return '<span class="badge bg-soft-primary text-secondary">' . number_format($item->active_questions_count) . '</span>';
            })
            ->addColumn('tournament', function ($item) {
                return optional($item->gameTournament)->name;
            })
            ->addColumn('category', function ($item) {
                return optional($item->gameCategory)->icon . ' ' . optional($item->gameCategory)->name;
            })
            ->addColumn('start_date', function ($item) {
                return dateTime($item->start_date, 'd M, Y');
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Active') . '
                  </span>';

                } else {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('In Active') . '
                  </span>';
                }
            })
            ->addColumn('created_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('locker', function ($item) {
                if (adminAccessRoute(config('role.manage_game.access.edit'))) {
                    $route = route('admin.match.locker', $item->id);
                    if ($item->is_unlock == 1) {
                        $btnClass = 'primary';
                        $iClass = 'fas fa-unlock';
                        $btnName = 'Unlock Now';
                    } else {
                        $btnClass = 'dark';
                        $iClass = 'fas fa-lock';
                        $btnName = 'Lock Now';
                    }
                    return "<a href='" . $route . "' class='btn btn-white btn-sm'>
                       <i class='" . $iClass . "'></i> $btnName
                      </a>";
                } else {
                    return '-';
                }
            })
            ->addColumn('action', function ($item) {
                if (adminAccessRoute(config('role.manage_game.access.edit'))) {
                    $updateRoute = route('admin.updateMatch', $item->id);
                    $delete = route('admin.deleteMatch', $item->id);

                    $makeQuestion = route('admin.addQuestion', $item->id);
                    $questionList = route('admin.infoMatch', $item->id);

                    $html = "<div class='btn-group' role='group'>
                      <a href='javascript:void(0)' class='btn btn-white btn-sm editBtn' data-action='" . $updateRoute . "'
                      data-match='" . $item . "' data-status='" . $item->status . "'>
                        <i class='fal fa-edit me-1'></i> Edit
                      </a>";

                    $html .= '<div class="btn-group">
                      <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                        <a class="dropdown-item" href="' . $makeQuestion . '">
                          <i class="fal fa-plus-circle dropdown-item-icon"></i> ' . trans("Make Question") . '
                       </a>
                       <a class="dropdown-item" href="' . $questionList . '">
                          <i class="fal fa-list-alt dropdown-item-icon"></i> ' . trans("Question List") . '
                       </a>
                        <a class="dropdown-item delete_btn" href="javascript:void(0)" data-bs-target="#delete"
                           data-bs-toggle="modal" data-route="' . $delete . '">
                          <i class="fal fa-trash dropdown-item-icon"></i> ' . trans("Delete") . '
                       </a>
                      </div>
                    </div>';

                    $html .= '</div>';
                    return $html;
                } else {
                    return '-';
                }
            })
            ->rawColumns(['checkbox', 'match', 'active_question', 'tournament', 'category', 'start_date', 'status', 'locker', 'action'])
            ->make(true);
    }


    public function ajaxListMatch(Request $request)
    {
        $team = GameTeam::where('category_id', $request->categoryId)->orderBy('name')->get();
        $tournament = GameTournament::where('category_id', $request->categoryId)->orderBy('name')->get();
        return [
            'team' => $team,
            'tournament' => $tournament,
        ];
    }

    public function storeMatch(Request $request)
    {
        $purifiedData = $request->all();
        $rules = [
            'category' => 'required',
            'tournament' => 'required',
            'team1' => 'required',
            'team2' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ];
        $message = [
            'category.required' => 'Category field is required',
            'tournament.required' => 'Tournament field is required',
            'team1.required' => 'Team 1 field is required',
            'team2.required' => 'Team 2 field is required',
            'start_date.required' => 'Start date field is required',
            'end_date.required' => 'End date field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {
            $gameMatch = new GameMatch();
            if ($request->has('category')) {
                $gameMatch->category_id = @$purifiedData['category'];
            }
            if ($request->has('tournament')) {
                $gameMatch->tournament_id = @$purifiedData['tournament'];
            }
            if ($request->has('team1')) {
                $gameMatch->team1_id = @$purifiedData['team1'];
            }
            if ($request->has('team2')) {
                $gameMatch->team2_id = @$purifiedData['team2'];
            }
            if ($request->has('start_date')) {
                $gameMatch->start_date = @$purifiedData['start_date'];
            }
            if ($request->has('end_date')) {
                $gameMatch->end_date = @$purifiedData['end_date'];
            }
            if ($request->has('name')) {
                $gameMatch->name = @$purifiedData['name'];
            }

            $gameMatch->status = $purifiedData['status'];
            $gameMatch->save();

            $query = $gameMatch;
            if (Carbon::parse($gameMatch->start_date) > Carbon::now()) {
                $type = 'UpcomingList';
            } else {
                $type = 'Enlisted';
            }
            $this->matchEvent($query, $type);
            return back()->with('success', 'Successfully Saved');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function updateMatch(Request $request, $id)
    {
        $purifiedData = $request->all();
        $rules = [
            'category' => 'required',
            'tournament' => 'required',
            'team1' => 'required',
            'team2' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ];
        $message = [
            'category.required' => 'Category field is required',
            'tournament.required' => 'Tournament field is required',
            'team1.required' => 'Team 1 field is required',
            'team2.required' => 'Team 2 field is required',
            'start_date.required' => 'Start date field is required',
            'end_date.required' => 'End date field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {
            $gameMatch = GameMatch::findOrFail($id);

            if ($request->has('category')) {
                $gameMatch->category_id = @$purifiedData['category'];
            }
            if ($request->has('tournament')) {
                $gameMatch->tournament_id = @$purifiedData['tournament'];
            }
            if ($request->has('team1')) {
                $gameMatch->team1_id = @$purifiedData['team1'];
            }
            if ($request->has('team2')) {
                $gameMatch->team2_id = @$purifiedData['team2'];
            }
            if ($request->has('start_date')) {
                $gameMatch->start_date = @$purifiedData['start_date'];
            }
            if ($request->has('end_date')) {
                $gameMatch->end_date = @$purifiedData['end_date'];
            }
            if ($request->has('name')) {
                $gameMatch->name = @$purifiedData['name'];
            }

            $gameMatch->status = $purifiedData['status'];
            $gameMatch->save();


            $query = $gameMatch;

            if (Carbon::parse($gameMatch->start_date) > Carbon::now()) {
                $type = 'UpcomingList';
            } else {
                $type = 'Enlisted';
            }
            $this->matchEvent($query, $type);

            return back()->with('success', 'Successfully Updated');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function deleteMatch($id)
    {
        $gameMatch = GameMatch::withCount('gameQuestions')->findOrFail($id);

        if (0 < $gameMatch->game_questions_count) {
            session()->flash('warning', 'This item has a lot of Question. At first delete those data');
            return back();
        }
        $gameMatch->delete();
        return back()->with('success', 'Successfully deleted');
    }

    public function multiStatusChange(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameMatch::select(['id', 'status'])->whereIn('id', $request->strIds)
                ->get()->map(function ($query) {
                    $query->status = ($query->status == 1) ? 0 : 1;
                    $query->save();
                    return $query;
                });
            session()->flash('success', 'Match Status has been change');
            return response()->json(['success' => 1]);
        }
    }

    public function addQuestion($match_id = null)
    {
        $match = GameMatch::with(['gameTeam1', 'gameTeam2'])
            ->findOrFail($match_id);

        if ($match->status == '2') {
            return redirect()->route('admin.listMatch')->with('error', 'Match already closed.');
        }

        $data['match'] = $match;
        return view('admin.match.freeQuestion', $data);
    }

    public function storeQuestion(Request $request)
    {

        if (!$request->index) {
            session()->flash('warning', 'Invalid Request');
            return back();
        }

        foreach ($request->index as $key => $value) {
            $betQues = new GameQuestions();
            $betQues->match_id = $request->match_id[$value][0];
            $betQues->creator_id = Auth::guard('admin')->id();
            $betQues->name = $request->question[$value][0];
            $betQues->status = $request->question_status[$value][0];
            $betQues->end_time = Carbon::parse($request->end_time[$value][0]);
            $betQues->save();
            if (!empty($request->option_name[$value])) {
                foreach ($request->option_name[$value] as $k => $item) {
                    $betOpt = new GameOption();
                    $betOpt->creator_id = Auth::guard('admin')->id();
                    $betOpt->question_id = $betQues->id;
                    $betOpt->match_id = $betQues->match_id;
                    $betOpt->option_name = $item;
                    $betOpt->ratio = $request->ratio[$value][$k];
                    $betOpt->status = $request->status[$value][$k];
                    $betOpt->save();
                }
            }

        }

        $query = GameMatch::find(collect($request->match_id)->collapse()->first());
        $this->matchEvent($query);

        session()->flash('success', 'Saved  Successfully');
        return back();
    }

    public function infoMatch($match_id)
    {
        $data['match'] = GameMatch::with(['gameTeam1', 'gameTeam2'])->findOrFail($match_id);
        $data['gameQuestions'] = GameQuestions::where('match_id', $match_id)->orderBy('id', 'desc')->get();
        return view('admin.match.questionList', $data);
    }

    public function updateQuestion(Request $request)
    {

        $purifiedData = $request->all();

        $rules = [
            'questionId' => 'required',
            'name' => 'required',
            'status' => 'required',
            'end_time' => 'required',
        ];
        $message = [
            'questionId.required' => 'Something Went Wrong',
            'name.required' => 'Name field is required',
            'status.required' => 'Status field is required',
            'end_time.required' => 'End Time field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {
            $gameQuestion = GameQuestions::findOrFail($request->questionId);

            if ($gameQuestion->result == 1) {
                return back()->with('error', 'Question Result Over');
            }
            $gameQuestion->name = $request->name;
            $gameQuestion->status = $request->status;
            $gameQuestion->end_time = $request->end_time;
            $gameQuestion->save();


            $query = $gameQuestion->gameMatch;
            $this->matchEvent($query);


            session()->flash('success', 'Updated  Successfully');
            return back();

        } catch (\Exception $e) {
            session()->flash('warning', 'Something Went Wrong');
            return back();
        }
    }

    public function deleteQuestion($id)
    {
        $gameQuestion = GameQuestions::withCount('gameOptions')->findOrFail($id);
        if (0 < $gameQuestion->game_options_count) {
            session()->flash('warning', 'This item has a lot of options. At first delete those data');
            return back();
        }
        $gameQuestion->delete();
        return back()->with('success', 'Successfully deleted');
    }

    public function activeQsMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameQuestions::whereIn('id', $request->strIds)->update([
                'status' => 1,
            ]);
            session()->flash('success', 'Questions Has Been Active');
            return response()->json(['success' => 1]);
        }

    }

    public function deActiveQsMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameQuestions::whereIn('id', $request->strIds)->update([
                'status' => 0,
            ]);
            session()->flash('success', 'Questions Has Been Deactive');
            return response()->json(['success' => 1]);
        }
    }

    public function closeQsMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameQuestions::whereIn('id', $request->strIds)->update([
                'status' => 2,
            ]);
            session()->flash('success', 'Questions Has Been Deactive');
            return response()->json(['success' => 1]);
        }
    }

    public function matchLocker($id)
    {
        $gamematch = GameMatch::find($id);
        if ($gamematch->is_unlock == 1) {
            $gamematch->is_unlock = 0;
            session()->flash('success', 'Match has been unlocked');
        } else {
            $gamematch->is_unlock = 1;

            session()->flash('info', 'Match has been locked');
        }
        $gamematch->save();

        $query = $gamematch;
        $this->matchEvent($query);
        return back();
    }

}
