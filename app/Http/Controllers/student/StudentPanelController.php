<?php

namespace App\Http\Controllers\student;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentPanelController extends Controller
{
    /**
     * Show the change password form
     */
    public function showChangePasswordForm()
    {
        // Check if user is authenticated
        if (Auth::check()) {
            return view('backend.student_panel.change_password');
        } else {
            // Redirect to login page if not authenticated
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
    }

    /**
     * Update the password
     */
    public function updatePassword(Request $request)
    {
        // Check if user is authenticated
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        // Validate the input
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Redirect with a success message
        return redirect()->route('student.profile')->with('success', 'Password updated successfully.');
    }
}
