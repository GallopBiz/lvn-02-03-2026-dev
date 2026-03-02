@extends('backend.layouts.main')
@section('main-container')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="main-content">
   <!-- ============ Body content start ============= -->
   <div class="main-content">
      <!-- <div class="separator-breadcrumb border-top"></div> -->
      <div class="row">
         <div class="breadcrumb">
            <h1 class="me-2">Edit of Course Fees Structure Master</h1>
         </div>
         <div class="separator-breadcrumb border-top"></div>
         <form id="progress-form" class="p-4 progress-form" action="{{ url('edit-course-fees-structure-master') }}" novalidate
                        method="post">
         @csrf
         <div class="row">
            <input type="hidden" name="stid" value="{{$Vehicallist[0]->id}}">
               <div class="col-md-3 form-group mb-3">
                  <label for="lastName1">Class Name</label>
                  <select id="Batch" class="form-control" name="class_name" autocomplete="shipping address-level1" required>
                     @if(!empty($Vehicallist))
                     <option value="{{$Vehicallist[0]->class_name}}">{{$Vehicallist[0]->class_name}}</option>
                     <option value="" disabled >Please select</option>

                     @foreach($class_names as $class_name):
                           <option {{ (!empty(session('sl_classname')) && (session('sl_classname')==$class_name->class_name )) ? 'selected' : '' }} value="{{ $class_name->class_name }}">{{ $class_name->class_name }}</option>
                        @endforeach
                     @else
                  <option disabled selected>Please select</option>
                     @(!empty($class_names)):
                        @foreach($class_names as $class_name):
                           <option {{ (!empty(session('sl_classname')) && (session('sl_classname')==$class_name->class_name )) ? 'selected' : '' }} value="{{ $class_name->class_name }}">{{ $class_name->class_name }}</option>
                        @endforeach
                     @ifend
                     @endif
                  </select>
               </div>
               <div class="col-md-3 form-group mb-3">
                  <label for="lastName1">Session Name</label>
                  <select id="session_name" class="form-control" name="session_name" autocomplete="shipping address-level1" required>
                  @if(!empty($Vehicallist))
                     <option value="{{$Vehicallist[0]->session_name}}">{{$Vehicallist[0]->session_name}}</option>
                     <option value="" disabled >Please select</option>

                     @foreach(config('global.session_name') as $each)
                     <option {{ (!empty(session('sl_sessionname')) && (session('sl_sessionname')==$each )) ? 'selected' : '' }} value="{{ $each }}">{{ $each }}</option>
                     @endforeach
                     @else
                     <option value="" disabled selected>Please select</option>
                     @foreach(config('global.session_name') as $each)
                     <option {{ (!empty(session('sl_sessionname')) && (session('sl_sessionname')==$each )) ? 'selected' : '' }} value="{{ $each }}">{{ $each }}</option>
                     @endforeach
                     @endif
                  </select>
               </div>
               <div class="col-md-3 form-group mb-3">
                  <label for="lastName1">Fees Type Name</label>
                  <select id="Fees_Type_Name" class="form-control" name="fees_type_name" autocomplete="shipping address-level1" required>
                     @if(!empty($Vehicallist))
                        @if(!empty($fees_types))
                           @foreach($fees_types as $fees_type)
                              @if($Vehicallist[0]->fees_type_name == $fees_type->fees_type)
                                 <option selected value="{{ $fees_type->fees_type }}">{{ $fees_type->fees_type }}</option>
                              @else 
                                 <option value="{{ $fees_type->fees_type }}">{{ $fees_type->fees_type }}</option>
                              @endif
                           @endforeach
                        @else
                              <option value="" selected>Please select</option>
                              @foreach($fees_types as $fees_type)
                                 <option {{ (!empty(session('sl_feetypename')) && (session('sl_feetypename')==$fees_type->fees_type )) ? 'selected' : '' }} value="{{ $fees_type->fees_type }}">{{ $fees_type->fees_type }}</option>
                              @endforeach
                        @endif
                     @endif
                  </select>
               </div>
               <!-- <div class="col-md-3 form-group mb-3"> -->
                  <!-- <label for="lastName1">Batch </label><br> -->
                  <!-- <select id="batch" class="form-control" name="batch" autocomplete="shipping address-level1"
                                 required>
                                 @if(!empty($Vehicallist))
                                 <option value="{{$Vehicallist[0]->batch}}">{{$Vehicallist[0]->batch}}</option>
                                 <option value="" disabled >Please select</option>
                                 <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='same_for_all' )) ? 'selected' : '' }} value="same_for_all">Same for all</option>
                                 <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='Opt1' )) ? 'selected' : '' }} value="Opt1">Opt1</option>
                                 <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='Opt2' )) ? 'selected' : '' }} value="Opt2">Opt2</option>
                                 <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='Opt3' )) ? 'selected' : '' }} value="Opt3">Opt3</option>
                                 @else
                                 <option value="" disabled selected>Please select</option>
                                 <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='same_for_all' )) ? 'selected' : '' }} value="same_for_all">Same for all</option>
                                 <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='Opt1' )) ? 'selected' : '' }} value="Opt1">Opt1</option>
                                 <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='Opt2' )) ? 'selected' : '' }} value="Opt2">Opt2</option>
                                 <option {{ (!empty(session('sl_batch')) && (session('sl_batch')=='Opt3' )) ? 'selected' : '' }} value="Opt3">Opt3</option>
                                 @endif
                              </select> -->
               <!-- </div> -->
         </div>
         <div class="col-md-12">
            <button type="button" id="addRowButton" class="btn btn-success">Add Row</button>
         </div>
         <br>
         <div id="data-container"></div>
         <div class = "col-md-3">
            <input type="text" id="totalFees" class="form-control" readonly>
         </div>
         <br>

         <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                </div>
         </form>
         
      </div>
   </div>
   <!-- end of main-content -->
   <!-- Footer Start -->
   <div class="flex-grow-1"></div>
   <!-- fotter end -->
