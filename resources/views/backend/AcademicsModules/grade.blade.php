@extends('backend.layouts.main')
@section('main-container')
<style>
    .uperletter {
        text-transform: capitalize;
    }

</style>





@php
$i = 0;
@endphp

<div class="main-content">
    <div class="breadcrumb">
        <div class="breadcrumb">
        
    </div>
    {{-- <div class="separator-breadcrumb border-top"></div> --}}

    <div class="row">
        <div class="col-md-6 mb-4">
            <h4>Set Grads :-</h4>


            <div class="separator-breadcrumb border-top"></div>
            @if(!empty($stream_master))
            <form id="progress-form" class="p-4 progress-form" action="{{url('store-gread')}}" method="post">
                <input type="hidden" @if(!empty($stream_master)) @foreach($stream_master as $streammaster) value=" {{ $streammaster->id }}" @endforeach @else value="" @endif name="id">
                @else
                <form id="progress-form" class="p-4 progress-form" action="{{url('save-gread')}}" method="post">
                    @endif
                    @csrf

                    <div class="row">

                        {{--<div class="col-md-3 form-group mb-3">
                         <label for="grading_name">Greading Name</label>
                         <input name="grading_name" class="form-control" id="grading_name" type="text"/>
                     </div> --}}



                     <h5>Co-Scholastic Areas :-</h5>
                     {{-- <div class="col-md-3 form-group mb-3">
                      <label for="min_per">Min %</label>
                      <input name="min_per" class="form-control" id="min_per" type="text"/>
                  </div> --}}
         
                
         
                     <div class="col-md-3 form-group mb-3">
                         <label for="min_per">Min % </label>
                         <input class="form-control" id="termigradecoscholasticareas" name="termigradecoscholasticareas" type="text" @if(!empty($stream_master)) @foreach($stream_master as $streammaster) value="{{ $streammaster->termigradecoscholasticareas }}" @endforeach @else value="" @endif placeholder="91" />
                     </div>
         
                     {{-- <div class="col-md-3 form-group mb-3">
                      <label for="max_per">Max %</label>
                      <input name="max_per" class="form-control" id="max_per" type="text"/>
                  </div> --}}
         
                  
         
                     <div class="col-md-3 form-group mb-3">
                         <label for="max_per">Max %</label>
                         <input class="form-control" id="termiigradecoscholasticareas" name="termiigradecoscholasticareas" type="text" @if(!empty($stream_master)) @foreach($stream_master as $streammaster) value="{{ $streammaster->termiigradecoscholasticareas }}" @endforeach @else value="" @endif placeholder="100" />
                     </div>
         
         
                     <!-- <h5>Discipline :-</h5> -->
                     {{-- <div class="col-md-3 form-group mb-3">
                      <label for="min_per">Min %</label>
                      <input name="min_per" class="form-control" id="min_per" type="text"/>
                  </div> --}}
         
         
                     <div class="col-md-3 form-group mb-3">
                         <label for="min_per">Type Grad</label>
                         <input class="form-control" id="termigradedicipline" name="termigradedicipline" type="text" @if(!empty($stream_master)) @foreach($stream_master as $streammaster) value="{{ $streammaster->termigradedicipline }}" @endforeach @else value="" @endif placeholder="A1" />
                     </div>
         
                     {{-- <div class="col-md-3 form-group mb-3">
                      <label for="max_per">Max %</label>
                      <input name="max_per" class="form-control" id="max_per" type="text"/>
                  </div> --}}
         
         
                     <!-- <div class="col-md-3 form-group mb-3">
                         <label for="term-ii-grade-dicipline">Term - II Grade</label>
                         <input class="form-control" id="termiigradedicipline" name="termiigradedicipline" type="text" @if(!empty($stream_master)) @foreach($stream_master as $streammaster) value="{{ $streammaster->termiigradedicipline }}" @endforeach @else value="" @endif placeholder="Term - II Grade" />
                     </div> -->
         
                     {{-- <div class="col-md-3 form-group mb-3">
                      <label for="grade">Grade</label>
                      <input name="grade" class="form-control" id="grade" type="text"/>
                  </div>                      --}}
         
         
                     {{-- <div class="col-md-3 form-group mb-3">
                         <label for="grade">Grade </label>
                         <input class="form-control uperletter" id="grade" name="grade" type="text" @if(!empty($stream_master)) @foreach($stream_master as $streammaster) value=" {{ $streammaster->grade }}" @endforeach @else value="" @endif placeholder="grade" />
                     </div> --}}
         
                     <input type="text" name="groups" id="groups" class="d-none">
         
         
                     <div class="col-md-12">
                         <button type="submit" id="submitBtn" class="btn btn-primary d-none">Submit</button>
                         <button type="button" id="submitclickBtn" class="btn btn-primary">Submit</button>
                         <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>
         
         
                         @if(request()->route()->getName() !== 'gread')
                         {{-- <a href="{{ url('gread') }}" class="btn btn-primary">Add New</a> --}}
                         @endif
         
                     </div>
                 </div>
                </form>
        </div>

       
    </div>
    <!-- end of main-content -->

    <div class="separator-breadcrumb border-top"></div>
    <div class="row">


        
    </div>





    @php
    $i = 0;
    @endphp

    <form id="progress-form" class="p-4 progress-form" action="{{url('save-gread')}}" method="post">
        
        @csrf

        <div class="row">

            {{--<div class="col-md-3 form-group mb-3">
             <label for="grading_name">Greading Name</label>
             <input name="grading_name" class="form-control" id="grading_name" type="text"/>
         </div> --}}

            {{-- <div class="col-md-3 form-group mb-3">
             <br>
             <label for="applicable">Applicable to overall Grade</label>
             <label class="radio-inline">
                 <input type="checkbox" name="applica value="Option 1" checked>Option 1
               </label>
               <label class="radio-inline">
                 <input type="checkbox" name="applicable" value="Option 2">Option 2
               </label>
               <label class="radio-inline">
                 <input type="checkbox" name="applicable" value="Option 3">Option 3
               </label>
         </div> --}}


    </form>

    <div class="col-md-12 mb-4">
        <div class="breadcrumb">
            <h1 class="me-2">List of Saved Records :-</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>

        <div class="card text-start">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Min %</th>
                                <th>Max %</th>
                                <th>Grad</th>
                                <!-- <th>Term II Grade Dicipline</th> -->
                                {{-- <th>Grade </th> --}}
                                <th>Action </th>

                            </tr>
                        </thead>
                        <tbody>


                            @if(!empty($stream))
                            @foreach($stream as $streams)
                            <tr>
                                <td>{{++$i}}</td>
                                <td class= "uperletter">{{$streams->termigradecoscholasticareas}}</td>
                                <td class= "uperletter">{{$streams->termiigradecoscholasticareas}}</td>
                                <td>{{$streams->termigradedicipline	}}</td>
                                <!-- <td>{{$streams->termiigradedicipline	}}</td> -->
                                {{-- <td class= "uperletter">{{$streams->grade}}</td> --}}

                                <td class='d-flex'>
                                    <a class="btn btn-primary m-1" href="{{ url('view-gread') .'/'.$streams->id}}">Edit</a>
                                    <!-- <form id="deleteForm" method="post" action="{{url('delete-streammaster')}}">                                
                                @csrf
                                <input type="hidden" name="table_name" value="streams">
                                <input type="hidden" name="delete_id" value="{{ $streams->id }}">
                                <button type="button" class="btn btn-danger m-1" onclick="confirmDelete(event)">Delete</button>
                            </form> -->
                                    <?php $a = "grades"."-".$streams->id ; ?>
                                    <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-gread').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                                </td>
                            </tr>
                            <!-- </?php $i++; ?> -->
                            @endforeach
                            @else
                            <tr>
                                <td colspan="9" class="text-center">No Data Found</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot>

                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>














    
    <!-- end of main-content -->
