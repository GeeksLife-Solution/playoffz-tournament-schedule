<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GameWaiver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WaiverController extends Controller
{
    public function newWaiver(){
        $title = "Add Waiver";
        return view(template() . 'user.waiver.create', compact('title'));
    }

    public function storeWaiver(Request $request)
    {
       try {
            // Validate the request
            $request->validate([
                'name' => 'required|string|max:255',
                'waiver.attachment_method' => 'required|in:file,textarea',
                'waiver.sign_method' => 'required|in:checkbox,initials,name,upload',
                'waiver.file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
                'waiver.text' => 'nullable|string'
            ]);
        
            // Initialize waiver data
            $waiverData = [
                'user_id' => auth()->id(),  // Assuming the user is logged in
                'name' => $request->name,
                'content' => null,
                'attachment' => null,
                'signature' => $request->waiver['sign_method'],
                'status' => 1, // Default status
            ];
        
            // Handle file upload if applicable
            if ($request->waiver['attachment_method'] === 'file' && $request->hasFile('waiver.file')) {
                $file = $request->file('waiver.file');
                $fileName = time() . '_' . $file->getClientOriginalName(); // Generate unique filename
                $filePath = 'waivers/' . $fileName; // Save inside 'assets/upload/waivers'
            
                // Store file using local disk
                Storage::disk('local')->putFileAs('waivers', $file, $fileName);
            
                // Save only the relative path in the database
                $waiverData['attachment'] = $filePath;
            }                                
        
            // Store waiver text if entered
            if ($request->waiver['attachment_method'] === 'textarea' && !empty($request->waiver['text'])) {
                $waiverData['content'] = $request->waiver['text'];
            }
        
            // Save waiver to database
            GameWaiver::create($waiverData);
        
            return redirect()->route('user.waiver.list')->with('success', 'Waiver added successfully.');
       } catch (\Throwable $th) {
        return redirect()->back()->with('error', 'Something went wrong! ' . $th->getMessage());
       }
    }

    public function listWaiver() {
        $title = "List Waivers";
        $userId = Auth::id();
        
        // Fetch waivers associated with the logged-in user
        $data['waiver'] = GameWaiver::where('user_id', $userId)
                            ->select('id', 'name', 'content', 'signature','attachment', 'created_at', 'status')->whereIn('status',[1,0])
                            ->get();
    
        return view(template() . 'user.waiver.list', compact('title', 'data'));
    }    

    public function updateWaiver(Request $request)
    {
        try {
            // Decrypt and validate waiver ID
            $urlParams = decryptUrlParam($request->eq);
            $waiverId = $urlParams['waiver_id'] ?? null;

            if (!$waiverId) {
                return back()->with('error', __('Invalid waiver ID.'));
            }

            // Find the waiver record
            $userId = Auth::id();    
            $waiver = GameWaiver::where('user_id', $userId)->where('id', $waiverId)->first();
            if (!$waiver) {
                return back()->with('error', __('Invalid waiver.'));
            }

            // Validate input
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'waiver.attachment_method' => 'required|in:file,textarea',
                'waiver.sign_method' => 'required|in:checkbox,initials,name,upload',
                'waiver.file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
                'waiver.text' => 'nullable|string'
            ]);

            // Prepare update data
            $waiverData = [
                'name' => $validatedData['name'],
                'signature' => $validatedData['waiver']['sign_method'],
            ];

            // Handle file upload if applicable
            if ($request->waiver['attachment_method'] === 'file' && $request->hasFile('waiver.file')) {
                $file = $request->file('waiver.file');
                $fileName = time() . '_' . $file->getClientOriginalName(); // Unique filename
                $filePath = 'waivers/' . $fileName; // Ensure correct directory
            
                // Store file using the public disk
                Storage::disk('public')->putFileAs('waivers', $file, $fileName);
            
                // Save only the relative path in the database
                $waiverData['attachment'] = $filePath;
            }            

            // Store waiver text if entered
            if ($validatedData['waiver']['attachment_method'] === 'textarea' && !empty($validatedData['waiver']['text'])) {
                $waiverData['content'] = $validatedData['waiver']['text'];
                $waiverData['attachment'] = null; // Clear file attachment if text is provided
            }

            // Update the waiver record
            $waiver->update($waiverData);

            return back()->with('success', __('Waiver updated successfully!'));
        } catch (\Throwable $th) {
            return back()->with('error', __('Something went wrong! ' . $th->getMessage()));
        }
    }

    public function destroyWaiver(Request $request)
    {
        try {
            // Decrypt and validate member ID
            $urlParams = decryptUrlParam($request->eq);
            $waiverId = $urlParams['waiver_id'] ?? null;
    
            if (!$waiverId) {
                return back()->with('error', __('Invalid member ID.'));
            }

            $userId = Auth::id();    
            $waiver = GameWaiver::where('user_id',$userId)->where('id',$waiverId)->first();
    
            // Soft delete by updating status
            $waiver->update(['status' => 2]);
    
            return back()->with('success', __('Waiver Destroyed successfully!'));
        } catch (\Throwable $th) {
            return back()->with('error', __('Something went wrong! ' . $th->getMessage()));
        }
    }    

}
