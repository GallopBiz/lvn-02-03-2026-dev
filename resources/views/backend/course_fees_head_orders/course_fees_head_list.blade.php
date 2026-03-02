@extends('backend.layouts.main')
@section('main-container')

<style>
   .uperletter {
       text-transform: capitalize;
   }
</style>
 <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->
    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css"/> -->
    <!-- <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"/> -->

<div class="main-content">
   <!-- ============ Body content start ============= -->
   <div class="main-content">
      <div class="breadcrumb" style="background-color:white;">
         <h1>Course Fees Head Orders Master</h1>
         <ul>
            <!-- <li><a href="#">Form</a></li> -->
            <!-- <li>Basic</li> -->
         </ul>
      </div>
      <div class="separator-breadcrumb border-top"></div>
      <div class="row">
         <div class="col-md-12">
            <div class="card mb-4">
               <div class="card-body">
                  <!-- <div class="card-title mb-3">Form Inputs</div> -->
                   <!-- <form> -->
                  @if(!empty($course_fees_head_orders_list_edit))
                  <form id="progress-form" class="p-4 progress-form" action="{{url('save-course-fees-head-orders')}}"  novalidate method="post">
                     <input type="hidden" @if(!empty($role)) @foreach($role as $roles) value=" {{ $roles->id }}" @endforeach @else value="" @endif name="id" >
                     @else
                  <form id="progress-form" class="p-4 progress-form" action="{{url('save-course-fees-head-orders')}}"  novalidate method="post">
                     @endif
                     {{ csrf_field() }}
                     <div class="row">
                        <div class="col-md-3 form-group mb-3">
                           <label for="firstName1">A/C Head Name :</label>
                           <input class="form-control uperletter" id="ac_head_name" name="ac_head_name" type="text" placeholder="Fess Type Name"/>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                           <label for="lastName1">Remarks : </label>
                           <input class="form-control uperletter" id="remarks" name="remarks" type="text" placeholder="remark"/>
                        </div>
                     </div>
                     <div class="col-md-12">
                        <button class="btn btn-primary">Save</button>
                     </div>
               </div>
               </form>
            </div>
         </div>
         <div class="breadcrumb" style="background-color:white;">
            <h1 class="me-2">List of Course Fees Head Orders Master</h1>
         </div>
         <div class="separator-breadcrumb border-top"></div>
         <div class="row">
            <div class="col-md-12 mb-4">
               <div class="card text-start">
                  <div class="card-body">
                     <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                           <thead>
                              <tr>
                                <th>#</th>
                                <th>SNO</th>
                                <th>A/C Head Name</th>
                                <th>Remarks</th>
                                <th>Action</th>
                              </tr>
                           </thead>
                           <tbody id="tablecontents">
                              @if(!empty(sizeof($course_fees_head_orders_list_arr)))
                              @foreach ($course_fees_head_orders_list_arr as $each_row)
                              <tr class="row1" data-id="{{ $each_row->id }}">
                                  <td class="pl-3"><i class="fa fa-sort"></i></td>
                                 <td>{{$loop->iteration}}</td>
                                 <td>{{ $each_row->ac_head_name }}</td>
                                 <td>{{ $each_row->remarks }}</td>
                                 <td> 
                                 <?php $a = "course_fees_head_master"."-".$each_row->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('course_fees_head_delete').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                                 </td>
                              </tr>
                              @endforeach
                              @else
                              <tr>
                                 <td colspan="9" class="text-center">No Data Found</td>
                              </tr>
                              @endif
                           </tbody>
                           <tfoot>
                              <tr>
                                 <th>#</th>
                                 <th>SNO</th>
                                 <th>Fees Type Title</th>
                                 <th>Remarks</th>
                                 <th>Action</th>
                              </tr>
                           </tfoot>
                        </table>
                        <button id="submitButton" class="btn btn-success">Submit Changes</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- end of main-content -->
   <!-- Footer Start -->
   <div class="flex-grow-1"></div>
   <!-- fotter end -->
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" defer></script>
  <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.js"></script>

   <script type="text/javascript">
      $(function () {
        $("#table").DataTable();

        $( "#tablecontents" ).sortable({
          items: "tr",
          cursor: 'move',
          opacity: 0.6,
          update: function() {
            //   sendOrderToServer();
            orderChanged = true; 
          }
        });

         $("#submitButton").on('click', function() {
            
            sendOrderToServer();
         });

        function sendOrderToServer() {
         // if (orderChanged) {
            // swal({
            //   title: 'Are you sure?',
            //   text: "It will moved from position !",
            //   type: 'warning',
            //   showCancelButton: true,
            //   confirmButtonColor: 'success',
            //   cancelButtonColor: '#d33',
            //   confirmButtonText: 'Yes, MOVE it!'
            // }).then(function() {
               
                var order = [];
               
                $('tr.row1').each(function(index,element) {
                  order.push({
                    id: $(this).attr('data-id'),
                    position: index+1
                  });
                });

            //     $.ajax({
            //       type: "POST", 
            //       dataType: "json", 
            //       url: "{{ url('course-fees-head-orders-sortable') }}",
            //       data: {
            //         order: order,
            //         _token: "{{ csrf_token() }}",
            //       },
            //       success: function(response) {
            //           console.log(response);
            //       }
            //     });

            //       swal(
            //          'Moved position!',
            //          'position moved successfully.',
            //          'success'
            //       );
         
            // })

            // console.log('Sending order to server...');
            // orderChanged = false;
            $.ajax({
                  type: "POST", 
                  dataType: "json", 
                  url: "{{ url('course-fees-head-orders-sortable') }}",
                  data: {
                    order: order,
                    _token: "{{ csrf_token() }}",
                  },
                  success: function(response) {
                      console.log(response);
                  }
                });
         // } else {
            // console.log('No changes to send.');
         // }
         alert("Send Success Fully");
        }
      });

    </script>

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
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
   <script>
      $("#delete").on('click', function() {
         sendOrderToServer();
      });

      function confirmDelete(event) {
        event.preventDefault(); // Prevents the default form submission
         console.log("hello")
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
                // If the user clicks on "Yes, delete it!", manually submit the form
                document.getElementById('deleteForm').submit();
            }
        });
    }
      </script> -->
</div>
@endsection
