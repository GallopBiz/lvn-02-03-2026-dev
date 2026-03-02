<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class StudentOnlineFeesController extends Controller
{
    private $razorpayKeyId = 'rzp_live_83HaVBhlz35Pwg'; // Replace with your Razorpay API key
    private $razorpayKeySecret = '81113lplAmdz5GLlAWnKzirA'; // Replace with your Razorpay API secret
	
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
                $db_name = "2023_2024";
            }
            
        }
		$db_name = "2025_2026";
        $custom_connectionConnectionName = 'custom_connection';
        $custom_connectionConfig = Config::get("database.connections.{$custom_connectionConnectionName}");
        $custom_connectionConfig['database'] = $db_name;
		Config::set("database.connections.{$custom_connectionConnectionName}", $custom_connectionConfig);
        Config::set('database.connections.custom_connection', $custom_connectionConfig);
        DB::reconnect('custom_connection');
    }
	
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $status = $request->input('status', 'all');

        $payments = null;
        $error = null;

        if ($request->has('search') && !empty($fromDate) && !empty($toDate)) {
            $query = DB::connection('custom_connection')->table('student_online_fee_payments as p')
                ->join('student_registration as f', 'p.student_id', '=', 'f.id')
                ->select(
                    'p.transaction_id',
                    'p.student_id',
                    DB::raw('SUM(p.allocated_amount) as allocated_amount'),
                    'p.payment_date',
                    'p.payment_gateway_transaction_id',
                    'p.status',
                    'p.created_at',
                    'f.student_name',
                    'f.scholar_no',
                    'f.class_name'
                )
                ->groupBy('p.transaction_id', 'p.student_id', 'p.payment_date', 'p.payment_gateway_transaction_id', 'p.status', 'p.created_at', 'f.student_name', 'f.scholar_no', 'f.class_name');

            $query->whereBetween($status === 'paid' ? 'p.payment_date' : 'p.created_at', [$fromDate, $toDate]);

            if ($status !== 'all') {
                $query->where('p.status', $status);
            }

            $payments = $query->get();
			
            if ($payments->isEmpty()) {
                $error = 'No data found for the selected criteria.';
            } else {
                foreach ($payments as $payment) {
                    $paymentTransactionId = $payment->payment_gateway_transaction_id;
                    $payment->razorpay_status = $this->fetchRazorpayStatus($paymentTransactionId);
                }
            }
        }

        return view('backend.student-online-fees.index', compact('payments', 'error'));
    }
	
	private function fetchRazorpayStatusByOurTransactionId($ourTransactionId, $fromDate, $toDate)
	{
		if (!$ourTransactionId) {
			return json_encode(['status' => 'Not paid']);
		}

		try {
			$url = "https://api.razorpay.com/v1/payments";

			// Convert date to timestamps
			$from = strtotime($fromDate);
			$to   = strtotime($toDate);

			// Fetch payments list
			$response = Http::withBasicAuth($this->razorpayKeyId, $this->razorpayKeySecret)
				->get($url, [
					'from' => $from,
					'to'   => $to,
					'count' => 100
				]);

			if (!$response->successful()) {
				return json_encode(['status' => 'Failed to fetch payments']);
			}

			$data = $response->json();

			if (!isset($data['items']) || empty($data['items'])) {
				return json_encode(['status' => 'not_found']);
			}

			// Loop through Razorpay payments
			foreach ($data['items'] as $payment) {
				if (
					isset($payment['notes']['transaction_id']) &&
					$payment['notes']['transaction_id'] == $ourTransactionId
				) {
					
					return json_encode([
						'status' => $payment['status'],
						'razorpay_payment_id' => $payment['id'],
						'amount' => $payment['amount'],
						'method' => $payment['method'] ?? null,
						'email' => $payment['email'] ?? null
					]);
				}
			}

			return json_encode(['status' => 'not_found']);
		} catch (\Exception $e) {
			return json_encode(['status' => 'API call error', 'message' => $e->getMessage()]);
		}
	}


	public function check_pending(Request $request)
{
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $status = $request->input('status', 'all');

    $payments = null;
    $error = null;

    if ($request->has('search') && !empty($fromDate) && !empty($toDate)) {

        $query = DB::connection('custom_connection')
            ->table('student_online_fee_payments as p')
            ->join('student_registration as f', 'p.student_id', '=', 'f.id')
            ->select(
                'p.transaction_id',
                'p.student_id',
                DB::raw('SUM(p.allocated_amount) as allocated_amount'),
                'p.payment_date',
                'p.payment_gateway_transaction_id',
                'p.status',
                'p.created_at',
                'f.student_name',
                'f.scholar_no',
                'f.class_name'
            )
            ->groupBy(
                'p.transaction_id',
                'p.student_id',
                'p.payment_date',
                'p.payment_gateway_transaction_id',
                'p.status',
                'p.created_at',
                'f.student_name',
                'f.scholar_no',
                'f.class_name'
            );

        // Filter by date
        $query->whereBetween(
            $status === 'paid' ? 'p.payment_date' : 'p.created_at',
            [$fromDate, $toDate]
        );

        // Filter by local DB status
        if ($status !== 'all') {
            $query->where('p.status', $status);
        }

        $payments = $query->get();

        if ($payments->isEmpty()) {
            $error = 'No data found for the selected criteria.';
        } else {
            foreach ($payments as $payment) {
                // Fetch from Razorpay based on our transaction_id
                $match = $this->fetchRazorpayStatusByOurTransactionId(
                    $payment->transaction_id,
                    $fromDate,
                    $toDate
                );

                $match = json_decode($match, true);

                if (is_array($match) && isset($match['status'])) {
                    // Update view data
                    $payment->razorpay_status = $match['status'];
                    $payment->razorpay_payment_id = $match['razorpay_payment_id'] ?? null;

                    // FIX: Ensure payment_date is available for blade
                    if (isset($match['created_at'])) {
                        $payment->payment_date = date('Y-m-d H:i:s', $match['created_at']);
                    }
                    if ($payment->status != 'refunded' && $match['status'] === 'refunded') {
                        DB::connection('custom_connection')
                            ->table('student_online_fee_payments')
                            ->where('transaction_id', $payment->transaction_id)
                            ->update([
                                'status' => 'refunded',
                                'payment_date' => isset($match['created_at'])
                                    ? date('Y-m-d H:i:s', $match['created_at'])
                                    : $payment->payment_date,
                                'payment_gateway_transaction_id' => $match['razorpay_payment_id'] ?? $payment->payment_gateway_transaction_id,
                                'updated_at' => now()
                            ]);
                    }

                    if ($payment->status === 'pending' && $match['status'] === 'captured') {
                        DB::connection('custom_connection')
                            ->table('student_online_fee_payments')
                            ->where('transaction_id', $payment->transaction_id)
                            ->where('status', 'pending') // extra safety
                            ->update([
                                'status' => 'paid',
                                'payment_date' => isset($match['created_at'])
                                    ? date('Y-m-d H:i:s', $match['created_at'])
                                    : $payment->payment_date,
                                'payment_gateway_transaction_id' => $match['razorpay_payment_id'] ?? $payment->payment_gateway_transaction_id,
                                'updated_at' => now()
                            ]);
                    }

                } else {
                    $payment->razorpay_status = 'not_found';
                    $payment->razorpay_payment_id = null;
                }
            }
        }
    }

    return view(
        'backend.student-online-fees.pending-payment',
        compact('payments', 'error')
    );
}


	
    public function student_daily_online_fees_settlement(Request $request)
	{
		try {
			// Fetch input dates from the request
			$fromDate = $request->input('from_date');
			$toDate = $request->input('to_date');
			$settlements = null; // Initialize settlements as null
			$error = null;       // Initialize error as null

			// Validate the input dates
			if (!$fromDate || !$toDate) {
				$error = 'From Date and To Date are required.';
				return view('backend.student-daily-online-fees-settlement', compact('settlements', 'error'));
			}

			// Convert the dates to Unix timestamps
			$fromTimestamp = strtotime($fromDate);
			$toTimestamp = strtotime($toDate);

			// Define Razorpay API credentials
			$apiKey = 'rzp_live_83HaVBhlz35Pwg'; // Replace with your Razorpay API key
			$apiSecret = '81113lplAmdz5GLlAWnKzirA'; // Replace with your Razorpay API secret

			// Razorpay API endpoint for fetching settlements
			$endpoint = "https://api.razorpay.com/v1/settlements?from={$fromTimestamp}&to={$toTimestamp}";

			// Making the API request using cURL
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $endpoint);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, "{$apiKey}:{$apiSecret}");

			$response = curl_exec($ch);
			$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			// Handle the API response
			if ($httpStatus == 200) {
				$responseData = json_decode($response, true);
				//print_r ($responseData); die;
				$settlements = $responseData['items'] ?? []; // Extract settlement items
			} else {
				$error = 'Unable to fetch settlements. Please try again.';
			}

			// Pass the settlements and error to the view
			return view('backend.student-online-fees.student-daily-online-fees-settlement', compact('settlements', 'error', 'fromDate', 'toDate'));
		} catch (\Exception $e) {
			$error = 'An unexpected error occurred: ' . $e->getMessage();
			return view('backend.student-online-fees.student-daily-online-fees-settlement', compact('settlements', 'error'));
		}
	}

    private function fetchRazorpayStatus($transactionId)
    {
        if (!$transactionId) {
            return 'Not paid';
        }

        try {
            $url = "https://api.razorpay.com/v1/payments/{$transactionId}";
            $response = Http::withBasicAuth($this->razorpayKeyId, $this->razorpayKeySecret)->get($url);

            if ($response->successful()) {
                return json_encode($response->json());
            } else {
                return 'Failed to fetch status';
            }
        } catch (\Exception $e) {
            return 'API call error: ' . $e->getMessage();
        }
    }

    public function student_daily_online_fees_reconcile(Request $request)
    {
        // Check if data is available in the GET parameters
        if (!$request->hasAny(['year', 'month', 'day', 'count', 'skip'])) {
            // No parameters provided, load the form view
            return view('backend.student-online-fees.reconcile');
        }

        // Use Laravel's built-in validation
        $validated = $request->validate([
            'year' => 'required|numeric|digits:4',
            'month' => 'required|numeric|between:1,12',
            'day' => 'nullable|numeric|between:1,31',
            'count' => 'nullable|numeric|between:1,1000',
            'skip' => 'nullable|numeric|min:0',
        ], [
            // Custom error messages
            'year.required' => 'Year is required and must be a 4-digit number.',
            'month.required' => 'Month is required and must be an integer between 1 and 12.',
            'day.between' => 'Day must be an integer between 1 and 31, if provided.',
            'count.between' => 'Count must be an integer between 1 and 1000.',
            'skip.min' => 'Skip must be a non-negative integer.',
        ]);
        // Prepare API URL for reconciliation data
        $apiUrl = 'https://api.razorpay.com/v1/settlements/recon/combined';

        // Query parameters
        $params = [
            'year' => $validated['year'],
            'month' => $validated['month'],
            'day' => $validated['day'] ?? null,  // Null if not provided
            'count' => $validated['count'] ?? 1000,  // Default to 1000 if not provided
            'skip' => $validated['skip'] ?? 0,  // Default to 0 if not provided
        ];

        // Make the GET request to the reconciliation API
        $response = Http::withBasicAuth($this->razorpayKeyId, $this->razorpayKeySecret)
                        ->get($apiUrl, $params);

        if ($response->successful()) {
            // Convert data into a collection
            $data = $response->json();
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $entityId = $item['entity_id'];
                    $settledAt = $item['settled_at'];
                    if (!empty($settledAt)) {
                        $settlementDate = Carbon::createFromTimestamp($settledAt)->toDateTimeString();
                        DB::connection('dynamic')->table('student_online_fee_payments')
                            ->where('payment_gateway_transaction_id', $entityId)
                            ->whereNull('settlement_date') // Only update if not already set
                            ->update([
                                'settlement_date' => $settlementDate,
                            ]);
                    }
                }
            }
            return view('backend.student-online-fees.reconcile', compact('data'));
        } else {
            // Handle API failure
            return view('backend.student-online-fees.reconcile', [
                'error' => 'Failed to fetch reconciliation data. Please try again later.',
            ]);
        }
    }

    public function exportIndex(Request $request)
    {
        try {
            return new StreamedResponse(function () use ($request) {
                $handle = fopen('php://output', 'w');
                if (!$handle) {
                    throw new \Exception('Unable to open file handle for output');
                }

                // Add column headers
                fputcsv($handle, [
                    'Sr.',
                    'Student Name',
                    'Scholar No.',
                    'Class Name',
                    'Contact',
                    'Email',
                    'Payment Received',
                    'Fee',
                    'Tax (charges)',
                    'Payment Date',
                    'Status',
                    'Captured',
                    'Method',
                    'Type',
                    'Issuer',
                    'Notes',
                    'Payment Gateway Transaction Id',
                    'Transaction No.',
                    'Description'
                ]);

                // Get input parameters
                $fromDate = $request->input('from_date');
                $toDate = $request->input('to_date');
                $status = $request->input('status', 'all');

                // Build query
                if ($request->has('search') && !empty($fromDate) && !empty($toDate)) {
                    $query = DB::connection('custom_connection')->table('student_online_fee_payments as p')
                        ->join('student_registration as f', 'p.student_id', '=', 'f.id')
                        ->select(
                            'p.transaction_id',
                            'p.student_id',
                            DB::raw('SUM(p.allocated_amount) as allocated_amount'),
                            'p.payment_date',
                            'p.payment_gateway_transaction_id',
                            'p.status',
                            'p.created_at',
                            'f.student_name',
                            'f.scholar_no',
                            'f.class_name'
                        )
                        ->groupBy('p.transaction_id', 'p.student_id', 'p.payment_date', 'p.payment_gateway_transaction_id', 'p.status', 'p.created_at', 'f.student_name', 'f.scholar_no', 'f.class_name');
        
                    $query->whereBetween($status === 'paid' ? 'p.payment_date' : 'p.created_at', [$fromDate, $toDate]);
        
                    if ($status !== 'all') {
                        $query->where('p.status', $status);
                    }
        
                    $payments = $query->get();
        
                    if ($payments->isEmpty()) {
                        $error = 'No data found for the selected criteria.';
                    } else {
                        foreach ($payments as $payment) {
                            $paymentTransactionId = $payment->payment_gateway_transaction_id;
                            $payment->razorpay_status = $this->fetchRazorpayStatus($paymentTransactionId);
                        }
                    }
                }
                
                // Process each payment
                foreach ($payments as $index => $payment) {
                    // Decode Razorpay details
                    $razorpayDetails = isset($payment->razorpay_status) ? json_decode($payment->razorpay_status, true) : null;
                    
                    // If Razorpay details are not set or decoding failed, initialize an empty array
                    if ($razorpayDetails === null) {
                        $razorpayDetails = [];
                    }
                
                    // Format payment date
                    $paymentDate = $payment->payment_date ? date('d-m-Y', strtotime($payment->payment_date)) : 'N/A';
                
                    // Prepare CSV data
                    fputcsv($handle, [
                        $index + 1,
                        $payment->student_name,
                        $payment->scholar_no,
                        $payment->class_name,
                        isset($razorpayDetails['contact']) ? $razorpayDetails['contact'] : 'N/A',
                        isset($razorpayDetails['email']) ? $razorpayDetails['email'] : 'N/A',
                        number_format($payment->allocated_amount, 0),
                        isset($razorpayDetails['fee']) ? number_format($razorpayDetails['fee'] / 100, 2) : 'N/A',
                        isset($razorpayDetails['tax']) ? $razorpayDetails['tax'] : 'N/A',
                        $paymentDate,
                        strtoupper($payment->status),
                        isset($razorpayDetails['captured']) ? $razorpayDetails['captured'] : 'N/A',
                        isset($razorpayDetails['method']) ? $razorpayDetails['method'] : 'N/A',
                        isset($razorpayDetails['card']['type']) ? $razorpayDetails['card']['type'] : 'N/A',
                        isset($razorpayDetails['card']['issuer']) ? $razorpayDetails['card']['issuer'] : 'N/A',
                        isset($razorpayDetails['notes']['transaction_id']) ? $razorpayDetails['notes']['transaction_id'] : 'N/A',
                        $payment->payment_gateway_transaction_id,
                        $payment->transaction_id,
                        isset($razorpayDetails['description']) ? $razorpayDetails['description'] : 'N/A',
                    ]);
                }

                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="online_payments.csv"',
            ]);
        } catch (\Exception $e) {
            // Catch exceptions and return a JSON error response
            return response()->json([
                'error' => 'Error while exporting: ' . $e->getMessage()
            ], 500);
        }
    }

   
    public function exportSettlement(Request $request)
    {
        return new StreamedResponse(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Amount', 'Status', 'Fees', 'Tax', 'UTR', 'Created At']);

            $fromDate = strtotime($request->input('from_date'));
            $toDate = strtotime($request->input('to_date'));

            $endpoint = "https://api.razorpay.com/v1/settlements?from={$fromDate}&to={$toDate}";
            $response = Http::withBasicAuth($this->razorpayKeyId, $this->razorpayKeySecret)->get($endpoint);

            if ($response->successful()) {
                $settlements = $response->json()['items'] ?? [];
                foreach ($settlements as $index => $settlement) {
                    fputcsv($handle, [
                        $settlement['id'],
                        number_format($settlement['amount'] / 100, 2),
                        $settlement['status'],
                        number_format($settlement['fees'] / 100, 2),
                        number_format($settlement['tax'] / 100, 2),
                        $settlement['utr'] ?? 'N/A', // Ensure there's a fallback if UTR is missing
                        date('d-m-Y H:i:s', $settlement['created_at'])
                    ]);
                }
            } else {
                fputcsv($handle, ['Error fetching data']);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="settlements.csv"',
        ]);
    }


    public function exportReconciliation(Request $request)
    {
        return new StreamedResponse(function () use ($request) {
            $handle = fopen('php://output', 'w');
            
            // CSV Headers matching table columns
            fputcsv($handle, [
                'S No.', 'Entity ID', 'Settlement ID', 'Amount', 'Fee', 'Tax', 'On Hold', 'Settled', 
                'Created At', 'Settled At', 'Total (for Settlement ID)', 'Settlement UTR', 'Method',
                'Card Network', 'Card Issuer', 'Card Type', 'Dispute ID', 'Notes'
            ]);

            $apiUrl = 'https://api.razorpay.com/v1/settlements/recon/combined';
            $params = [
                'year' => $request->input('year'),
                'month' => $request->input('month'),
                'day' => $request->input('day'),
                'count' => $request->input('count', 1000),
                'skip' => $request->input('skip', 0),
            ];

            $response = Http::withBasicAuth($this->razorpayKeyId, $this->razorpayKeySecret)
                ->get($apiUrl, $params);
            if ($response->successful()) {
                $data = $response->json();
                $totals = [];

                // Calculate totals for each settlement_id
                foreach ($data['items'] as $item) {
                    $settlement_id = $item['settlement_id'];
                    if (!isset($totals[$settlement_id])) {
                        $totals[$settlement_id] = 0;
                    }
                    $totals[$settlement_id] += $item['amount'];
                }

                foreach ($data['items'] as $index => $item) {
                    fputcsv($handle, [
                        $index + 1,
                        $item['entity_id'],
                        $item['settlement_id'],
                        number_format($item['amount'] / 100, 2),
                        number_format($item['fee'] / 100, 2),
                        number_format($item['tax'] / 100, 2),
                        $item['on_hold'] ? 'Yes' : 'No',
                        $item['settled'] ? 'Yes' : 'No',
                        date('d-m-Y H:i:s', $item['created_at']),
                        date('d-m-Y H:i:s', $item['settled_at']),
                        number_format($totals[$item['settlement_id']] / 100, 2),
                        $item['settlement_utr'],
                        $item['method'],
                        $item['card_network'] ?: 'N/A',
                        $item['card_issuer'] ?: 'N/A',
                        $item['card_type'] ?: 'N/A',
                        $item['dispute_id'] ?: 'N/A',
                        $item['notes']
                    ]);
                }
            } else {
                fputcsv($handle, ['Error fetching data']);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reconciliation.csv"',
        ]);
    }


}
