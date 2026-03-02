<?php
namespace App\Http\Controllers\map;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\Student_registration;


class MapController extends Controller
{
    /**
     * redirect admin after login
     *
     * @return \Illuminate\View\View
     */
    public function showMap($id)
    {
        $vehical = $id;
        // $location = DB::table('locations')->select('origin_lat', 'origin_lng', 'destination_lat', 'destination_lng')->first();
        $originLat = 37.7749; // San Francisco latitude
        $originLng = -122.4194; // San Francisco longitude

        $destinationLat = 34.0522; // Los Angeles latitude
        $destinationLng = -118.2437; // Los Angeles longitude
        // return view('map.map', [
        //     'originLat' => $originLat,
        //     'originLng' => $originLng,
        //     'destinationLat' => $destinationLat,
        //     'destinationLng' => $destinationLng,
        // ]);

        $apiKey = 'AIzaSyBa0_Zia458Lqzrwk7PzzpU7JIwJAkITdk';
        return view('map.map', compact('apiKey','vehical'));
    }

    public function showMapNew($id)
    {
        $vehical = $id;
        // $location = DB::table('locations')->select('origin_lat', 'origin_lng', 'destination_lat', 'destination_lng')->first();
        $originLat = 37.7749; // San Francisco latitude
        $originLng = -122.4194; // San Francisco longitude

        $destinationLat = 34.0522; // Los Angeles latitude
        $destinationLng = -118.2437; // Los Angeles longitude
        // return view('map.map', [
        //     'originLat' => $originLat,
        //     'originLng' => $originLng,
        //     'destinationLat' => $destinationLat,
        //     'destinationLng' => $destinationLng,
        // ]);

        $apiKey = 'AIzaSyBa0_Zia458Lqzrwk7PzzpU7JIwJAkITdk';
        return view('map.map-new', compact('apiKey','vehical'));
    }

    public function getNewDestination(Request $request)
    {
        $vehical = $request->vehical;
        // Fetch the new destination from the database
        // For example, assuming you have a 'cars' table with 'latitude' and 'longitude' columns
        // $newDestination = DB::select('id')->get(); // Replace 1 with the car ID or any other logic to get the car data
        // $newDestination = DB::table('busdata')->select('latitude','longitude')->where('vehicle_no','=','MP09FA6154')->first();
       
        // $newDestination = json_decode(file_get_contents("http://13.127.144.213/webservice?token=getLiveData&user=lokmanyaschool&pass=gps@123&vehicle_no=".$vehical."&format=json"));
        
        // if (!empty($newDestination->root->VehicleData[0])) {
        //     return response()->json($newDestination->root->VehicleData[0]);
        //     // return Response::json("Hello");
        // } else {
        //     // return Response::json(['error' => 'New destination not found'], 404);
        //     return response()->json(['error'=>'New destination not found'],404);
        // }
        
        if (isset($_SESSION['number'])) {
            $_SESSION['number']++;
        } else {
            // If the session variable doesn't exist, initialize it to 1
            $_SESSION['number'] = 1;
        }
        // if(DB::connection('dynamic')->getDatabaseName())
        // {
        // echo "Connected sucessfully to database ".DB::connection()->getDatabaseName().".";
        // }
        $vehical = explode("-", $vehical)[1];        
        // $busdatamasters = DB::table('gps_data1')->where('is_delete',0)->where('vehicle_no',$vehical)->orderBy('id', 'desc')->first();
  
        $busdatamasters = DB::connection('dynamic')->table('gps_data1')->select('latitude', 'longitude','Location','Speed' ,'Vehicle_No', 'Status') ->where('is_delete', 0) ->where('vehicle_no', $vehical) ->orderBy('id', 'desc')->take(1)->first();
        
        //->skip($_SESSION['number']) ->take(1)->toSql();
        
        // ->first(); 


    //    print_r($busdatamasters); die();





        if (!empty($busdatamasters)) {
            return response()->json($busdatamasters);
            // return Response::json("Hello");
        } else {
            // return Response::json(['error' => 'New destination not found'], 404);
            return response()->json(['error'=>'New destination not found'],404);
        }
    }



    public function stuaddress(Request $request){
        $addcur = $request->value;
    
        $student = Student_registration::where('id', $addcur)->first(); // Use first() instead of get()
    
        if ($student) {
            $jsonStr = $student->json_str;
            $decodedData = json_decode($jsonStr, true);
    
            if (isset($decodedData['present_address'])) {
                $presentAddress = $decodedData['present_address'];
                // print_r($presentAddress);
                return json_encode($presentAddress,1);
            } else {
                $presentAddress = "Present address not found in the data.";
                return json_encode($presentAddress,1);

            }
        } else {
            $presentAddress = "Student not found.";
            return json_encode($presentAddress,1);
        }
    
        exit;
    }
}