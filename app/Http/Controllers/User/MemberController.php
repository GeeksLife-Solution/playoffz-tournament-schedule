<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GameMember;
use XContains\XContains\Cont\RT;

class MemberController extends Controller
{
    public function listMember(){
        $title = "List Members";
        $userId = Auth::id();
        $data['member'] = GameMember::select('name','email','status','created_at','id')->where('user_id',$userId)->where('status',1)->get();
        return view(template() . 'user.member.list',compact('title','data'));
    }

    public function addMember(){
        $title = "Add Member";
        return view(template() . 'user.member.add', compact('title'));
    }

    public function storeMember(Request $request)
    {
        try {
            // Validate input fields
            $request->validate([
                'name'  => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ]);
        
            // Check if the email already exists with status = 1
            $existingMember = GameMember::where('email', $request->email)->where('status', 1)->first();
            if ($existingMember) {
                return back()->with('error','This email is already associated with an active member.');
            }
        
            // Insert new member
            GameMember::create([
                'user_id'    => Auth::id(),
                'name'       => $request->name,
                'email'      => $request->email,
                'status'     => 1, // Active by default
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        
            return redirect()->route('user.member.list')->with('success', __('Member added successfully!'));
        } catch (\Throwable $th) {
            return back()->with('error', __('Something went wrong! ' . $th->getMessage()));
        }
    }
    
    // Update existing member
    public function updateMember(Request $request)
    {
        try {
            // Decrypt and validate member ID
            $urlParams = decryptUrlParam($request->eq);
            $memberId = $urlParams['member_id'] ?? null;
    
            if (!$memberId) {
                return back()->with('error', __('Invalid member ID.'));
            }
    
            // Find member or fail
            $userId = Auth::id();    
            $member = GameMember::where('user_id',$userId)->where('id',$memberId)->first();
            if (!$member) {
                return back()->with('error', __('Invalid Member'));
            }
    
            // Validate input
            $validatedData = $request->validate([
                'name'   => 'required|string|max:255',
                'email'  => 'required|email|max:255',
                'status' => 'required|in:0,1',
            ]);
    
            // Check for existing active email (excluding current member)
            $existingMember = GameMember::where('email', $validatedData['email'])
                ->where('status', 1)
                ->where('id', '!=', $memberId)
                ->exists();
    
            if ($existingMember) {
                return back()->withInput()->with('error','This email is already associated with an active member.');
            }
    
            // Update member details
            $member->update($validatedData);
    
            return back()->with('success', __('Member updated successfully!'));
        } catch (\Throwable $th) {
            return back()->with('error', __('Something went wrong! ' . $th->getMessage()));
        }
    }    

    public function destroyMember(Request $request)
    {
        try {
            // Decrypt and validate member ID
            $urlParams = decryptUrlParam($request->eq);
            $memberId = $urlParams['member_id'] ?? null;
    
            if (!$memberId) {
                return back()->with('error', __('Invalid member ID.'));
            }

            $userId = Auth::id();    
            // Find member or fail
            $member = GameMember::where('user_id',$userId)->where('id',$memberId)->first();
    
            // Soft delete by updating status
            $member->update(['status' => 2]);
    
            return back()->with('success', __('Member Destroyed successfully!'));
        } catch (\Throwable $th) {
            return back()->with('error', __('Something went wrong! ' . $th->getMessage()));
        }
    }    
    
}
