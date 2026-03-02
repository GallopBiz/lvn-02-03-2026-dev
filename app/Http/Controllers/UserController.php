<?php
    
namespace App\Http\Controllers;
    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        // print_r($user); die();
        if (!$user->hasRole('Admin')) {
            $userId = auth()->id();
            $data = User::where('id',$userId)->get();
// print_r($data); die();

        } else {
            $data = User::orderBy('id','DESC')->where('status','a')->get();
        }
        return view('users.index',compact('data'));
            // ->with('i', ($request->input('page', 1) - 1) * 10);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $roles = Role::pluck('student_name','student_name')->all();
        $roles = Role::pluck('name','name')->all();
        
        // print_r($data); die();

        return view('users.create',compact('roles'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $jsondata =  $input['jsoninput'];
        
        // $this->validate($request, [
        //     'student_name' => 'required',
        //     // 'email' => 'required|email|unique:users,email',
        //     'password' => 'required|same:confirm-password',
        //     'roles' => 'required'
        // ]);
        // $this->validate($request, [
        //     'student_name' => 'required', // Assuming 'name' is equivalent to 'student_name'
        //     'email' => 'required|email|unique:users,email',
        //     'password' => 'required|same:confirm-password',
        //     'roles' => 'required',
        // ]);
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $input['jsondata'] = $jsondata;
    
        // return $jsondata;

        // $jsondata = $request->input('jsoninput');
        // $input['jsondata'] = $jsondata;


        $user = User::create($input);
        $user->assignRole($request->input('roles'));
    
        return redirect()->route('users.index')
                        ->with('success','User created successfully');
    }
    

    // public function store(Request $request)
    // {
    //     // Validate specific fields for step 1
    //     // $this->validate($request, [
    //     //     'student_name' => 'required',
    //     //     'email' => 'required|email|unique:users,email',
    //     //     'password' => 'required|same:confirm-password',
    //     //     'roles' => 'required',
    //     // ]);
    
    //     // Get all form data as an associative array
    //     $input = $request->all();
    
    //     // Remove the token and any other fields you don't want in the JSON data
    //     unset($input['_token']);
    
    //     // Get JSON data from the hidden input field
    //     $jsondata = json_decode($input['jsoninput'], true);
    
    //     // Merge the JSON data with the main input data
    //     $input = array_merge($input, $jsondata);
    
    //     // Hash the password
    //     $input['password'] = Hash::make($input['password']);
    
    //     // Save the model to the database
    //     $user = User::create($input);
    
    //     // Assign roles to the user
    //     $user->assignRole($input['roles']);
    
    //     return redirect()->route('users.index')->with('success', 'User created successfully');
    // }
    













//     public function store(Request $request)
// {
//     // Validate specific fields for step 1
//     $this->validate($request, [
//         'student_name' => 'required',
//         'email' => 'required|email|unique:users,email',
//         'password' => 'required|same:confirm-password',
//         'roles' => 'required',
//     ]);

//     // Get data from the form steps
//     $jsondata = [];

//     // Step 1 data
//     $jsondata['student_name'] = $request->input('student_name');
//     $jsondata['email'] = $request->input('email');
//     $jsondata['password'] = Hash::make($request->input('password'));
//     $jsondata['roles'] = $request->input('roles');

//     // Step 2 data
//     $jsondata['employeeShort'] = $request->input('employeeShort');
//     // Add more fields from Step 2 as needed

//     // Step 3 data
//     $jsondata['gender'] = $request->input('gender');
//     $jsondata['dob'] = $request->input('dob');
//     // Add more fields from Step 3 as needed

//     // Step 4 data
//     $jsondata['permanent_address'] = $request->input('permanent_address');
//     $jsondata['city'] = $request->input('city');
//     // Add more fields from Step 4 as needed

//     // Step 5 data
//     $jsondata['marital_status'] = $request->input('marital_status');
//     $jsondata['dom'] = $request->input('dom');
//     // Add more fields from Step 5 as needed

//     // Convert the array to JSON
//     $jsondata = json_encode($jsondata);

//     // Create a new instance of the User model
//     $user = new User;

//     // Set the attributes for the model
//     $user->jsondata = $jsondata;

//     // Save the model to the database
//     $user->save();

//     // Assign roles to the user
//     $user->assignRole($request->input('roles'));

//     return redirect()->route('users.index')->with('success', 'User created successfully');
// }





    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('users.show',compact('user'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
    
        return view('users.edit',compact('user','roles','userRole'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // print_r($request->all());die();
        $this->validate($request, [
            'student_name' => 'required',
            // 'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);
    
        $input = $request->all();
        if(!empty($input['password'])){ 
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input,array('password'));    
        }
    


        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();
    
        $user->assignRole($request->input('roles'));
    
        return redirect()->route('users.index') 
                        ->with('success','User updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
                        ->with('success','User deleted successfully');
    }
}