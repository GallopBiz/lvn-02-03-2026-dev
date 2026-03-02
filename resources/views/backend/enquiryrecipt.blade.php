@extends('backend.layouts.main')
@section('main-container')

<style>
.form_section1_div {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
    border: 1px solid #e4e0e0;
    padding: 15px 20px;
    box-sizing: border-box;
    background: #fff;
}

img.school-logo {
    display: block;
    max-width: 250px;
    margin: 0 auto 5px;
    height: auto;
	margin-bottom:-10px;
}

.student-info-print {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.student-left, .student-right {
    width: 48%;
}

.red-underline {
    border-bottom: 2px solid red;
    margin: 8px 0;
}

/* Print Styles */
@media print {
    body {
        margin: 0;
        padding: 0;
    }

    .btn, .btn-danger {
        display: none !important;
    }

    body * {
        visibility: hidden;
    }

    #printme, #printme * {
        visibility: visible;
    }

    #printme {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: auto; /* allow content height */
        max-height: 148mm; /* half of A4 height (297mm/2) */
        padding: 8mm 10mm; /* smaller print margins */
        box-sizing: border-box;
        border: 2px solid #000;
        background: #fff;
        overflow: hidden;
    }

    .school-logo {
        max-width: 180px;
        margin-bottom: 5px;
    }

    .student-info-print {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .student-left, .student-right {
        width: 48%;
        float: left;
    }

    .student-left {
        text-align: left;
    }

    .student-right {
        text-align: right;
    }

    h5 {
        font-size: 12px !important;
        margin: 2px 0;
        line-height: 1.1;
    }

    .table {
        margin-bottom: 0;
        border-collapse: collapse;
        width: 100%;
        font-size: 12px !important;
    }

    .table th, .table td {
        padding: 4px 6px !important;
        border: 1px solid #000 !important;
    }

    .red-underline {
        margin-top: 4px;
        margin-bottom: 8px;
    }
}
</style>

<div class="main-content">
    

    <div class="form_section1_div" id="printme">
        <div class="row">
            @if(!empty($all_inquiry))
                @foreach($all_inquiry as $each_inq)
                    <?php $notificationData1 = json_decode($each_inq->json_str, true); ?>

                    <!-- Header and Logo -->
                    <div class="col-md-12 mb-2 text-center">
                        <img src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="School Logo" class="school-logo"><br>
                        <div style="font-size: 10px; line-height: 1.3;">
                            <strong>KESAR BAGH ROAD, LOKMANYA NAGAR, INDORE (M.P.)</strong><br>
                            Phone: 0731-2360169, 4651250-8 | Email: lvnschoolofficial@gmail.com<br>
                            Website: www.lvnindore.org<br>
                            <strong>C.B.S.E. Affiliation No.1030429</strong>
                        </div>
                        <div class="red-underline"></div>
                    </div>

                    <!-- Student Info -->
                   <div class="col-md-12 student-info-print">
						<div class="student-left">
							<h5 style="font-size: 12px; margin: 4px;">
								<strong>Student Name:</strong> {{ ucwords(strtolower($each_inq->student_name ?? '')) }}
							</h5>
							<h5 style="font-size: 12px; margin: 4px;">
								<strong>Father Name:</strong> {{ $notificationData1['fathername_prefix'] ?? '' }}. {{ ucwords(strtolower($notificationData1['fathername'] ?? '')) }}
							</h5>
							<h5 style="font-size: 12px; margin: 4px;">
								<strong>Class:</strong> {{ $each_inq->class_name ?? '' }}
							</h5>
						</div>
						<div class="student-right" style="text-align:right !important;">
							<h5 style="font-size: 12px; margin: 4px;">
								<strong>Session:</strong> {{ $each_inq->session_name ?? '' }}
							</h5>
							<h5 style="font-size: 12px; margin: 4px;">
								<strong>Receipt No.:</strong> {{ $each_inq->form_number ?? '' }}
							</h5>
							<h5 style="font-size: 12px; margin: 4px;">
								<strong>Date:</strong> {{ !empty($each_inq->created_at) ? date('d-m-Y', strtotime($each_inq->created_at)) : '' }}
							</h5>
						</div>
					</div>
					<!-- Fee Table -->
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Particular</th>
                                    <th style="text-align:right;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Admission Form</td>
                                    <td style="text-align:right;">&#x20B9;500</td>
                                </tr>
                                <tr>
                                    <td class="text-end" style="text-align:left !important;"><strong>Grand Total</strong></td>
                                    <td style="text-align:right;"><strong>&#x20B9;500</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end" style="padding-top:20px;">
                                        .........................................<br>
                                        <strong>Approved by: Name</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                @endforeach
            @endif
        </div>
    </div>
	<div class="text-center">
		<button class="btn btn-sm btn-success" onclick="window.print()" style="text-align:center;">Print Receipt</button>
	</div>
</div>

@endsection
