<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Kyc;
use App\Models\UserKyc;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KycVerificationController extends Controller
{
    use Upload;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function kycShow($slug, $id)
    {
        $data['kycs'] = Kyc::where('status', 1)->get();
        $data['kyc'] = Kyc::where('status', 1)->findOrFail($id);
        return view($this->theme . 'user.profile.kyc.show', $data);
    }

    public function kycVerificationSubmit(Request $request, $id)
    {
        $kyc = Kyc::where('status', 1)->findOrFail($id);
        try {
            $params = $kyc->input_form;
            $reqData = $request->except('_token', '_method');
            $rules = [];
            if ($params !== null) {
                foreach ($params as $key => $cus) {
                    $rules[$key] = [$cus->validation == 'required' ? $cus->validation : 'nullable'];
                    if ($cus->type == 'file') {
                        $rules[$key][] = 'image';
                        $rules[$key][] = 'mimes:jpeg,jpg,png';
                        $rules[$key][] = 'max:2048';
                    } elseif ($cus->type == 'text') {
                        $rules[$key][] = 'max:191';
                    } elseif ($cus->type == 'number') {
                        $rules[$key][] = 'integer';
                    } elseif ($cus->type == 'textarea') {
                        $rules[$key][] = 'min:3';
                        $rules[$key][] = 'max:300';
                    }
                }
            }

            $validator = Validator::make($reqData, $rules);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $reqField = [];
            foreach ($request->except('_token', '_method', 'type') as $k => $v) {
                foreach ($params as $inKey => $inVal) {
                    if ($k == $inKey) {
                        if ($inVal->type == 'file' && $request->hasFile($inKey)) {
                            try {
                                $file = $this->fileUpload($request[$inKey], config('filelocation.kyc.path'), null, null, 'webp', 60);
                                $reqField[$inKey] = [
                                    'field_name' => $inVal->field_name,
                                    'field_value' => $file['path'],
                                    'field_driver' => $file['driver'],
                                    'validation' => $inVal->validation,
                                    'type' => $inVal->type,
                                ];
                            } catch (\Exception $exp) {
                                session()->flash('error', 'Could not upload your ' . $inKey);
                                return back()->withInput();
                            }
                        } else {
                            $reqField[$inKey] = [
                                'field_name' => $inVal->field_name,
                                'validation' => $inVal->validation,
                                'field_value' => $v,
                                'type' => $inVal->type,
                            ];
                        }
                    }
                }
            }

            UserKyc::create([
                'user_id' => auth()->id(),
                'kyc_id' => $kyc->id,
                'kyc_type' => $kyc->name,
                'kyc_info' => $reqField
            ]);

            return back()->with('success', 'KYC Sent Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function verificationCenter()
    {
        $data['userKycs'] = UserKyc::own()->latest()->get();
        return view($this->theme . 'user.profile.kyc.verification-center', $data);
    }
}