</div>





<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function confirmDelete(event) {
        event.preventDefault(); // Prevents the default link navigation

        Swal.fire({
            title: 'Are you sure?'
            , text: "You won't be able to revert this!"
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonColor: '#d33'
            , cancelButtonColor: '#3085d6'
            , confirmButtonText: 'Yes, delete it!'
            , cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If the user clicks on "Yes, delete it!", navigate to the delete URL
                window.location.href = event.target.href;
            }
        });
    }



    //  submitclickBtn

    document.addEventListener("DOMContentLoaded", function() {


        $('#submitclickBtn').click(function() {

            // let selectedGrad = document.getElementById("grad_table_body").children;
            // let selectedGradData = [];

            // for (var i = 0; i < selectedGrad.length; i++) {
            //     let tr_elm = selectedGrad[i];
            //     let gradObj = {};

            //     if (tr_elm.children[3].children[0].checked) {
            //         gradObj['grade'] = (tr_elm.children[1].innerHTML).trim();
            //         gradObj['grading_name'] = (tr_elm.children[2].innerHTML).trim();
            //         selectedGradData.push(gradObj);
            //     }
            // }

            // console.log(selectedGradData);

            // const selectedGradjsonData = JSON.stringify(selectedGradData);
            
            let classes = [];
            $("input:checkbox[name=class_checkbox]:checked").each(function(){
                classes.push($(this).val());
            });
            const selectedGradjsonData = JSON.stringify(classes);

            $("#groups").val(selectedGradjsonData)
            $("#submitBtn").click();

        })
    })

    document.addEventListener('DOMContentLoaded', function() {
        $("#reset").on("click", function () {
            $("#grading_name").val("");     
            $("#max_per").val("");     
            $("#min_per").val("");                      
            $("#grade").val(""); 
            $("input[type='checkbox']").prop('checked', false);
            $("input[type='radio']").prop('checked', false);              
        });
    })


