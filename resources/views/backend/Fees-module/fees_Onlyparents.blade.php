<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet" />
    <link href="http://localhost/lvn-school/public/assets/backend/css/themes/lite-purple.css" rel="stylesheet" />
    <link href="http://localhost/lvn-school/public/assets/backend/css/plugins/perfect-scrollbar.css" rel="stylesheet" />
    <link rel="stylesheet" href="http://localhost/lvn-school/public/assets/backend/css/plugins/fontawesome-5.css" />
    <link href="http://localhost/lvn-school/public/assets/backend/css/plugins/metisMenu.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="http://localhost/lvn-school/public/assets/backend/css/plugins/toastr.css" />
    <link rel="stylesheet" href="http://localhost/lvn-school/public/assets/backend/css/plugins/perfect-scrollbar.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" />


</head>
<body>
    {{-- <h1>hello</h1> --}}



    <div class="main-content p-4">
        <div class="breadcrumb">
            <h1>Fees Details</h1>
            <ul>
                <!-- <li><a href="href">Form</a></li> -->
                <!-- <li>Basic</li> -->
            </ul>
        </div>
        <div class="separator-breadcrumb border-top"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <!-- <div class="card-title mb-3">Form Inputs</div> -->
                        <!-- <form> -->
                        <form action="{{url('save_feesreceipt_challan')}}" class="progress-form" novalidate method="POST">
                            {{ csrf_field() }}



                            <div class="row p-0">
                                <div class="col-9 p-4">
                                    <div class="col-md-6 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <label class="form-label" for="form1">Student Search</label>
                                            {{-- <select required id="inq-form-nomenu" class="form-control" name="search_student" id="search_student" data-live-search="true">
                                                <option data-tokens="china">Select The Student</option>
                                                <//?php if(!empty($data_student_name)) {
                                                foreach($data_student_name as $name){
                                                    echo '<option value="'.$name->form_number.'" data-tokens="'.$name->student_name.'">'.$name->student_name.'-'.$name->form_number.'</option>';
                                                }
                                            }
                                            ?>
                                            </select> --}}

                                            <div class="input-group">
                                                <input type="text" class="form-control col-8" name="search_student" id="search_student">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary" id="saveButton">search</button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>




                                    <div class="col-md-6 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <label class="form-label" for="form1">Due Upto :</label>
                                            {{-- <select required name="due_upto" id="due_upto_date" onchange="select_due_upto(this);" class="form-control">
                                                <option value="select"> -- Select -- </option>
                                            </select> --}}
                                            <input type="date" required name="due_upto" id="due_upto_date" onchange="select_due_upto(this);" class="form-control">
                                        </div>
                                    </div>

                                    {{-- bottom table --}}


                                    <div class="col-md-12 form-group mb-12">

                                        <p>Head Name : </p> <span id="head_names"></span>
                                        <p>Due Ammount : </p>

                                        <div class="table-responsive">
                                            {{-- <table class="table table-bordered head_table"> --}}
                                            <table class="table ">
                                                <tr>
                                                    {{-- <td>Head Name</td>
                                                    <td>Due Ammount</td>                                                    --}}
                                                </tr>
                                                <tbody class="head_table_body">

                                                </tbody>


                                            </table>
                                        </div>

                                    </div>

                                    <div class="col-md-6"></div>
                                    <div class="col-md-6 mb-4">

                                        <div class="table-responsive">

                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <td> total Due Amount :
                                                            <input type="text" disabled name="total_dueamount" value="0" class="form-control">
                                                            <input type="hidden" name="hidden_total_dueamount" value="">
                                                        </td>

                                                        <td> <label class="form-label" for="form1">Your Ammount <br>
                                                                <input type="text" class="form-control" value="" placeholder="Type Your Amount" id="advance_received_prew" name="advance_received_prew">
                                                        </td>

                                                </tbody>
                                            </table>

                                            <div class="card-title mb-3 text-end">
                                                <button class="btn btn-success" id="saveButton">Save</button>
                                            </div>
                                        </div>

                                    </div>



                                </div>

                                <div class="col-3 p-4" style="background: rgb(20, 2, 68); color:white; border-top-right-radius: 13px; border-bottom-right-radius: 13px;">
                                    <div class="col-md-12 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Name : </lable>
                                            <input type="hidden" id="name_student_geti" name="name_student">
                                            <lable id="name_student_get" class="text text-danger"></lable>
                                        </div>
                                    </div>
                                    <div class="col-md-12 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Father's Name : </lable>
                                            <input type="hidden" id="name_father_geti" name="name_father">
                                            <lable id="name_father_get" class="text text-danger"></lable>
                                        </div>
                                    </div>
                                    <div class="col-md-12 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Class Name : </lable>
                                            <input type="hidden" id="name_classsection_geti" name="name_classsection">
                                            <lable id="name_class_get" class="text text-danger"></lable>
                                        </div>
                                    </div>
                                    <div class="col-md-12 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Adm Dt </lable>
                                            <input type="hidden" id="name_adm_geti" name="name_admdt">
                                            <lable id="name_adm_get" class="text text-danger"></lable>
                                        </div>
                                    </div>

                                    <div class="col-md-12 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Serial No : </lable>
                                            <input type="hidden" id="name_formno_geti" name="name_formno">
                                            <lable id="name_formno_get" class="text text-danger"></lable>
                                        </div>
                                    </div>
                                    <div class="col-md-12 form-group mb-3">
                                        <div class="form-outline w-auto">
                                            <lable>Scholar No : </lable>
                                            <input type="hidden" id="name_scholarno" name="name_scholarno">
                                            <lable id="name_scholarno_geti" class="text text-danger"></lable>
                                        </div>
                                    </div>

                                </div>


                            </div>



                        </form>
                    </div>
                </div>
            </div>
        </div>



        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.full.min.js"></script>
        <script type="text/javascript">
            $.noConflict();

            jQuery(document).ready(function($) {

                function searchStudent(e) {
                    // var form_number = $("#inq-form-nomenu").val();
                    var form_number = $("#search_student").val();
                    // var form_number = '1324'
                    //   console.log(form_number);

                    let _token = $("_token").val();

                    $.ajax({
                        type: "POST",
                        // data: {form_number:form_number},
                        data: {
                            student_id: form_number
                        }
                        , url: "{{url('search-scholer-payments')}}"
                        , headers: {
                            'X-CSRF-TOKEN': _token
                        }
                        , dataType: 'json'
                        , success: function(data) {
                            if (data) {
                                console.log(data);


                                $("#name_student_get").html(data.student_name);
                                $("#name_father_get").html(data.fathername);
                                $("#name_class_get").html(data.class_name);
                                $("#name_adm_get").html(data.adm_dt);
                                $("#name_scholarno_geti").html(data.formno);

                                const headNames = data.course_fees_head_orders_list_arr;

                                headNames.forEach(elem => {
                                    $("#head_names").append(`<span class="badge badge-light ms-1">${elem.ac_head_name}</span>`)

                                });


                                // $("#name_formno_get").html(data.class_name);

                            }
                        }
                    })

                }

                $("#saveButton").click(function() {
                    // console.log("hello");
                    searchStudent();
                });


                $("#inq-form-nomenu").select2().on('change', searchStudent)

            });

        </script>
</body>
</html>
