<?php
namespace App\Http\Controllers;
use App\Models\AddVehial;
use Illuminate\Http\Request;
use League\Csv\Writer;
use Illuminate\Http\Response;
use App\Models\Inquiry_registration;
use DB;

class CSVController extends Controller
{
    public function export(Request $request)
    {
        $tableName = $request->input('table_name');
        $columnNames = $request->input('column_names');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
 
        // print_r('start_date '.$start_date);
        // print_r('end_date '.$end_date);
        // die();
        // Fetch data from the specified table and columns
        $data = DB::table($tableName)
            ->select($columnNames)
            // ->where('is_delete', 0)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(json_str, '$.enquirydate')) BETWEEN ? AND ?", [$start_date, $end_date])
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(json_str, '$.amount')) = ?", [500])
            ->get();
        // Fetch data from the database
        // $data = AddVehial::all();
        

        // Create a new CSV writer
        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        // Add CSV headers
        $column_list = [
            'Sr no',
            'Form number',
            'Class Name',
            'Student Name',
            'Father Name',
            'Mother Name',
            'Session Name',
            'Mobile Number',
            'Enquiry Date',
        ];
        $csv->insertOne($column_list);
        // echo"<pre>";
        
        // Add CSV data rows
        $row = [];
        $o = 1;
        foreach ($data as $k => $item) {
            
            
            // foreach ($columnNames as $column) {
                // Check if the column contains JSON data
                
                
                if (!empty($item->json_str)) {
                    
                    $jsonData = json_decode($item->json_str, true);
                    
                    foreach($jsonData as $key => $jsonData_1){
                        $row[$key] = $jsonData_1;
                        
                    }
                }
                $originalDate = $row['enquirydate'];
                $formattedDate = date('d-m-Y', strtotime($originalDate));

                $data_value = [
                    'id' => $o,
                    'formno' => $row['formno'],
                    'class_name' => $item->class_name,
                    'student_name' => $item->student_name,
                    'fathername' => $row['fathername'],
                    'mothername' => $row['mothername'],
                    'session_name' => $item->session_name,
                    'mobile_number' => $item->mobile_number,
                    'enquirydate' => $formattedDate,
                ];
                // print_r($data_value);
            // }
            // print_r($item->json_str);
            // 
            $csv->insertOne($data_value);
            $o++;
        }
        // print_r($data_value);
        // exit;

