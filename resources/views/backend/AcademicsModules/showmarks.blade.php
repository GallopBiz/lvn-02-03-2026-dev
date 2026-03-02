@extends('backend.layouts.main')
@section('main-container')
<style type="text/css">
   .validation_err{
      color: red!important;
   }
   input[type="number"] {
    appearance: textfield;
    -webkit-appearance: textfield;
    -moz-appearance: textfield;
}
input {
    position: relative;
}
input[type="date"]::-webkit-calendar-picker-indicator {
    background-position: right;
    background-size: auto;
    cursor: pointer;
    position: absolute;    
    bottom: 0;
    left: 0;
    right: 0;
    top: 7px;
    width: auto;
}

.uperletter{
  text-transform: capitalize;
} 
</style>
<div class="main-content pt-4"> 
          <div class="breadcrumb">     
            <h1 class="me-2">Exam Report</h1>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
                  <div class="form_section1_div">  
                    <form class="" novalidate="novalidate" method="post" action="{{url('show_report_markss')}}">
                        @csrf
                        <div class="row">
                           <div class="col-md-2 form-group mb-3">
                            <label for="firstName1">Class Name</label>
                            <select id="class_name" class="form-control" name="classname" autocomplete="" required>
                               <option value="" selected>Please select</option>
                               @foreach($classlist as $each)
                                @if(!empty($class_name) && $class_name == $each->class_name)
                                  <option selected value="{{$each->class_name}}">{{$each->class_name}}</option>
                                @else
                                  <option value="{{$each->class_name}}">{{$each->class_name}}</option>
                                @endif
                               @endforeach
                            </select>
                            <span class="classname_msg validation_err"></span>
                          </div> 
                           <div class="col-md-2 form-group mb-3">
                              <label for="firstName1">Section :</label>
                              <select required name="section_name" class="form-control" id="section_name" required>
                                <option value=""> -- Please select -- </option>
                                @if (!empty($section_name))
                                  <option selected value="{{$section_name}}">{{$section_name}}</option>
                                @endif
                            </select>
                           </div>
                            <div class="col-md-2 form-group mb-3">
                            <label for="firstName1">Term :</label>
                            <select name="term_name" class="form-control" id="term_name">
                                <option value="">-- Please select --</option>
                                @if (!empty($stream_master))
                                @foreach ($stream_master as $s_item)
                                {{ $c_stream = $s_item->exam_name }}
                                @endforeach
                                @endif
                                @foreach ($examslist as $examslist)
                                <option {{( (!empty($term_name)) && ($term_name==$examslist->exam_name)) ? 'selected' :
                                                    '' }} value="{{ $examslist->exam_name }}">{{ $examslist->exam_name }}</option>
                                @endforeach
                            </select>
                            </div>

                            <div class="col-md-2 form-group mb-3">
                            <label for="firstName1">Report Type :</label>
                            <?php
                              $optionsArray = [
                                'Consolidated',
                                'Consolidated Subject Wise',
                                'Consolidated Student Wise',
                                'Consolidated weightage Wise',
                                'Consolidated Percent Wise',
                                'Result Analysis',
                              ]; 
                              echo '<select required name="report_type" class="form-control" id="report_type">';
                              echo '<option value="">-- Please select --</option>';

                              // Loop through the optionsArray and generate options
                              foreach ($optionsArray as $option) {
                                if(!empty($report_type) && $report_type == $option){
                                  echo '<option value="' . htmlspecialchars($option) . '" selected>' . htmlspecialchars($option) . '</option>';
                                } else {
                                  echo '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
                                }
                              }

                              echo '</select>';
                            ?>
                            <!-- <select required name="report_type" class="form-control" id="report_type">
                                <option value="">-- Please select --</option>
                                <option value="Consolidated">Consolidated</option>
                                <option value="consolidated_subject_wise">Consolidated Subject Wise</option>
                                <option value="consolidated_student_wise">Consolidated Student Wise</option>
                                <option value="consolidated_weightage_wise">Consolidated weightage Wise</option>
                                <option value="consolidated_percent_wise">Consolidated Percent Wise</option>
                                <option value="result_analysis">Result Analysis</option>
                            </select> -->
                            </div>

                            <div class="col-md-2 form-group mb-3">
                                <label for="firstName1">Exam Title :</label>
                                <select required name="exam_title" class="form-control" id="exam_title">
                                    <option value="">-- Please select --</option>
                                </select>
                            </div>
                            <div class="col-md-1 form-group mb-1">
                                <span><label for="firstName1">Best of two :</label></span><br>
                                <input type="checkbox" id="best_of_two" value="best_of_two">
                            </div>
                            <div class="col-md-1 form-group mb-1">
                                <span><label for="firstName1">PDF :</label></span><br>
                                <input type="checkbox" id="pdf_check" value="pdf_check">
                            </div>
                        
                            <div class="col-md-3">
                                <button class="btn btn-primary">Search</button>
                                <input type="reset" class="btn btn-danger text text-white" value="Reset">
                                {{-- <a class="btn btn-primary" href="{{url('adminenquirylist')}}">Clear</a> --}}
                            </div><div class="separator-breadcrumb"></div>
                            <!-- <div class="col-md-1">
                                <button class="btn btn-warning">Export</button>
                            </div> -->
                        </div>
                    </form>
                </div>
            </div>
            <div class="separator-breadcrumb border-top"></div>
                      <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <h4 class="card-title mb-3 text-end">
                    <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="application_for">
                      <!-- <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button> -->
                  </form></div>


                  </h4>
                  <?php //print_r($subject);?>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                         <!--  <th>Application For</th> -->
                          <th>S No.</th>
                          <th>Student Name</th>
                          <th>Sch No.</th>
                          <?php if(!empty($subject['Computer Science'])){ ?>
                            <th>Computer Science <?php echo (!empty($subject['Computer Science']) ? $subject['Computer Science'] : ''); ?></th>
                          <?php } ?>
                          <th>English <?php echo (!empty($subject['English']) ? $subject['English'] : ''); ?></th>                                               
                          <th>Hindi <?php echo (!empty($subject['Hindi']) ? $subject['Hindi'] : ''); ?></th>
                          <th>Mathematics <?php echo (!empty($subject['Mathematics']) ? $subject['Mathematics'] : ''); ?></th>
                          <?php if(!empty($subject['Sanskrit'])){ ?>
                            <th>Sanskrit <?php echo (!empty($subject['Sanskrit']) ? $subject['Sanskrit'] : ''); ?></th>
                          <?php } ?>
                          <th>Science <?php echo (!empty($subject['Science']) ? $subject['Science'] : ''); ?></th>
                          <th>Social Science <?php echo (!empty($subject['Social Science']) ? $subject['Social Science'] : ''); ?></th>
                          <th>Total <br>Obtained Percent Grade</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(!empty($studentmarks))
                          <?php $num = 1; 
                          $subjectNames = ['English', 'Hindi', 'Mathematics', 'Science', 'Social Science','Sanskrit','Computer Science'];
                            echo '<b>Class Teacher Name : ' . (isset($class_teacher->teacher_name) ? $class_teacher->teacher_name  : "" ). '</b>';
                            foreach ($studentmarks as $studentName => $subjectMarks) {
                              // echo '<pre>';
                              // print_r($subjectMarks);
                              // print_r($arrar);
                              foreach ($subjectNames as $subject) {
                                if (!array_key_exists($subject, $subjectMarks)) {
                                    $subjectMarks[$subject] = 0;
                                    $studentmarks_grade[$studentName][$subject] = 0;
                                }
                              }
                            
                              // if(!array_key_exists($subjectNames, $subjectMarks)){
                              //   $subjectMarks['English'] = 0;
                              //   $studentmarks_grade[$studentName]['English'] = 0;
                              // }
                              // die();
                              $eng = (isset($subjectMarks['English']) && !empty($subjectMarks['English'])) ? $subjectMarks['English'] : '';
                              $hindi = (isset($subjectMarks['Hindi']) ? $subjectMarks['Hindi'] : '');
                              $math = (isset($subjectMarks['Mathematics']) ? $subjectMarks['Mathematics'] : '');
                              $sci = (isset($subjectMarks['Science']) ? $subjectMarks['Science'] : '');
                              $sosci = (isset($subjectMarks['Social Science']) ? $subjectMarks['Social Science'] : '');
                              $cs = (isset($subjectMarks['Computer Science']) ? $subjectMarks['Computer Science'] : '');
                              $san = (isset($subjectMarks['Sanskrit']) ? $subjectMarks['Sanskrit'] : '');
                              echo '<tr>';
                              echo '<td>' . $num++ . '</td>';
                              echo '<td>' . $studentName . '</td>';
                              echo '<td>' . (isset($subjectMarks['scholar_no']) ? $subjectMarks['scholar_no'] : '') . '</td>';
                              if(!empty($subjectMarks['Computer Science'])){
                                echo '<td>' . $cs .' '. $studentmarks_grade[$studentName]['Computer Science'].'</td>';
                              }
                              echo '<td>' . $eng .' '. $studentmarks_grade[$studentName]['English'].'</td>';
                              echo '<td>' . $hindi .' '. $studentmarks_grade[$studentName]['Hindi']. '</td>';
                              echo '<td>' . $math .' '. $studentmarks_grade[$studentName]['Mathematics']. '</td>';
                              if(!empty($subjectMarks['Sanskrit'])){
                                echo '<td>' . $san .' '. $studentmarks_grade[$studentName]['Sanskrit'].'</td>';
                              }
                              echo '<td>' . $sci .' '. $studentmarks_grade[$studentName]['Science']. '</td>';
                              echo '<td>' . $sosci .' '. $studentmarks_grade[$studentName]['Social Science']. '</td>';
                              echo '<td> '. (floatval($eng) + floatval($hindi) + floatval($math) + floatval($sci) + floatval($sosci)) . ' <b>'. number_format((floatval($eng) + floatval($hindi) + floatval($math) + floatval($sci) + floatval($sosci)) / floatval($studentmarks_total_sum_max[$studentName]) * 100, 2) .' '. $student_grade[$studentName]. '</b></td>';
                              echo '</tr>';
                          }
                          ?>
                          
                        @else
                        
                        <tr><td colspan="9" class="text-center"><span class="fontcolor-error">There Are No Records Available</span></td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <!-- <th>Application For</th> -->
                          <th>S No.</th>
                          <th>Student Name</th>
                          <th>Sch No.</th>
                          <?php if(!empty($subject['Computer Science'])){ ?>
                            <th>Computer Science <?php echo (!empty($subject['Computer Science']) ? $subject['Computer Science'] : ''); ?></th>
                          <?php } ?>
                          <th>English <?php echo (!empty($subject['English']) ? $subject['English'] : ''); ?></th>                                               
                          <th>Hindi <?php echo (!empty($subject['Hindi']) ? $subject['Hindi'] : ''); ?></th>
                          <th>Mathematics <?php echo (!empty($subject['Mathematics']) ? $subject['Mathematics'] : ''); ?></th>
                          <?php if(!empty($subject['Sanskrit'])){ ?>
                            <th>Sanskrit <?php echo (!empty($subject['Sanskrit']) ? $subject['Sanskrit'] : ''); ?></th>
                          <?php } ?>
                          <th>Science <?php echo (!empty($subject['Science']) ? $subject['Science'] : ''); ?></th>
                          <th>Social Science <?php echo (!empty($subject['Social Science']) ? $subject['Social Science'] : ''); ?></th>
                          <th>Total <br>Obtained Percent Grade</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        
          <!-- end of main-content -->
        <!-- </div> -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<script>
  $(document).ready( function() {
    setTimeout(function() {
      var year = $("#year").val()
      $("#session_name").val(year);
    }, 1000);

    
  });

  function findGrade(percentage, rowNumber) {
    alert(percentage);
    let token = document.getElementsByName("_token")[0].value
    $.ajax({
        data: {
            percentage: percentage
        }
        , url: "{{url('grade_percentage')}}"
        , headers: {
            'X-CSRF-TOKEN': token
        }
        , method: "POST"
        , dataType: 'json'
        , success: function(data) {
            // console.log(data);
            // return data;
            $('#grade_' + rowNumber).val(data);
        }
        , error: function(xhr, status, error) {
            console.error(error);
        }
    });
  };
  $('#class_name').on('change', function() {
    var iso2 = $("#class_name").val();
    let token = document.getElementsByName("_token")[0].value
    console.log(iso2);
    if (iso2) {
        $.ajax({
            data: {
                id: iso2
            }
            , url: "{{url('classsection-view')}}/" + iso2
            , headers: {
                'X-CSRF-TOKEN': token
            }
            , method: "POST"
            , dataType: 'json'
            , success: function(data) {
                console.log(data);
                $('#section_name').html('<option value=""> -- Select All -- </option>');
                for (var i = 0; i < data.length; i++) {
                    var studentData = data[i].section_name;
                    // alert(studentData);
                    <?php if(!empty($section_name)){ ?>
                        if ('<?php echo $section_name; ?>' === studentData) {
                            $('#section_name').append('<option value="' + studentData + '" selected >' + studentData + '</option>');
                        } else {
                            $('#section_name').append('<option value="' + studentData + '">' + studentData + '</option>');
                        }
                    <?php } else { ?>
                        $('#section_name').append('<option value="' + studentData + '">' + studentData + '</option>');
                    <?php } ?>  
                    // console.log(studentData, ' ', selected_section);                            
                    
                }
            }
            , error: function(xhr, status, error) {
                console.error(error);
            }
        });

    } else {
        $('#section_name').html('<option value="">Select class first</option>');
    }
});
</script>

@endsection