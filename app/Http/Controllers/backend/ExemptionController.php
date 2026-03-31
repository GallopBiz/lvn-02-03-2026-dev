<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ExemptionController extends Controller
{
    public function exemptionList()
    {
		$query = DB::table('student_fees_exempted as sfe')
			->join('student_registration as sr', 'sfe.scholar_no', '=', 'sr.scholar_no')
			->join('generate_duechartstatus as gds', 'sr.id', '=', 'gds.student_id')
			->where('sfe.exempt_per', '!=', 0)
			->select(
				'sfe.*',
				'sr.student_name as student_name',
				'sr.id as student_reg_id',
				'sr.json_str as student_json',
				'gds.json_str'
			);


		$scholar_no = request('scholar_no');
		if ($scholar_no) {
			$query->where('sfe.scholar_no', 'like', "%$scholar_no%");
		}

		$student_name = request('student_name');
		if ($student_name) {
			$query->where('sr.student_name', 'like', "%$student_name%");
		}

		$allExemptions = $query->get();

		$results = [];
		foreach ($allExemptions as $row) {
			$json = json_decode($row->json_str, true);
			if (!is_array($json) || !isset($json[0]['json_str'])) {
				continue;
			}
			$data = json_decode($json[0]['json_str'], true);

			$studentJson = json_decode($row->student_json, true);
			$className = $studentJson['classname'] ?? '';
			$section = $studentJson['section_name'] ?? '';

			$discountTotal = 0;
			$account_name = $data['account_name'] ?? [];
			$discount_fees = $data['fees'] ?? [];
			$fees = $data['fees'] ?? [];
			$totalFees = 0;
			$totalDiscountedFees = 0;

			if (isset($data['orig_fees'])) {
				$fees = $data['orig_fees'];
			}

			foreach ($account_name as $index => $accountName) {
				if (isset($fees[$index], $discount_fees[$index]) && trim($accountName) === trim($row->head_name)) {
					$discountTotal += $fees[$index] - $discount_fees[$index];
				}
				$totalFees += $fees[$index] ?? 0;
				$totalDiscountedFees += $discount_fees[$index] ?? 0;
			}

			$results[] = [
				'student_id' => $row->student_id,
				'student_reg_id' => $row->student_reg_id,
				'student_name' => $row->student_name,
				'scholar_no' => $row->scholar_no,
				'head_name' => $row->head_name,
				'exempt_per' => $row->exempt_per,
				'lum' => $row->lum,
				'lssm' => $row->lssm,
				'lunch' => $row->lunch,
				'staff' => $row->staff,
				'sibling' => $row->sibling,
				'last_year_lssm' => $row->last_year_lssm,
				'total_discount_amount' => $discountTotal,
				'total_fees'    => $totalFees,
				'total_fees_after_discount' => $totalDiscountedFees,
				'class_name' => $className,
				'section' => $section,
			];
		}

		// Manual pagination after processing
		$page = request()->get('page', 1);
		$perPage = 20;
		$offset = ($page - 1) * $perPage;
		$paginatedResults = new \Illuminate\Pagination\LengthAwarePaginator(
			array_slice($results, $offset, $perPage),
			count($results),
			$perPage,
			$page,
			['path' => request()->url(), 'query' => request()->query()]
		);
		return view('backend.exemptions.index', [
			'exempted_students' => $paginatedResults,
			'scholar_no' => $scholar_no,
			'student_name' => $student_name
		]);
    }

    public function exportExemptionCSV()
	{
		$exemptions = DB::table('student_fees_exempted as sfe')
			->join('student_registration as sr', 'sfe.scholar_no', '=', 'sr.scholar_no')
			->join('generate_duechartstatus as gds', 'sr.id', '=', 'gds.student_id')
			->where('sfe.exempt_per', '!=', 0)
			->select('sfe.*', 'sr.student_name', 'sr.json_str', 'sr.id as student_reg_id', 'gds.json_str as due_json')
			->get();

		$results = [];

		foreach ($exemptions as $row) {
			$studentJson = json_decode($row->json_str, true);
			$class = $studentJson['classname'] ?? 'N/A';
			$section = $studentJson['section_name'] ?? 'N/A';

			$dueJson = json_decode($row->due_json, true);
			$data = json_decode($dueJson[0]['json_str'] ?? '{}', true);

			$discountTotal = 0;
			$account_name = $data['account_name'] ?? [];
			$discount_fees = $data['fees'] ?? [];
			$fees = $data['orig_fees'] ?? $data['fees'] ?? [];

			$totalFees = 0;
			$totalDiscountedFees = 0;

			foreach ($account_name as $index => $accountName) {
				if (trim($accountName) === trim($row->head_name)) {
					$discountTotal += $fees[$index] - $discount_fees[$index];
				}
				$totalFees += $fees[$index];
				$totalDiscountedFees += $discount_fees[$index];
			}

			if ($discountTotal <= 0) continue;

			$types = [];
			if ($row->lum) $types[] = 'Lum';
			if ($row->lssm) $types[] = 'LSSM';
			if ($row->lunch) $types[] = 'Lunch';
			if ($row->staff) $types[] = 'Staff';
			if ($row->sibling) $types[] = 'Sibling';
			if ($row->last_year_lssm) $types[] = 'Last Year LSSM';

			$results[] = [
				'Scholar No' => $row->scholar_no,
				'Student Name' => $row->student_name,
				'Class' => $class,
				'Section' => $section,
				'Head' => $row->head_name,
				'Exempt %' => $row->exempt_per,
				'Discount Type' => implode(', ', $types),
				'Total Fees' => number_format($totalFees, 2),
				'Discount Amount' => number_format($discountTotal, 2),
				'After Discount' => number_format($totalDiscountedFees, 2),
			];
		}

		// Set headers and stream CSV
		$headers = [
			"Content-type" => "text/csv",
			"Content-Disposition" => "attachment; filename=exemption_report.csv",
			"Pragma" => "no-cache",
			"Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
			"Expires" => "0"
		];

		$callback = function () use ($results) {
			$handle = fopen('php://output', 'w');
			if (!empty($results)) {
				fputcsv($handle, array_keys($results[0]));
				foreach ($results as $row) {
					fputcsv($handle, $row);
				}
			} else {
				fputcsv($handle, ['No data found']);
			}
			fclose($handle);
		};

		return Response::stream($callback, 200, $headers);
	}

}
