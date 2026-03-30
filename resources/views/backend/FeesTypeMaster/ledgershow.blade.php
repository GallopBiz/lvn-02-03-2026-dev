@extends('backend.layouts.main')
@section('main-container')

<style>
  .uperletter {
    text-transform: capitalize;
  } 

  @media print {
    .no-print-col {
      display: none !important;
    }
    .print-info {
      display: none !important;
    }
  }

  @media screen {
    .print-info {
      display: block !important;
    }
  }
</style>

<!-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" /> -->
<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css" rel="stylesheet" /> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css"/> 

<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content">
    <!-- ============ Body content start ============= -->
        <div class="main-content">
        <div class="breadcrumb">
            <h1>Student Ledger :</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="col-md-4 form-group mb-3">
                                <div class="form-outline w-auto p-4 progress-form">
                                    <label class="form-label" for="form1">Student Search</label>
                                    <select class="form-control select2" onchange="select_data(this);" name="search_student" id="search_student" data-live-search="true">
                                        <option data-tokens="china">Select The Student</option>
                                        <?php if(!empty($data_student_name)) {
                                            foreach($data_student_name as $name){
                                                $selected = "";
                                                if(!empty($student_data)){
                                                    if($student_data->id == $name->id){
                                                        $selected = "selected";
                                                    }
                                                }
                                                echo '<option '.$selected.' value="'.$name->id.'" data-tokens="'.$name->student_name.' '.$name->scholar_no.'">'.$name->student_name.' '.$name->scholar_no.'</option>';
                                                                    
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>     
           <div class="row">
            <div class="col-md-12">
              <div class="card mb-4">
                <div class="card-body"> 
                   <div class="card-title mb-3">Form Inputs</div>  
                   <form id="progress-form" class="p-4 progress-form" action="{{url('search-student-ledger')}}"  novalidate method="post">
                    {{ csrf_field() }}
                    <div class="row">
                    <input type="hidden" class="student_id" name="student_id" value="0">
                      <div class="col-md-3 form-group mb-3 uperletter">
                        <label for="firstName1">Student Name : </label>
                        <input
                          class="form-control uperletter"
                          id="student_name"
                          name="student_name"
                          type="text" 
                          <?php
                            if(!empty($student_data)){
                              echo 'value="'.$student_data->student_name.'"';
                            } 
                          ?>
                          readonly placeholder="Student Name"
                        />
                      </div>
                      <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Class : </label>
                        <input
                          class="form-control uperletter"
                          id="class" readonly
                          name="class"
                          <?php
                            if(!empty($student_data)){
                              echo 'value="'.$student_data->class_name.'"';
                            } 
                          ?>
                          type="text" placeholder="class"
                        />
                      </div>
                      <!-- <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Enrollment No : </label>
                        <input
                          class="form-control"
                          id="enrollment_no"
                          name="enrollment_no" readonly
                          type="text" placeholder="Enrollment No"
                        />
                      </div>  -->
                      <!-- <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Section : </label>
                        <input
                          class="form-control"
                          id="section"
                          name="section"
                          type="text" placeholder="Section"
                        />
                      </div>  -->

                      <div class="col-md-3 form-group mb-3">
                        <label for="lastName1">Upto date : </label>
                        <input
                          class="form-control"
                          id="upto_date"
                          name="uptodate" readonly
                          type="date"
                          placeholder="Upto date"
                        />
                      </div>
                     <!--  <div class="col-md-3 form-group mb-3">
                        <label for="session">Date type : </label>
                        <select id="date_type" class="form-control" name="date_type">
                            <option value=""> Due Date </option>
                            @foreach(config('global.session_name') as $each)
                            <option value="{{$each}}">{{$each}}</option>
                            @endforeach
                        </select>
                      </div> -->
                      <!-- <div class="col-md-3 form-group mb-3">
                        <label for="session">Pocket Money : </label>
                        <select id="session" class="form-control" name="session">
                            <option value=""> All </option>
                            @foreach(config('global.session_name') as $each)
                            <option value="{{$each}}">{{$each}}</option>
                            @endforeach
                        </select>
                      </div> -->
                      <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Scholar Number : </label>
                        <input
                          class="form-control"
                          id="scholar_number" readonly
                          name="scholar_number"
                          type="text" placeholder="Scholar Number"
                        />
                      </div>
                      
                      <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Father's name : </label>
                        <input
                          class="form-control uperletter" readonly
                          id="father_name"
                          name="father_name"
                          <?php
                            // if(!empty($student_data)){
                            //   echo '<pre>';
                            //   print_r($student_data);die();
                            //   $arr = json_decode($student_data,1);
                            //   echo 'value="'.$arr['student_father_name'].'"';
                            // } 
                          ?>
                          type="text" placeholder="Father's name"
                        />
                      </div>
                    <!--   <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Gender : </label>
                        <input
                          class="form-control"
                          id="gender"
                          name="gender"
                          type="text" placeholder="Gender"
                        />
                      </div> -->
                      <!-- <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Category : </label>
                        <input
                          class="form-control"
                          id="category"
                          name="category"
                          type="text" placeholder="Category"
                        />
                      </div> -->
                     <!--  <div class="col-md-3 form-group mb-3">
                        <label for="firstName1">Student Type : </label>
                        <input
                          class="form-control"
                          id="student_type"
                          name="student_type"
                          type="text" placeholder="Student Type"
                        />
                      </div> -->
                      <!-- <div class="col-md-6 form-group mb-6">
                        <label for="firstName1">Fee Commentment Remark : </label>
                        <input
                          class="form-control"
                          id="feecommentment_remark" readonly
                          name="feecommentment_remark"
                          type="text" placeholder="Fee Commentment Remark"
                        />
                      </div> -->
                      <!-- <div class="col-md-6 form-group mb-6">
                        <label for="firstName1">Don't Show Taxes in Print : </label>
                        <input
                          id="dstaxprint" readonly
                          name="dstaxprint"
                          type="checkbox"
                        />
                      </div> -->
                      <!-- <div class="col-md-6 form-group mb-6">
                        <label for="firstName1">Full Narration : </label>
                        <input
                          id="full_narration" readonly
                          name="full_narration"
                          type="checkbox"
                        />
                      </div> -->

                      <div class="col-md-12">
                        <button class="btn btn-primary">Search</button>
                      </div>
                    </div>
                   </div>
                  </form>
                </div>
              </div>
              
          </div>
            <div class="breadcrumb">
                <h1>Student ledger :-</h1>
                <ul>
                <!-- <li><a href="href">Form</a></li> -->
                <!-- <li>Basic</li> -->
                </ul>
            </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  @php
                  $serial = 1;
                  use Carbon\Carbon;
                  @endphp
                  @php
                  $totalDr = 0;
				  $totalrefundable_cr=0;
                  $totalDrAfterDiscount = 0;
                  $totalDiscount = 0;
                  $totalCr = 0;
              @endphp                       
              {{-- Table --}}

              <div class="text-end mb-3">
                <button onclick="printLedgerTable()" class="btn btn-info mb-3">Print Ledger</button>
              </div>
              <div class="print-student-detail">
                <div class="print-info mb-3">
                  <p><strong>Student Name:</strong> {{ $student_data->student_name ?? '' }}</p>
                  <p><strong>Class:</strong> {{ $student_data->class_name ?? '' }}</p>
                  <p><strong>Scholar Number:</strong> {{ $student_data->scholar_no ?? '' }}</p>
                </div>

                <table class="table table-bordered">
                  <thead>
                      <tr>
                          <th>S. NO.</th>
                          <th>Entry Date</th>
                          <th class="no-print-col">Due Date</th>
                          <th>Amount Dr</th>
                          <th>Amount Cr</th>
                          <th>Refundable Dr</th>
                          <th>Refundable Cr</th>
                          <th>Remarks</th>
                          <th class="no-print-col">Action</th>
                      </tr>
                  </thead>
                  <tbody>
                  @isset($mergedRows)
                            @php
                             // echo "<pre>";print_r($mergedRows);echo "</pre>";
                            @endphp
                      @forelse($mergedRows as $row)
                        @php
                          
                            $formattedDate = Carbon::parse($row['entry_date']);
                            $entryDate = $formattedDate->format('d-m-Y');

                            $due_date = "-";
                            if($row['due_date']!="-"){
                            $due_dateDate = Carbon::parse($row['due_date']);
                            $due_date = $due_dateDate->format('d-m-Y');
                            }
                        @endphp
                        @if($formattedDate->lte(Carbon::now()))
                          @php
                              // school dont wwant to display discount and other details hack to fix it 
                              if(!empty($row['amount_dr_discount']) && $row['amount_dr_discount'] > 0){
                                $row['amount_dr'] = $row['amount_dr_after_discount'];
                                $row['amount_dr_after_discount'] =  0;
                                $row['amount_dr_discount'] = 0;
                              }
                              $dr = floatval($row['amount_dr'] ?? 0);
                              $drAfterDiscount = floatval($row['amount_dr_after_discount'] ?? $dr);
                              $discount = floatval($row['amount_dr_discount'] ?? 0);
                              $cr = floatval($row['amount_cr'] ?? 0);
                              $refundable_cr = floatval($row['refundable_cr'] ?? 0);
                              $totalDr += $dr;
                              $totalDrAfterDiscount += $drAfterDiscount;
                              $totalDiscount += $discount;
                              $totalCr += $cr;
                              if($cr ==0 && $refundable_cr == 3000){
                              $totalrefundable_cr=  $refundable_cr;
                              }else{
                                $totalCr += $refundable_cr;
                              }
                          @endphp
                          <tr>
                              <td>{{ $serial++ }}</td>
                              <td>{{ $entryDate }}</td>
                              <td class="no-print-col">{{ $due_date }}</td>
                              <td>
                                  @if(!empty($row['amount_dr_discount']) && $row['amount_dr_discount'] > 0)
                                      <span style="text-decoration: line-through; color: red;">
                                          {{ number_format($row['amount_dr'], 2) }}
                                      </span>
                                      <br>
                                      <span style="color: green;">
                                          {{ number_format($row['amount_dr_after_discount'], 2) }}
                                      </span>
                                  @else
                                      {{ $row['amount_dr'] !== '' ? number_format($row['amount_dr'], 2) : '' }}
                                  @endif
                              </td>
                              <td>{{ $row['amount_cr'] !== '' ? number_format($row['amount_cr'], 2) : '' }}</td>
                              <td></td>
                              <td>{{ isset($row['refundable_cr']) && $row['refundable_cr'] !== '' ? number_format($row['refundable_cr'], 2) : '' }}</td>
                              <td style="font-size:10px;max-width:200px;" >{{ $row['remarks'] }}</td>
                              <td class="no-print-col">
                                @php  
                                if(!empty($student_data) && $row['amount_cr']!=='' && !str_contains($row['remarks'], 'Opening Balance')){
                                          echo ' 
                                          <form method="post" action="student-ledger-receipt-challan">
                                            <input type = "hidden" name = "_token" value = "'.csrf_token().'">
                                            <input type="hidden" name="student_id" value="'.$student_data->id.'">
                                            <input type="hidden" name="class_name" value="'.$student_data->class_name.'">
                                            <input type="hidden" name="due_upto" value="'.$row['due_date'].'">
                                            <button type="submit" class="btn btn-primary btn-sm removeItem">View</button>
                                          </form>
                                          ';
                                        }
                                @endphp      
                              </td>
                          </tr>
                        @endif
                      @empty
                          <tr><td colspan="9">No fees data available.</td></tr>
                      @endforelse
                      <tr style="font-weight: bold; background-color: #f0f0f0;">
                          <td colspan="3" class="text-end" style="text-align:left !important;">Total</td>
                          <td>
                              {{ number_format($totalDr, 2) }}
                              @if($totalDiscount > 0)
                                  <br>
                                  <span style="color: green;">After Discount: {{ number_format($totalDrAfterDiscount, 2) }}</span>
                              @endif
                          </td>
                          <td>{{ number_format($totalCr, 2) }}</td>
						  <td></td>
						  <td>{{ $totalrefundable_cr }}</td>
                          <td colspan="1">
                              @if($totalDiscount > 0)
                                  <span style="color: orange;">Discount Applied: {{ number_format($totalDiscount, 2) }}</span>
                              @endif
                          </td>
                          <td colspan="2" class="no-print-col"></td>
                      </tr>

                      <tr style="font-weight: bold; background-color: #e0f7fa;">
                          <td colspan="4" class="text-end" style="text-align:left !important;">Balance</td>
                          <td colspan="6">
                              {{ number_format(abs($totalDr - $totalCr), 2) }}
                          </td>
                      </tr>
                  <!--<tr style="font-weight: bold; background-color: #e0f7fa;">
                          <td colspan="3" class="text-end">Print Tuition Certificate</td>
                          <td colspan="6">
                              @if(isset($mergedRows) && count($mergedRows))
                      <form method="POST" action="{{ route('print.certificate') }}" target="_blank">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student_data->id ?? '' }}">
                        <input type="hidden" name="merged_data" value="{{ base64_encode(serialize($mergedRows)) }}">
                        <button type="submit" class="btn btn-success">Print Certificate</button>
                      </form>
                        @endif
                    </td>
                      </tr>--!>
                  <tr style="font-weight: bold; background-color: #e0f7fa;">
                    <td colspan="3" class="text-end no-print-col" style="text-align:left !important;">Print Tuition Certificate</td>
                    <td colspan="6">
                    @php
                      $jsonData = json_decode($student_data->json_str, true);
                    @endphp
                      @if(isset($mergedRows) && count($mergedRows))
                      <form method="POST" action="{{ route('generate.tuition.certificate') }}" target="_blank">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student_data->id ?? '' }}">
                        <!-- Use values from JSON -->
                        <input type="hidden" name="scholar_no" value="{{ $jsonData['scholar_no'] ?? '' }}">
                        <input type="hidden" name="studentname" value="{{ $jsonData['studentname'] ?? '' }}">
                        <input type="hidden" name="fathername" value="{{ $jsonData['fathername'] ?? '' }}">
                        <input type="hidden" name="student_dob" value="{{ $jsonData['student_dob'] ?? '' }}">
                        <input type="hidden" name="classname" value="{{ $jsonData['classname'] ?? '' }}">
                        <input type="hidden" name="section_name" value="{{ $jsonData['section_name'] ?? '' }}">
                        <input type="hidden" name="batch" value="{{ $jsonData['batch'] ?? '' }}">
                        <input type="hidden" name="total_credited" value="{{ $total_tuition_fees ?? '' }}">
                        <input type="hidden" name="merged_data" value="{{ base64_encode(serialize($mergedRows)) }}">
                        <input type="hidden" name="present_address" value="{{ $jsonData['present_address'] ?? '' }}">
                        <input type="hidden" name="permanent_address" value="{{ $jsonData['permanent_address'] ?? '' }}">
                        <input type="hidden" name="gender" value="{{ $jsonData['gender'] ?? '' }}">
                        <button type="submit" class="btn btn-success no-print-col">Print Certificate</button>
                      </form>
                      @endif
                    </td>
                  </tr>


                    @else
                      <tr><td colspan="9">No data provided.</td></tr>
                    @endisset
                  </tbody>
              </table>
            </div>
						
                  <!-- <h4 class="card-title mb-3 text-end"><a href="{{url('add-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                  <div class="table-responsive">
                    <!-- <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>S. NO.</th>
                            <th>Due Date</th>
                            <th>Entry Date</th>
                            <th>Ammount Dr</th>
                            <th>Ammount Cr</th>
                            <th>Refundable Dr</th>
                            <th>Refundable Cr</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $total_dueamount_ = 0; $by_cash_ = 0;?>
                        <?php
                          $totalnextyear1 = 0;
                          $refundable = 0;
                        ?>
                        <?php if(!empty($totalnextyear)) {
                          // print_r($totalnextyear);
                          $i = 1;
                          
                          $previousFeesDate = null; // Initialize variables to track previous values
                          $previousCreatedAt = null;
                          $previousTotalNextYear = null;
                          $mergedAccountNames = []; // Initialize an array to store merged account_names
                          
                          foreach ($totalnextyear as $next) {
                              echo '<tr>';
                              // print_r($next);
                              if($next->account_name == 'CUATION MONEY'){
                                $totalnextyear1 = $next->totalnextyear - $next->fees;
                                $refundable = $next->fees;
                              }
                              // Check if the current fees_date, created_at, and totalnextyear are different from the previous ones
                              if ($next->fees_date != $previousFeesDate || $next->created_at != $previousCreatedAt || $next->totalnextyear != $previousTotalNextYear) {
                                  
                                  $id_n = $i;
                                  $fees_date = $next->fees_date;
                                  $created_at = $next->created_at;
                                  
                                  
                                  // echo '<td>0</td>';
                                  $previousFeesDate = $next->fees_date; // Update previous values
                                  $previousCreatedAt = $next->created_at;
                                  $previousTotalNextYear = $next->totalnextyear;
                          
                                  // Display the merged account_names for the same group
                                  if (!empty($mergedAccountNames)) {
                                      // echo '<td>' . implode(', ', $mergedAccountNames) . '</td>';
                                  } else {
                                      // echo '<td></td>';
                                  }
                          
                                  // Clear the mergedAccountNames array for the next group
                                  $mergedAccountNames = [$next->account_name];
                              } else {
                                  // Append account_name to the mergedAccountNames for the same group
                                  $mergedAccountNames[] = $next->account_name;
                              }
                              $i++;
                          }
                          
                          // Display the last row outside the loop
                          if (!empty($mergedAccountNames)) {
                              echo '<td>' . $id_n . '</td>';
                              echo '<td>' . $fees_date . '</td>';
                              echo '<td>' . $created_at . '</td>';
                              echo '<td>0</td>';
                              echo '<td>' . $totalnextyear1 . '</td>';
                              echo '<td>0</td>';
                              echo '<td>'. $refundable .'</td>';
                              echo '<td> Academic Fee / Receipt No : ' . $next->receipt_number. ' / Received Type : '. $next->received_type . ' / Reference Number : ' . $next->reference_number . '</td>'; //implode(', ', $mergedAccountNames)
                              echo '<td> 
                                <a href="perstudueamount/'.$next->scholar_no.'">
                                <button type="submit" class="btn btn-primary btn-sm removeItem">View</button>
                              </a>
                              </td>';
                          } else {
                              echo '<td></td>';
                              echo '<td></td>';
                              echo '<td></td>';
                              echo '<td></td>';
                              echo '<td></td>';
                              echo '<td></td>';
                              echo '<td></td>';

                          }
                          echo '</tr>';
                          
                            
                        } ?>

                        @if(!empty($challan_data))

                        @php $json_data = json_decode($challan_data); @endphp

                        <?php //$firstRow = true; //echo '<pre>'; 
                        // foreach($json_data as $obj){
                        //   if ($firstRow) {
                        //     $firstRow = false; // Set the flag to false after processing the first row
                        //     continue; // Skip the first row
                        //   }
                    
                        //   print_r($obj);
                        // }  
                        

                        $sno = 1;

                        // Loop through the objects and add rows
                        $firstRow = true;
                        foreach ($challan_data as $object) {
                         // if ($firstRow) {
                           // $firstRow = false; // Set the flag to false after processing the first row
                          //  continue; // Skip the first row
                        //  }
                          $data = json_decode($object->str_json, true);
                          // print_r($data);
                          // // Extract relevant data
                          $head_name = explode(',', $data['head_name']);
                          $head_due_amount = explode(',', $data['head_due_amount']);
                          $head_rec_ammount1 = explode(',', $data['head_rec_ammount']);
                          $rec_ammount = array_sum($head_rec_ammount1);
                          $by_cash = $data['by_cash'];
                          $total_dueamount = $data['grand_total_due']; // Extract the "total_dueamount" value
                          $payment_by_select = $data['payment_by_select'];

                          // // Create an array to hold remarks for each fee item
                          $remarks = [];
                          for ($i = 0; $i < count($head_name); $i++) {
                              $remarks[] = $head_name[$i] . ' ( ' . $head_due_amount[$i] . ' )';
                          }

                          // // Add a row for 'by_cash' and 'total_dueamount' and display remarks and head_name
                          echo '<tr>';
                          echo "<td>$sno</td>";
                          echo '<td>' . $object->due_upto . '</td>';
                          echo '<td>' . $data['payment_date']. '</td>';
                          echo "<td>$by_cash</td>";
                          echo '<td>0</td>';
                          echo '<td>' . $payment_by_select . '</td>'; // Display payment_by_select as remarks
                          echo '<td> 0 </td>';
                          echo '<td> 0 </td>';
                          echo '<td>  </td>';
                          // echo '<td> 
                          // <form method="post" action="{{url(fees-types-master-delete)}}">
                          //   <!-- @csrf -->
                          //   <input type="hidden" name="table_name" value="fees_types_master">
                          //   <input type="hidden" name="delete_id" value="{{ $fees_type->id }}">
                          //   <button type="submit" class="btn btn-primary btn-sm removeItem">View</button>
                          //   </form>
                          // </td>';
                          echo '</tr>';

                          $by_cash_ += $by_cash;
                          $sno++;

                          echo '<tr>';
                          echo "<td>$sno</td>";
                          echo '<td>' . $object->due_upto . '</td>';
                          echo '<td>' . $data['payment_date'] . '</td>';
                          echo '<td>0</td>';
                          echo '<td>';if(!empty($rec_ammount)){ echo $rec_ammount;}else{ echo $total_dueamount;}'</td>'; // Display the "total_dueamount" value
                          echo '<td>0</td>';
                          echo '<td> 0 </td>';
                          echo '<td>' . implode(' / ', $remarks) . ' etc </td>'; // Display all head_name values as remarks
                          if(!empty($student_data)){
                            echo '<td> 
                            <form method="post" action="student-ledger-receipt-challan">
                              <input type = "hidden" name = "_token" value = "'.csrf_token().'">
                              <input type="hidden" name="student_id" value="'.$student_data->id.'">
                              <input type="hidden" name="class_name" value="'.$student_data->class_name.'">
                              <input type="hidden" name="due_upto" value="'.$object->due_upto.'">
                              <button type="submit" class="btn btn-primary btn-sm removeItem">View</button>
                            </form>
                            </td>';
                              // echo '<option selected value="'.$name->id.'" data-tokens="'.$name->student_name.'">'.$name->student_name.'</option>';
                          }
                          
                          
                          echo '</tr>';
                          if(!empty($rec_ammount)){ 
                             $total_dueamount_ += $rec_ammount;
                           }else{
                            $total_dueamount_ += $total_dueamount;
                          }
                          $sno++;

                      }
                        
                        
                        
                        ?>
                       
                       
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?php echo $by_cash_; ?></th>
                            <th><?php echo $total_dueamount_ + $totalnextyear1; ?></th>
                            <th>0</th>
                            <th><?php echo $refundable;?></th>
                            <th></th>
                            <th></th>
                        </tr>
                      </tfoot>
                    </table> -->
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    <!-- end of main-content -->
    <div class="flex-grow-1"></div>

