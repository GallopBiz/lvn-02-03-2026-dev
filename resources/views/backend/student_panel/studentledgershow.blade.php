@extends('backend.layouts.main')
@section('main-container')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css"/> 

<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content">
    <div class="main-content">          
        <div class="breadcrumb">
            <h1>Student ledger :-</h1>
            <ul></ul>
        </div>
        <div class="separator-breadcrumb border-top"></div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>S. NO.</th>
                                        <th>Entry Date</th>
                                        <th>Ammount Dr</th>
                                        <th>Ammount Cr</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $total_dueamount_ = 0; $by_cash_ = 0; @endphp

                                    @if(!empty($challan_data))
                                        @php $sno = 1; @endphp
                                        @foreach($challan_data as $object)
                                            @php 
                                                $data = json_decode($object->str_json, true);
                                                $head_name = explode(',', $data['head_name']);
                                                $head_due_amount = explode(',', $data['head_due_amount']);
                                                $by_cash = $data['by_cash'];
                                                $total_dueamount = $data['grand_total_due'];
                                                $payment_by_select = $data['payment_by_select'];
                                                $remarks = [];
                                                for ($i = 0; $i < count($head_name); $i++) {
                                                    $remarks[] = $head_name[$i] . ' ( ' . $head_due_amount[$i] . ' )';
                                                }
                                            @endphp

                                            {{-- Dr amount row: no receipt shown --}}
                                            <tr>
                                                <td>{{ $sno++ }}</td>
                                                <td>{{ \Carbon\Carbon::parse($object->created_at)->format('d/m/Y') }}</td>
                                                <td>{{ $by_cash }}</td>
                                                <td>0</td>
                                                <td>{{ $payment_by_select }}</td>
                                                <td></td>
                                            </tr>

                                            {{-- Cr amount row: show receipt / no receipt --}}
                                            <tr>
                                                <td>{{ $sno++ }}</td>
                                                <td>{{ \Carbon\Carbon::parse($object->created_at)->format('d/m/Y') }}</td>
                                                <td>0</td>
                                                <td>{{ $total_dueamount }}</td>
                                                <td>{{ implode(' / ', $remarks) }} etc</td>
                                                <td>
                                                    @if(!empty($data['challan_id']))
                                                        <a href="https://school.lvnindore.org.in/thank-you?txn_id={{ $data['challan_id'] }}" 
                                                           class="btn btn-primary btn-sm" target="_blank">View Receipt</a>
                                                    @else
                                                        <span class="text-muted">No Receipt</span>
                                                    @endif
                                                </td>
                                            </tr>

                                            @php 
                                                $by_cash_ += $by_cash;
                                                $total_dueamount_ += $total_dueamount; 
                                            @endphp
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">No Data Found</td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th>{{ $by_cash_ }}</th>
                                        <th>{{ $total_dueamount_ }}</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.full.min.js"></script>
<script type="text/javascript">
   $.noConflict();
   jQuery(document).ready(function($){
       $("#search_student").select2();
   });
</script>

<script>
$(document).ready(function() {
    $("#search_student").trigger("change");
    setTimeout(function() {
        $("input[type='search']").attr("placeholder", "Search here by student details");
    }, 100);
});

function select_data(ele){
    student_id = ele.value;
    $('.student_id').val(student_id);
    $.ajax({
        type : "POST",
        data : {student_id:student_id},
        url : "{{ url('get_student_info') }}",
        headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        dataType : 'json',
        success: function(data){
            $("input[name='student_name']").val(data.student_name);
            $("input[name='class']").val(data.class_name);
            $("input[name='enrollment_no']").val(data.formno);
            $("input[name='father_name']").val(data.fathername);
        }
    });
}
</script>

@endsection
