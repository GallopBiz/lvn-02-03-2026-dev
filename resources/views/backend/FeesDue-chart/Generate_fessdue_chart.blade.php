@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Generate Fees Due Chart

            </h1>
        </div>
        <form action=" {{url('save_due_chart')}} " method="post">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Class Name </label>
                    <select id="classname" class="form-control" name="classname" autocomplete="" required>
                              <option value="" disabled selected>Please select</option>
                              @foreach(config('global.class_name') as $each)
                              <option value="{{$each}}">{{$each}}</option>
                              @endforeach
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Section Name</label>
                    <select id="secationname" class="form-control" name="sectionname" autocomplete="" required>
                              <option value="" disabled selected>Please select</option>
                              <option value="A">A</option>
                              <option value="B">B</option>
                              <option value="C">C</option>
                           </select>
                </div>
               
                <div class="col-md-9 m-2">
                    <button class="btn btn-primary">@if(!empty($edit_data)) Update @else Submit @endif</button>
                   
                </div>
                 </form>
                 <div class="col-md-2 m-2">
                    <a href="" target="_blank" class="btn btn-primary">New</a>
                    <a href="" target="_blank" class="btn btn-primary">Exit</a>
                    <a href="" target="_blank" class="btn btn-primary">Help</a>
                </div>
            </div>
       
    @if(empty($edit_data))
        <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <!-- <h4 class="card-title mb-3 text-end"><a href="{{url('add-student-registrations')}}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                          <th>SNO</th>
                          <th>Class Name</th>
                          <th>Section Name</th>
                          <th>Chart Generate</th>
                          <th>Total Due</th>
                          <th>Not Chart Generate</th>
                          <th>Total Student</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(!empty($late_fees_master_arr))
                        @foreach ($late_fees_master_arr as $each_data)
                        <tr>
                            <td>{{ $each_data->id }}</td>
                            <td>{{ $each_data->late_fees_amount }}</td>
                            <td>{{ $each_data->from_amount }}</td>
                            <td>{{ $each_data->to_amount }}</td>
                            <td>{{ $each_data->upto }}</td>
                            <td> 
                              <a class="btn btn-primary btn-sm m-1" href="{{ url('late-fees-master-edit') .'/'.$each_data->id}}">Edit</a>
                               
                                <input type="hidden" name="table_name" value="late_fees_master">
                                <input type="hidden" name="delete_id" value="{{ $each_data->id }}">
                                <button type="submit" class="btn btn-primary btn-sm removeItem">Delete</button>

                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>SNO</th>
                          <th>Class Name</th>
                          <th>Section Name</th>
                          <th>Chart Generate</th>
                          <th>Total Due</th>
                          <th>Not Chart Generate</th>
                          <th>Total Student</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif

    </div>
    <!-- end of main-content -->
</div>

<link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/sweetalert2.min.css" />

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="{{url('assets/backend')}}/js/plugins/sweetalert2.min.js"></script>
<script src="{{url('assets/backend')}}/js/scripts/sweetalert.script.min.js"></script>

<script type="text/javascript">

  $('.removeItem').click(function (event) {
    event.preventDefault();

    swal({
      title: 'Are you sure?',
      text: "It will permanently deleted !",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: 'success',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
    }).then(function() {

      var myUrl = "{{url('late-fees-master-delete')}}";
      var table_name = $("input[name=table_name]").val();
      var delete_id = $("input[name=delete_id]").val();

      $.ajax({
          url: myUrl,
          type: "POST",
          data: { 
              "_token": "{{ csrf_token() }}",
              table_name : table_name,
              delete_id : delete_id
          },
          success: function () {
            swal(
              'Deleted!',
              'Your file has been deleted.',
              'success'
            );

            setTimeout(function () {
                window.location.reload(1);
            }, 2500);
          }
      });

    })
     
  });

</script>

@endsection

