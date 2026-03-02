<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class InquiryReportController extends Controller
{
    // Show the inquiry report table
    public function inquiryreport(Request $request)
    {
        $startDate = $request->input('fromdate');
        $endDate = $request->input('todate');
        $status = $request->input('status') ?? 'all';

        $baseQuery = DB::connection('dynamic')->table('inquiry_registration')
                        ->where('is_delete', 0);

        // Apply date filter
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Apply status filter if not 'all'
        if ($status !== 'all') {
            $baseQuery->where('status', $status);
        }

        // Get counts
        $totalAll = (clone $baseQuery)->count();
        $totalPreInquiry = (clone $baseQuery)->where('status', 'p')->count();
        $totalInquiry = (clone $baseQuery)->where('status', 'i')->count();
        $totalRegistered = (clone $baseQuery)->where('status', 'r')->count();
        $totalFormSelected = (clone $baseQuery)->where('save_status', 'Form Selected')->count();

        // Get all filtered data
        $allData = $baseQuery->orderBy('created_at', 'desc')->get();

        // Map the status values to user-friendly labels
        foreach ($allData as $row) {
            if ($row->status == 'i') {
                $row->status_label = 'Enquiry';
            } elseif ($row->status == 'p') {
                $row->status_label = 'Pre Enquiry';
            } elseif ($row->status == 'r') {
                $row->status_label = 'Registered';
            }
        }

        return view('backend.inquiryreport', compact(
            'startDate',
            'endDate',
            'totalAll',
            'totalPreInquiry',
            'totalInquiry',
            'totalRegistered',
            'totalFormSelected',
            'allData'
        ));
    }

    // Export to CSV
    public function inquiryreportexport(Request $request)
    {
        $startDate = $request->input('fromdate') ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->input('todate') ?? Carbon::now()->endOfMonth()->toDateString();
        $status = $request->input('status') ?? 'all';

        $data = DB::connection('dynamic')->table('inquiry_registration')
            ->where('is_delete', 0)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($status != 'all') {
            $data->where('status', $status);
        }

        $data = $data->orderBy('created_at', 'desc')->get();

        // Prepare CSV data
        $csvData = [];
        $csvData[] = ['ID', 'Form Number', 'Student Name', 'DOB', 'Class', 'Status', 'Created At'];

        foreach ($data as $row) {
            $statusLabel = '';
            if ($row->status == 'i') {
                $statusLabel = 'Enquiry';
            } elseif ($row->status == 'p') {
                $statusLabel = 'Pre Enquiry';
            } elseif ($row->status == 'r') {
                $statusLabel = 'Registered';
            }

            $csvData[] = [
                $row->id,
                $row->form_number,
                $row->student_name,
                $row->date_of_birth,
                $row->class_name,
                $statusLabel,
                Carbon::parse($row->created_at)->format('Y-m-d'),
            ];
        }

        $filename = 'inquiry_report_' . date('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        foreach ($csvData as $line) {
            fputcsv($handle, $line);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }
}
