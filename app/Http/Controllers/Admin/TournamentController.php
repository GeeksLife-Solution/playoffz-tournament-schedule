<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameCategory;
use App\Models\GameTournament;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Yajra\DataTables\Facades\DataTables;

class TournamentController extends Controller
{
    public function listTournament()
    {
        $data['tournaments'] = collect(GameTournament::selectRaw('COUNT(id) AS totalTournament')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeTournament')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeTournamentPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS inActiveTournament')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS inActiveTournamentPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayTournament')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayTournamentPercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthTournament')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthTournamentPercentage')
            ->get()
            ->toArray())->collapse();

        $data['categories'] = GameCategory::whereStatus(1)->orderBy('name', 'asc')->get();
        return view('admin.tournament.list', $data);
    }

    public function listTournamentSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStatus = $request->filterStatus;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $categories = GameTournament::orderBy('id', 'DESC')
            ->with('gameCategory')
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('name', 'LIKE', '%' . $filterName . '%');
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
                        });
                });
            });
        return DataTables::of($categories)
            ->addColumn('checkbox', function ($item) {
                return '<input type="checkbox" id="chk-' . $item->id . '"
                                       class="form-check-input row-tic tic-check" name="check" value="' . $item->id . '"
                                       data-id="' . $item->id . '">';

            })
            ->addColumn('name', function ($item) {
                return $item->name;
            })
            ->addColumn('category', function ($item) {
                return optional($item->gameCategory)->icon . ' ' . optional($item->gameCategory)->name;
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
            ->addColumn('action', function ($item) {
                if (adminAccessRoute(config('role.manage_game.access.edit'))) {
                    $updateRoute = route('admin.updateTournament', $item->id);
                    $delete = route('admin.deleteTournament', $item->id);

                    $html = "<div class='btn-group' role='group'>
                      <a href='javascript:void(0)' class='btn btn-white btn-sm editBtn' data-action='" . $updateRoute . "'
                      data-title='" . $item->name . "' data-status='" . $item->status . "' data-category='" . $item->gameCategory->id . "'>
                        <i class='fal fa-edit me-1'></i> Edit
                      </a>";

                    $html .= '<div class="btn-group">
                      <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
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
            ->rawColumns(['checkbox', 'name', 'category', 'status', 'created_at', 'action'])
            ->make(true);
    }

    public function storeTournament(Request $request)
    {

        $purifiedData = $request->all();
        $rules = [
            'name' => 'required|max:40',
            'category' => 'required',
        ];
        $message = [
            'name.required' => 'Name field is required',
            'category.required' => 'Category field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {

            $gameTournament = new GameTournament();

            if ($request->has('name')) {
                $gameTournament->name = @$purifiedData['name'];
            }
            if ($request->has('category')) {
                $gameTournament->category_id = $request->category;
            }

            $gameTournament->status = $purifiedData['status'];

            $gameTournament->save();
            return back()->with('success', 'Successfully Saved');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function updateTournament(Request $request, $id)
    {
        $purifiedData = $request->all();
        $rules = [
            'name' => 'required|max:40',
            'category' => 'required',
        ];
        $message = [
            'name.required' => 'Name field is required',
            'category.required' => 'Category field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {
            $gameTournament = GameTournament::findOrFail($id);

            if ($request->has('name')) {
                $gameTournament->name = @$purifiedData['name'];
            }

            if ($request->has('category')) {
                $gameTournament->category_id = $request->category;
            }

            $gameTournament->status = $purifiedData['status'];

            $gameTournament->save();
            return back()->with('success', 'Successfully Updated');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function deleteTournament($id)
    {
        $gameTournament = GameTournament::with('gameMatch')->findOrFail($id);

        if (0 < count($gameTournament->gameMatch)) {
            session()->flash('warning', 'This tournament has a lot of match');
            return back();
        }

        $gameTournament->delete();
        return back()->with('success', 'Successfully deleted');
    }

    public function multiStatusChange(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameTournament::select(['id', 'status'])->whereIn('id', $request->strIds)
                ->get()->map(function ($query) {
                    $query->status = ($query->status == 1) ? 0 : 1;
                    $query->save();
                    return $query;
                });
            session()->flash('success', 'Tournament Status has been change');
            return response()->json(['success' => 1]);
        }
    }
}
