<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\GameCategory;
use App\Models\GameSchedule;

class ScheduleController extends Controller
{
   public function listSchedule(){
    $title = "List Schedule";
    $userId = Auth::id();
    $data['schedule'] = GameSchedule::with('gameCategory')->where('user_id',$userId)->where('status',1)->get();
    return view(template() . 'user.schedule.list',compact('title','data'));
   }

   public function createSchedule(){
    $title = "Create Schedule";
    $data['category'] = GameCategory::select('name','id')->get();
    return view(template() . 'user.schedule.create', compact('title','data'));
   }

   public function storeSchedule(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'category' => 'required',
                'team' => 'required|integer|min:2',
                'type' => 'required|string',
            ]);

            // Use Cache to retrieve category to reduce DB queries
            $category = GameCategory::select('name', 'id','image')->where('id', $validatedData['category'])->first();

            if (!$category) {
                return redirect()->back()->with('error','Invalid category selected.');
            }

            // Generate tournament name
            $scheduleName = "{$validatedData['team']} Team {$category->name} Schedule";

            // Start Database Transaction
            DB::beginTransaction();

            // Insert schedule data
            GameSchedule::create([
                // 'tid' => 1,
                'category_id' => $category->id,
                'image' => $category->image,
                'name' => $scheduleName,
                'user_id' => Auth::id(),
                'teams' => $validatedData['team'],
                'type' => $validatedData['type'],
                'status'=>1
            ]);

            // Commit Transaction
            DB::commit();

            return redirect()->route('user.schedule.list')->with('success', 'Tournament and Schedule created successfully!');
        } catch (\Exception $e) {
            // Rollback Transaction in case of an error
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }

    public function Registrations(){
        $title = "Registrations";
        return view(template() . 'user.registrations.index', compact('title'));
    }

    public function editSchedule(Request $request)
    {
        $title = "Edit Schedule";

        try {
            // Attempt to decrypt and validate member ID
            $urlParams = decryptUrlParam($request->eq);
            $scheduleId = $urlParams['schedule_id'] ?? null;

            if (!$scheduleId) {
                return redirect()->back()->with('error', 'Invalid schedule');
            }

            $userId = Auth::id();

            // Fetch the schedule
            $data['schedule'] = GameSchedule::with('gameCategory')
                ->where('user_id', $userId)
                ->whereIn('status', [1,0])
                ->first();

            return view(template() . 'user.schedule.edit', compact('title', 'data'));

        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while processing your request.');
        }
    }

}
