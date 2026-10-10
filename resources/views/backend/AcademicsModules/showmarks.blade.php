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
                              <select required name="section_name" class="form-control" id="section_name">
                                <option value=""> -- Please select -- </option>
                                @if(!empty($sections) && count($sections) > 0)
                                  @foreach($sections as $sec)
                                    <option value="{{ $sec }}" {{ (!empty($section_name) && $section_name == $sec) ? 'selected' : '' }}>{{ $sec }}</option>
                                  @endforeach
                                @elseif (!empty($section_name))
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
                                @if(!empty($examslist))
                                    @foreach(collect($examslist)->pluck('exam_name')->filter(function($v) { return !empty(trim((string)$v)); })->unique()->values() as $eName)
                                        <option value="{{ $eName }}" {{ (!empty($term_name) && $term_name == $eName) ? 'selected' : '' }}>{{ $eName }}</option>
                                    @endforeach
                                @endif
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
                  @if(!empty($report_type) && ($report_type == 'Consolidated Subject Wise' || $report_type == 'consolidated_subject_wise'))
                    <style id="consolidated-marksheet-print-styles">
                        .consolidated-print-area { width: 100%; color: #111; font-family: "Times New Roman", Times, serif; background: #fff; }
                        .sheet { width: 100%; margin: 0 auto; padding: 10px; overflow: auto; }
                        .title { text-align: center; line-height: 1.2; margin-bottom: 8px; }
                        .title h2 { margin: 0; font-size: 18px; font-weight: bold; }
                        .title h3 { margin: 4px 0 0; font-size: 14px; font-weight: bold; }
                        .title .exam-line { margin-top: 2px; font-size: 13px; font-weight: bold; }
                        .meta { display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: bold; margin: 8px 0; }
                        .consolidated-print-area table { width: 100%; border-collapse: collapse; }
                        .consolidated-print-area th,
                        .consolidated-print-area td { border: 1px solid #222; padding: 4px 6px; font-size: 11.5px; line-height: 1.2; vertical-align: middle; }
                        .consolidated-print-area th { text-align: center; font-weight: bold; background-color: #f1f5f9; }
                        .consolidated-print-area td.center { text-align: center; }
                        .consolidated-print-area td.right { text-align: right; }
                        .student-start td { border-top: 2px solid #222; }
                        .rollup td { font-weight: bold; background: #f8fafc; }
                    </style>
                    @php
                        $formatMark = fn ($value) => is_numeric($value) ? number_format(round((float) $value, 0, PHP_ROUND_HALF_UP), 2, '.', '') : '';
                        $formatPercentage = fn ($value) => is_numeric($value) ? number_format((float) $value, 2, '.', '') : '';
                        $hasSubject = function ($subjects, $subject) {
                            $needle = strtolower(preg_replace('/\s+/', ' ', trim((string) $subject)));
                            return collect($subjects)->contains(fn ($item) => str_contains($item, $needle) || str_contains($needle, $item));
                        };
                    @endphp
                    @if(!empty($consolidated_reports) && count($consolidated_reports) > 0)
                        @foreach($consolidated_reports as $report)
                            @php
                                $classNameStr = (string) ($report['class']->class_name ?? '');
                                $isClassOne = preg_match('/(^|\D)(1|i|one|first)(\D|$)/i', $classNameStr);
                            @endphp
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="text-end mb-3">
                                        <button type="button" onclick="printConsolidatedReport()" class="btn btn-primary btn-sm me-2">
                                            <i class="i-Printer me-1"></i> Print Landscape
                                        </button>
                                        <a href="{{ route('academic.consolidated-marksheets.print', ['class_name' => $class_name, 'section_name' => $section_name]) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="i-Printer me-1"></i> Full Print View
                                        </a>
                                    </div>
                                    <div class="consolidated-print-area">
                                        <main class="sheet">
                                            <div class="title">
                                                <h2>Lokmanya Vidya Niketan</h2>
                                                <h3>Consolidated Term - II (Annual) Examination &nbsp;&nbsp; Session : {{ str_replace('_', '-', $report['session_year'] ?? '2025-2026') }}</h3>
                                            </div>
                                            <div class="meta">
                                                <div>Class-Section : {{ $report['class']->class_name }}{{ $report['class']->section_name ? ' - '.$report['class']->section_name : '' }}</div>
                                                <div>ClassTeacher : {{ $report['class_teacher'] ?? '' }}</div>
                                            </div>
                                            <table class="table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 45px;" rowspan="2">Sr.<br>No</th>
                                                        <th style="width: 150px;" rowspan="2">Student Name</th>
                                                        <th style="width: 90px;" rowspan="2">Sch. No<br>Att.</th>
                                                        <th style="width: 120px;" rowspan="2">Subject</th>
                                                        <th colspan="7">Term - I</th>
                                                        <th colspan="7">Term - II</th>
                                                        <th style="width: 60px;" rowspan="2">%</th>
                                                        <th style="width: 60px;" rowspan="2">Division</th>
                                                        <th style="width: 60px;" rowspan="2">Result</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="width: 40px;">PT<br>(5)</th>
                                                        <th style="width: 40px;">MAS<br>(5)</th>
                                                        <th style="width: 40px;">PF<br>(5)</th>
                                                        <th style="width: 40px;">SEA<br>(5)</th>
                                                        <th style="width: 40px;">HY<br>(80)</th>
                                                        <th style="width: 50px;">Total<br>(100)</th>
                                                        <th style="width: 45px;">Grade</th>
                                                        <th style="width: 40px;">PT<br>(5)</th>
                                                        <th style="width: 40px;">MA<br>(5)</th>
                                                        <th style="width: 40px;">PF<br>(5)</th>
                                                        <th style="width: 40px;">SE<br>(5)</th>
                                                        <th style="width: 40px;">ANN<br>(80)</th>
                                                        <th style="width: 50px;">Total<br>(100)</th>
                                                        <th style="width: 45px;">Grade</th>
                                                    </tr>
                                                </thead>
                                                @foreach(collect($report['students'])->values() as $studentIndex => $studentRow)
                                                    @php
                                                        $serialNo = $studentIndex + 1;
                                                        $subjectRows = collect($studentRow['subjects']);
                                                        $existingSubjects = $subjectRows->pluck('subject')->map(fn ($subject) => strtolower(preg_replace('/\s+/', ' ', trim((string) $subject))))->all();
                                                        $classOneSubjects = $isClassOne
                                                            ? collect(['Computer Science', 'Sanskrit'])
                                                                ->reject(fn ($subject) => $hasSubject($existingSubjects, $subject))
                                                                ->map(fn ($subject) => [
                                                                    'subject' => $subject,
                                                                    'term_1' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                                                    'term_2' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                                                ])
                                                                ->values()
                                                            : collect();
                                                        $displaySubjects = $subjectRows->merge($classOneSubjects)->values();
                                                        $rowspanCount = $displaySubjects->count() + ($subjectRows->count() > 0 ? 1 : 0);
                                                    @endphp
                                                    <tbody class="student-group">
                                                        @forelse($displaySubjects as $subjectIndex => $subject)
                                                            <tr class="{{ $subjectIndex === 0 ? 'student-start' : '' }}">
                                                                @if($subjectIndex === 0)
                                                                    <td class="center" rowspan="{{ $rowspanCount }}">{{ $serialNo }}</td>
                                                                    <td rowspan="{{ $rowspanCount }}">{{ $studentRow['student']->student_name }}</td>
                                                                    <td class="center" rowspan="{{ $rowspanCount }}">
                                                                        {{ $studentRow['student']->scholar_no }}<br>
                                                                        {{ $studentRow['attendance'] }}
                                                                    </td>
                                                                @endif
                                                                <td>{{ $subject['subject'] }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_1']['pt'] ?? null) ? $formatMark($subject['term_1']['pt']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_1']['mas'] ?? null) ? $formatMark($subject['term_1']['mas']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_1']['pf'] ?? null) ? $formatMark($subject['term_1']['pf']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_1']['sea'] ?? null) ? $formatMark($subject['term_1']['sea']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_1']['theory'] ?? null) ? $formatMark($subject['term_1']['theory']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_1']['total'] ?? null) ? $formatMark($subject['term_1']['total']) : '' }}</td>
                                                                <td class="center">{{ $subject['term_1']['grade'] ?? '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_2']['pt'] ?? null) ? $formatMark($subject['term_2']['pt']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_2']['mas'] ?? null) ? $formatMark($subject['term_2']['mas']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_2']['pf'] ?? null) ? $formatMark($subject['term_2']['pf']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_2']['sea'] ?? null) ? $formatMark($subject['term_2']['sea']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_2']['theory'] ?? null) ? $formatMark($subject['term_2']['theory']) : '' }}</td>
                                                                <td class="center">{{ is_numeric($subject['term_2']['total'] ?? null) ? $formatMark($subject['term_2']['total']) : '' }}</td>
                                                                <td class="center">{{ $subject['term_2']['grade'] ?? '' }}</td>
                                                                <td class="center"></td>
                                                                <td class="center"></td>
                                                                <td class="center"></td>
                                                            </tr>
                                                        @empty
                                                            <tr class="student-start">
                                                                <td class="center">{{ $serialNo }}</td>
                                                                <td>{{ $studentRow['student']->student_name }}</td>
                                                                <td class="center">
                                                                    {{ $studentRow['student']->scholar_no }}<br>
                                                                    {{ $studentRow['attendance'] }}
                                                                </td>
                                                                <td colspan="15" class="center">No assigned subjects found</td>
                                                                <td class="center">{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                                                <td class="center">{{ $studentRow['division'] }}</td>
                                                                <td class="center">{{ $studentRow['result'] }}</td>
                                                            </tr>
                                                        @endforelse
                                                        @if($subjectRows->count() > 0)
                                                            <tr class="rollup">
                                                                <td colspan="5"></td>
                                                                <td class="center">{{ $formatMark($subjectRows->sum(fn ($subject) => $subject['term_1']['theory'] ?? 0)) }}</td>
                                                                <td class="center">{{ $formatMark($subjectRows->sum(fn ($subject) => $subject['term_1']['total'] ?? 0)) }}</td>
                                                                <td></td>
                                                                <td colspan="4"></td>
                                                                <td class="center">{{ $formatMark($subjectRows->sum(fn ($subject) => $subject['term_2']['theory'] ?? 0)) }}</td>
                                                                <td class="center">{{ $formatMark($subjectRows->sum(fn ($subject) => $subject['term_2']['total'] ?? 0)) }}</td>
                                                                <td></td>
                                                                <td class="center"><b>{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</b></td>
                                                                <td class="center"><b>{{ $studentRow['division'] }}</b></td>
                                                                <td class="center"><b>{{ $studentRow['result'] }}</b></td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                @endforeach
                                            </table>
                                        </main>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning text-center">There Are No Consolidated Records Available For The Selected Filter</div>
                    @endif
                  @else
                  <div class="d-flex justify-content-end align-items-center mb-3">
                    @if(!empty($studentmarks))
                      <button type="button" onclick="printStandardReportLandscape()" class="btn btn-primary btn-sm">
                        <i class="i-Printer me-1"></i> Print Landscape
                      </button>
                    @endif
                  </div>
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
                          <th>Total</th>
                          <th>Grade</th>
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
                                echo '<td>' . $cs . '</td>';
                              }
                              echo '<td>' . $eng . '</td>';
                              echo '<td>' . $hindi . '</td>';
                              echo '<td>' . $math . '</td>';
                              if(!empty($subjectMarks['Sanskrit'])){
                                echo '<td>' . $san . '</td>';
                              }
                              echo '<td>' . $sci . '</td>';
                              echo '<td>' . $sosci . '</td>';
                              echo '<td>' . (floatval($eng) + floatval($hindi) + floatval($math) + floatval($sci) + floatval($sosci)) . '</td>';
                              echo '<td><b>' . $student_grade[$studentName] . '</b></td>';
                              echo '</tr>';
                          }
                          ?>
                          
                        @else
                        
                        <tr><td colspan="12" class="text-center"><span class="fontcolor-error">There Are No Records Available</span></td></tr>
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
                          <th>Total</th>
                          <th>Grade</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  @endif
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

