<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class DailyCollectionController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $year = $request->input('year');

        $rawData = DB::table('feesreceiptchallan')->get();

        // Prepare daily collections
        $collections = $rawData->map(function ($row) {
            $json = json_decode($row->str_json, true);
            return (object) array_merge((array) $row, $json);
        })->filter(function ($item) use ($fromDate, $toDate) {
            if (!isset($item->payment_date)) return false;
            $paymentDate = $item->payment_date;

            if ($fromDate && $toDate) {
                return $paymentDate >= $fromDate && $paymentDate <= $toDate;
            } elseif ($fromDate) {
                return $paymentDate >= $fromDate;
            } elseif ($toDate) {
                return $paymentDate <= $toDate;
            }
            return true;
        })->values();

        // Daily pagination
        $page = Paginator::resolveCurrentPage('page');
        $perPage = 20;
        $total = $collections->count();
        $results = $collections->slice(($page - 1) * $perPage, $perPage)->values();

        $paginatedCollections = new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);

        // Prepare and paginate yearly collections
        $paginatedYearlyCollections = collect();
        if ($year) {
            $startYear = $year;
            $startDate = $startYear . '-04-01';
            $endDate = ($startYear + 1) . '-03-31';

            $filteredYearly = $rawData->map(function ($row) {
                $json = json_decode($row->str_json, true);
                return (object) array_merge((array) $row, $json);
            })->filter(function ($item) use ($startDate, $endDate) {
                if (!isset($item->payment_date)) return false;
                return $item->payment_date >= $startDate && $item->payment_date <= $endDate;
            })->values();

            $pageYearly = Paginator::resolveCurrentPage('pageYearly');
            $totalYearly = $filteredYearly->count();
            $resultsYearly = $filteredYearly->slice(($pageYearly - 1) * $perPage, $perPage)->values();

            $paginatedYearlyCollections = new LengthAwarePaginator($resultsYearly, $totalYearly, $perPage, $pageYearly, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'pageYearly',
            ]);
        }

        return view('backend.DailyCollection.index', [
            'collections' => $paginatedCollections,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'year' => $year,
            'yearlyCollections' => $paginatedYearlyCollections,
        ]);
    }

    public function export(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $rawData = DB::table('feesreceiptchallan')->get();

        $filtered = $rawData->map(function ($row) {
            $json = json_decode($row->str_json, true);
            return (object) array_merge((array) $row, $json);
        })->filter(function ($item) use ($fromDate, $toDate) {
            if (!isset($item->payment_date)) return false;
            $paymentDate = $item->payment_date;

            if ($fromDate && $toDate) {
                return $paymentDate >= $fromDate && $paymentDate <= $toDate;
            } elseif ($fromDate) {
                return $paymentDate >= $fromDate;
            } elseif ($toDate) {
                return $paymentDate <= $toDate;
            }

            return true;
        });

        $headers = ['Sr. No', 'Voucher No.', 'Scholar No', 'Class', 'Section', 'Student Name', 'Payment Date', 'Payment By', 'Sub Total Received'];
        $rows = [];
        $index = 1;

        foreach ($filtered as $item) {
            $parts = preg_split('/[\-|–|\|]/', $item->name_classsection ?? '', 2);
            $class = trim($parts[0] ?? '');
            $section = trim($parts[1] ?? '');

            $rows[] = [
                $index++,
				$item->id,
                $item->name_scholarno ?? '',
                $class,
                $section,
                $item->name_student ?? '',
                $item->payment_date ?? '',
                $item->payment_by_select ?? '',
                $item->sub_total_received ?? '',
            ];
        }

        $filename = 'daily_collection_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ]);
    }
	public function exportYearly(Request $request)
	{
		$year = $request->input('year');

		if (!$year) {
			return redirect()->back()->with('error', 'Year is required for export.');
		}

		$startDate = $year . '-04-01';
		$endDate = ($year + 1) . '-03-31';

		$rawData = DB::table('feesreceiptchallan')->get();

		$filtered = $rawData->map(function ($row) {
			$json = json_decode($row->str_json, true);
			return (object) array_merge((array) $row, $json);
		})->filter(function ($item) use ($startDate, $endDate) {
			if (!isset($item->payment_date)) return false;
			return $item->payment_date >= $startDate && $item->payment_date <= $endDate;
		});

		$headers = ['Sr. No', 'Voucher No.', 'Scholar No', 'Class', 'Section', 'Student Name', 'Payment Date', 'Payment By', 'Sub Total Received'];
		$rows = [];

		$index = 1;
		foreach ($filtered as $item) {
			$parts = preg_split('/[\-|–|\|]/', $item->name_classsection ?? '', 2);
			$class = trim($parts[0] ?? '');
			$section = trim($parts[1] ?? '');

			$rows[] = [
				$index++,
				$item->id,
				$item->name_scholarno ?? '',
				$class,
				$section,
				$item->name_student ?? '',
				$item->payment_date ?? '',
				$item->payment_by_select ?? '',
				$item->sub_total_received ?? '',
			];
		}

		$filename = 'yearly_collection_' . $year . '_' . now()->format('Ymd_His') . '.csv';

		$callback = function () use ($headers, $rows) {
			$file = fopen('php://output', 'w');
			fputcsv($file, $headers);
			foreach ($rows as $row) {
				fputcsv($file, $row);
			}
			fclose($file);
		};

		return Response::stream($callback, 200, [
			"Content-Type" => "text/csv",
			"Content-Disposition" => "attachment; filename={$filename}",
		]);
	}

}
