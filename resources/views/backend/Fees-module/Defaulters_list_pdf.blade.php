
    @php
        $i = 0;
    @endphp
    <div class="main-content">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
                            <h3 class="ml-4">Lokmanya Vidhya Niketan</h3>
                            <h4 class="ml-4">Defaulter List Summary Report As On  {{ now()->format('d-M-Y') }}</h4>
                            <table class="display table table-striped table-bordered" id="zero_configuration_table"
                                style="width: 100%ss">
                                
                                <thead style="border-top:2px solid black; border-bottom:2px solid black;">
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Scholar No.</th>
                                        <th>Enrollment No.</th>
                                        <th>Student Name</th>
                                        <th>Class Name</th>
                                        <th>Section</th>
                                        <th>Account Name</th>
                                        <th>Balance Amount</th>
                                        <th>Min Date</th>
                                        <th>Max Date</th>
                                        <th>Student</th>
                                    </tr>
                                </thead>
                            
                                <tbody>

                                    @if (!empty($teachersubjects))
                                    @foreach ($teachersubjects as $teachersubject)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>{{ $teachersubject->scholar_no }}</td>                                                
                                            <td>{{ $teachersubject->enrollment_no }}</td>
                                            <td>{{ $teachersubject->student_name }}</td>
                                            <td>{{ $teachersubject->class_name }}</td>
                                            <td>{{ $teachersubject->section_name }}</td>
                                            <td>{{ $teachersubject->account_name }}</td>
                                            <td>{{ $teachersubject->balance_amount }}</td>
                                            <td>{{ date("d-m-Y", strtotime($teachersubject->min_date))}}</td>
                                            <td>{{ date("d-m-Y", strtotime($teachersubject->max_date)) }}</td>
                                            <td>{{ $teachersubject->student }}</td>

                                      </tr>
                                        @endforeach
            
                                    @endif
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end of main-content -->
    </div>
    