function printConsolidatedReport() {
    var contentNode = document.querySelector('.consolidated-print-area');
    if (!contentNode) return;
    var content = contentNode.cloneNode(true);
    var printWindow = window.open('', '_blank', 'width=1250,height=850');
    printWindow.document.open();
    printWindow.document.write(`
        <html>
        <head>
            <title>Consolidated Marksheet Report</title>
            <style>
                @page { size: A4 landscape; margin: 4mm; }
                * { box-sizing: border-box; }
                body { margin: 0; padding: 6px; background: #fff; font-family: "Times New Roman", Times, serif; color: #000; }
                .sheet { width: 100%; margin: 0 auto; page-break-after: always; break-after: page; }
                .sheet:last-child { page-break-after: auto; break-after: auto; }
                .title { text-align: center; line-height: 1.15; margin-bottom: 6px; }
                .title h2 { margin: 0; font-size: 16px; font-weight: bold; }
                .title h3 { margin: 2px 0 0; font-size: 13px; font-weight: bold; }
                .meta { display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; font-weight: bold; margin: 4px 0 8px 0; }
                table { width: 100%; border-collapse: collapse; table-layout: fixed; }
                th, td { border: 1px solid #000; padding: 3px 4px; font-size: 10px; line-height: 1.1; vertical-align: middle; }
                th { text-align: center; font-weight: bold; background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                td.center { text-align: center; }
                td.right { text-align: right; }
                .student-group { page-break-inside: avoid; break-inside: avoid; }
                .student-start td { border-top: 2px solid #000; }
                .rollup td { font-weight: bold; background-color: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            </style>
        </head>
        <body>${content.outerHTML}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.onload = function() {
        setTimeout(function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }, 400);
    };
}

function printStandardReportLandscape() {
    var tableNode = document.getElementById('zero_configuration_table');
    if (!tableNode) return;
    var tableContent = tableNode.outerHTML;
    var teacherInfo = document.querySelector('.card-body b') ? document.querySelector('.card-body b').outerHTML : '';
    var printWindow = window.open('', '_blank', 'width=1250,height=850');
    printWindow.document.open();
    printWindow.document.write(`
        <html>
        <head>
            <title>Exam Marks Report</title>
            <style>
                @page { size: A4 landscape; margin: 6mm; }
                * { box-sizing: border-box; }
                body { margin: 0; padding: 10px; background: #fff; font-family: Arial, sans-serif; color: #000; }
                h3 { text-align: center; margin: 0 0 10px 0; font-size: 16px; }
                .teacher-info { margin-bottom: 10px; font-weight: bold; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; font-size: 11px; }
                th, td { border: 1px solid #000; padding: 5px 8px; text-align: center; }
                th { background-color: #f2f2f2 !important; font-weight: bold; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                tr { page-break-inside: avoid; break-inside: avoid; }
            </style>
        </head>
        <body>
            <h3>Exam Marks Report</h3>
            <div class="teacher-info">${teacherInfo}</div>
            ${tableContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.onload = function() {
        setTimeout(function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }, 400);
    };
}
</script>

@endsection