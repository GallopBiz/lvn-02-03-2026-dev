@extends('backend.layouts.main')
@section('main-container')

<style>
  .uperletter {
    text-transform: capitalize;
  }
  .float-end {
    float: right;
  }
  .pending {
    color: red !important;
  }
  .paid {
    color: green !important;
  }
  .failed {
    color: orange !important;
  }
  table {
    border-collapse: collapse;
    width: 100%;
  }
  
  th, td {
    padding: 8px;
    text-align: left;
  }
  .json-table td {
    word-break: break-word;
  }
</style>

<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Daily Collection</h1>
        </div>

        <div class="separator-breadcrumb border-top"></div>

        <!-- Display error message if there is an error -->
        @if($error)
            <div class="alert alert-danger">
                {{ $error }}
            </div>
        @endif

        <!-- Filter Form -->
        <form id="filter-form" class="p-4" action="{{ url('student-online-fees-pending-payment') }}" method="GET">
            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="from_date">From Date</label>
                    <input class="form-control" id="from_date" name="from_date" type="date" value="{{ request('from_date') }}" placeholder="From Date">
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="to_date">To Date</label>
                    <input class="form-control" id="to_date" name="to_date" type="date" value="{{ request('to_date') }}" placeholder="To Date">
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label>&nbsp;</label>
                    <div>
                        <!-- Search button -->
                        <button type="submit" class="btn btn-primary" name="search" value="search">Search</button>
                    </div>
                </div>
            </div>
        </form>

        <br>
    </div>

    <div class="separator-breadcrumb border-top"></div>
    @if(!is_null($payments))
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">List of Payments</h1>
            </div>

            <div class="separator-breadcrumb border-top"></div>

            <div class="card text-start">
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- Only show the Export CSV button if there is data in the $payments variable -->
                        @if(!$error && $payments && count($payments) > 0)
                            <div class="form-group mb-3 float-end">
                                <a href="{{ route('student-online-fees.export'). '?' . http_build_query(request()->query()) }}" class="btn btn-success">Export CSV</a>
                            </div>
                        @endif

                        @if(!$error) <!-- Only display table if there is no error -->
                            <table class="display table table-striped table-bordered" id="payments_table" style="width: 100%">
                                <thead>
									<tr>
										<th>Sr.</th>
										<th>Student Name</th>
										<th>Scholar No.</th>
										<th>Class Name</th>
										<th>Contact</th>
										<th>Email</th>
										<th>Payment Received</th>
										<th>Fee</th>
										<th>Tax (charges)</th>
										<th>Payment Date</th>
										<th>Status</th>
										<th>Captured</th>
										<th>Method</th>
										<th>Type</th>
										<th>Issuer</th>
										<th>Notes</th>
										<th>Payment Gateway Transaction Id</th>
										<th>Transaction No.</th>
										<th>Description</th>
									</tr>
								</thead>
								<tbody>
									@php $i = 1; @endphp
									@forelse($payments as $payment)
										@php
											$date = "";
											if(!is_null($payment->payment_date)){
												$date = date('d-m-Y', strtotime($payment->payment_date));
											}

											// Check if razorpay_details exists and decode it
											$razorpayDetails = isset($payment->razorpay_status) ? json_decode($payment->razorpay_status, true) : null;
                                            // print_r($razorpayDetails);
                                            $payStatus = isset($razorpayDetails['status']) ? strtoupper($razorpayDetails['status']) : $payment->status;
                                            if($payStatus=='CAPTURED'){
                                                $payStatus = "PAID";
                                            }
                                        @endphp
                                        <tr>
											<td>{{ $i++ }}</td>
											<td>{{ $payment->student_name }}</td>
											<td>{{ $payment->scholar_no }}</td>
											<td>{{ $payment->class_name }}</td>
											<td>{{ $razorpayDetails['contact'] ?? 'N/A' }}</td>
											<td>{{ $razorpayDetails['email'] ?? 'N/A' }}</td>
											<td>{{ number_format($payment->allocated_amount, 0) }}</td> <!-- Format to remove .00 -->
											<td>{{ isset($razorpayDetails['fee']) ? number_format($razorpayDetails['fee'] / 100, 2) : 'N/A' }}</td> <!-- Fee is in paise -->
											<td>{{ $razorpayDetails['tax'] ?? 'N/A' }}</td>
											<td>{{ $date }}</td>
											<td class="{{ $payment->status }}">{{ $payStatus }}</td>
											<td>{{ $razorpayDetails['captured'] ?? 'N/A' }}</td>
											<td>{{ $razorpayDetails['method'] ?? 'N/A' }}</td>
											<td>{{ $razorpayDetails['card']['type'] ?? 'N/A' }}</td>
											<td>{{ $razorpayDetails['card']['issuer'] ?? 'N/A' }}</td>
											<td>{{ $razorpayDetails['notes']['transaction_id'] ?? 'N/A' }}</td>
											<td>{{ $payment->payment_gateway_transaction_id }}</td>
											<td>{{ $payment->transaction_id }}</td>
											<td>{{ $razorpayDetails['description'] ?? 'N/A' }}</td>
										</tr>
									@empty
										<tr><td colspan="20" class="text-center">No Data Found</td></tr>
									@endforelse
								</tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
