@extends('backend.layouts.main')
@section('main-container')

<style>

.uperletter{
  text-transform: capitalize;
} 

</style>


<div class="main-content">
   <!-- ============ Body content start ============= -->
   <div class="main-content">
      <div class="separator-breadcrumb border-top"></div>
      <div class="row">
         <div class="breadcrumb">
            <h1 class="me-2">List of Course Fees Structure Master</h1>
         </div>
         <div class="separator-breadcrumb border-top"></div>
         <div class="row">
            <div class="col-md-12 mb-4">
               <div class="card text-start">
                  <div class="card-body">
                  <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="class_name">
                      <input type="hidden" name="column_names[]" value="session_name">
                      <input type="hidden" name="column_names[]" value="fees_type_name">
                      <input type="hidden" name="column_names[]" value="cast_category">
                      <input type="hidden" name="column_names[]" value="batch">
                      <input type="hidden" name="table_name" value="course_fees_structure_master">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                     <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                           <thead>
                              <tr>
                                 <th>S.NO.</th>
                                 <th>Class Sem. Name</th>
                                 <th>Session Name</th>
                                 <th>Batch</th>
                                 <th>Fees Type Name</th>
                                 <th>Caste</th>
                                 <th>Action</th>
                              </tr>
                           </thead>
                           <tbody>
                              @if(!empty($cfs_list))
                              {{-- <pre>{{ print_r($cfs_list, true) }}</pre> --}}
                                 @foreach($cfs_list as $each)
                              <tr>
                                 <td>{{$loop->iteration}}</td>
                                 <td>{{$each->class_name}}</td>
                                 <td>{{$each->session_name}}</td>
                                 <td>{{$each->batch}}</td>
                                 <td class="uperletter">{{$each->fees_type_name}}</td>
                                 <td>{{$each->cast_category}}</td>
                                 <td>
                                    <!-- <a href="#" class="removeItem btn btn-danger btn-sm" data-delete_id='{{$each->id}}'>Delete</a> -->
                                    <a class="btn btn-raised ripple btn-primary m-1" href="{{ url('create-course-fees-structure-master-view') .'/'.$each->id}}">Edit</a>
                              <?php $a = "course_fees_structure_master"."-".$each->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('course-fees-structure-master-delete').'/'.$a}}" onclick="confirmDelete(event)">delete</a>
                                 </td>
                              </tr>
                                 @endforeach
                              @else
                                 <tr>
                                    <td colspan="7">No data found</td>
                                 </tr>
                              @endif
                           </tbody>
                           <tfoot>
                              <tr>
                                 <th>S.NO.</th>
                                 <th>Class Sem. Name</th>
                                 <th>Session Name</th>
                                 <th>Batch</th>
                                 <th>Fees Type Name</th>
                                 <th>Caste</th>
                                 <th>Action</th>
                              </tr>
                           </tfoot>
                        </table>
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
</div>

<!-- <link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/sweetalert2.min.css" /> -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<!-- <script src="{{url('assets/backend')}}/js/plugins/sweetalert2.min.js"></script> -->
<!-- <script src="{{url('assets/backend')}}/js/scripts/sweetalert.script.min.js"></script> -->
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