</script>

<script>
     $(document).ready(function () {
        // Handle the "Action" checkbox change event
        $('.class_checkbox_header').change(function () {
            // Get the state of the "Action" checkbox
            var isChecked = $(this).prop('checked');

            // Set the state of all checkboxes in the column based on the "Action" checkbox
            $('.class_checkbox').prop('checked', isChecked);
        });


        function create_exammaster(e){
                e.preventDefault();
                $("#classes_error").html("");
                const _token = document.getElementsByName("_token")[0].value;

                let classes = [];
                $("input:checkbox[name=class_checkbox]:checked").each(function(){
                  classes.push($(this).val());
                });

                if(classes.length<1){
                  $("#classes_error").html("Please select at least one class.")
                  return
                }

                let classes_names = classes.join(",");                               

                var formData = new FormData()
                formData.append('_token', _token);                
                formData.append('exam_name', $('#exam_name').val());
                formData.append('max_marks_theory', $('#max_marks_theory').val());
                formData.append('max_marks_practical', $('#max_marks_practical').val());
                formData.append('fail_if', $('#fail_if').val());                
                formData.append('exam_type', $('#exam_type').val());
                
                formData.append('remarks', $('#remarks').val());                
                formData.append('is_ser', $('#is_ser').is(":checked"));
                formData.append('class_name', classes_names);
                // alert(formData);
                // formData.append('file', $('#file_path').prop('files')[0])
                $.ajax({
                   // url: "/lvn-school/public/save-exammaster",
                  //  {{url('save-exammaster')}},
                  url: "{{url('save-exammaster')}}",
                    type: "POST",
                    // dataType: "json",
                    data: formData,                    
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(response) {
                      console.log(response);
                      toastr.success("exammaster has been created successfully.", "Success");                     
                      // toastr.error("exammaster has been created successfully.", "Success");                     
                      setTimeout(() => {
                        // location.reload()
                        window.location.href = "{{ route('exammaster') }}";

                      }, 1500);
                    }
                });
            }
    






    });


</script>



</div>
@endsection
