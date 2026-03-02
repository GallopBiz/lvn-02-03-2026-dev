<?php
namespace App\Http\Controllers\backend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use DB;
use Hash;
use DataTables;
use App\Http\Requests\StoreFileRequest;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class DefaulterController extends Controller
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
                $db_name = "2023_2024";
            }
            
        }

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');

    }

    public function defaulterList(Request $request)
		{
			$today = Carbon::now();

			$students = DB::table('student_registration')
				->select('id', 'student_name', 'scholar_no')
				->get()
				->keyBy('id');

			$dueData = DB::table('generate_duechartstatus')->get();
			$defaulters = [];

			foreach ($dueData as $dueRecord) {
				$studentId = $dueRecord->student_id;

				$outerJsonArray = json_decode($dueRecord->json_str, true);
				if (!isset($outerJsonArray[0]['json_str'])) continue;

				$feesStructure = json_decode($outerJsonArray[0]['json_str'], true);
				if (!$feesStructure || !isset($feesStructure['due_date'])) continue;

				// Fetch paid receipts
				$paidReceipts = [];
				$receiptRows = DB::table('feesreceiptchallan')
					->where('student_id', $studentId)
					->get();

				foreach ($receiptRows as $row) {
					$decoded = json_decode($row->str_json, true);
					if (is_array($decoded)) {
						$paidReceipts[] = $decoded;
					}
				}

				$dueDates = $feesStructure['due_date'];
				$accountNames = $feesStructure['account_name'];
				$terms = $feesStructure['term'];
				$fees = $feesStructure['fees'];

				for ($i = 0; $i < count($dueDates); $i++) {
					$rawDate = trim($dueDates[$i]);

					// Try to parse the date
					try {
						$dueDate = Carbon::createFromFormat('d-m-Y', $rawDate);
					} catch (\Exception $e1) {
						try {
							$dueDate = Carbon::createFromFormat('Y-m-d', $rawDate);
						} catch (\Exception $e2) {
							continue;
						}
					}

					if ($dueDate->greaterThan(Carbon::now())) continue;

					// Calculate how much has been paid for this head+term+date
					$paidAmount = 0;
					foreach ($paidReceipts as $payment) {
						if (
							$payment['head_name'] === $accountNames[$i] &&
							$payment['term_str'] === $terms[$i] &&
							Carbon::parse($payment['head_due_date'])->format('d-m-Y') === $dueDates[$i]
						) {
							$paidAmount += floatval($payment['amount'] ?? 0);
						}
					}

					$dueAmount = floatval($fees[$i]);
					$remaining = $dueAmount - $paidAmount;

					if ($remaining > 0.01) {
						$studentInfo = $students[$studentId] ?? null;

						$defaulters[$studentId]['info'] = [
							'name' => $studentInfo->student_name ?? 'N/A',
							'scholar_no' => $studentInfo->scholar_no ?? 'N/A',
						];

						$defaulters[$studentId]['dues'][] = [
							'account_name' => $accountNames[$i],
							'term' => $terms[$i],
							'due_date' => $dueDates[$i],
							'amount' => $remaining,
						];
					}
				}
			}

			return view('backend.defaulters.list', compact('defaulters'));
		}


    public function list(Request $request)
    {
        
        return view('defaulters.list', compact('defaulters'));
    }

    
}