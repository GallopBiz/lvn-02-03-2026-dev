<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\partymaster;
use App\Models\CommanModel;


class partycontroller extends Controller
{
    public function index(){
        return view('backend.Transport.parymaster');
    }
    public function addpartymaster(Request $request){
        // echo"<pre>";
        // echo 1;
        print_r($request->all());
        partymaster::create($request->post());
        // echo"done";
        return redirect()->route('list-party-master')->with('success',' has been created successfully.');
    }
    public function list_party_master(){
        $listpartymaster= partymaster::where('is_delete',0)->get();
        return view('backend.Transport.parytmasterlist',compact('listpartymaster'));

    }
    public function view($id){
        $listpartymaster = partymaster::whereId($id)->get();
        // $listpartymaster = partymaster::orderBy('id','desc')->paginate(5);
        // echo"<pre>";
        // print_r($listpartymaster);
        // exit;
        return view('backend.Transport.parymaster',compact('listpartymaster'));
    }
    public function store(Request $request){
        $data = [
            'Party_Name' => $request->Party_Name,
            'Address' => $request->Address,
            'Tax' => $request->Tax,
            'City' => $request->City,
            'State' => $request->State,
            'PinCode' => $request->PinCode,
            'locality' =>$request->locality,
            'STDCode' => $request->STDCode,
            'REsidence_ph_no_1' => $request->REsidence_ph_no_1,
            'Office_ph_no_1' => $request->Office_ph_no_1,
            'REsidence_ph_no_2' => $request->REsidence_ph_no_2,
            'Mobile' => $request->Mobile,
            'emailId' => $request->emailId,
            'Fax_no_' => $request->Fax_no_,
            'Service_Tax_no_' => $request->Service_Tax_no_,
            'PAN_no_' => $request->PAN_no_,
            'CST_no_' => $request->CST_no_,
            'TIN_no_' => $request->TIN_no_,
            'TAN_no_' => $request->TAN_no_,
            'GST_no_' => $request->GST_no_,
            'Contactif' => $request->Contactif,
            'validUpto' => $request->validUpto,
            'Person_Name' => $request->Person_Name,
            'Mobile_NO_' => $request->Mobile_NO_,
            'Department_' => $request->Department_,
            'Post' => $request->Post,

        ];
        // echo"<pre>";
        // print_r($data);
        // exit;
        partymaster::whereId($request->id)->update($data);
        // echo "done";
        return redirect()->route('list-party-master')->with('success','party master has been Updated successfully.');
        // return view('backend.Transport.parymaster');
        // return view('backend.Transport.parytmasterlist')->with('success','party  Master has been Updated successfully.');
    }
    public function party_master_delete($id)
    {
        // echo $id;
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }
    
    public function delete($id){

        // echo $id;
        partymaster::where('id',$id)->delete();
         return redirect()->back();
        }
}
//finish