</div>

<link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/sweetalert2.min.css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="{{url('assets/backend')}}/js/plugins/sweetalert2.min.js"></script>
<script src="{{url('assets/backend')}}/js/scripts/sweetalert.script.min.js"></script>
<!-- Add jQuery library to your HTML if it's not already included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Sample JSON data (replace this with your actual JSON data)
    var jsonData = {!! json_encode(json_decode($Vehicallist[0]->json_str)) !!};
    console.log(jsonData);

    // Function to dynamically generate the HTML table
    function generateTable() {
        var dataContainer = document.getElementById('data-container');
        dataContainer.innerHTML = ''; // Clear existing content

        var table = document.createElement('table');
        table.className = 'table table-bordered'; // Add Bootstrap classes for styling

        // Create table header
        var thead = document.createElement('thead');
        var headerRow = document.createElement('tr');

        var headers = ['S.No.', 'Fees Date', 'Acc. Name', 'Fees', 'Due Date', 'Term', 'Clear'];
        headers.forEach(function (headerText) {
            var th = document.createElement('th');
            th.textContent = headerText;
            headerRow.appendChild(th);
        });

        thead.appendChild(headerRow);
        table.appendChild(thead);

        // Create table body
        var tbody = document.createElement('tbody');

        for (var i = 0; i < jsonData.fees_date.length; i++) {
            var obj = {
                fees_date: jsonData.fees_date[i],
                account_name: jsonData.account_name[i],
                fees: jsonData.fees[i],
                due_date: jsonData.due_date[i],
                term: jsonData.term[i],
            };

            var row = document.createElement('tr');

            // S.No.
            var tdSNo = document.createElement('td');
            tdSNo.textContent = i + 1; // Increment S.No. starting from 1
            row.appendChild(tdSNo);

            // Fees Date
            // var tdFeesDate = document.createElement('td');
            // var inputFeesDate = document.createElement('input');
            // inputFeesDate.type = 'date';
            // inputFeesDate.name = 'fees_date[]';
            // inputFeesDate.value = obj.fees_date;
            // inputFeesDate.className = 'form-control';
            // tdFeesDate.appendChild(inputFeesDate);
            // row.appendChild(tdFeesDate);
            var tdDueDate = document.createElement('td');
            var inputDueDate = document.createElement('input');
            inputDueDate.type = 'date';
            inputDueDate.name = 'fees_date[]';

            // Assuming obj.fees_date is in yyyy-mm-dd format
            var dateParts = obj.fees_date.split('-');
            if (dateParts.length === 3) {
               var formattedDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
               inputDueDate.value = formattedDate;
            } else {
               inputDueDate.value = obj.fees_date; // Fallback if the date format is invalid
            }

            inputDueDate.className = 'form-control';
            tdDueDate.appendChild(inputDueDate);
            row.appendChild(tdDueDate);


            // Account Name
            var tdAccountName = document.createElement('td');
            var selectAccountName = document.createElement('select');
            selectAccountName.className = 'form-control';
            selectAccountName.name = 'account_name[]';

            // Add a "Please Select" option
            var pleaseSelectOption = document.createElement('option');
            pleaseSelectOption.value = ''; // You can set the value to an empty string or any other value you prefer
            pleaseSelectOption.textContent = 'Please Select';
            selectAccountName.appendChild(pleaseSelectOption);

            // Assuming you have PHP variable $course_fees_head_master available in your JavaScript code
            @if(!empty($course_fees_head_master))
                @foreach($course_fees_head_master as $each)
                    var option = document.createElement('option');
                    option.value = '{{$each->ac_head_name}}';
                    option.textContent = '{{$each->ac_head_name}}';
                    selectAccountName.appendChild(option);
                @endforeach
            @endif

            // Set the selected option based on obj.account_name
            selectAccountName.value = obj.account_name;
            tdAccountName.appendChild(selectAccountName);
            row.appendChild(tdAccountName);

            // Fees
            var tdFees = document.createElement('td');
            var inputFees = document.createElement('input');
            inputFees.type = 'text';
            inputFees.name = 'fees[]';
            inputFees.value = obj.fees;
            // Add onchange event handler to inputFees
            inputFees.onchange = function () {
               updateTotal(); // Call the function to update the total when fees change
            };
            inputFees.className = 'form-control orderFees_main';
            tdFees.appendChild(inputFees);
            row.appendChild(tdFees);

            // Due Date
            // var tdDueDate = document.createElement('td');
            // var inputDueDate = document.createElement('input');
            // inputDueDate.type = 'date';
            // inputDueDate.name = 'due_date[]';
            // inputDueDate.value = obj.due_date;
            // inputDueDate.className = 'form-control';
            // tdDueDate.appendChild(inputDueDate);
            // row.appendChild(tdDueDate);
            var tdDueDate = document.createElement('td');
            var inputDueDate = document.createElement('input');
            inputDueDate.type = 'date';
            inputDueDate.name = 'due_date[]';

            // Assuming obj.fees_date is in yyyy-mm-dd format
            var dateParts = obj.due_date.split('-');
            if (dateParts.length === 3) {
               var formattedDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
               inputDueDate.value = formattedDate;
            } else {
               inputDueDate.value = obj.fees_date; // Fallback if the date format is invalid
            }

            inputDueDate.className = 'form-control';
            tdDueDate.appendChild(inputDueDate);
            row.appendChild(tdDueDate);


            // Term
            var tdTerm = document.createElement('td');
            var selectTerm = document.createElement('select');
            selectTerm.className = 'form-control';
            selectTerm.name = 'term[]';

            // Assuming you have PHP variable $terms available in your JavaScript code
            @if(!empty($terms))
               @foreach($terms as $term)
                  var option = document.createElement('option');
                  option.value = '{{$term->terms}}';
                  option.textContent = '{{$term->terms}}';

                  // Check if this term matches the one from jsonData and set as selected
                  if ('{{$term->terms}}' === obj.term) {
                        option.selected = true;
                  }

                  selectTerm.appendChild(option);
               @endforeach
            @endif

            tdTerm.appendChild(selectTerm);
            row.appendChild(tdTerm);


            // Clear Button
            var tdClear = document.createElement('td');
            var clearButton = document.createElement('button');
            clearButton.textContent = 'Clear';
            clearButton.className = 'btn btn-danger';
 
            // Add type="button" to prevent form submission
            clearButton.type = 'button';

            // Use a function closure to capture the value of i
            (function (index) {
               clearButton.onclick = function () {
                     var sNo = index + 1; // Get the S.No.
                     // alert(sNo);
                     clearRowFields(sNo); // Pass S.No. to the function
                     return false;
               };
            })(i); // Pass the current value of i to the function closure


            tdClear.appendChild(clearButton);
            row.appendChild(tdClear);

            tbody.appendChild(row);
        }

        table.appendChild(tbody);
        dataContainer.appendChild(table);
    }

    // Function to clear fields of the current row
            
         // Function to clear fields of the current row
         function clearRowFields(sNo) {
            // alert(sNo);
            var fff = sNo + {!! json_encode($Vehicallist[0]->id) !!};
            var row = document.querySelector('tbody tr:nth-child(' + sNo + ')');
            //  alert(fff);
            var inputs = row.querySelectorAll('input, select');
            
            inputs.forEach(function (input) {
               input.value = '';
            });
            
            // Make an AJAX request to delete the row on the server
            $.ajax({
               method: 'POST',
               headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
               data:  { sNo: sNo, value: {!! json_encode($Vehicallist[0]->id) !!}, jsonData: JSON.stringify(jsonData) }, // Send jsonData as part of the request body
               url: "{{ url('delete-row') }}",
               dataType: 'json',
               success: function (response) {
                     // Handle the response from the server if needed
                     console.log('AJAX request successful:', response);
                     
                     // Assuming the response contains the updated JSON data,
                     // you can replace the current jsonData with the updated data
                     jsonData = response.updatedData;
                     location.reload();
                     // console.log(jsonData);
               },
               error: function (xhr, status, error) {
                     // Handle errors if the AJAX request fails
                     console.error('AJAX request error:', status, error);
               }
            });
         }

         function generateTermDropdown(selectElement, selectedTerm) {
    // Assuming you have PHP variable $terms available in your JavaScript code
    @if(!empty($terms))
        @foreach($terms as $term)
            var option = document.createElement('option');
            option.value = '{{$term->terms}}';
            option.textContent = '{{$term->terms}}';

            // Check if this term matches the selectedTerm and set as selected
            if ('{{$term->terms}}' === selectedTerm) {
                option.selected = true;
            }

            selectElement.appendChild(option);
        @endforeach
    @endif
}

       // Function to add a new row to the table
       function addRow() {
        var tbody = document.querySelector('tbody');
        var newRow = document.createElement('tr');

        // S.No.
        var tdSNo = document.createElement('td');
        tdSNo.textContent = tbody.children.length + 1; // Increment S.No. starting from the current row count + 1
        newRow.appendChild(tdSNo);

        // Fees Date
        var tdFeesDate = document.createElement('td');
        var inputFeesDate = document.createElement('input');
        inputFeesDate.type = 'date';
        inputFeesDate.name = 'fees_date[]';
        inputFeesDate.value = '';
        inputFeesDate.className = 'form-control';
        tdFeesDate.appendChild(inputFeesDate);
        newRow.appendChild(tdFeesDate);

        // Account Name
        var tdAccountName = document.createElement('td');
        var selectAccountName = document.createElement('select');
        selectAccountName.className = 'form-control';
        selectAccountName.name = 'account_name[]';

        // Add a "Please Select" option
        var pleaseSelectOption = document.createElement('option');
        pleaseSelectOption.value = ''; // You can set the value to an empty string or any other value you prefer
        pleaseSelectOption.textContent = 'Please Select';
        selectAccountName.appendChild(pleaseSelectOption);

        // Assuming you have PHP variable $course_fees_head_master available in your JavaScript code
        @if(!empty($course_fees_head_master))
            @foreach($course_fees_head_master as $each)
                var option = document.createElement('option');
                option.value = '{{$each->ac_head_name}}';
                option.textContent = '{{$each->ac_head_name}}';
                selectAccountName.appendChild(option);
            @endforeach
        @endif

        tdAccountName.appendChild(selectAccountName);
        newRow.appendChild(tdAccountName);

        // Fees
        var tdFees = document.createElement('td'); 
        var inputFees = document.createElement('input');
        inputFees.type = 'text';
        inputFees.name = 'fees[]';
        inputFees.value = '';
        // Add onchange event handler to inputFees
         inputFees.onchange = function () {
            updateTotal(); // Call the function to update the total when fees change
         };
        inputFees.className = 'form-control orderFees_main';
        tdFees.appendChild(inputFees);
        newRow.appendChild(tdFees);

        // Due Date
        var tdDueDate = document.createElement('td');
        var inputDueDate = document.createElement('input');
        inputDueDate.type = 'date';
        inputDueDate.name = 'due_date[]';
        inputDueDate.value = '';
        inputDueDate.className = 'form-control';
        tdDueDate.appendChild(inputDueDate);
        newRow.appendChild(tdDueDate);

 // Term
 var tdTerm = document.createElement('td');
    var selectTerm = document.createElement('select');
    selectTerm.className = 'form-control';
    selectTerm.name = 'term[]';

    // Call the generateTermDropdown function to populate options
    generateTermDropdown(selectTerm, ''); // Pass the selected term as an empty string for new rows

    tdTerm.appendChild(selectTerm);
    newRow.appendChild(tdTerm);

        // Clear Button
        var tdClear = document.createElement('td');
        var clearButton = document.createElement('button');
        clearButton.textContent = 'Clear';
        clearButton.className = 'btn btn-danger';
 
        // Add type="button" to prevent form submission
        clearButton.type = 'button';

        clearButton.onclick = function () {
        // Remove the new row from the table
        tbody.removeChild(newRow);
        return false;
         };

        tdClear.appendChild(clearButton);
        newRow.appendChild(tdClear);

        tbody.appendChild(newRow);
   //  console.log(inputFees);

    }
