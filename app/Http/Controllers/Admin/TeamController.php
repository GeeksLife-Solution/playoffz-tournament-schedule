<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameCategory;
use App\Models\GameTeam;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TeamController extends Controller
{
    use Upload;

    public function listTeam()
    {
        $data['teams'] = collect(GameTeam::selectRaw('COUNT(id) AS totalTeam')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeTeam')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeTeamPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS inActiveTeam')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS inActiveTeamPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayTeam')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayTeamPercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthTeam')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthTeamPercentage')
            ->get()
            ->toArray())->collapse();

        $data['categories'] = GameCategory::whereStatus(1)->orderBy('name', 'asc')->get();
        return view('admin.team.list', $data);
    }

    public function listTeamSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStatus = $request->filterStatus;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $categories = GameTeam::orderBy('id', 'DESC')
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
                $url = getFile($item->driver, $item->image);
                return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                                <div class="flex-shrink-0">
                                  <div class="avatar avatar-sm avatar-circle">
                                    <img class="avatar-img" src="' . $url . '" alt="Image Description">
                                  </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . $item->name . '</h5>
                                </div>
                              </a>';
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
                $img = getFile($item->driver, $item->image);
                $updateRoute = route('admin.updateTeam', $item->id);
                $delete = route('admin.deleteTeam', $item->id);

                $html = "<div class='btn-group' role='group'>
                      <a href='javascript:void(0)' class='btn btn-white btn-sm editBtn' data-action='" . $updateRoute . "'
                      data-image='" . $img . "'
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
            })
            ->rawColumns(['checkbox', 'name', 'category', 'status', 'created_at', 'action'])
            ->make(true);
    }

    public function storeTeam(Request $request)
    {
        $purifiedData = $request->all();

        if ($request->has('image')) {
            $purifiedData['image'] = $request->image;
        }
        $rules = [
            'name' => 'required|max:40',
            'category' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png,webp',
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

            $gameTeam = new GameTeam();

            if ($request->has('category')) {
                $gameTeam->category_id = @$purifiedData['category'];
            }
            if ($request->has('name')) {
                $gameTeam->name = @$purifiedData['name'];
            }
            if ($request->hasFile('image')) {
                try {
                    $uploadedImage = $this->fileUpload($request->image, config('filelocation.team.path'), null, config('filelocation.team.size'), 'webp', 60);
                    if ($uploadedImage) {
                        $gameTeam->image = $uploadedImage['path'];
                        $gameTeam->driver = $uploadedImage['driver'];
                    }
                } catch (\Exception $exp) {
                    return back()->with('error', 'Image could not be uploaded.');
                }
            }

            $gameTeam->status = $purifiedData['status'];

            $gameTeam->save();
            return back()->with('success', 'Successfully Saved');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function updateTeam(Request $request, $id)
    {
        $purifiedData = $request->all();
        if ($request->has('image')) {
            $purifiedData['image'] = $request->image;
        }
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
            $gameTeam = GameTeam::findOrFail($id);

            if ($request->has('category')) {
                $gameTeam->category_id = @$purifiedData['category'];
            }
            if ($request->has('name')) {
                $gameTeam->name = @$purifiedData['name'];
            }
            if ($request->hasFile('image')) {
                $uploadedImage = $this->fileUpload($request->image, config('filelocation.team.path'), null, config('filelocation.team.size'), 'webp', 60, $gameTeam->image, $gameTeam->driver);
                if ($uploadedImage) {
                    $gameTeam->image = $uploadedImage['path'];
                    $gameTeam->driver = $uploadedImage['driver'];
                }
            }

            $gameTeam->status = $purifiedData['status'];

            $gameTeam->save();
            return back()->with('success', 'Successfully Updated');

        } catch (\Exception $e) {
            return back();
        }
    }

    public function deleteTeam($id)
    {
        $gameTeam = GameTeam::with(['gameTeam1', 'gameTeam2'])->findOrFail($id);

        if (0 < count($gameTeam->gameTeam1)) {
            session()->flash('warning', 'This team has a lot of match');
            return back();
        }
        if (0 < count($gameTeam->gameTeam2)) {
            session()->flash('warning', 'This team has a lot of match');
            return back();
        }

        $gameTeam->delete();
        return back()->with('success', 'Successfully deleted');
    }

    public function multiStatusChange(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            GameTeam::select(['id', 'status'])->whereIn('id', $request->strIds)
                ->get()->map(function ($query) {
                    $query->status = ($query->status == 1) ? 0 : 1;
                    $query->save();
                    return $query;
                });
            session()->flash('success', 'Team Status has been change');
            return response()->json(['success' => 1]);
        }
    }
}
