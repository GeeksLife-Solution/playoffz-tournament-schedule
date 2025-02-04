<?php

namespace App\Traits;

use App\Events\AdminNotification;
use App\Events\MatchNotification;
use App\Events\UserNotification;
use App\Mail\SendMail;
use App\Models\Admin;
use App\Models\FireBaseToken;
use App\Models\InAppNotification;
use App\Models\ManualSmsConfig;
use App\Models\NotificationTemplate;
use App\Models\SmsControl;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Facades\App\Services\BasicCurl;
use Facades\App\Services\SMS\BaseSmsService;


trait Notify
{
    public function sendMailSms($user, $templateKey, $params = [], $subject = null, $requestMessage = null)
    {
        $this->mail($user, $templateKey, $params, $subject, $requestMessage);
        $this->sms($user, $templateKey, $params, $requestMessage = null);
    }

    public function mail($user, $templateKey = null, $params = [], $subject = null, $requestMessage = null)
    {
        try {
            $basic = basicControl();

            if ($basic->email_notification == 1) {
                return false;
            }

            $email_body = $basic->email_description;

            $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('language_id', $user->language_id)->where('notify_for', 0)->first();
            if (!$templateObj) {
                $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('notify_for', 0)->first();
            }

            $message = str_replace("[[name]]", $user->username, $email_body);

            if (!$templateObj && $subject == null) {
                return false;
            } else {
                if ($templateObj) {
                    $message = str_replace("[[message]]", $templateObj->email, $message);
                    if (empty($message)) {
                        $message = $email_body;
                    }
                    foreach ($params as $code => $value) {
                        $message = str_replace('[[' . $code . ']]', $value, $message);
                    }
                } else {
                    $message = str_replace("[[message]]", $requestMessage, $message);
                }
            }

            $subject = ($subject == null) ? $templateObj->subject : $subject;
            $email_from = $basic->sender_email;


            Mail::to($user)->queue(new SendMail($email_from, $subject, $message));
        } catch (\Exception $exception) {
            return true;
        }
    }

    public function sms($user, $templateKey, $params = [], $requestMessage = null)
    {
        $basic = basicControl();
        if ($basic->sms_notification != 1) {
            return false;
        }

        $smsControl = ManualSmsConfig::firstOrCreate();

        $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('language_id', $user->language_id)->where('notify_for', 0)->first();
        if (!$templateObj) {
            $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('notify_for', 0)->first();
        }
        if (!$templateObj) {
            return 0;
        }

        if (!$templateObj->status['sms']) {
            return false;
        }

        if (!$templateObj && $requestMessage == null) {
            return false;
        } else {
            if ($templateObj) {
                $template = $templateObj->sms;
                foreach ($params as $code => $value) {
                    $template = str_replace('[[' . $code . ']]', $value, $template);
                }
            } else {
                $template = $requestMessage;
            }
        }

        if (config('SMSConfig.default') == 'manual') {
            $headerData = is_null($smsControl->header_data) ? [] : json_decode($smsControl->header_data, true);
            $paramData = is_null($smsControl->header_data) ? [] : json_decode($smsControl->header_data, true);
            $paramData = http_build_query($paramData);
            $actionUrl = $smsControl->header_data;
            $actionMethod = $smsControl->action_method;
            $formData = is_null($smsControl->form_data) ? [] : json_decode($smsControl->form_data, true);
            $formData = isset($headerData['Content-Type']) && $headerData['Content-Type'] == "application/x-www-form-urlencoded" ? http_build_query($formData) : (isset($headerData['Content-Type']) && $headerData['Content-Type'] == "application/json" ? json_encode($formData) : $formData);

            foreach ($headerData as $key => $data) {
                $headerData[] = "{$key}:$data";
            }

            if ($actionMethod == 'GET') {
                $actionUrl = $actionUrl . '?' . $paramData;
            }

            $formData = recursive_array_replace("[[receiver]]", $user->phone, recursive_array_replace("[[message]]", $template, $formData));
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $actionUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $actionMethod,
                CURLOPT_POSTFIELDS => $formData,
                CURLOPT_HTTPHEADER => $headerData
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        } else {
            BaseSmsService::sendSMS($user->phone, $template);
            return true;
        }
    }


