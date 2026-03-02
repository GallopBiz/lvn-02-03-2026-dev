<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use DB;

class UserPermissionController extends Controller
{
    public function index(){
        // echo "hyy"; 
        $pdata = DB::table('permissions')->get();
        // print_r($pdata);exit;   
        return view('roles.createpermission',compact('pdata'));
    }
    public function save_permission(Request $request){

        $data =[
            'name' => $request->permission,
            'guard_name' => "web"
        ];

        Permission::create($data);
        return redirect()->route('permission')->with('success',' has been created successfully.');
// echo"</pre>";print_r($data);exit;
    }
    public function view($id){
        $fdata = DB::table('permissions')->where('id',$id)->get();
// echo"</pre>";print_r($fdata);exit;
        return view('roles.createpermission',compact('fdata'));

    }
    public function store_permission(Request $request){
        $data = [
            'name' =>$request->permission,
        ];
        // print_r($data);exit;
        DB::table('permissions')->whereId($request->id)->update($data);
        return redirect()->route('permission')->with('success',' has been updated successfully.');
    }
    public function bs_soft_delete($id){
        // print_r($id);exit;
        DB::table('permissions')->where('id', $id)->delete();
        return redirect()->route('permission')->with('success',' has been deleted successfully.');
        
    }
}
