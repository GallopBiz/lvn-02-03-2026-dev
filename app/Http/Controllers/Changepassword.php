<?php

namespace App\Http\Controllers;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\ChangePassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Student_registration;
class Changepassword extends Controller
{
    //   public function create(){
    //     echo 'Hello';
    // }


    public function showChangePasswordForm()
    {
        return view('backend.enquiryview');
    }

    // Method to update the password
    // public function updatePassword(Request $request)
    // {
    //     // Validate the form input
    //     $request->validate([
    //         'password' => [
    //             'required',
                
                
                
    //             Rule::unique('student_registration', 'password')->ignore(Auth::user()->id),
    //         ],
    //     ], [
    //         'password.unique' => 'The new password must be different from the current password.',
    //     ]);

    
    //     $student = Auth::user();

    
    //     $student->password = Hash::make($request->password);
    //     $student->save();
    //     return redirect()->route('password.change.form')->with('success', 'Password updated successfully!');
    // }
    public function updatePassword(Request $request)
    {
     
        $newPassword = $request->input('password'); // Assuming 'password' is coming from a form input or request parameter
    
    
        $hashedPassword = Hash::make($newPassword);
    
  
        $id = $request->input('id'); 


        $student = Student_registration::where('id', $id)->first();

        // $student = DB::table('student_registration')
        //     ->where('id', $id)
            // ->update(['password' => $hashedPassword]);

        // return $hashedPassword;

        // return $id;


        $student->password = $hashedPassword;
    
        $student->save();

    
  
        // return redirect()->route('scholars_profile', $id)->with('success', 'Password updated successfully!');
        return back()->with('success', 'Password updated successfully!');
        
    }
    



}