    public function verifyToMail($user, $templateKey = null, $params = [], $subject = null, $requestMessage = null)
    {

        $basic = basicControl();
        if ($basic->email_verification == 0) {
            return false;
        }

        $email_body = $basic->email_description;
        $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('language_id', $user->language_id)->first();
        if (!$templateObj) {
            $templateObj = NotificationTemplate::where('template_key', $templateKey)->first();
        }

        if (!$templateObj) {
            return 0;
        }

        if (!$templateObj->status['mail']) {
            return false;
        }

        $message = str_replace("[[name]]", $user->username, $email_body);

        if (!$templateObj && $subject == null) {
            return false;
        } else {
            if ($templateObj) {
                $message = str_replace("[[message]]", $templateObj->email, $message);
                if (empty($message)) {
                    $message = $email_body;
                }
                foreach ($params as $code => $value) {
                    $message = str_replace('[[' . $code . ']]', $value, $message);
                }
            } else {
                $message = str_replace("[[message]]", $requestMessage, $message);
            }
        }

        $subject = ($subject == null) ? $templateObj->subject : $subject;
        $email_from = ($templateObj) ? $templateObj->email_from : $basic->sender_email;

        Mail::to($user)->send(new SendMail($email_from, $subject, $message));
    }

    public function verifyToSms($user, $templateKey, $params = [], $requestMessage = null)
    {

        $basic = basicControl();
        if ($basic->sms_verification == 0) {
            return false;
        }

        $smsControl = ManualSmsConfig::firstOrCreate(['id' => 1]);
        $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('language_id', $user->language_id)->where('notify_for', 0)->first();
        if (!$templateObj) {
            $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('notify_for', 0)->first();
        }


        if (!$templateObj || $templateObj->status['sms'] == 0) {
            return false;
        }


        if (!$templateObj && $requestMessage == null) {
            return false;
        } else {
            if ($templateObj) {
                $template = $templateObj->sms;
                foreach ($params as $code => $value) {
                    $template = str_replace('[[' . $code . ']]', $value, $template);
                }
            } else {
                $template = $requestMessage;
            }
        }

        if (config('SMSConfig.default') == 'manual') {

            $phone = $user->phone_code . '' . $user->phone;
            $headerData = is_null($smsControl->header_data) ? [] : json_decode($smsControl->header_data, true);
            $paramData = is_null($smsControl->header_data) ? [] : json_decode($smsControl->header_data, true);
            $paramData = http_build_query($paramData);
            $actionUrl = $smsControl->header_data;
            $actionMethod = $smsControl->action_method;
            $formData = is_null($smsControl->form_data) ? [] : json_decode($smsControl->form_data, true);
            $formData = recursive_array_replace("[[receiver]]", $phone, recursive_array_replace("[[message]]", $template, $formData));
            $formData = isset($headerData['Content-Type']) && $headerData['Content-Type'] == "application/x-www-form-urlencoded" ? http_build_query($formData) : (isset($headerData['Content-Type']) && $headerData['Content-Type'] == "application/json" ? json_encode($formData) : $formData);

            foreach ($headerData as $key => $data) {
                $headerData[] = "{$key}:$data";
            }

            if ($actionMethod == 'GET') {
                $actionUrl = $actionUrl . '?' . $paramData;
            }

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $actionUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $actionMethod,
                CURLOPT_POSTFIELDS => $formData,
                CURLOPT_HTTPHEADER => $headerData
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        } else {
            BaseSmsService::sendSMS($user->phone, $template);
            return true;
        }
    }