// Function to update the total fees
function updateTotal() {
    var totalFees = 0;
    var feeInputs = document.querySelectorAll('input[name="fees[]"]');

    feeInputs.forEach(function (input) {
        var fee = parseFloat(input.value) || 0;
        totalFees += fee;
    });

    var totalInput = document.getElementById('totalFees');
    totalInput.value = totalFees.toFixed(2);
}
// Call the function to generate the HTML table when the page loads
window.onload = function () {
    generateTable();
    // Initialize total fees on page load
    updateTotal();
    // Add a click event listener to the "Add Row" button
    var addRowButton = document.getElementById('addRowButton');
    addRowButton.addEventListener('click', function() {
        addRow();
        updateTotal(); // Update the total when a new row is added
    });
};
</script>


<script type="text/javascript">

   $('.removeItem').click(function (event) {
      
      event.preventDefault();

      var delete_id = $(this).data('delete_id');

      $(this).parents('tr').hide();
     swal({
       title: 'Are you sure?',
       text: "It will permanently deleted !",
       type: 'warning',
       showCancelButton: true,
       confirmButtonColor: 'success',
       cancelButtonColor: '#d33',
       confirmButtonText: 'Yes, delete it!'
     }).then(function() {
   
       var myUrl = "{{url('course-fees-structure-master-delete')}}";

       $.ajax({
           url: myUrl,
           type: "POST",
           data: { 
               "_token": "{{ csrf_token() }}",
               delete_id : delete_id
           },
           success: function (response) {

             swal(
               'Deleted!',
               'Your file has been deleted.',
               'success'
             );

           }
       });
      
     })
      
   });
   
</script>
@endsection
