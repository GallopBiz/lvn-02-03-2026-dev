<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RouteVehicleMapController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Use selected database based on cookie
        $db_name = $_COOKIE['selectedYear'] ?? '2024_2025';

        $dynamicConfig = Config::get('database.connections.dynamic');
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');

        $searchVehicle = $request->input('search_vehicle');

        // ✅ Fetch all schedule rows with valid JSON
        $rows = DB::connection('dynamic')->table('schedulemasterall')
            ->select('schedule_check_one')
            ->whereNotNull('schedule_check_one')
            ->get();

        $routeVehicleMap = [];

        $user = auth()->user();
        $sessionId = Auth::user()->id;

        // ✅ Check if current user is student with required transport
        $student = DB::connection('dynamic')->table('student_registration')
            ->where('id', $sessionId)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(json_str, '$.required_school_transport')) = '1'")
            ->first();

        if ($student || !($user->hasRole('Student'))) {
            // ✅ Fetch only active vehicles (is_delete = 0)
            $vehicles = DB::connection('dynamic')->table('vehicel')
                ->select('vehicelno', 'gps_tracking_url')
                ->where('is_delete', 0) // 🛑 Exclude deleted vehicles
                ->get()
                ->keyBy('vehicelno');

            foreach ($rows as $row) {
				//print_r($row);
                $jsonData = json_decode($row->schedule_check_one, true);

                if (is_array($jsonData)) {
                    foreach ($jsonData as $entry) {
                        $routeName = $entry['route_name'] ?? null;
                        $vehicleNo = $entry['vehicelno'] ?? null;
						
						if($routeName=="-- select Route --") {
							continue;
						}
						
                        // 🛑 Skip if route or vehicle is missing or deleted
                        if (!$routeName || !$vehicleNo || !isset($vehicles[$vehicleNo])) {
                            continue;
                        }

                        // 🔎 Apply search filter if provided
                        if ($searchVehicle && stripos($vehicleNo, $searchVehicle) === false) {
                            continue;
                        }

                        // Custom GPS tracking URL for two SML buses (use internal route)
                        // For MP09DW6891, use provided static tracking URL (SML API commented)
                        if ($vehicleNo === 'MP09DW6891') {
                            // SML API: $gpsUrl = url('/gps/sml/' . $vehicleNo);
                            $gpsUrl = 'https://pro.smlsaarthi.com/share/live?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJsb2dpbklkIjo4MjczLCJ1c2VybmFtZSI6IlNNTFdBUlJPT01fMjAyNSIsInJvbGVzIjoiQ0xJRU5UIiwidW5pcXVlSWQiOiJpdF84NjA1NjAwNjcxMzk1NjAiLCJhcGlVcmkiOiJ3c3M6Ly9kZXYtYXBpLmFxdWlsYXRyYWNrLmNvbSIsInRpdGxlIjoiODYwNTYwMDY3MTM5NTYwIiwiaHR0cFVyaSI6Imh0dHBzOi8vZGV2LWFwaS5hcXVpbGF0cmFjay5jb20vZ3JhcGhxbCIsInNvdXJjZSI6IkZNUyIsImlhdCI6MTc3MDcwMTc4NCwiZXhwIjoxODAxMzMzODAwfQ.SqdaqXc1tJ6RtaeEqNHik8vulsEWPLR1VbxlkrEch2o';
                        } elseif ($vehicleNo === 'MP09AP2965') {
                            // SML API: $gpsUrl = url('/gps/sml/' . $vehicleNo);
                            $gpsUrl = 'https://pro.smlsaarthi.com/share/live?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJsb2dpbklkIjo4MjczLCJ1c2VybmFtZSI6IlNNTFdBUlJPT01fMjAyNSIsInJvbGVzIjoiQ0xJRU5UIiwidW5pcXVlSWQiOiJpdF84NjI1NjcwNzc1MjAwOTEiLCJhcGlVcmkiOiJ3c3M6Ly9kZXYtYXBpLmFxdWlsYXRyYWNrLmNvbSIsInRpdGxlIjoiODYyNTY3MDc3NTIwMDkxIiwiaHR0cFVyaSI6Imh0dHBzOi8vZGV2LWFwaS5hcXVpbGF0cmFjay5jb20vZ3JhcGhxbCIsInNvdXJjZSI6IkZNUyIsImlhdCI6MTc3MDcyNDkzNCwiZXhwIjoxODAxMzMzODAwfQ.fb4AjbTdLiseNUTOptCUmoOfJlo9-Fc-kOPme4jTwkE';
                        } else {
                            $gpsUrl = $vehicles[$vehicleNo]->gps_tracking_url ?? null;
                        }

                        $routeVehicleMap[] = [
                            'route_name'       => $routeName,
                            'vehicle_no'       => $vehicleNo,
                            'gps_tracking_url' => $gpsUrl,
                        ];
                    }
                }
            }
        }

        // ✅ Sort routes alphabetically by route name
        usort($routeVehicleMap, function ($a, $b) {
            return strcmp($a['route_name'], $b['route_name']);
        });

        // ✅ Return partial view for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('backend.Transport.partials.route_vehicle_table', ['routes' => $routeVehicleMap])->render(),
            ]);
        }

        // ✅ Return full view for normal requests
        return view('backend.Transport.route-vehicle-map', [
            'routes' => $routeVehicleMap,
            'searchVehicle' => $searchVehicle,
        ]);
    }
}
