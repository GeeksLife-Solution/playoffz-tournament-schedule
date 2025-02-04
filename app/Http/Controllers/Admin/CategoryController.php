<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function listCategory()
    {
        $data['categories'] = collect(GameCategory::selectRaw('COUNT(id) AS totalCategory')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeCategory')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeCategoryPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS inActiveCategory')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS inActiveCategoryPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayCategory')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayCategoryPercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthCategory')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthCategoryPercentage')
            ->get()
            ->toArray())->collapse();

        $games = config('games');
        ksort($games);
        $data['games'] = $games;

        return view('admin.category.list', $data);
    }

    public function listCategorySearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStatus = $request->filterStatus;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $categories = GameCategory::orderBy('id', 'DESC')
            ->withCount('activeTournament', 'activeTeam', 'activeMatch')
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
                    $subquery->where('name', 'LIKE', "%{$search}%");
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
            ->addColumn('active_tournament', function ($item) {
                return '<span class="badge bg-soft-primary text-primary">' . number_format($item->active_tournament_count) . '</span>';
            })
            ->addColumn('active_team', function ($item) {
                return '<span class="badge bg-soft-primary text-success">' . number_format($item->active_team_count) . '</span>';
            })
            ->addColumn('active_match', function ($item) {
                return '<span class="badge bg-soft-primary text-secondary">' . number_format($item->active_match_count) . '</span>';
            })
            ->addColumn('icon', function ($item) {
                return $item->icon;
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
                    $updateRoute = route('admin.updateCategory', $item->id);
                    $delete = route('admin.deleteCategory', $item->id);

                    $html = "<div class='btn-group' role='group'>
                      <a href='javascript:void(0)' class='btn btn-white btn-sm editBtn' data-action='" . $updateRoute . "'
                      data-title='" . $item->name . "' data-status='" . $item->status . "' data-icon='" . $item->icon . "'>
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
            ->rawColumns(['checkbox', 'name', 'active_tournament', 'active_team', 'active_match', 'icon', 'status', 'created_at', 'action'])
            ->make(true);
    }

    public function storeCategory(Request $request)
    {

        $purifiedData = $request->all();

        $rules = [
            'title' => 'required|max:40',
            'icon' => 'required',
        ];
        $message = [
            'title.required' => 'Title field is required',
            'icon.required' => 'Icon field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {
            $gameCategory = new GameCategory();

            if (isset($purifiedData['title'])) {
                $gameCategory->name = @$purifiedData['title'];
            }
            if (isset($purifiedData['icon'])) {
                $gameCategory->icon = $request->icon;
            }

            $gameCategory->status = $purifiedData['status'];

            $gameCategory->save();
            return back()->with('success', 'Successfully Saved');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function updateCategory(Request $request, $id)
    {
        $purifiedData = $request->all();
        $rules = [
            'title' => 'required|max:40',
            'icon' => 'required',
        ];
        $message = [
            'title.required' => 'Title field is required',
            'icon.required' => 'Icon field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        try {
            $gameCategory = GameCategory::findOrFail($id);

            if ($request->has('title')) {
                $gameCategory->name = @$purifiedData['title'];
            }
            if ($request->has('icon')) {
                $gameCategory->icon = $request->icon;
            }

            $gameCategory->status = $purifiedData['status'];

            $gameCategory->save();
            return back()->with('success', 'Successfully Updated');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function deleteCategory($id)
    {
        $gameCategory = GameCategory::with(['gameTournament', 'gameTeam', 'gameMatch'])->findOrFail($id);

        if (0 < count($gameCategory->gameTournament)) {
            session()->flash('warning', 'This category has a lot of tournament');
            return back();
        }
        if (0 < count($gameCategory->gameTeam)) {
            session()->flash('warning', 'This category has a lot of team');
            return back();
        }
        if (0 < count($gameCategory->gameMatch)) {
            session()->flash('warning', 'This category has a lot of Match');
            return back();
        }

        $gameCategory->delete();
        return back()->with('success', 'Successfully deleted');
    }

    public function multiStatusChange(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameCategory::select(['id', 'status'])->whereIn('id', $request->strIds)
                ->get()->map(function ($query) {
                    $query->status = ($query->status == 1) ? 0 : 1;
                    $query->save();
                    return $query;
                });
            session()->flash('success', 'Category Status has been change');
            return response()->json(['success' => 1]);
        }

    }

}
