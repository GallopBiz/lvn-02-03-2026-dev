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
        <div class="form_section1_div">
            <div class="breadcrumb">
                <h1 class="me-2">Employee Basic Deduction</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>
            @if (!empty($stream_master))
                <form id="progress-form" class="p-4 progress-form" action="{{ url('store-deduction') }}" method="post">
                    <input type="hidden"
                        @if (!empty($stream_master)) @foreach ($stream_master as $streammaster)
                            value=" {{ $streammaster->id }}"
                            @endforeach
                        @else
                            value="" @endif
                        name="id">
                @else
                    <form id="progress-form" class="p-4 progress-form" action="{{ url('save-deduction') }}" method="post">
            @endif

            @csrf
            <div class="row">


                <div class="col-md-3 form-group mb-3">
                    <label for="name">Name</label>
                    <input class="form-control uperletter" id="name" name="name" type="text"
                        @if (!empty($stream_master)) @foreach ($stream_master as $streammaster)
                      value=" {{ $streammaster->name }}"
                    @endforeach
                  @else
                    value="" @endif
                        placeholder="Name" />
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="amount">Amount</label>
                    <input class="form-control" id="amount" name="amount" type="text"
                        @if (!empty($stream_master)) @foreach ($stream_master as $streammaster)
                      value="{{ $streammaster->amount }}"
                    @endforeach
                  @else
                    value="" @endif
                        placeholder="Amount"   onchange="validateAmount(this)"/>
                    @error('amount')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    <span  class="text-danger" id="amountError"></span>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"
                        placeholder="Description">{{ $streammaster->description ?? '' }}</textarea>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn"
                        value="Reset Form">Reset</button>

                    @if (request()->route()->getName() !== 'basicDeduction')
                        <a href="{{ url('basicDeduction') }}" class="btn btn-primary">Add New</a>
                    @endif

                </div>
            </div>
            </form>
            <br>
        </div>

        <div class="separator-breadcrumb border-top"></div>
        <div class="row">


            <div class="col-md-12 mb-4">
                <div class="breadcrumb d-flex justify-content-between">
                    <h1 class="me-2">List of Basic Deductions :-</h1>
                    <a href="{{ route('deductions.manage') }}" class="btn btn-primary">
                        Manage Employee Deduction
                    </a>
                </div>
                
                <div class="separator-breadcrumb border-top"></div>

                <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Action </th>
                                        <!-- <th>Section</th>
                                            <th>Class Strength</th> -->
                                    </tr>
                                </thead>
                                <tbody>


                                    @if (!empty($stream))
                                        @foreach ($stream as $streams)
                                            <tr>
                                                <td>{{ ++$i }}</td>
                                                <td class= "uperletter">{{ $streams->name }}</td>
                                                {{-- <td>{{$streams->HolidayDate}}</td>  --}}
                                                <td>{{ $streams->amount }}</td>
                                                <td>{{$streams->description}}</td>
                                                <td class='d-flex'>
                                                    <a class="btn btn-primary m-1"
                                                        href="{{ url('view-deduction') . '/' . $streams->id }}">Edit</a>
                                                    @csrf
                                                    <a class="btn btn-raised ripple btn-danger m-1"
                                                        href="{{ url('delete-deduction') . '/' . $streams->id }}"
                                                        onclick="confirmDelete(event)">Delete</a>
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
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If the user clicks on "Yes, delete it!", navigate to the delete URL
                    window.location.href = event.target.href;
                }
            });
        }
        function validateAmount(input) {
          
            
        // Allow only numbers, including decimal points (e.g. 123, 12.34, 0.56)
        const regex = /^[0-9]*(\.[0-9]{0,2})?$/;
        const value = input.value;
        if (!regex.test(value)) {
            console.log(">>>>>>>");
            // If the value doesn't match the regex, remove the last character
            $("#amountError").text("Please enter a valid number (int or decimal).");
            input.setCustomValidity("Please enter a valid number (int or decimal).");
        } else {
            console.log("<<<");
            $("#amountError").text("");
            input.setCustomValidity(""); // Reset validity
        }
    }
        document.addEventListener('DOMContentLoaded', function() {
            $("#reset").on("click", function() {
                $("#name").val("");
                $("#amount").val("");
                $("#description").val("");
            });

        })
    </script>



@endsection
