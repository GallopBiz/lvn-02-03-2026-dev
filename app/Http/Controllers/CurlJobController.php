<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\CurlJob;
use Illuminate\Support\Facades\Validator;

class CurlJobController extends Controller
{
    public function getTransactionsLog(Request $request)
    {
       // Validate the request
        $validator = Validator::make($request->all(), [
            'fromDate' => 'required|date_format:Y-m-d',
            'toDate' => 'required|date_format:Y-m-d|after_or_equal:fromDate',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $serialNumber = 'AEXY211460054';
        $userName = 'dev';
        $userPassword = 'Test@123';
        $strDataList = 'Blank';
        $url = 'http://45.248.190.34:8083/iclock/WebAPIService.asmx?op=GetTransactionsLog';
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <GetTransactionsLog xmlns="http://tempuri.org/">
                     <FromDateTime>' . $fromDate . '</FromDateTime>
            <ToDateTime>' . $toDate . '</ToDateTime>
                    <SerialNumber>' . $serialNumber . '</SerialNumber>
                    <UserName>' . $userName . '</UserName>
                    <UserPassword>' . $userPassword . '</UserPassword>
                    <strDataList>' . $strDataList . '</strDataList>
                </GetTransactionsLog>
            </soap:Body>
        </soap:Envelope>';

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml',
        ])->send('POST', $url, [
            'body' => $xml,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'SOAP request failed.'], 500);
        }

        // Parse the response XML
        $xml = simplexml_load_string($response->body());
        $namespaces = $xml->getNamespaces(true);
        $soapBody = $xml->children($namespaces['soap'])->Body;
        $getTransactionsLogResponse = $soapBody->children($namespaces[''])->GetTransactionsLogResponse;
        $getTransactionsLogResult = (string) $getTransactionsLogResponse->GetTransactionsLogResult;
        $strDataList = (string) $getTransactionsLogResponse->strDataList;

        // Split strDataList into individual logs
        $logs = explode("\n", trim($strDataList));
        foreach ($logs as $log) {
            list($userId, $timestamp) = explode("\t", trim($log));
            $logDate = date('Y-m-d', strtotime($timestamp));

            // Check if there's already an entry for this user and date
            $existingLog = DB::table('employee_thumb_attendance')
                ->where('ess_emp_code', $userId)
                ->where('log_date', $logDate)
                ->first();

            if ($existingLog) {
                // Update the existing entry
                $inTime = min($existingLog->in_time, $timestamp);
                $outTime = max($existingLog->out_time, $timestamp);
                DB::table('employee_thumb_attendance')
                    ->where('id', $existingLog->id)
                    ->update([
                        'in_time' => $inTime,
                        'out_time' => $outTime,
                    ]);
            } else {
                // Insert a new entry
				$inTime = min($existingLog->in_time, $timestamp);
                $outTime = max($existingLog->out_time, $timestamp);
                DB::table('employee_thumb_attendance')->insert([
                    'ess_emp_code' => $userId,
                    'log_date' => $logDate,
                    'in_time' => $inTime,
                    'out_time' => $outTime,
                ]);
            }
        }

        return response()->json(['result' => $getTransactionsLogResult], 200);
    }
}
