@extends('backend.layouts.main')
@section('main-container')
    @if (!empty($data))
        <style>
            .bordered-table {
                border-collapse: collapse;
                width: 100%;
            }

            .bordered-table th,
            .bordered-table td {
                border: 1px solid #dddddd;
                padding: 8px;
                text-align: left;
            }

            .bordered-table tr:nth-child(even) {
                background-color: #f2f2f2;
            }

            .table-container {
                position: relative;
            }

            .search-container {
                position: absolute;
                top: 0;
                right: 0;
                margin: 10px;
            }
        </style>

        <form action="{{ url('save-due-chart') }}" method="post">
            @csrf
            <?php
            echo "<input type='hidden' name='classname' value='" . $info['id'] . "'>";
            echo "<input type='hidden' name='secationname1' value='" . $info['session'] . "'>";
            ?>
            <input type="submit" id="backbtn" class="previous btn btn-primary col-1 mb-2 mt-2 d-none" value="Back">
        </form>

        

        <div class="col-md-12">
            <div class="d-flex justify-content-between">
                <label for="backbtn" class="previous btn btn-primary col-1 mb-2 mt-2 text-white">
                    Back
                </label>
                <div class="row justify-content-end">
                    <div class="col-md-8">
                        <input type="text" name="search_term" id="studentSearch" class="form-control custom-search-class"
                            placeholder="Search student">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
                    </div>
                </div>
            </div>

        </div>


        <div class="row mt-4">
            <div class="col-12">

                {{-- <table class="display table table-striped table-bordered col-1 mt-4" id="zero_configuration_table" style="width: 100%">
            <!-- Table content here -->
        </table> --}}

                <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                    <thead>
                        <tr>
                            <th>SNO</th>
                            <th>Student Id</th>
                            <th>Student Name</th>
                            <th>Class Name</th>
                            {{-- <th>Section Name</th> --}}
                            <th>Session Name</th>
                            <th>Amount</th>
                            <th>json_str</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php $index=1; foreach($data as $obj){ ?>
                        <tr>
                            <td><?php print_r($index); ?></td>
                            <td><?php print_r($obj['student_id']); ?></td>
                            <td><?php print_r($obj['student_name']); ?></td>
                            <td><?php print_r($obj['class_name']); ?></td>
                            <td><?php print_r($obj['session_name']); ?></td>
                            <td><?php print_r($obj['amount']); ?></td>
                            <td><?php 
                $arr = json_decode($obj['json_str'],1);               
                foreach($arr as $str){
                    // echo $str['account_name'];
                    $arr1 = json_decode($str['json_str'],1);                    
                    $k=0; ?>
                                <table class="bordered-table">
                                    <tr>
                                        <th>Date</th>
                                        <th>Struct</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Term</th>
                                    </tr>


                                    <?php //$numRows = count($arr1['fees_date']);
                                    ?>

                                    <?php   if(!empty($arr1['fees_date'])) {

                        // print_r(count($arr1['fees_date']));?>

                                    <?php for ($i = 0; $i < count($arr1['fees_date']); $i++) : ?>
                                    <tr>
                                        <td><?php echo date('d-m-Y', strtotime($arr1['fees_date'][$i])); ?></td>
                                        <td><?php echo $arr1['account_name'][$i]; ?></td>
                                        <td><?php echo $arr1['fees'][$i]; ?></td>
                                        {{-- <td><//?php echo $arr1['due_date'][$i]; ?></td> --}}
                                        <td><?php echo date('d-m-Y', strtotime($arr1['due_date'][$i])); ?></td>

                                        <td><?php echo $arr1['term'][$i]; ?></td>
                                    </tr>
                                    <?php endfor; ?>


                                    <?php



                  
                  }  
                
                  ?>



                                    <?php 
                   echo '</table>'; 
                    
                }
                //print_r($obj['json_str']); 
            
            ?>

                            </td>
                        </tr>
                        <?php 
    // if($index==610){
    //     die();
    // }
    $index++; 
    } 
    ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>SNO</th>
                            <th>Student Id</th>
                            <th>Student Name</th>
                            <th>Class Name</th>
                            {{-- <th>Section Name</th> --}}
                            <th>Session Name</th>
                            <th>Amount</th>
                            <th>json_str</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
@endsection
