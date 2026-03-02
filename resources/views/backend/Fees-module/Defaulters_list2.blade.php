@extends('backend.layouts.main')
@section('main-container')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    input {
    position: relative;
}
    input[type="date"]::-webkit-calendar-picker-indicator {
    background-position: right;
    background-size: auto;
    cursor: pointer;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    top: 7px;
    width: auto;
}

.uperletter{
  text-transform: capitalize;
}  


</style>
    @php
        $i = 0;
    @endphp
    <div class="main-content">
        <div class="form_section1_div">
            <div class="breadcrumb">
                <h1 class="me-2">Defaulters List </h1>
            </div>

            <div class="separator-breadcrumb border-top"></div>
            @if (!empty($teacher_subjects))
                <form id="progress-form" class="p-4 progress-form" action="{{ url('store_defaulters_list') }}" novalidate
                    method="post">
                    <input type="hidden" @if (!empty($teacher_subjects)) @foreach ($teacher_subjects as $teacher_subject)
                value=" {{ $teacher_subject->id }}" name="id" value="">
                @endforeach
            @else
                 @endif
                        
                @else
                     <form id="progress-form" class="p-4 progress-form"  action="{{ url('save_defaulters_list') }}" novalidate
                        method="post"> 
                        <!-- <form id="progress-form" class="p-4 progress-form" action="{{ url('search_defaulters_list') }}" novalidate
                        method="post"> -->
            @endif

            @csrf


            <div class="row">

                {{-- <div class="col-md-3 form-group mb-3">                  
                    <label for="year">Year</label>                    
                    <select name="year" class="form-control" id="year">                       
                            <option value="" selected> -- Please select -- </option>
                            <option value="2020">2020</option>
                            <option value="2021">2021</option>                        
                    </select>
                </div> --}}


                {{-- <div class="col-md-3 form-group mb-3">
                    <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="lastName1">Year</label>
                    <select name="year" class="form-control" id="year">
                        @if (!empty($sessions))
                            <option value=""> -- Please select -- </option>
                            <option value="" selected> -- Please select -- </option>
                            @foreach ($studentsession as $studentsession)
                                <option value="{{ $studentsession->year }}"
                                    {{ $sessions[sizeof($sessions) - 1]->year == $studentsession->year ? 'selected' : '' }}>
                                    {{ $studentsession->year }}</option>
                            @endforeach
                        @else
                            <option value="" selected> -- Please select -- </option>
                            @foreach ($studentsession as $studentsession)
                                <option value="{{ $studentsession->year }}">{{ $studentsession->year }}
                                </option>
                            @endforeach
                    </select>
                    @endif
                </div> --}}

                <div class="col-md-3 form-group mb-3">
                    <label for="firstName1">Class Name</label>
                    {{-- <select id="classname" class="form-control" name="class_name" autocomplete="" required>
                       @if(!empty($formdata[0]))
                            <option value=""> -- Please select -- </option>
                                    @foreach($studentclasses as $studentclasses)
                                        @if($formdata[0]['class_name'] != $studentclasses->class_name)
                                        <option value="{{ $studentclasses->class_name }}">{{ $studentclasses->class_name }}
                                        </option>
                                        @else
                                           <option selected value="{{$formdata[0]['class_name']}}">{{$formdata[0]['class_name']}}</option>

                                        @endif

                                     @endforeach
                        @else
                        @if (!empty($studentclasses))
                                <option value="" selected> -- Please select -- </option>
                                     @foreach($studentclasses as $studentclasses)
                                        <option value="{{ $studentclasses->class_name }}">{{ $studentclasses->class_name }}
                                        </option>
                                     @endforeach
                        @else
                            <option value="" selected> -- Please select -- </option>
                        @endif
                        @endif
                    </select> --}}

                    <select required id="class_name" class="form-control" name="class_name" autocomplete="" required>
                        <option value="">-- Please select --</option>
                        @if (!empty($stream_master))
                        @foreach ($stream_master as $s_item)
                        {{ $class_stream = $s_item->class_name }}
                        @endforeach
                        @endif
                        @foreach ($classlist as $each)
                        <option {{( (!empty($class_stream)) && ($stream_master[0]->class_name==$each->class_name)) ? 'selected' :
                                                  '' }} value="{{ $each->class_name }}">{{ $each->class_name }}</option>
                        @endforeach
                    </select>




                    <span class="classname_msg validation_err"></span>
                  </div> 
                   

               {{--  <div class="col-md-3 form-group mb-3">
                    <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="lastName1">Class Name</label>
                    <select name="class_name" class="form-control" id="class_name">
                        @if(!empty($formdata[0]))
                               <option value="{{$formdata[0]['class_name']}}">{{$formdata[0]['class_name']}}</option>
                                @foreach($studentclasses as $studentclass)
                                <?php //print_r($studentclass);?>
                                        <option value="{{ $studentclass }}">{{ $studentclass }}
                                        </option>
                                     @endforeach
                        @else
                        @if (!empty($studentclasses))
                                <option value="" selected> -- Please select -- </option>
                                     @foreach($studentclasses as $studentclasses)
                                        <option value="{{ $studentclasses->class_name }}">{{ $studentclasses->class_name }}
                                        </option>
                                     @endforeach
                        @else
                            <option value="" selected> -- Please select -- </option>
                        @endif
                        @endif
                    </select>
                </div>--}}

                <div class="col-md-3 form-group mb-3">
                    <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="lastName1">Section Name</label>
                    <select name="section_name" class="form-control" id="section_name">
                    @if(!empty($formdata[0]))
                        <option value="{{$formdata[0]['section_name']}}">{{$formdata[0]['section_name']}}</option>
                    @else
                        @if (!empty($teacher_subjects))
                             <option value="{{$formdata[0]['teacher_subjects']}}">{{$formdata[0]['teacher_subjects']}}</option>
                        @else

                        @endif
                        @endif
                    </select>

                </div>
{{-- 
                <div class="col-md-3 form-group mb-3">
                    <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="lastName1">Date type</label>
                    <select name="date_type" class="form-control" id="date_type">
                        @if (!empty($teacher_subjects))
                            <option value="" selected> -- Please select -- </option>
                            <!-- <option value="11th SC(BPSY)">11th SC(BPSY)</option> -->
                            <option value="Due Date"
                                {{ $teacher_subjects[sizeof($teacher_subjects) - 1]->date_type == 'Due Date' ? 'selected' : '' }}>
                                Due Date</option>
                            <option value="Entry Date"
                                {{ $teacher_subjects[sizeof($teacher_subjects) - 1]->date_type == 'Entry Date' ? 'selected' : '' }}>
                                Entry Date</option>
                        @else
                            <option value="" selected> -- Please select -- </option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->date_type }}">{{ $subject->date_type }}</option>
                            @endforeach

                    </select>
                    @endif
                </div> --}}

                <div class="col-md-3 form-group mb-3">                  
                    <label for="lastName1">Date Type</label>                    
                    <select name="date_type" class="form-control" id="date_type">  
                    <?php $selected = ""; ?>                  
                    @if(!empty($formdata[0]))
                        <?php $selected = $formdata[0]['date_type']; ?>
                    @else       
                    @endif                     
                    <option value="" <?php echo ($selected == "") ? 'selected' : ''; ?> > -- Please select -- </option>
                    <option value="DUE DATE" <?php echo ($selected  == "DUE DATE") ? 'selected' : ''; ?> >DUE DATE</option>
                    <option value="ENTRY DARE" <?php echo ($selected  == "ENTRY DARE") ? 'selected' : ''; ?> >ENTRY DATE</option>   

                </select>
                </div>

                {{-- <div class="col-md-3 form-group mb-3">
                    <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="lastName1">Date</label>
                    <input type="date" class="form-control" name="date" value="{{ !empty($formdata[0]['date']) ? $formdata[0]['date'] : '' }}" id="picker2">
                </div> --}}


                 <div class="col-md-3 form-group mb-3">
                    <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="ac_head_name">Show Single Head</label>
                    <select name="ac_head_name" class="form-control" id="ac_head_name">
                    @if(!empty($formdata[0]))
                    <option value=""> -- Please select -- </option>
                        @foreach($headss as $head)
                            @if($formdata[0]['ac_head_name'] == $head->ac_head_name)
                            <option selected value="{{ $head->ac_head_name }}">{{ $head->ac_head_name }}
                            </option>
                            @else
                            <option value="{{ $head->ac_head_name }}">{{ $head->ac_head_name }}
                            </option>
                            @endif
                        @endforeach
                    @else 
                    <option value="" selected> -- Please select -- </option>
                    @foreach($headss as $head)
                        <option value="{{ $head->ac_head_name }}">{{ $head->ac_head_name }}
                        </option>
                    @endforeach
                    @endif
                    </select>
                </div> 

                {{-- <div class="col-md-3 form-group mb-3">                  
                    <label for="lastName1">Select Batch</label>                    
                    <select name="select_batch" class="form-control" id="select_batch">                       
                            <option value="" selected> -- Please select -- </option>
                            <option value="Teacher name 1">Teacher name 1</option>
                            <option value="Teacher name 2">Teacher name 2</option>                        
                    </select>
                </div> --}}


                {{-- <div class="col-md-3 form-group mb-3">                  
                    <label for="lastName1">Scholarship ?</label>                    
                    <select name="scholarship" class="form-control" id="scholarship">                       
                            <option value="" selected> -- Please select -- </option>
                            <option value="BOTH">BOTH</option>
                            <option value="NON SCHOLARSHIP">NON SCHOLARSHIP</option> 
                            <option value="SCHOLARSHIP">SCHOLARSHIP</option>                       
                    </select>
                </div> --}}
                {{-- <div class="col-md-3 form-group mb-3">                  
                    <label for="lastName1">Student ID</label>                    
                    <select name="student_id" class="form-control" id="student_id">                       
                            <option value="" selected> -- Please select -- </option>
                            <option value="BOTH">BOTH</option>
                            <option value="NON SCHOLARSHIP">NON SCHOLARSHIP</option> 
                            <option value="SCHOLARSHIP">SCHOLARSHIP</option>                       
                    </select>
                </div> --}}

                <div class="col-md-3 form-group mb-3">
                    <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="lastName1">Student</label>
                    <select name="student_id" class="form-control" id="student_id">
                    @if(!empty($studentname[0]) )
                        <option value="{{$studentname[0]['student_name']}}">{{$studentname[0]['student_name'] }}</option>
                        @foreach($student_ids as $student)
                            <option value="{{ $student->student_id }}">{{ $student->student_name }}</option>
                        @endforeach
                    @else   
                        <option value="" selected> -- Please select -- </option>
                        @foreach($student_ids as $student)
                            <option value="{{ $student->student_id }}">{{ $student->student_name }}</option>
                        @endforeach
                    @endif

                          
                    </select>
                </div>


                {{-- <div class="row"> --}}

                <!--
                                <div class="col-md-3 form-group mb-3">
                        s                <label for="remarks"> </label>
                                        <input
                                          class="form-control"
                                          id="remarks"
                                          name="remarks"
                                          type="text"
                                          {{-- @if (!empty($exam_master)) @foreach ($exam_master as $exammaster) --}}
                                          {{-- value=" {{ $exammaster->remarks }}" --}}
                                        {{-- @endforeach --}}
                                      {{-- @else --}}
                                        {{-- value="" @endif --}}
                                          placeholder="Remark"
                                        />
                                      </div> -->
                                      
                <div class="col-md-12">
                    <label for="remarks"> Export PDF </label>
                    <input type="checkbox" name="pdf" value="PDF"> |  
{{-- 
                    <label for="remarks"> Export CSV </label>
                    <input type="checkbox" name="pdf" value="PDF"> |  --}}

                    {{-- <button class="btn btn-primary" name="btn" value="submit text">Search</button> --}}
                    <input hidden type="text" name="keyword" id="search-keyword" placeholder="Search...">
                    {{-- <button  class="btn btn-primary" type="button" id="search-button">Search</button> --}}
                    <button id="search-button" class="btn btn-primary" name="btn" value="submit text">Search</button>
                    <input type="reset" class="btn btn-danger text text-white" value="Reset">


                </div>
            </div>
            </form>
            <br>


            {{-- <form id="" class="" action="{{ url('copy-teachersubject') }}" novalidate method="post">
                @csrf

                <div class="col-md-3 form-group mb-3">
                    <!-- <input type="text" id="schedule_nameb" name="schedule_nameb" value=""> -->
                    <label for="pre_session">Session Name</label>
                    <select name="pre_session" class="form-control" id="pre_session">
                        @if (!empty($sessions))
                            <option value=""> -- Please select -- </option>
                            <option value="" selected> -- Please select -- </option>
                            @foreach ($pre_studentsessions as $pre_studentsession)
                                <option value="{{ $pre_studentsession->session_name }}"
                                    {{ $sessions[sizeof($sessions) - 1]->session_name == $pre_studentsession->session_name ? 'selected' : '' }}>
                                    {{ $pre_studentsession->session_name }}</option>
                            @endforeach
                        @else
                            <option value="" selected> -- Please select -- </option>
                            @foreach ($pre_studentsessions as $pre_studentsession)
                                <option value="{{ $pre_studentsession->session_name }}">
                                    {{ $pre_studentsession->session_name }}</option>
                            @endforeach
                    </select>
                    @endif
                </div>


                <button type="button" onclick="copy_data(event)" class="btn btn-primary">Copy from previous
                    year</button>
            </form> --}}

        </div>
        <div class="breadcrumb">
            <h1 class="me-2">Search Result :-</h1>
            <!-- <th>Sr.</th>
                                        <th>Scholar No.</th>
                                        <th>Student Name</th>
                                        <th>Class Name</th>
                                        <th>Section</th>
                                        <th>Account Name</th>
                                        <th>Required Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Balance Amount</th>
                                        <th>Due Date</th>
                                        <th>Enter Date</th> -->
            <div class="form-group">
                <form method="POST" action="{{ route('exports.csv') }}">
                    @csrf
                    <!-- <input type="hidden" name="table_name" value="defaulters_lists"> -->
                    <input type="hidden" name="table_name" value="defaulters_lists">
                    <input type="hidden" name="column_names[]" value="scholar_no">
                    <input type="hidden" name="column_names[]" value="student_name">
                    <input type="hidden" name="column_names[]" value="class_name">
                    <input type="hidden" name="column_names[]" value="section_name">
                    <input type="hidden" name="column_names[]" value="account_name">
                    {{-- <input type="hidden" name="column_names[]" value="required_amount">
                    <input type="hidden" name="column_names[]" value="paid_amount">
                    <input type="hidden" name="column_names[]" value="balance_amount">
                    <input type="hidden" name="column_names[]" value="due_date">
                    <input type="hidden" name="column_names[]" value="enter_date"> --}}

                    {{-- <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button> --}}
                </form>



                {{-- <form method="POST" action="{{ route('exports.csv') }}">
                    @csrf
                    <!-- Define the columns you want to export as CSV -->
                    <input type="hidden" name="column_names[]" value="scholar_no">
                    <input type="hidden" name="column_names[]" value="student_name">
                    <!-- Add other columns here -->
                
                    {{-- <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button> --}}
                {{-- </form>  --}}
                <form method="POST" action="{{ route('exportdefaulterslist') }}">
                <input type="hidden" name="studentid" value="enter_date">
                <input type="hidden" name="column_names[]" value="enter_date">
                <input type="hidden" name="column_names[]" value="enter_date">
                <input type="hidden" name="column_names[]" value="enter_date">

                </form>
            </div>
        </div>
        <div class="separator-breadcrumb border-top"></div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card text-start">
                    <div class="card-body">
                        <!-- <h4 class="card-title mb-3 text-end"><a href="{{ url('add-student-registrations') }}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                        <div class="table-responsive">
                        <div class="card-title mb-3 text-end">
                        @if (!empty($result))
                        <?php
                        // $csv->insertOne(['SN','Class','Sesstion','Student Name','Roll No','Scholar No.','Enrollment No.','Stu Mob.','Father Mob.','Fees']);        
                        //
                        // echo '<pre>';
                        // print_r($result);
                        $l = 1;
                        foreach($result as $obj_r){
                            foreach($obj_r as $obj_v){
                                // print_r($obj_v);
                                if (preg_match('/class_name:\s*([^\s]+)/', $obj_v, $matches)) {
                                    $class_name = $matches[1];
                                } else {
                                    $class_name = 'NA';
                                }
                                if (preg_match('/student_id:\s*([^\s]+)/', $obj_v, $matches)) {
                                    $student_id = $matches[1];
                                } else {
                                    $student_id = 'NA';
                                }
                                if (preg_match('/due:\s*([^\s]+)/', $obj_v, $matches)) {
                                    $due = $matches[1];
                                } else {
                                    $due = 'NA';
                                }

                                foreach($student_ids as $student){
                                    if($student->student_id == $student_id){
                                        $studentname = $student->student_name;
                                        $father_mobile = $student->father_mobile;
                                        $mobile_number = $student->mobile_number;
                                    }
                                }
                                
                                $data_result[] = [
                                    'SN' => $l,
                                    'Class' => $class_name,
                                    'Sesstion' => '',
                                    'Student_Name' => $studentname,
                                    'Roll_No' => '',
                                    'Scholar_No' => $student_id,
                                    'Enrollment_No' => '',
                                    'Stu_Mob' => $mobile_number,
                                    'Father_Mob' => $father_mobile,
                                    'Fees' => $due,
                                ];
                                $l++;
                            }
                            
                        }
                        $studentFees = array();

                        foreach ($data_result as $student) {
                            // Assuming 'Scholar No' is the key for the Scholar No value
                            $scholarNo = $student['Scholar_No'];

                            if (isset($studentFees[$scholarNo])) {
                                // If Scholar No exists in the $studentFees array, add fees to the existing total
                                $studentFees[$scholarNo]['Fees'] += intval($student['Fees']);
                            } else {
                                // If Scholar No doesn't exist in the $studentFees array, initialize it
                                $studentFees[$scholarNo] = $student;
                            }
                        }
                        
                        // print_r($studentFees);
                        // echo '</pre>';
                        $a = json_encode($studentFees);
                         //$outputArray["id"] = $k;
                         //$studentFees2=
                                // $outputArray["class"] = 01;
                                // $outputArray["Session"] = 01;
                                // $outputArray["Student_Name"] = 01;
                                // $outputArray["Roll_No"] = 01;
                                // $outputArray["Scholar_No"] = 01;
                                // $outputArray["Enrollment_No"] = 01;
                                // $outputArray["Stu_Mob"] = 01;
                                // $outputArray["Father_Mob"] = 01;
                                // $outputArray["Fees"] = 01; 
                                // $outputArray3 = json_encode($outputArray);
                                ?>
                                 <!-- <a class="btn btn-raised ripple btn-raised-warning m-1" href="{{url('defaulter_list_csv').'/'.$outputArray3}}">export csv</a> -->
                                @else
                                <a class="btn btn-raised ripple btn-raised-warning m-1" href="">export csv</a>
                                @endif
                    </div>
                           <?php       if (isset($_COOKIE['selectedYear'])) {
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
        // $db_name = "2023_2024";

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
        $challan_data ="";
        $challan_data ="";
        
         if(!empty($classname)){
             $student_data = DB::connection('dynamic')->table('student_registration')->where('class_name', $classname)->get();
         }else{
            $classname1 = array("Nursery","KG1","KG2","01","02","03","04","06","08","09","10","11","12");
            $student_data = DB::connection('dynamic')->table('student_registration')->whereIn('class_name', $classname1)->orderBy('class_name', 'asc')->get();
         }
         //$student_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('due_upto', '05-05-2024')->join('student_registration', 'feesreceiptchallan.student_id', '=', 'student_registration.id')->groupBy('feesreceiptchallan.student_id')->get();
             $i=1; $total_dueamount_ = 0; $by_cash_ = 0; $sno = 1;   $sno2 = 2;                    
                          $totalnextyear1 = 0;
                          $refundable = 0;
             foreach($student_data as $student_data1){
                $challan_data = DB::connection('dynamic')->table('feesreceiptchallan')->where('student_id', $student_data1->id)->get();
                 $firstRow = true; 
                foreach ($challan_data as $object) {    

                    if ($firstRow) {
                                    $firstRow = false; // Set the flag to false after processing the first row
                                    continue; // Skip the first row
                                    echo "next";
                    }
                        $data = json_decode($object->str_json, true); 
                            $term_str = explode(',', $data['term_str']);
                            $head_name = explode(',', $data['head_name']);
                              $head_due_amount = explode(',', $data['head_due_amount']);
                              $head_rec_ammount1 = explode(',', $data['head_rec_ammount']);
                              
                              $rec_ammount = array_sum($head_rec_ammount1);
                              $by_cash = $data['by_cash'];
                              $total_dueamount = $data['grand_total_due']; // Extract the "total_dueamount" value
                              $payment_by_select = $data['payment_by_select'];
                              $term_str = explode(',', $data['term_str']);


                              // // Create an array to hold remarks for each fee item
                              $remarks = [];
                              for ($i = 0; $i < count($head_name); $i++) {
                                  $remarks[] = $head_name[$i] . ' ( ' . $head_due_amount[$i] . ' )';
                              }

                              

                              $by_cash_ += $by_cash;
                              $sno++;

                              
                              if(!empty($rec_ammount)){ 
                                 $total_dueamount_ += $rec_ammount;
                               }else{
                                $total_dueamount_ += $total_dueamount;
                              }
                              $sno++;
                              $a1 = $total_dueamount_ + $totalnextyear1;
                              $b2 =$by_cash_ - $a1;

                    if(!empty($data['discount']['lumpsum_fees'])){   
                    }else{                 
                            if($object->due_upto == '05-05-2024'){
                                echo $sno2; echo ' '.$student_data1->student_name; echo "<br>"; $sno2++;
                            }
                    }
                }
         }
         //print_r($student_data); 
         die();
       
        ?>
        <table class="display table table-striped table-bordered" id="zero_configuration_table"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Class Name</th>
                                        <!-- <th>Enrollment No.</th> -->
                                        <th>Scholar No</th>
                                        <th>Student Name</th>
                                        <!-- <th>Discount</th> -->
                                        
                                        <th>Defaulter</th>
                                        <!-- <th>Min Date</th> -->
                                        <th>Fees</th>
                                        <!-- <th>Enter Date</th> -->
                                        <!-- <th>Student</th> -->
                                        <!-- {{-- <th>Action</th> --}} -->
                                    </tr>
                                </thead>
                                <tbody>
        <?php
                
       
       echo " </tfoot></table>"; ?>
                      
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end of main-content -->
    </div>
    
@endsection

<script defer>

function nextyearfeechange(e){
    const label = document.getElementById('next_yesr_fees');
    const checkbox = document.getElementById('next_year_fees');
    label.value = checkbox.checked ? 'Yes' : 'No';
    
}
   
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('search-form');
        const searchResultsContainer = document.getElementById('search-results-container');

        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(searchForm);

            fetch('{{ route('search') }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                searchResultsContainer.innerHTML = data;
            });
        });
    });

 
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
    // Function to handle the date input
    $("#picker2").on("input", function() {
      // Do something with the date value
      const dateValue = $(this).val();
      console.log("Selected Date:", dateValue);
    });

    var session_select = $('#section_name').val();
    var select_val = $('#classname').val();

    if(select_val != null && select_val != ""){
        $.ajax({
            data: { id: select_val },
            url: "{{url('classsection-view')}}/" + select_val,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            method: "POST",
            dataType: 'json',
            success: function (data) {
                // console.log(data);
                $('#section_name').html('<option value=""> -- Please select -- </option>');
                for (var i = 0; i < data.length; i++) {
                    var studentData = data[i].section_name;
                    if(session_select == studentData){
                        $('#section_name').append('<option selected value="' + studentData + '">' + studentData + '</option>');
                    } else {
                        $('#section_name').append('<option value="' + studentData + '">' + studentData + '</option>');
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }

    $('#classname').on('change', function () {
        var iso2 = $(this).val();
        console.log(iso2);
        if (iso2) {
            $.ajax({
                data: { id: iso2 },
                url: "{{url('classsection-view')}}/" + iso2,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                method: "POST",
                dataType: 'json',
                success: function (data) {
                      // console.log(data);
                      $('#section_name').html('<option value=""> -- Please select -- </option>');
                      for (var i = 0; i < data.length; i++) {
                          var studentData = data[i].section_name;
                          // console.log(studentData);
                          $('#section_name').append('<option value="' + studentData + '">' + studentData + '</option>');
                      }
                  },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        } else {
            $('#section_name').html('<option value="">Select class first</option>');
        }
    });
  });
</script>

<script>
    $(document).ready(function() {
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('student_id');
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
        }
    });
  
    function capitalizeFirstLetter(str) {
        var words = str.toLowerCase().split(' ');
        for (var i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
        }
        return words.join(' ');
    }
});
  </script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('ac_head_name');
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
        }
    });
  
    function capitalizeFirstLetter(str) {
        var words = str.toLowerCase().split(' ');
        for (var i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
        }
        return words.join(' ');
    }
  </script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('date_type');
        for (var i = 0; i < select.options.length; i++) {
            select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
        }
    });
  
    function capitalizeFirstLetter(str) {
        var words = str.toLowerCase().split(' ');
        for (var i = 0; i < words.length; i++) {
            words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
        }
        return words.join(' ');
    }
  </script>
