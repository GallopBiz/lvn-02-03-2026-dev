@extends('backend.layouts.main')


@section('main-container')

<style type="text/css">
    .validation_err {
        color: red !important;
    }

    input[type="number"] {
        appearance: textfield;
        -webkit-appearance: textfield;
        -moz-appearance: textfield;
    }

    input {
        position: relative;
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

    tr.disabled {
        background-color: #8080806b;
    }

    .badge {
        display: inline-block;
        min-width: 10px;
        padding: 3px 7px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        background-color: #777;
        border-radius: 10px;
    }

</style>




<meta name="csrf-token" content="{{ csrf_token() }}" />

<style type="text/css">
    /* Define your mark sheet layout */
    .mark-sheet {
        width: 210mm;
        /* A4 width */
        height: 297mm;
        /* A4 height */
        margin: 0 auto;
        background-color: #fff;
        padding: 10mm;
        font-family: Arial, sans-serif;
        page-break-before: always;
    }

    .header {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
    }

    .student-details {
        margin-top: 20px;
        text-align: center;
    }

    .main-subject {
        margin-top: 20px;
        border-collapse: collapse;
        width: 100%;
        border:1px solid gray;
    }

    /* .main-subject th,
    .main-subject td {
        border: 1px solid #000;
        padding: 8px;
    } */

    /* .main-subject th {
        background-color: #ccc;
    } */

   
.main-subject tr {
  border-left: 1px solid gray;
  border-right: 1px solid gray;
}

.main-subject td {
    border-left: 1px solid gray;
    border-right: 1px solid gray;    
}

.main-subject th {
    border-left: 1px solid gray;
    border-right: 1px solid gray;
    border-bottom: .5px solid gray;    

}

    .page-footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        text-align: center;
    }

    .fold-line {
        border-top: 1px dashed #000;
        width: 100%;
        margin-top: 10mm;
        margin-bottom: 10mm;
        page-break-before: always;
    }

    .passport-photo {
        width: 50%;
        float: left;
        text-align: center;
    }

    .student-details-text {
        width: 50%;
        float: left;
    }

</style>


<style>
    .box {
        border: 1px solid #000;
        padding: 10px;
        margin-bottom: 20px;
    }

    .box-heading {
        font-weight: bold;
    }

    .sub-heading {
        margin-top: 10px;
        font-weight: bold;
    }

    .main-sub-heading {
        font-weight: bold;
        text-align: center;
        margin-top: 10px;
    }

</style>

<!-- Your existing HTML code remains the same -->

<style>
    .box {
        border: 1px solid #000;
        width: 100px;
        /* Adjust width as needed */
        height: 100px;
        /* Adjust height as needed */
        margin-bottom: 20px;
    }

    .box-heading {
        font-weight: bold;
    }

    .sub-heading {
        margin-top: 5px;
        font-weight: bold;
    }

    .main-sub-heading {
        font-weight: bold;
        text-align: center;
        margin-top: 5px;
    }

    /* .page {
  width: 29.7cm;
  height: 21cm;  */

  /* width: 21cm;
  min-height: 29.7cm; */

  /* padding: 2cm;
  margin: 1cm auto;
  border: 1px #D3D3D3 solid;
  border-radius: 5px;
  background: white;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
} */

/* .subpage {
  padding: 1cm;
  border: 5px red solid;
  
  height: 256mm;

  width: 256mm;
  outline: 2cm #FFEAEA solid;
} */

@page {
  size: A4;
  margin: 0;
}

@media print {
  .page {
    margin: 0;
    border: initial;
    border-radius: initial;
    width: initial;
    min-height: initial;
    box-shadow: initial;
    background: initial;
    page-break-after: always;
  }

  .square-image {
    width: 100px;
    height: 100px;
    overflow: hidden;
}
.square-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border: 1px solid #000; /* Optional, adds a border for visual clarity */
}



}

</style>


<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content pt-4">

    <div class="breadcrumb">
        <h2>Marksheet Information</h2>
    </div>
    <div class="separator-breadcrumb border-top"></div>

   <div class="page">
    <div class="subpage">

   
    <div class="row col-12 p-2">
        <div class="col-6">
            <div class="col-11 row ">
                {{-- <h1>box 1</h1> --}}

                <div class="col-8">
               
