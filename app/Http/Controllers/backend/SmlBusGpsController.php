<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SmlBusGpsController extends Controller
{
    // You may want to store these in .env or config for security
    private $apiBaseUrl = 'https://customer-api.smlsaarthi.com';
    private $username = '6265106766'; // TODO: Replace with real username
    private $password = 'Lvn@1234'; // TODO: Replace with real password

    private function getToken()
    {
        $response = Http::timeout(20)->asJson()->post($this->apiBaseUrl . '/login', [
            'username' => $this->username,
            'password' => $this->password,
        ]);
        if ($response->ok() && isset($response['token'])) {
            return $response['token'];
        }
        return null;
    }

    public function track(Request $request, $vehicleNo)
    {
        $token = $this->getToken();
        if (!$token) {
            // Display real error if authentication fails
            return response('<div style="padding:2em;text-align:center;font-size:1.3em;color:#c00;">'
                . 'Authentication failed: Unable to authenticate with GPS API.'
                . '</div>', 401)
                ->header('Content-Type', 'text/html');
        }

        // Map vehicle number to chassis number (replace with real mapping)
        $chassisMap = [
            'MP09DW6891' => 'MBUZT54XGL0317124', // TODO: Replace with real chassis number
            'MP09AP2965' => 'MBUWEL4XDK0329345', // TODO: Replace with real chassis number
        ];
        $chassisNumber = $chassisMap[$vehicleNo] ?? null;
        if (!$chassisNumber) {
            return response('<div style="padding:2em;text-align:center;font-size:1.3em;color:#555;">Vehicle not found or not mapped for tracking.</div>', 404)
                ->header('Content-Type', 'text/html');
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->timeout(20)->asJson()->post($this->apiBaseUrl . '/vehicleDetails', [
            'chassisNumber' => $chassisNumber,
        ]);

        if ($response->ok()) {
            $data = $response->json();
            // Render using a Blade view for live tracking
            if ($request->ajax() || $request->wantsJson()) {
                // For AJAX, return only the tracking data as JSON
                return response()->json([
                    'tracking' => $data,
                ]);
            }
            return view('backend.bus_tracking', [
                'vehicle_no' => $vehicleNo,
                'tracking' => $data,
            ]);
        } else {
            return response('<div style="padding:2em;text-align:center;font-size:1.3em;color:#555;">Unable to fetch vehicle details. Please try again later.</div>', $response->status())
                ->header('Content-Type', 'text/html');
        }
    }
}