        // Set the response headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=Student Enquiry List.csv',
        ];

        // Return the CSV file as a response
        return response($csv->getContent(), 200, $headers);
    }

    public function export_registrations(Request $request)
    {
        $query = DB::table('student_registration')
            ->where('status', 'r')
            ->orderBy('id', 'desc');

        // Apply filters from request if provided
        if ($request->filled('form_number')) {
            $query->where('form_number', $request->form_number);
        }
        if ($request->filled('serial_number')) {
            $query->where('form_number', $request->serial_number);
        }
        if ($request->filled('class_name')) {
            $query->where('class_name', $request->class_name);
        }
        if ($request->filled('application_for')) {
            $query->where('application_for', $request->application_for);
        }
        if ($request->filled('student_name')) {
            $query->where('student_name', 'like', '%' . $request->student_name . '%');
        }
        if ($request->filled('session_name')) {
            $query->where('session_name', $request->session_name);
        }

        $data = $query->get();
        $jsonRows = [];

        foreach ($data as $item) {
            $jsonData = !empty($item->json_str) ? json_decode($item->json_str, true) : [];
            $jsonData = is_array($jsonData) ? $jsonData : [];
            $jsonRows[$item->id] = $jsonData;
        }

        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        $csv->insertOne([
            'Sr no',
            'Form No.',
            'Scholar No',
            'DOB',
            'Class Name',
            'Section Name',
            'Student Name',
            'Father Name',
            'Session Name',
            'Mobile Number',
            'Gender',
            'Present Address',
            'Phone Number',
            'SSSMID',
            'Family SSSMID',
            'Is Staff Applied For Admission',
            'Aadhar No.',
            'Bank Name',
            'Branch Name',
            'Account Number',
            'Ifsc Code',
            'Create Date',
            'Registration Date',
        ]);

        foreach ($data as $index => $item) {
            $jsonData = $jsonRows[$item->id] ?? [];
            $studentName = trim(($jsonData['studentname_prefix'] ?? '') . ' ' . ($item->student_name ?? $jsonData['studentname'] ?? ''));
            $fatherName = trim(($jsonData['fathername_prefix'] ?? '') . ' ' . ($jsonData['student_father_name'] ?? $jsonData['fathername'] ?? ''));
            $sessionName = $jsonData['batch'] ?? $jsonData['intended_session'] ?? $item->session_name ?? '';
            $mobileNumber = $jsonData['father_mobile'] ?? $jsonData['mobile_number'] ?? $item->mobile_number ?? '';
            $sectionName = $jsonData['section_name'] ?? '';

            $csv->insertOne([
                $index + 1,
                $item->form_number ?? '',
                $item->scholar_no ?? '',
                $this->formatCsvDate($item->date_of_birth ?? null),
                $item->class_name ?? '',
                $sectionName,
                $studentName,
                $fatherName,
                $sessionName,
                $mobileNumber,
                $this->formatCsvValue($jsonData['gender'] ?? ''),
                $this->formatCsvValue($jsonData['present_address'] ?? ''),
                $this->formatCsvValue($jsonData['phone_number'] ?? $item->phone_number ?? ''),
                $this->formatCsvValue($jsonData['SSSMID'] ?? ''),
                $this->formatCsvValue($jsonData['family_SSSMID'] ?? ''),
                $this->formatCsvValue($jsonData['is_staff_applied_for_admission'] ?? ''),
                $this->formatCsvValue($jsonData['AadharNo'] ?? ''),
                $this->formatCsvValue($jsonData['bankName'] ?? ''),
                $this->formatCsvValue($jsonData['branchName'] ?? ''),
                $this->formatCsvValue($jsonData['account_number'] ?? ''),
                $this->formatCsvValue($jsonData['ifsc_code'] ?? ''),
                $this->formatCsvDate($item->created_at ?? null),
                $this->formatCsvDate($item->registration_date ?? null),
            ]);
        }

        return response($csv->getContent(), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=Registered_Students_Detail_Report.csv',
        ]);
    }

    private function formatCsvDate($date)
    {
        if (empty($date) || strtotime($date) === false) {
            return '';
        }

        return date('d-m-Y', strtotime($date));
    }

    private function formatCsvValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }
    public function exportCsvenquiry()
    {
        $all_inquiry = Inquiry_registration::select('session_name', 'student_name', 'class_name', 'json_str',)->get(); // Make sure to replace this with the actual data you want to export

        $csvExporter->insertOne(['Serial No', 'Class Name', 'Student Name', 'Session Name', 'Enquiry Date', 'Received Amount', 'Reference Number']);

        foreach ($all_inquiry as $index => $row) {
            $notificationData1 = json_decode($row->json_str, true);
            $enquiryDate = !empty($notificationData1['enquirydate']) ? date('d-m-Y', strtotime($notificationData1['enquirydate'])) : '';
            $receivedAmount = !empty($notificationData1['received_amount']) ? $notificationData1['received_amount'] : '';
            $referenceNumber = !empty($notificationData1['reference_number']) ? $notificationData1['reference_number'] : '';

            $csvExporter->insertOne([
                $index + 1,
                $row->class_name,
                $row->student_name,
                $row->session_name,
                $enquiryDate,
                $receivedAmount,
                $referenceNumber,
            ]);
        }

        $csvExporter->output('export.csv');
        $csvExporter->resetStream();

        return Response::make($csvExporter->output(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ]);
    }
    public function exportenquiry(){
        $all_inquiry = Inquiry_registration::select('session_name', 'student_name', 'class_name', 'json_str',)->get(); // Make sure to replace this with the actual data you want to export

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csvExporter=['Serial No', 'Class Name', 'Student Name', 'Session Name', 'Enquiry Date', 'Received Amount', 'Reference Number'];
        $csv->insertOne($csvExporter);
        foreach ($all_inquiry as $index => $row) {
            $notificationData1 = json_decode($row->json_str, true);
            $enquiryDate = !empty($notificationData1['enquirydate']) ? date('d-m-Y', strtotime($notificationData1['enquirydate'])) : '';
            $receivedAmount = !empty($notificationData1['received_amount']) ? $notificationData1['received_amount'] : '';
            $referenceNumber = !empty($notificationData1['reference_number']) ? $notificationData1['reference_number'] : '';

            $csv->insertOne([
                $index + 1,
                $row->class_name,
                $row->student_name,
                $row->session_name,
                $enquiryDate,
                $receivedAmount,
                $referenceNumber,
            ]);
        }
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename= Inquiry.csv',
        ];

        // Return the CSV file as a response
        return response($csv->getContent(), 200, $headers);
    }
    public function filterexportenquiry(Request $request){
        $session_name = $request->input('session_name');
        $class_name = $request->input('class');
        $studentname = $request->input('student_name');
        $fromdate = $request->input('fromdate');
        $todate = $request->input('todate');
        // print_r($request->all());exit;
        $records1 = DB::connection('dynamic')->table('inquiry_registration')->where(['status' => 'i']);

        if (!empty($session_name)) {
            $records1->where('session_name', $session_name);
        }
        
        if (!empty($class_name)) {
            $records1->where('class_name', $class_name);
        }
        
        if (!empty($studentname)) {
            $records1->where('student_name', 'LIKE', '%' . $studentname . '%');
        }
        
        if (!empty($fromdate)) {
            $records1->whereBetween('created_at', [$fromdate, $todate]);
        }
        
        $EnqSecdata = $records1->get();
        
        
       $all_inquiry = $records1->get();
       $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csvExporter=['Serial No', 'Class Name', 'Student Name', 'Session Name', 'Enquiry Date', 'Received Amount', 'Reference Number'];
        $csv->insertOne($csvExporter);
        foreach ($all_inquiry as $index => $row) {
            $notificationData1 = json_decode($row->json_str, true);
            $enquiryDate = !empty($notificationData1['enquirydate']) ? date('d-m-Y', strtotime($notificationData1['enquirydate'])) : '';
            $receivedAmount = !empty($notificationData1['received_amount']) ? $notificationData1['received_amount'] : '';
            $referenceNumber = !empty($notificationData1['reference_number']) ? $notificationData1['reference_number'] : '';

            $csv->insertOne([
                $index + 1,
                $row->class_name,
                $row->student_name,
                $row->session_name,
                $enquiryDate,
                $receivedAmount,
                $referenceNumber,
            ]);
        }
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename= filterInquiry.csv',
        ];

        // Return the CSV file as a response
        return response($csv->getContent(), 200, $headers);
    }

    public function exportdueamount(){
        $all_inquiry = DB::connection('dynamic')
            ->table('totalnextyear')
            ->join('student_registration', 'totalnextyear.scholar_no', '=', 'student_registration.scholar_no')
            ->select('student_registration.json_str','totalnextyear.scholar_no', 'totalnextyear.fees_date', 'totalnextyear.received_type',  'totalnextyear.account_name','totalnextyear.reference_number', 'totalnextyear.receipt_number', 'totalnextyear.due_date','totalnextyear.fees', 'totalnextyear.totalnextyear', 'student_registration.student_name','student_registration.class_name')
            ->groupBy('totalnextyear.scholar_no')
            ->get();
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        // $date_text =  "Print Date : ".date('d-M-Y');
        $date_text = "Print Date : " . date('d-m-Y');


        $extraHeaders = ['','','','Lokmanya Vidya Niketan','Student Balance Report','Date From 01-Apr-2023 To 31-Mar-2024',$date_text];
        
        $csv->insertOne($extraHeaders);
        
        $csvExporter=['Index        ','Class        ','Section        ','Scholar No', 'Enrollment No', 'Student Name', 'Father Name','Gender        ', 'OPENING        ', 'CURRENT FEE ASSIGNED        ', 'TOTALDUES        ', 'RECEIVED        ', 'Late Fee        ', 'FEE DUES        ' , 'Father Mob        ', 'Student Mob        ', 'Remarks        ' , 'Category        ', 'Batch Name        '];
        $csv->insertOne($csvExporter);

        foreach ($all_inquiry as $index => $row) {
            $arr_data = json_decode($row->json_str, true);
            $fatherName = $arr_data['fathername'];
            $gender = (!empty($arr_data['gender'])) ? $arr_data['gender'] : '';
            $father_mob = $arr_data['father_mobile'];
            $student_mob = $arr_data['phone_number'];
            $category = (!empty($arr_data['category'])) ? $arr_data['category'] : '';
            $batchname = (!empty($arr_data['batch'])) ? $arr_data['batch'] : '';
            $csv->insertOne([
                $index + 1, 
                $row->class_name,
                '-',
                $row->scholar_no,
                '-',
                $row->student_name,
                $fatherName,
                $gender,
                '0',
                '0',
                '0',
                '-'.$row->totalnextyear,
                '0',
                '-'.$row->totalnextyear,
                $father_mob,
                $student_mob,
                '-',
                $category,
                $batchname,               
            ]);
        }
        // echo"<pre>";print_r($csv);exit;
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename= Dueamountstu.csv',
        ];

        // Return the CSV file as a response
        return response($csv->getContent(), 200, $headers);

    }
    public function filterdueamountcsv(Request $request){
        $class_name = $request->get('classname');
        $student_name = $request->get('student_name');
        $fromdate = $request->get('fromdate');
        $reciptno = $request->get('receipt_number');
        $todate = $request->get('todate');
        $all_inquiry = DB::connection('dynamic')
        ->table('totalnextyear')
        ->join('student_registration', 'totalnextyear.scholar_no', '=', 'student_registration.scholar_no')
        ->select('student_registration.json_str','totalnextyear.scholar_no', 'totalnextyear.fees_date', 'totalnextyear.received_type',  'totalnextyear.account_name','totalnextyear.reference_number', 'totalnextyear.receipt_number', 'totalnextyear.due_date','totalnextyear.fees', 'totalnextyear.totalnextyear', 'student_registration.student_name','student_registration.class_name')
        ->when(!empty($class_name), function ($query) use ($class_name) {
            return $query->where('student_registration.class_name', $class_name);
        })
        ->when(!empty($student_name), function ($query) use ($student_name) {
            return $query->where('student_registration.student_name', 'LIKE', '%' . $student_name . '%');
        })
        ->when(!empty($fromdate) && !empty($todate), function ($query) use ($fromdate, $todate) {
            return $query->whereBetween('totalnextyear.created_at', [$fromdate, $todate]);
        })
        ->when(!empty($reciptno), function ($query) use ($reciptno) {
            return $query->where('totalnextyear.receipt_number', $reciptno);
        })
        ->groupBy('totalnextyear.scholar_no')
        ->get();
        // echo"<pre>";print_r($all_inquiry);exit;
        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        $date_text =  "Print Date : ".date('d-M-Y');
        $extraHeaders = ['','','','Lokmanya Vidya Niketan','Student Balance Report','Date From 01-Apr-2023 To 31-Mar-2024',$date_text];
        
        $csv->insertOne($extraHeaders);

        $csvExporter=['Index        ','Class        ','Section        ','Scholar No', 'Enrollment No', 'Student Name', 'Father Name','Gender        ', 'OPENING        ', 'CURRENT FEE ASSIGNED        ', 'TOTALDUES        ', 'RECEIVED        ', 'Late Fee        ', 'FEE DUES        ' , 'Father Mob        ', 'Student Mob        ', 'Remarks        ' , 'Category        ', 'Batch Name        '];
        $csv->insertOne($csvExporter);

        foreach ($all_inquiry as $index => $row) {
            $arr_data = json_decode($row->json_str, true);
            $fatherName = $arr_data['fathername'];
            $gender = (!empty($arr_data['gender'])) ? $arr_data['gender'] : '';
            $father_mob = $arr_data['father_mobile'];
            $student_mob = $arr_data['phone_number'];
            $category = (!empty($arr_data['category'])) ? $arr_data['category'] : '';
            $batchname = (!empty($arr_data['batch'])) ? $arr_data['batch'] : '';
            $csv->insertOne([
                $index + 1, 
                $row->class_name,
                '-',
                $row->scholar_no,
                '-',
                $row->student_name,
                $fatherName,
                $gender,
                '0',
                '0',
                '0',
                '-'.$row->totalnextyear,
                '0',
                '-'.$row->totalnextyear,
                $father_mob,
                $student_mob,
                '-',
                $category,
                $batchname,               
            ]);
        }
        // echo"<pre>";print_r($csv);exit;
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename= filterdueamountstu.csv',
        ];

        // Return the CSV file as a response
        return response($csv->getContent(), 200, $headers);
    }
}