    public function userFirebasePushNotification($user, $templateKey, $params = [], $action = null)
    {
        try {
            $basic = basicControl();
            $notify = config('firebase');

            if (!$basic->push_notification) {
                return false;
            }
            if ($notify['user_foreground'] == 0 && $notify['user_background'] == 0) {
                return false;
            }

            $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('language_id', $user->language_id)->first();

            if (!$templateObj) {
                $templateObj = NotificationTemplate::where('template_key', $templateKey)->first();
                if (!$templateObj->status['push']) {
                    return false;
                }
            }

            $template = '';
            if ($templateObj) {
                $template = $templateObj->push;
                foreach ($params as $code => $value) {
                    $template = str_replace('[[' . $code . ']]', $value, $template);
                }
            }

            $users = FireBaseToken::where('tokenable_id', $user->id)->get();

            foreach ($users as $user) {
                $data = [
                    "to" => $user->token,
                    "notification" => [
                        "title" => $templateObj->name . ' from ' . $basic->site_title,
                        "body" => $template,
                        "icon" => getFile(config('filesystems.default'), basicControl()->favicon),
                    ],
                    "data" => [
                        "foreground" => (int)$notify['user_foreground'],
                        "background" => (int)$notify['user_background'],
                        "click_action" => $action
                    ],
                    "content_available" => true,
                    "mutable_content" => true
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $notify['serverKey']
                ])
                    ->acceptJson()
                    ->post('https://fcm.googleapis.com/fcm/send', $data);
            }


        } catch (\Exception $e) {
            return 0;
        }
    }

    public function userPushNotification($user, $templateKey, $params = [], $action = [])
    {
        try {
            $basic = basicControl();

            if ($basic->in_app_notification != 1) {
                return false;
            }

            $templateObj = NotificationTemplate::where('template_key', $templateKey)
                ->where('language_id', $user->language_id)->where('notify_for', 0)
                ->first();

            if (!$templateObj) {
                $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('notify_for', 0)->first();
                if (!$templateObj || !$templateObj->status['in_app']) {
                    return false;
                }
            }

            if ($templateObj) {
                $template = $templateObj->in_app;
                foreach ($params as $code => $value) {
                    $template = str_replace('[[' . $code . ']]', $value, $template);
                }
                $action['text'] = $template;
            }

            $inAppNotification = new InAppNotification();
            $inAppNotification->description = $action;
            $user->inAppNotification()->save($inAppNotification);
            event(new UserNotification($inAppNotification, $user->id));

        } catch (\Exception $e) {
            return 0;
        }

    }

    public function adminFirebasePushNotification($templateKey, $params = [], $action = null)
    {
        try {
            $basic = basicControl();
            $notify = config('firebase');
            if (!$notify) {
                return false;
            }

            if (!$basic->push_notification) {
                return false;
            }

            $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('notify_for', 1)->first();
            if (!$templateObj || !$templateObj->status['push']) {
                return false;
            }

            if (!$templateObj) {
                return false;
            }

            $template = '';
            if ($templateObj) {
                $template = $templateObj->push;
                foreach ($params as $code => $value) {
                    $template = str_replace('[[' . $code . ']]', $value, $template);
                }
            }
            $admins = FireBaseToken::where('tokenable_type', Admin::class)->get();

            foreach ($admins as $admin) {
                $data = [
                    "to" => $admin->token,
                    "notification" => [
                        "title" => $templateObj->name,
                        "body" => $template,
                        "icon" => getFile(config('filesystems.default'), basicControl()->favicon),
                        "data" => [
                            "foreground" => (int)$notify['admin_foreground'],
                            "background" => (int)$notify['admin_background'],
                            "click_action" => $action['link']
                        ],
                        "content_available" => true,
                        "mutable_content" => true
                    ]
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $notify['serverKey']
                ])
                    ->acceptJson()
                    ->post('https://fcm.googleapis.com/fcm/send', $data);
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function adminPushNotification($templateKey, $params = [], $action = [])
    {

        try {
            $basic = basicControl();
            if ($basic->in_app_notification != 1) {
                return false;
            }

            $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('notify_for', 1)->first();
            if (!$templateObj || !$templateObj->status['in_app']) {
                return false;
            }

            if ($templateObj) {
                $template = $templateObj->in_app;
                foreach ($params as $code => $value) {
                    $template = str_replace('[[' . $code . ']]', $value, $template);
                }
                $action['text'] = $template;
            }

            $admins = Admin::all();
            foreach ($admins as $admin) {
                $inAppNotification = new InAppNotification();
                $inAppNotification->description = $action;
                $admin->inAppNotification()->save($inAppNotification);
                event(new AdminNotification($inAppNotification, $admin->id));
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function adminMail($templateKey = null, $params = [], $subject = null, $requestMessage = null)
    {
        $basic = basicControl();

        if ($basic->email_notification != 1) {
            return false;
        }

        $email_body = $basic->email_description;
        $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('notify_for', 1)->first();
        if (!$templateObj) {
            $templateObj = NotificationTemplate::where('template_key', $templateKey)->where('notify_for', 1)->first();
        }
        if (!$templateObj || !$templateObj->status['mail']) {
            return false;
        }

        $message = $email_body;
        if ($templateObj) {
            $message = str_replace("[[message]]", $templateObj->email, $message);

            if (empty($message)) {
                $message = $email_body;
            }
            foreach ($params as $code => $value) {
                $message = str_replace('[[' . $code . ']]', $value, $message);
            }
        } else {
            $message = str_replace("[[message]]", $requestMessage, $message);
        }

        $subject = ($subject == null) ? $templateObj->subject : $subject;
        $email_from = $basic->sender_email;
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $message = str_replace("[[name]]", $admin->username, $message);
            Mail::to($admin)->queue(new SendMail($email_from, $subject, $message));
        }
    }

    /*
     * type Should Edit or
     */
    public function matchEvent($query, $type = 'Edit')
    {
        $throughEvent = [
            "id" => $query->id,
            "start_date" => $query->start_date,
            "end_date" => $query->end_date,
            "category_id" => $query->category_id,
            "tournament_id" => $query->tournament_id,
            "team1_id" => $query->team1_id,
            "team2_id" => $query->team1_id,
            "name" => $query->name,
            "name_slug" => slug($query->name),
            "slug" => slug(optional($query->gameTeam1)->name . ' vs ' . optional($query->gameTeam2)->name),
            'status' => $query->status,
            'team1' => optional($query->gameTeam1)->name,
            'team1_img' => getFile(optional($query->gameTeam1)->driver, optional($query->gameTeam1)->image),
            'team2' => optional($query->gameTeam2)->name,
            'team2_img' => getFile(optional($query->gameTeam2)->driver, optional($query->gameTeam2)->image),

            'game_category' => [
                'id' => optional($query->gameCategory)->id,
                'name' => optional($query->gameCategory)->name,
                'slug' => slug(optional($query->gameCategory)->name),
                'icon' => optional($query->gameCategory)->icon,
            ],
            'game_tournament' => [
                'id' => optional($query->gameTournament)->id,
                'name' => optional($query->gameTournament)->name,
                'slug' => slug(optional($query->gameTournament)->name)
            ],
            'totalQuestion' => count($query->activeQuestions),
            // Get Questions
            'questions' => $query->activeQuestions->where('end_time', '>', Carbon::now())->map(function ($question) use ($query) {
                return [
                    'id' => $question->id,
                    'name' => $question->name,
                    'limit' => $question->limit,
                    'end_time' => $question->end_time,
                    'is_unlock' => $question->is_unlock,
                    'status' => $question->status,
                    // Get Options
                    'options' => $question->activeGameOptions->map(function ($option) use ($question, $query) {
                        return [
                            'id' => $option->id,
                            'match_id' => $option->match_id,
                            'category_name' => optional($query->gameCategory)->name,
                            'category_icon' => optional($query->gameCategory)->icon,
                            "tournament_name" => optional($query->gameTournament)->name,
                            "match_name" => optional($query->gameTeam1)->name . ' vs ' . optional($query->gameTeam2)->name,
                            'question_id' => $option->question_id,
                            'question_name' => $question->name,
                            'option_name' => $option->option_name,
                            'ratio' => (float)$option->ratio,
                            'is_unlock_question' => $question->is_unlock,
                            'is_unlock_match' => $query->is_unlock,
                        ];
                    })
                ];
            })
        ];


        $throughEvent = (object)$throughEvent;

        if (basicControl()->in_app_notification == 1) {
            event(new MatchNotification($throughEvent, $type));
        }

    }
}