</div>

<script>
$( document ).ready(function() {
  $("#search_student").trigger("change");
  setTimeout(function() {
      $("input[type='search']").attr("placeholder", "Search here by student details");
  }, 100);
});
function select_data(ele){
    student_id = ele.value;
    $('.student_id').val(student_id);
    console.log(student_id);
    $.ajax({
        type : "POST",
        data : {student_id:student_id},
        url : "{{ url('get_student_info') }}",
        headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        dataType : 'json',
        success: function(data){

          console.log(data);

          $("input[name='student_name']").val(data.student_name);
          $("input[name='class']").val(data.class_name);
          $("input[name='enrollment_no']").val(data.formno);
          $("input[name='father_name']").val(data.fathername);
          $("input[name='scholar_number']").val(data.scholar_no);
          // $("input[name='feecommentment_remark']").val(data.fee_commitment_remarks);
        }
    });
}

</script>
<script>
function printLedgerTable() {
    // Clone the table and wrap it in a printable layout
    const table = document.querySelector('.print-student-detail');
    if (!table) {
        alert('Ledger table not found.');
        return;
    }

    const newWin = window.open('', '', 'width=900,height=700');
    newWin.document.write(`
        <html>
            <head>
                <title>Print Ledger</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    table, th, td { border: 1px solid black; }
                    th, td { padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
					@media print {
						.no-print-col,
						.no-print-col * {
							display: none !important;
						}
					}
					
                </style>
            </head>
            <body>
                <h2>Student Ledger</h2>
                ${table.outerHTML}
            </body>
        </html>
    `);
    newWin.document.close();
    newWin.focus();
    newWin.print();
    // Optionally, close the print window after printing
    // newWin.close();
}
</script>


@endsection