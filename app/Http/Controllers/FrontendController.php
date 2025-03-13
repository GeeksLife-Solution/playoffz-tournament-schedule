<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\Content;
use App\Models\ContentDetails;
use App\Models\GameCategory;
use App\Models\GameMatch;
use App\Models\ManageMenu;
use App\Models\PageDetail;
use App\Models\Subscriber;
use App\Traits\Frontend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Facades\App\Console\Commands\Cron;

class FrontendController extends Controller
{
    use Frontend;

    public function page($slug = '/')
    {
        try {
            $selectedTheme = basicControl()->theme ?? 'light';
            $existingSlugs = collect([]);
            DB::table('pages')->select('slug')->get()->map(function ($item) use ($existingSlugs) {
                $existingSlugs->push($item->slug);
            });

            if (!in_array($slug, $existingSlugs->toArray())) {
                abort(404);
            }

            $pageDetails = PageDetail::with('page')
                ->whereHas('page', function ($query) use ($slug, $selectedTheme) {
                    $query->where(['slug' => $slug, 'template_name' => $selectedTheme]);
                })
                ->firstOrFail();

            $pageSeo = [
                'page_title' => optional($pageDetails->page)->page_title,
                'meta_title' => optional($pageDetails->page)->meta_title,
                'meta_keywords' => implode(',', optional($pageDetails->page)->meta_keywords ?? []),
                'meta_description' => optional($pageDetails->page)->meta_description,
                'og_description' => optional($pageDetails->page)->og_description,
                'meta_robots' => optional($pageDetails->page)->meta_robots,
                'meta_image' => getFile(optional($pageDetails->page)->meta_image_driver, optional($pageDetails->page)->meta_image),
                'breadcrumb_image' => optional($pageDetails->page)->breadcrumb_status ?
                    getFile(optional($pageDetails->page)->breadcrumb_image_driver, optional($pageDetails->page)->breadcrumb_image) : null,
            ];
            $sectionsData = $this->getSectionsData($pageDetails->sections, $pageDetails->content, $selectedTheme);

            return view("themes.{$selectedTheme}.page", compact('sectionsData', 'pageSeo'));
        } catch (\Exception $e) {
            \Cache::forget('ConfigureSetting');
//            die("Unable to establish a connection to the database. Please check your connection settings and try again later");

            return redirect()->route('instructionPage');
        }
    }

    // public function category($slug = null, $id = null)
    // {
    //     // Fetch all active game categories (always needed for listing)
    //     $data['gameCategories'] = GameCategory::withCount('gameActiveMatch')
    //         ->where('status', 1)
    //         ->orderBy('game_active_match_count', 'desc')
    //         ->get();

    //     // Fetch the selected category with only the latest 5 schedules
    //     if ($id) {
    //         $data['selectedCategory'] = GameCategory::where('id', $id)
    //             ->with(['gameSchedule' => function ($query) {
    //                 $query->latest()->limit(5); // Fetch latest 5 schedules
    //             }])
    //             ->first();
    //     } else {
    //         $data['selectedCategory'] = null;
    //     }

    //     return view(template() . 'home', $data);
    // } 

    public function category($slug = null, $id = null)
    {
        // Fetch all active game categories with active match count
        $gameCategories = GameCategory::withCount('gameActiveMatch')
            ->where('status', 1)
            ->orderByDesc('game_active_match_count')
            ->get();

        // Fetch the selected category along with latest 5 schedules and their matches
        $selectedCategory = $id
            ? GameCategory::with(['gameSchedule' => function ($query) {
                    $query->latest()->limit(5)->with(['gameMatch.team1', 'gameMatch.team2']);
                }])
                ->find($id)
            : null;

        $showall = false;

        return view(template() . 'home', compact('gameCategories','showall', 'selectedCategory'));
    }

