@extends('backend.layouts.main')
@section('main-container')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{url('assets/backend/')}}/js/jquery.js" />
​
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Generate Fees Due Chart
            </h1>
        </div>
        <form action="{{url('save-due-chart')}}" method="post">
            @csrf
            <div class="row">
            <div class="col-md-4 form-group mb-3">
                              <label for="studentname">Class</label>
                              <select id="classname" class="form-control" name="classname" autocomplete="shipping address-level1" required>
                              @if(!empty($classname))
                              <option value=""> -- Please select -- </option>
                              @foreach($datas as $country)
                                @if($classname == $country->class_name)
                                  <option selected value="{{$country->class_name}}">{{$country->class_name}}</option>
                                @else
                                <option value="{{$country->class_name}}">{{$country->class_name}}</option>
                                @endif
                              @endforeach
                              @else
                                <option value="" selected> -- Please select -- </option>
                                     @foreach($datas as $country)
                                        <option value="{{$country->class_name}}">{{$country->class_name}}</option>
                                     @endforeach
                              @endif
                              </select>
                           </div>
                           <div class="col-md-4 form-group mb-3">
                              <label for="studentname">Section</label>
                              <select id="secationname" class="form-control" name="secationname1" autocomplete="shipping address-level1" required>
                              <?php //print_r($sectionname);die(); ?>
                              @if(!empty($sectionname))
                                <option selected value="{{$sectionname}}">{{$sectionname}}</option>
                              @else
                              @endif
                                <option value=""> -- Please select -- </option>
                                <option value="A"> -- Select All -- </option>
                              </select> 
                           </div>
                <div class="col-md-9 m-2">
                    <button class="btn btn-primary">Submit</button>
                </div>
        </form>
                 <div class="col-md-2 m-2">
                    <!-- <a href="" target="_blank" class="btn btn-primary">New</a>
                    <a href="" target="_blank" class="btn btn-primary">Exit</a>
                    <a href="" target="_blank" class="btn btn-primary">Help</a> -->
                </div>
            </div>
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
                          <th>Chart not generate</th>
                          <th>Total Student</th>
                          <th>Total Due</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php
                          $totalfees = 0;
                        if(!empty($feesstructure)){ ?>
                        @foreach ($feesstructure as $feesdata)
                        <?php $notificationData1 = json_decode($feesdata->json_str, true);
                        $totalfees = $feesdata->total_above_fees;
                        
                        
                        ?>
                        @endforeach
                     
                      <?php } else {
                      } ?>
                        <?php $notchartgenerate=0;  $class=""; $count="";$totaldue=""; $totalduafees="";?>
                        <?php if(!empty($feesstructure)){ ?>
                          @foreach ($duachart as $each_data)
                            <?php
                                  $nd = json_decode($each_data->json_str, true);
                                  $totalstuddent = count($duachart);
                                  // foreach($nd as $nd2){
                                  //     if($nd2['admission_type'] == "RTE"){
                                          //$notchartgenerate= ($notchartgenerate + 1);
                                      //}
                                    //}
                                  $finalfeesstudent = $totalstuddent - $notchartgenerate;
                                  $totalduafees= ((!empty($totalfees) ?  $totalfees : 0) * $finalfeesstudent);
                                  $class =$each_data->class_name;
                                  $count= count($duachart);
                                  $totaldue = $totalduafees;                                                                                                    
                                  $studentid[]=$each_data->id;
                                  $studentid1=json_encode($studentid);
                                                              ?>
                        @endforeach
                        <?php } else {
                        } ?>
                        @if(!empty($duachart) && !empty($each_data))
                        <tr>
                            <td>1</td>
                            <td>{{$class}} </td>
                            <td>{{ $sectionname }}</td>
                            <td><?php if($duachart_data) {
                                echo count($duachart_data);
                              } else {
                                echo 0;
                            } ?></td>
                            <td><?php if($duachart_data) {
                                echo $count - count($duachart_data);
                              } else {
                                echo 0;
                            } ?></td>
                            <td>{{ $count }} </td>
                            <td><?php if((!empty($totaldue))) { echo number_format($totaldue, 2);} else { echo 'Create Structure'; }?></td>
                            <td>
                              <form method="post" action="{{url('save_student_duechart')}}">
                                @csrf
                                <input type="hidden" name="studentname" value="<?php if(!(empty($each_data))){ ?>{{$each_data->id}}<?php }?>">
                                <input type="hidden" name="classname" value="{{$class}}">
                                <input type="hidden" name="sectionname" value="{{$sectionname}}">
                                <input type="hidden" name="amount" value="<?php echo (!empty($totalfees)) ? $totalfees : ''; ?>">
                                <input type="hidden" name="session_name" value="<?php if(!(empty($each_data))){ ?>{{$each_data->session_name}}<?php }?>">
                                <input type="hidden" name="studentid" value="<?php if(!(empty($studentid1))){ echo $studentid1;}?>">
                                <button class="btn btn-primary">Generate Chart</button>
                                <?php // echo (!empty($totaldue)) ? '<button class="btn btn-primary">Generate Chart</button>' : '<button class="btn btn-primary" disabled="true">Generate Chart</button>'; 
                                ?>
                              </form>
                            </td>
                        </tr>
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
                          <th>Chart not generate</th>
                          <th>Total Student</th>
                          <th>Total Due</th>
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
    <!-- end of main-content -->
</div>
<link rel="stylesheet" href="{{url('assets/backend')}}/css/plugins/sweetalert2.min.css" />
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script> -->
<script src="{{url('assets/backend')}}/js/plugins/sweetalert2.min.js"></script>
<script src="{{url('assets/backend')}}/js/scripts/sweetalert.script.min.js"></script>
<script src="{{url('assets/backend')}}/js/jquery.js"></script>
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
  
  $(document).ready(function () {
  var session_select = $('#secationname').val();
  var select_val = $('#classname').val();
  console.log(session_select);
  console.log(select_val);

  if(select_val != null && select_val != ""){
    $.ajax({
        data: { id: select_val },
        url: "{{url('classsection-view')}}/" + select_val,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: "POST",
        dataType: 'json',
        success: function (data) {
              // console.log(data);
              $('#secationname').html('<option value="A"> -- Select All -- </option>');
              for (var i = 0; i < data.length; i++) {
                  var studentData = data[i].section_name;
                  if(session_select == studentData){
                    $('#secationname').append('<option selected value="' + studentData + '">' + studentData + '</option>');
                  } else {
                    $('#secationname').append('<option value="' + studentData + '">' + studentData + '</option>');
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
        // console.log(iso2);
        if (iso2) {
            $.ajax({
                data: { id: iso2 },
                url: "{{url('classsection-view')}}/" + iso2,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                method: "POST",
                dataType: 'json',
                success: function (data) {
                      // console.log(data);
                      $('#secationname').html('<option value="A"> -- Select All -- </option>');
                      for (var i = 0; i < data.length; i++) {
                          var studentData = data[i].section_name;
                          // console.log(studentData);
                          $('#secationname').append('<option value="' + studentData + '">' + studentData + '</option>');
                      }
                  },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        } else {
            $('#secationname').html('<option value="">Select class first</option>');
        }
    });
});
</script>
@endsection
