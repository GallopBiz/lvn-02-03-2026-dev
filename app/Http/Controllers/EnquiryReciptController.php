<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommanModel;
use DB;
use Illuminate\Support\Facades\Config;

class EnquiryReciptController extends Controller
{
    public function __construct(Request $request)
    {
        if (isset($_COOKIE['selectedYear'])) {
            $selectedYear = $_COOKIE['selectedYear'];
            // Now you can use $selectedYear in your PHP code
            $selectedYear;
        }
        
        if (!empty($request->session)){
            $db_name = $request->session; 
        } else if (!empty($request->session_name)){
            $db_name = $request->session_name;
        } else {
            if (isset($_COOKIE['selectedYear'])) {
                $db_name = $_COOKIE['selectedYear'];
            } else {
                $db_name = session('db_names') ?? Config::get('database.connections.dynamic.database') ?? '2025_2026';
            }
            
        }

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');

    }

    public function index($id){
        $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->where('id', $id)->get();

        if ($all_inquiry->isEmpty()) {
            $candidateDbs = array_unique(array_filter([
                session('db_names'),
                isset($_COOKIE['selectedYear']) ? $_COOKIE['selectedYear'] : null,
                Config::get('database.connections.dynamic.database'),
                '2025_2026',
                '2026_2027',
                '2027_2028',
            ]));

            foreach ($candidateDbs as $db) {
                try {
                    Config::set('database.connections.dynamic.database', $db);
                    DB::purge('dynamic');
                    $found = DB::connection('dynamic')->table('inquiry_registration')->where('id', $id)->get();
                    if (!$found->isEmpty()) {
                        $all_inquiry = $found;
                        session(['db_names' => $db]);
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        $data['all_inquiry'] = $all_inquiry;
        return view('backend.enquiryrecipt', $data);
    }
}