    public function getFullSchedule($scheduleId)
    {
        // Fetch all matches for the given schedule in a single query with relationships
        $matches = GameMatch::where('schedule_id', $scheduleId)
            ->with(['team1', 'team2'])
            ->orderBy('id')
            ->get()
            ->map(function ($match) {
                return [
                    'id' => $match->id,
                    'round' => $match->round,
                    'team1_name' => $match->team1->name ?? ($match->team1_placeholder ?? 'BYE'),
                    'team2_name' => $match->team2->name ?? ($match->team2_placeholder ?? 'BYE'),
                    'team1_score' => $match->team1_score ?? '-',
                    'team2_score' => $match->team2_score ?? '-',
                    'winner_id' => $match->winner_id,
                ];
            });

        return response()->json($matches);
    }


    public function tournament($slug = null, $id)
    {
        $data['gameCategories'] = GameCategory::with(['activeTournament'])->withCount('gameActiveMatch')->whereStatus(1)->orderBy('game_active_match_count', 'desc')->get();
        return view(template() . 'home', $data);
    }

    public function match($slug = null, $id)
    {
        $data['gameCategories'] = GameCategory::with(['activeTournament'])->withCount('gameActiveMatch')->whereStatus(1)->orderBy('game_active_match_count', 'desc')->get();
        return view(template() . 'home', $data);
    }

    public function betResult()
    {
        $data['betResult'] = GameMatch::with(['gameQuestions.gameOptionResult', 'gameTeam1', 'gameTeam2'])
            ->whereHas('gameQuestions.gameOptionResult', function ($qq) {
                $qq->where('result', '1');
            })
            ->orderBy('id', 'desc')->limit(10)->get();
        return view(template() . 'user.betResult.index', $data);
    }

    public function contactSend(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|max:91',
            'subject' => 'required|max:100',
            'message' => 'required|max:1000',
        ]);
        $requestData = $request->all();
        $name = $requestData['name'];
        $email_from = $requestData['email'];
        $subject = $requestData['subject'];
        $message = $requestData['message'] . "<br>Regards<br>" . $name;
        $from = $email_from;

        Mail::to(basicControl()->sender_email)->send(new SendMail($from, $subject, $message));
        return back()->with('success', 'Mail has been sent');
    }

    public function subscribe(Request $request)
    {
        $purifiedData = $request->all();
        $validationRules = [
            'email' => 'required|email|min:8|max:100|unique:subscribers',
        ];
        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            session()->flash('error', 'Email Field is required');
            return back()->withErrors($validate)->withInput();
        }
        $purifiedData = (object)$purifiedData;

        $subscribe = new Subscriber();
        $subscribe->email = $purifiedData->email;
        $subscribe->save();

        return back()->with('success', 'Subscribed successfully');
    }

    public function blogDetails($id)
    {
        $blogDetails = ContentDetails::findOrFail($id);

        $pageDetails = PageDetail::with('page')
            ->whereHas('page', function ($query) {
                $query->where(['slug' => 'blog', 'template_name' => basicControl()->theme]);
            })
            ->firstOrFail();

        $pageSeo = [
            'page_title' => optional($pageDetails->page)->page_title,
            'meta_title' => optional($pageDetails->page)->meta_title,
            'meta_keywords' => implode(',', optional($pageDetails->page)->meta_keywords ?? []),
            'meta_description' => optional($pageDetails->page)->meta_description,
            'meta_image' => getFile(optional($pageDetails->page)->meta_image_driver, optional($pageDetails->page)->meta_image),
            'breadcrumb_image' => optional($pageDetails->page)->breadcrumb_status ?
                getFile(optional($pageDetails->page)->breadcrumb_image_driver, optional($pageDetails->page)->breadcrumb_image) : null,
        ];

        $relatedPosts = Content::where('name', 'blog')->where('type', 'multiple')
            ->whereHas('contentDetails', function ($query) use ($id) {
                $query->where('id', '!=', $id);
            })->get();

        return view(template() . 'blog_details', compact('blogDetails', 'relatedPosts', 'pageSeo'));
    }
}