@foreach($subjectCombinations as $combination)
<div class="col-11">
    {{-- <h1>{{ $combination->combination_name }}</h1> --}}

    <table class="main-subject">
        <tr>
            <th>{{ $combination->combination_name }}</th>
            {{-- <th>{{ $combination->subject }}</th> --}}
            <th>T-1</th>
            <th>T-2</th>
        </tr>

        {{-- @foreach(json_decode($combination->selected_subjects_data) as $subjectData) --}}
        @foreach(json_decode($combination->selected_subjects_data) ?? [] as $subjectData)

        <tr>
            <td>{{ $subjectData->subject }}</td>
            <td>{{ $subjectData->order === '1' ? 'A' : '' }}</td>
            <td>{{ $subjectData->order === '2' ? 'A+' : '' }}</td>
        </tr>
        @endforeach
    </table>
</div>
@endforeach

               
               
 {{-- <div class="col-8">

                    <table class="main-subject">
                        <tr>
                            <th>English</th>
                            <th>T-1</th>
                            <th>T-2</th>
                        </tr>

                        @foreach($englishData as $data)
                        <tr>
                            <td>{{ $data['subject'] }}</td>
                            <td>{{ $data['t1'] }}</td>
                            <td>{{ $data['t2'] }}</td>
                        </tr>
                    @endforeach --}}


                        {{-- <tr>
                            <td>*abc 1</td>
                            <td>A+</td>
                            <td>B</td>
                        </tr> --}}
                        {{-- <tr>
                            <td>*abc 2</td>
                            <td>A++</td>
                            <td>B+</td>
                        </tr>
                        <tr>
                            <td>*abc 3</td>
                            <td>A+</td>
                            <td>C</td>
    
                        </tr>
                        <tr>
                            <td>*abc 4</td>
                            <td>A+</td>
                            <td>B</td> --}}
                        {{-- </tr> --}}
                        <!-- Add subject and grade data here -->
                        {{-- <tr>
                            <td>Main Subject 1</td>
                            <td>Sub Subject 1.1</td>
                            <td>A+</td>
                        </tr> --}}
                        <!-- Add more subjects and grades as needed -->
                    {{-- </table> --}}


                   
                </div>

                <div class="col-4">
                    
                    <table class="table-bordered col-12 w-100 mt-4">
                        <tr>
                            <th class="text-center d-flex justify-content-center">General Remark</th>                            
                        </tr>         
                        <tr>
                            <td class="d-flex justify-content-center" style="height: 5rem">Term-I</td>
                        </tr>               
                        <tr>
                            <td class="d-flex justify-content-center" style="height: 5rem">Term-II</td>
                        </tr>                                                                                     
                    </table>       
                    
                    <table class="table-bordered col-12 w-100 mt-4">
                        <tr>
                            <td  colspan="2" class="text-center">Attendence</td>                                                                                   
                        </tr>         
                        <tr>
                            <th class="text-center">Term-I</th>                            
                            <th class="text-center">Term-II</th>                            
                        </tr>         
                        <tr class="text-center">
                          <td style="height: 2rem;">27 / 114</td>
                          <td style="height: 2rem;">69 / 239</td>
                        </tr>                                                                                                          
                    </table>       


                    <table class="table-bordered col-12 w-100 mt-4">
                        <tr>
                            <td  colspan="2" class="text-center">Overall Grade</td>                                                                                   
                        </tr>         
                        <tr>
                            <th class="text-center">Term-I</th>                            
                            <th class="text-center">Term-II</th>                            
                        </tr>         
                        <tr class="text-center">
                          <td style="height: 2rem;">A</td>
                          <td style="height: 2rem;">B</td>
                        </tr>                                                                                                          
                    </table>       

                </div>

            
                <div class="row" >
                    <div class="col mt-4">
                        <p>Class Teacher Name & Signature</p>
                    </div>
                    <div class="col mt-4">
                        <p>Parent's Signature</p>
                    </div>
                    <div class="col mt-4 ">
                        <p>Principal Name & Signature</p>
                    </div>

                    <P class="mt-4">Congratulations...!! Promoted to class</P>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="col-12 border" style="height: 100%">

                {{-- <h1>box 2</h1> --}}
                <div class="">
                    <div class="header">
                        <img src="http://localhost/lvn-school/public/assets/backend/images/header-logo.png" alt="School Logo"  style="width: 100px; height: auto; margin-top: 10px;">
                        <h1>School Name</h1>
                        <p>School Address</p>
                        <p>Email Address</p>
                    </div>
                    <div class="text-center">
                        <div class="square-image">
                            <img src="http://localhost/lvn-school/public/assets/backend/images/student.png" alt="Student Photo" style="width: 100px; height: 100px;">
                        </div>
                        <div class="d-block mt-4" style="margin-left: 0rem">
                            <p>T-II Examination</p>
                            <p>Session: 2023-2024</p>

                        </div>
                
                    </div>

                   
                    <div style="margin-left: 13rem">
                        <table>
                            <tbody>
                                <tr>
                                    <td>Scholar Number : </td>
                                    <td>123456</td>
                                </tr>
                                <tr>
                                    <td>Student Name : </td>
                                    <td>John Doe</td>
                                </tr>
                                <tr>
                                    <td>Date of Birth : </td>
                                    <td>01/01/2000</td>
                                </tr>

                                <tr>
                                    <td>Class : </td>
                                    <td>02</td>
                                </tr>


                                <tr>
                                    <td>Father's Name : </td>
                                    <td>Jack Doe</td>
                                </tr>
                                <tr>
                                    <td>Mother's Name : </td>
                                    <td>Ani Deo</td>
                                </tr>
                                <tr>
                                    <td>Address : </td>
                                    <td>US</td>
                                </tr>
                                <tr>
                                    <td>Contact : </td>
                                    <td>998877554422</td>
                                </tr>
                            </tbody>
                        </table>
    
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
</div>

    <hr>

    <div class="row col-12 p-2">
        <div class="col-6">
            <div class="col-11 ">
                {{-- <h1>box 3</h1> --}}
                
                <div class="row" >
                <div class="col mt-4 ">
                    <p>Scholer no: <u>12345</u></p>
                </div>
                <div class="col mt-4 ">
                    <p>Class: <u>Kg1-A</u></p>
                </div>
                </div>
                @foreach($subjectCombinations as $combination)
                <div class="col-11 ">
                    {{-- <h1>{{ $combination->combination_name }}</h1> --}}
                
                    <table class="main-subject">
                        <tr>
                            <th>{{ $combination->combination_name }}</th>
                            {{-- <th>{{ $combination->subject }}</th> --}}
                            <th>T-1</th>
                            <th>T-2</th>
                        </tr>
                
                        {{-- @foreach(json_decode($combination->selected_subjects_data) as $subjectData) --}}
                        @foreach(json_decode($combination->selected_subjects_data) ?? [] as $subjectData)

                        <tr>
                            <td>{{ $subjectData->subject }}</td>
                            <td>{{ $subjectData->order === '1' ? 'A' : '' }}</td>
                            <td>{{ $subjectData->order === '2' ? 'A+' : '' }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endforeach
            
            </div>
        </div>
        <div class="col-6">
            <div class="col-12 ">
                {{-- <h1>box 4</h1> --}}
                
                <div class="row" >
                    <div class="col mt-4 ">
                        <p>Scholer's Name: <u>divya sharma</u></p>
                    </div>
                </div>
                {{-- <h5>Scholer's Name: divya sharma</h5> --}}

              @foreach($subjectCombinations as $combination)
            <div class="col-11 ">
             {{-- <h1>{{ $combination->combination_name }}</h1> --}}

    <table class="main-subject">
        <tr>
            <th>{{ $combination->combination_name }}</th>
            {{-- <th>{{ $combination->subject }}</th> --}}
            <th>T-1</th>
            <th>T-2</th>
        </tr>

        {{-- @foreach(json_decode($combination->selected_subjects_data) as $subjectData) --}}
        @foreach(json_decode($combination->selected_subjects_data) ?? [] as $subjectData)

        <tr>
            <td>{{ $subjectData->subject }}</td>
            <td>{{ $subjectData->order === '1' ? 'A' : '' }}</td>
            <td>{{ $subjectData->order === '2' ? 'A+' : '' }}</td>
        </tr>
        @endforeach
    </table>
</div>
@endforeach










            </div>
        </div>
    </div>


</div>


@endsection
