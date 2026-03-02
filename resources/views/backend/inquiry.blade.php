<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">    <!-- JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
</head>

<style>

.uperletter{
  text-transform: capitalize;
}  


</style>

<body>
@include('backend.layouts.header')

  <body class="text-start">    
    <div class="app-admin-wrap layout-sidebar-vertical sidebar-full">
    @include('backend.layouts.sidebar')

      <section>
        <div class="container">
            <ul class="nav nav-pills nav-pills-bg-soft justify-content-sm-end mb-4 ">
                <!-- <a class="btn btn-success" href="javascript:void(0)" id="createNewProduct"> Create New Product</a> -->
            </ul>
            <table class="table table-bordered data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Student Name</th>
                        <th>Class Name</th>
                        <th>Phone Number</th>
                        <th width="280px">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    <section>
     
    <div class="modal fade" id="ajaxModel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modelHeading"></h4>
                </div>
                <div class="modal-body">
                    <form id="productForm" name="productForm" class="form-horizontal">
                    <input type="hidden" name="product_id" id="product_id">
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Student Name</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="student_name" name="student_name" placeholder="Enter Name" value="" maxlength="50" required="">
                            </div>
                        </div>
        
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Class Name</label>
                            <div class="col-sm-12">
                                <textarea id="class_name" name="class_name" required="" placeholder="Enter Details" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Phone Number</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="mobile_number" name="mobile_number" placeholder="Enter Mobile Number" value="" maxlength="50" required="">
                            </div>
                        </div>
            
                        <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary" id="saveBtn" value="create">Save changes
                        </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="callModel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modelHeading1"></h4>
                </div>
                <div class="modal-body">
                    <form id="productForm1" name="productForm1" class="form-horizontal">
                    Student name : <span id="student_name1"></span>
                    Class name : <span id="class_name1"></span>
                    Mobile number : <span id="mobile_number1"></span>
                    <input type="hidden" name="mobile_number" id="mobile_number2">
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Call Tag</label>
                            <div class="col-sm-12">
                                <select name="call_tag" id="" class="form-control">
                                    <option value="">Please select</option>
                                    <option value="connect">connect</option>
                                    <option value="reingging">reingging</option>
                                    <option value="Switch Off">Switch Off</option>
                                </select>
                            </div>
                        </div>
        
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Call Note</label>
                            <div class="col-sm-12">
                                <textarea id="call_note" name="call_note" required="" placeholder="Enter Details" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary" id="saveBtn1" value="create">Save changes
                        </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewcallModel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modelHeading1"></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Call Note</label>
                        <div class="col-sm-12">
                            <textarea id="call_note_v" name="call_note_v" required="" placeholder="Enter Details" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
      
<script type="text/javascript">
  $(function () {
      
    /*------------------------------------------
     --------------------------------------------
     Pass Header Token
     --------------------------------------------
     --------------------------------------------*/ 
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
    });
      
    /*------------------------------------------
    --------------------------------------------
    Render DataTable
    --------------------------------------------
    --------------------------------------------*/
    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('inquiry-data.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'student_name', student_name: 'student_name'},
            {data: 'class_name', class_name: 'class_name'},
            {data: 'mobile_number', mobile_number: 'mobile_number'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

    
      
    /*------------------------------------------
    --------------------------------------------
    Click to Button
    --------------------------------------------
    --------------------------------------------*/
    $('#createNewProduct').click(function () {
        $('#saveBtn').val("create-Call");
        $('#product_id').val('');
        $('#productForm').trigger("reset");
        $('#modelHeading').html("Create New Call");
        $('#ajaxModel').modal('show');
    });

    $('body').on('click', '.callStudent', function () {
      var product_id = $(this).data('id');
      $.get("{{ route('inquiry-data.index') }}" +'/' + product_id +'/edit', function (data) {
          $('#modelHeading1').html("Call Student");
          $('#saveBtn').val("edit-user");
          $('#callModel').modal('show');
          $('#mobile_number2').val(data.mobile_number);
          $('#class_name1').html(data.class_name);
          $('#student_name1').html(data.student_name);
          $('#call_note').html(data.mobile_number);

      })
    });

    $('body').on('click', '.ViewStudent', function () {
        // $('#viewcallModel').modal('show');
        var mobile = $(this).data('id');
        alert(mobile);
        $.post("{{ route('inquiry-data-view') }}",{
            mobile_number: mobile
        }, function (data) {
            
          $('#modelHeading2').html("Edit Produc");
          $('#saveBtn').val("edit-user");
          $('#viewcallModel').modal('show');
          $('#product_id').val(data.id);
          var note = ""
          for (var i = 0; i <= data.length; i++){
                note += " \n " + data[i].call_note
                $('#call_note_v').val(note);
          }
      })
    });
      
    /*------------------------------------------
    --------------------------------------------
    Click to Edit Button
    --------------------------------------------
    --------------------------------------------*/
    $('body').on('click', '.editProduct', function () {
      var product_id = $(this).data('id');
      $.get("{{ route('inquiry-data.index') }}" +'/' + product_id +'/edit', function (data) {
          $('#modelHeading').html("Edit Produc");
          $('#saveBtn').val("edit-user");
          $('#ajaxModel').modal('show');
          $('#product_id').val(data.id);
          $('#class_name').val(data.class_name);
          $('#student_name').val(data.student_name);
      })
    });
      
    /*------------------------------------------
    --------------------------------------------
    Create Product Code
    --------------------------------------------
    --------------------------------------------*/
    $('#saveBtn1').click(function (e) {
        e.preventDefault();
        $(this).html('Sending..');
        $.ajax({
          data: $('#productForm1').serialize(),
          url: "{{ route('inquiry-data.store') }}",
          type: "POST",
          dataType: 'json',
          success: function (data) {
       
              $('#productForm1').trigger("reset");
              $('#callModel').modal('hide');
              table.draw();
           
          },
          error: function (data) {
              console.log('Error:', data);
              $('#saveBtn').html('Save Changes');
          }
      });
    });
      
    /*------------------------------------------
    --------------------------------------------
    Delete Product Code
    --------------------------------------------
    --------------------------------------------*/
    $('body').on('click', '.deleteProduct', function () {
     
        var product_id = $(this).data("id");
        confirm("Are You sure want to delete !");
        
        $.ajax({
            type: "DELETE",
            url: "{{ route('inquiry-data.store') }}"+'/'+product_id,
            success: function (data) {
                table.draw();
            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    });
       
  });
</script>
</html>