@extends('backend.layouts.main')
@section('main-container')
<!-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" /> -->
<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css" rel="stylesheet" /> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css"/> 

<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content">
    <!-- ============ Body content start ============= -->
        <div class="main-content">
        <div class="breadcrumb">
            <h1>Student Ledger : </h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-body">
                          <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                {{-- <div class="form-outline w-auto p-4 progress-form">
                                    <label class="form-label" for="form1">Student Search</label>
                                    <select class="form-control" onchange="select_data(this);" name="search_student" id="search_student" data-live-search="true">
                                        <option data-tokens="china">Select The Student</option>
                                        </?php if(!empty($data_student_name)) {
                                            foreach($data_student_name as $name){
                                                if(!empty($student_data)){
                                                  if($student_data->id == $name->id){
                                                    echo '<option selected value="'.$name->id.'" data-tokens="'.$name->student_name.'">'.$name->student_name.'</option>';
                                                  }
                                                }
                                                echo '<option value="'.$name->id.'" data-tokens="'.$name->student_name.'">'.$name->student_name.'</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                               --}}

                               <div class="form-outline w-auto p-4 progress-form">
                                <label class="form-label" for="form1">Student Search</label>
                                <select class="form-control" onchange="select_data(this);" name="search_student" id="search_student" data-live-search="true">
                                    <option value="" disabled selected>Please Select</option>
                                    <option value="all" data-tokens="all">All</option>
                                    @if(!empty($data_student_name))
                                        @foreach($data_student_name as $name)
                                            <option value="{{ $name->id }}" data-tokens="{{ $name->student_name }}" {{ (!empty($student_data) && $student_data->id == $name->id) ? 'selected' : '' }}>
                                                {{ $name->student_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div<div class="form-outline w-auto p-4 progress-form">
                              <label class="form-label" for="form1">Student Search</label>
                              <select class="form-control" onchange="select_data(this);" name="search_student" id="search_student" data-live-search="true">
                                  <option value="" disabled selected>Please Select</option>
                                  <option value="all" data-tokens="all">All Students</option>
                                  @if(!empty($data_student_name))
                                      @foreach($data_student_name as $name)
                                          <option value="{{ $name->id }}" data-tokens="{{ $name->student_name }}" {{ (!empty($student_data) && $student_data->id == $name->id) ? 'selected' : '' }}>
                                              {{ $name->student_name }}
                                          </option>
                                      @endforeach
                                  @endif
                              </select>
                          </div>
                          
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                  <form id="progress-form" class="p-4 progress-form" action="{{url('search-cancle-student-ledger')}}"  novalidate method="post">
                                   {{ csrf_field() }}
                                   <input type="hidden" class="student_id" name="student_id" value="0">
                                        <input
                                              class="form-control"
                                              id="student_name"
                                              name="student_name"
                                              type="hidden"
                                              <?php
                                                if(!empty($student_data)){
                                                  echo 'value="'.$student_data->student_name.'"';
                                                } 
                                              ?>
                                              readonly placeholder="Student Name"
                                            />
                                            <input
                                              class="form-control"
                                              id="class" readonly
                                              name="class"
                                              <?php
                                                if(!empty($student_data)){
                                                  echo 'value="'.$student_data->class_name.'"';
                                                } 
                                              ?>
                                              type="hidden" placeholder="class"
                                            />
                                            <input
                                              class="form-control"
                                              id="scholar_number" readonly
                                              name="scholar_number"
                                              type="hidden" placeholder="Scholar Number"
                                            />
                                            <input
                                                class="form-control" readonly
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
                                                type="hidden" placeholder="Father's name"
                                              />
                                              <div class="form-outline w-auto p-4 progress-form">
                                              <button class="btn btn-primary">Search</button></div>
                                  </form>
                                </div>
                                                  </div>
                        </div>
                    </div>
                </div>
            </div>     
           <div class="row">
            <div class="col-md-12">
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
                  <!-- <h4 class="card-title mb-3 text-end"><a href="{{url('add-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>S. NO.</th>
                            <th>Due Date</th>
                            <th>Entry Date</th>
                            <th>Ammount Dr</th>
                            <th>Ammount Cr</th>
                            <th>Scholar No</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $total_dueamount_ = 0; $by_cash_ = 0;?>
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
                          if ($firstRow) {
                            $firstRow = false; // Set the flag to false after processing the first row
                            continue; // Skip the first row
                          }
                          $data = json_decode($object->str_json, true);
                          // print_r($object);
                          // // Extract relevant data
                          $head_name = explode(',', $data['head_name']);
                          $head_due_amount = explode(',', $data['head_due_amount']);
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
                          echo '<td>' . $object->created_at . '</td>';
                          echo '<td>'.$by_cash.'</td>';
                          echo "<td>$total_dueamount</td>"; // Display the "total_dueamount" value
                          echo '<td>' . $object->scholar_no . '</td>';
                          echo '<td>' . implode(' / ', $remarks) . ' etc </td>'; // Display all head_name values as remarks
                          if(!empty($student_data)){
                            $a = "feesreceiptchallan" . "-" . $object->id;
                            // print_r($a);exit;  
                            echo '<td>';
                            echo ' <form method="post" action="student-ledger-receipt-challan">
                            <input type = "hidden" name = "_token" value = "'.csrf_token().'">
                            <input type="hidden" name="student_id" value="'.$student_data->id.'">
                            <input type="hidden" name="class_name" value="'.$student_data->class_name.'">
                            <input type="hidden" name="due_upto" value="'.$object->due_upto.'">
                            <button type="submit" class="btn btn-primary btn-sm removeItem">View</button>
                            </form>';
                           
                            echo '<a class="btn btn-raised ripple btn-danger m-1" href="' . url('cancle_student_ledger_delete') . '/' . $a . '" onclick="confirmDelete(event)">Delete</a>';
                           
                            echo '</td>';
                          }
                          
                          echo '</tr>';
                          $by_cash_ += $by_cash;

                          $total_dueamount_ += $total_dueamount;
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
                            <th><?php echo $total_dueamount_; ?></th>
                            <th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    <!-- end of main-content -->
    <div class="flex-grow-1"></div>

</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.full.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
     function confirmDelete(event) {
        event.preventDefault(); // Prevents the default link navigation

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If the user clicks on "Yes, delete it!", navigate to the delete URL
                window.location.href = event.target.href;
            }
        });
    }
</script>
<script type="text/javascript">
   $.noConflict();
        jQuery(document).ready(function($){
  $("#search_student").select2();
}); </script>
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script> -->

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
          // $("input[name='feecommentment_remark']").val(data.fee_commitment_remarks);
        }
    });
}


</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      var select = document.getElementById('search_student');
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

@endsection