<?php 
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
$host = "localhost";
$user = "root";
$password = "";
$dbname = "2023_2024";

$con = mysqli_connect($host, $user, $password, $dbname);

if (!$con) {
 die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['submit']))
{
     
    $fileMimes = array(
        'text/x-comma-separated-values',
        'text/comma-separated-values',
        'application/octet-stream',
        'application/vnd.ms-excel',
        'application/x-csv',
        'text/x-csv',
        'text/csv',
        'application/csv',
        'application/excel',
        'application/vnd.msexcel',
        'text/plain'
    );
 
    // Validate selected file is a CSV file or not
    if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $fileMimes))
    {
 
        // Open uploaded CSV file with read-only mode
        $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

        // Skip the first line
        fgetcsv($csvFile);

        // Parse data from CSV file line by line        
        while (($getData = fgetcsv($csvFile, 15000, ",")) !== FALSE)
        {
            // Get row data
            $name = $getData[0];
            $student_name = $getData[0];
            $form_number = $getData[1];
            
            $session_name = "2023_2024";
            $mobile_number = null;

            $scholar_no = $getData[1];
            $formattedBatch = $getData[2];
            $batch = str_replace("-", "_", $formattedBatch);

            $class = $getData[3];

            $section = $getData[4];
            $bus_a = $getData[5];
            $bus_apply = null;
            if($bus_a === 'TRUE'){
                $bus_apply = date('Y-m-d');
            }
            
            $category = $getData[6];
            $caste = $getData[7];
            $dateTime = DateTime::createFromFormat('d-M-Y', $getData[8]);
            // print_r($getData);die();
            $date_of_birth = $dateTime->format('Y-m-d');//$getData[3];
            
            $father_name = $getData[9];
            $father_designation = $getData[10];
            $father_education = $getData[11];
            $father_email = $getData[12];
            $father_mobile = $getData[13];
            $father_office_address = $getData[14];
            $father_office_phone = $getData[15];
            $mother_name = $getData[16];
            $mother_education = $getData[17];
            $mother_mobile = $getData[18];
            $mother_occupation = $getData[19];
            $mother_office_address = $getData[20];
            $mother_office_phone = $getData[21];
            $mother_tongue = $getData[22];
            $permanent_address = $getData[23];
            $permanent_phone = $getData[24];
            $religion = $getData[25];
            $gender = $getData[26];
            $sibblings = $getData[27];
            $samagraID = $getData[28];
            $aadhaarNo = $getData[29];
            $bankname = $getData[30];
            $StuBankAccNo = $getData[31];
            $IFSC_Code = $getData[32];
            $IsRTE = ($getData[33] === 'TRUE') ? 'RTE' : 'Non RTE';

            // $status = "i";

            // $data = [
            //     "enquiryno" => $getData[4],
            //     "formno" => $getData[4],
            //     "studentname_prefix" => "",
            //     "student_dob" => $getData[21],
            //     "fathername_prefix" => "Mr.",
            //     "fathername" => $getData[25],
            //     "fathermobile" => $getData[29],
            //     "fatheroccupation" => $getData[30],
            //     "mothername_prefix" => "Mrs.",
            //     "mothername" => $getData[49],
            //     "mothermobile" => $getData[53],
            //     "motheroccupation" => $getData[54],
            //     "gender" => $getData[73],
            //     "admission_type" => "",
            //     "email" => $getData[79],
            //     "remarks" => "",
            //     "address" => $getData[61],
            //     "city" => $getData[62],
            //     "pincode" => $getData[65],
            //     "state" => $getData[63],
            //     "religion" => $getData[69],
            //     "caste" => $getData[16],
            //     "category" => $getData[15],
            //     "received_amount" => "",
            //     "presentlyclass" => "",
            //     "presentlyschool" => "",
            //     "hear_aboutus" => "",
            //     "follow_up_date" => "",
            //     "inter_dt" => $getData[11],
            //     "Adm" => $getData[11],
            //     "folloupdate_status" => "",
            //     "visited" => "",
            //     "followup_Cancel" => "",
            //     "Follows" => "",
            //     "Response" => "",
            //     "Counseller" => "",
            //     "Priority" => "",
            //     "followup_remark" => "",
            //     "assign_calling" => "",
            //     "enquiry_through" => "",
            //     "siblings_name" => "",
            //     "sibling_class" => "",
            //     "siblings_school" => "",
            //     "siblings_bod" => "",
            //     "siblings_namesecond" => "",
            //     "sibling_class_second" => "",
            //     "siblings_school_second" => "",
            //     "siblings_bod_second" => "",
            // ];
            
            $arrayVar = [
                "scholar_no"=> $scholar_no,
                "application_for"=> $IsRTE,
                "form_number"=> $scholar_no,
                "studentname_prefix"=> "",
                "studentname"=> $student_name,
                "received_amount"=> null,
                "amount"=> null,
                "gender"=> $gender,
                "student_dob"=> $date_of_birth,
                "nationality"=> "INDIA",
                "session_name"=> "2023_2024",
                "present_address"=> $permanent_address,
                "permanent_address"=> $permanent_address,
                "phone_number"=> $permanent_phone,
                "mobile_number"=> null,
                "mother_tongue"=> $mother_tongue,
                "remarks"=> null,
                "vaccaination"=> null,
                "SSSMID"=> $samagraID,
                "family_SSSMID"=> null,
                "AadharNo"=> $aadhaarNo,
                "student_medical_conserns"=> null,
                "present_school_name"=> null,
                "is_sibling_applied_for_admission"=> $sibblings,
                "searchfather"=> null,
                "siblings_namesecond"=> [
                    null
                ],
                "siblings_section_second"=> "",
                "siblings_bod_second"=> [
                    null
                ],
                "driver_name"=> null,
                "bus_facility_start_date"=> $bus_apply,
                "staff_name"=> [
                    "Select Staff"
                ],
                "fathername_prefix"=> "Mr.",
                "fathername"=> $father_name,
                "father_education"=> $father_education,
                "father_organization"=> null,
                "father_designation"=> $father_designation,
                "father_office_telephone"=> null,
                "father_email_id"=> $father_email,
                "father_mobile"=> $father_mobile,
                "fatherSSSMID"=> null,
                "fatherAadharNo"=> "",
                "father_res_address"=> $father_office_address,
                "father_emergency_contact"=> $father_office_phone,
                "mothername_prefix"=> "Mrs",
                "mothername"=> $mother_name,
                "mother_education"=> $mother_education,
                "mother_organization"=> null,
                "mother_office_telephone"=> $mother_office_phone,
                "mother_email"=> null,
                "mother_mobile"=> $mother_mobile,
                "motherSSSMID"=> null,
                "motherAadharNo"=> "",
                "mother_res_address"=> $mother_office_address,
                "mother_emergency_contact"=> null,
                "guardian_name"=> null,
                "guardian_office_telephone"=> null,
                "guardian_email_id"=> null,
                "guardian_mobile"=> null,
                "guardian_permanent_address"=> null,
                "guardian_emergency_contact"=> null,
                "guardian_relation"=> null,
                "bankName"=> $bankname,
                "branchName"=> null,
                "account_number"=> $StuBankAccNo,
                "ifsc_code"=> $IFSC_Code,
                "iid"=> "",
                "submit"=> "submit",
                "bus_facility_end_date"=> "",
                "id"=> null,
                "classname"=> $class,
                "batch"=> $batch,
                "section_name"=> $section,
                "father_dob"=> "",
                "mother_dob"=> "",
                "mother_ocupation"=> $mother_occupation,
                "local_guardian"=> "",
                "local_address"=> "",
                "guradian_phone"=> "",
                "guradian_mobile"=> "",
                "guradian_parent_category"=> "",
                "guradian_new_category"=> "",
                "guradian_new_house"=> "",
                "category"=> $category,
                "religion"=> $religion,
                "student_caste"=> $caste,
                "required_school_transport"=> null,
                "birth_certificate_chk"=> "yes",
                "transfer_certificate_chk"=> "yes",
                "address_proof_chk"=> "yes",
                "cast_chk"=> null,
                "aadhar_chk"=> "yes",
                "bankb_chk"=> "yes",
                "stuprof_chk"=> "yes",
                "sssmprof_chk"=> "yes",
                "salaryprof_chk"=> "yes",
                "last_report_card_chk"=> "yes",
                "BirthCertificate"=> null,
                "TransferCertificate"=> null,
                "AddressProff"=> null,
                "CastProff"=> null,
                "aadharProff"=> null,
                "bankbProff"=> null,
                "StuProf"=> null,
                "sssmprof"=> null,
                "salaryprof"=> null,
                "LastReportCard"=> null,
                "files"=> []
            ];
            
            // $str_json = json_encode($data);
            $arrayVar_json = json_encode($arrayVar);
            // mysqli_query($con, "INSERT INTO inquiry_registration (form_number, date_of_birth, class_name, student_name, gender, session_name, mobile_number, json_str, status) 
            // VALUES ('" . $form_number . "', '" . $date_of_birth . "', '" . $class_name . "', '" . $student_name . "', '" . $gender . "', '" . $session_name . "', '" . $mobile_number . "', '" . $str_json . "', '" . $status . "')");

            mysqli_query($con, "INSERT INTO student_registration (application_for , form_number, scholar_no, date_of_birth, class_name, student_name, session_name, json_str, mobile_number, inq_mode, status, type, password) 
            VALUES ('CBSE','" . $form_number . "', '" . $getData[1] . "' ,'" . $date_of_birth . "', '" . $class . "', '" . $student_name . "', '" . $session_name . "', '" . $arrayVar_json . "', '" . $mobile_number . "', 'off', 'r', 's', '$2y$10$9fnwTyu.dNpYFReaxNS/OetOgCOIzAzyCDwi7po4A30Hi5PoTPVni')");
        }

        // Close opened CSV file
        fclose($csvFile);

        header("Location: create.php");         
    }
    else
    {
        echo "Please select valid file";
    }
}
?>