<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\backend\InquiryEntryController;

echo "=== SIMULATING ADMISSION IN 2026_2027 FOR NEXT YEAR (2027_2028) ===\n";
Config::set('database.connections.dynamic.database', '2026_2027');
DB::purge('dynamic');
DB::reconnect('dynamic');

$dummyReq = Request::create('/');
$controller = new InquiryEntryController($dummyReq);

// Submit enquiry for next year
$saveReq = Request::create('/save_student_preinquiryentry', 'POST', [
    'next_year' => 1,
    'formno' => 1,
    'enquirydate' => date('Y-m-d'),
    'date_of_birth' => '2015-05-10',
    'studentname_prefix' => 'Master',
    'studentname' => 'Rahul Sharma NextYear',
    'fathername_prefix' => 'Mr',
    'fathername' => 'Amit Sharma',
    'fathermobile' => '9876543210',
    'mothername_prefix' => 'Mrs',
    'mothername' => 'Sunita Sharma',
    'mothermobile' => '9876543211',
    'classname' => '01',
    'admission_type' => 'CBSE',
    'gender' => 'Male',
    'state' => 1,
    'city' => 'Indore',
    'pincode' => '452001',
    'address' => 'Vijay Nagar'
]);

$controller->save_student_preinquiryentry($saveReq);

// Check in 2026_2027
$in2026 = DB::table('2026_2027.inquiry_registration')
    ->where('student_name', 'Rahul Sharma NextYear')
    ->first();
echo "Saved in 2026_2027: form={$in2026->form_number}, next_year={$in2026->next_year}, session={$in2026->session_name}\n";

// Check mirrored in 2027_2028
$in2027 = DB::table('2027_2028.inquiry_registration')
    ->where('student_name', 'Rahul Sharma NextYear')
    ->first();
echo "Mirrored in 2027_2028: form={$in2027->form_number}, next_year={$in2027->next_year}, session={$in2027->session_name}\n";

// Now switch to 2027_2028 and test get_next_enquiry_number
echo "\n=== SWITCHING TO 2027_2028 ===\n";
Config::set('database.connections.dynamic.database', '2027_2028');
DB::purge('dynamic');
DB::reconnect('dynamic');

$controller27 = new InquiryEntryController($dummyReq);

// When staff logs in and DID NOT CLICK next_year checkbox:
$reqCurrent27 = Request::create('/get-next-enquiry-number', 'GET', ['next_year' => 0]);
$resCurrent27 = $controller27->get_next_enquiry_number($reqCurrent27);
echo "In 2027_2028 (next_year=0, did NOT click): " . $resCurrent27->getContent() . "\n";

// When staff DOES click next_year checkbox (for 2028_2029):
$reqNext27 = Request::create('/get-next-enquiry-number', 'GET', ['next_year' => 1]);
$resNext27 = $controller27->get_next_enquiry_number($reqNext27);
echo "In 2027_2028 (next_year=1, DID click): " . $resNext27->getContent() . "\n";

$view27 = $controller27->admin_preenquiryform();
$viewData27 = $view27->getData();
echo "admin_preenquiryform in 2027_2028 default: currentEnquiryNo = {$viewData27['currentEnquiryNo']}, nextYearEnquiryNo = {$viewData27['nextYearEnquiryNo']}\n";
