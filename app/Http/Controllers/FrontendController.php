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

    public function category($slug = null, $id)
    {
        $data['gameCategories'] = GameCategory::with(['activeTournament'])->withCount('gameActiveMatch')->whereStatus(1)->orderBy('game_active_match_count', 'desc')->get();
        return view(template() . 'home', $data);
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
