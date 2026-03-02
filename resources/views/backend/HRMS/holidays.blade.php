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
                <h1 class="me-2">Holidays</h1>
            </div>

            {{-- <div class="separator-breadcrumb border-top"></div>
                  <form id="progress-form" class="p-4 progress-form" action="#"  novalidate method=""> --}}

            <div class="separator-breadcrumb border-top"></div>
            @if (!empty($stream_master))
                <form id="progress-form" class="p-4 progress-form" action="{{ url('store-holidays') }}" method="post">
                    <input type="hidden"
                        @if (!empty($stream_master)) @foreach ($stream_master as $streammaster)
                            value=" {{ $streammaster->HolidayID }}"
                            @endforeach
                        @else
                            value="" @endif
                        name="id">
                @else
                    <form id="progress-form" class="p-4 progress-form" action="{{ url('save-holidays') }}" method="post">
            @endif

            @csrf
            <div class="row">


                <div class="col-md-3 form-group mb-3">
                    <label for="HolidayName"> HolidayName</label>
                    <input class="form-control uperletter" id="HolidayName" name="HolidayName" type="text"
                        @if (!empty($stream_master)) @foreach ($stream_master as $streammaster)
                      value=" {{ $streammaster->HolidayName }}"
                    @endforeach
                  @else
                    value="" @endif
                        placeholder="HolidayName" />
                    @error('HolidayName')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="HolidayStartDate">Holiday Start Date</label>
                    <input class="form-control" id="HolidayStartDate" name="HolidayStartDate" type="date"
                        @if (!empty($stream_master)) @foreach ($stream_master as $streammaster)
                      value="{{ $streammaster->HolidayStartDate }}"
                    @endforeach
                  @else
                    value="" @endif
                        placeholder="Holiday Start Date" />
                    @error('HolidayStartDate')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="HolidayEndDate">Holiday End Date</label>
                    <input class="form-control" id="HolidayEndDate" name="HolidayEndDate" type="date"
                        @if (!empty($stream_master)) @foreach ($stream_master as $streammaster)
                      value="{{ $streammaster->HolidayEndDate }}"
                    @endforeach
                  @else
                    value="" @endif
                        placeholder="Holiday End Date" />
                    @error('HolidayEndDate')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="HolidayDescription">Holiday Description</label>
                    <textarea class="form-control" id="HolidayDescription" name="HolidayDescription" rows="3"
                        placeholder="Holiday Description">{{ $streammaster->HolidayDescription ?? '' }}</textarea>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn"
                        value="Reset Form">Reset</button>

                    @if (request()->route()->getName() !== 'holidays')
                        <a href="{{ url('holidays') }}" class="btn btn-primary">Add New</a>
                    @endif

                </div>
            </div>
            </form>
            <br>
        </div>

        <div class="separator-breadcrumb border-top"></div>
        <div class="row">


            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">List of Saved Holidays :-</h1>
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
                                        <th>Holiday Name</th>
                                        <th>Holiday Start Date</th>
                                        <th>Holiday End Date</th>
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
                                                <td class= "uperletter">{{ $streams->HolidayName }}</td>
                                                {{-- <td>{{$streams->HolidayDate}}</td>  --}}
                                                <td>{{ date('d-m-Y', strtotime($streams->HolidayStartDate)) }}</td>
                                                <td>{{ date('d-m-Y', strtotime($streams->HolidayEndDate)) }}</td>
                                                <td>{{ $streams->HolidayDescription }}</td>
                                                <td class='d-flex'>
                                                    <a class="btn btn-primary m-1"
                                                        href="{{ url('view-holidays') . '/' . $streams->HolidayID }}">Edit</a>
                                                    <!-- <form id="deleteForm" method="post" action="{{ url('delete-holidays') }}">
                                        @csrf
                                        <input type="hidden" name="table_name" value="streams">
                                        <input type="hidden" name="delete_id" value="{{ $streams->id }}">
                                        <button type="button" class="btn btn-danger m-1" onclick="confirmDelete(event)">Delete</button>
                                    </form> -->
                                                    <a class="btn btn-raised ripple btn-danger m-1"
                                                        href="{{ url('delete-holidays') . '/' . $streams->HolidayID }}"
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

        function setupDateRange(startDateId, endDateId) {
            const startDateInput = document.getElementById(startDateId);
            const endDateInput = document.getElementById(endDateId);

            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', function() {
                    const startDate = new Date(startDateInput.value);

                    if (startDate) {
                        // Set the minimum date of the end date
                        endDateInput.min = startDateInput.value;

                        // Clear the end date if it is invalid
                        if (new Date(endDateInput.value) < startDate) {
                            endDateInput.value = '';
                        }
                    }
                });
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            $("#reset").on("click", function() {
                $("#HolidayName").val("");
                $("#HolidayStartDate").val("");
                $("#HolidayEndDate").val("");
                $("#HolidayDescription").val("");
            });

            setupDateRange('HolidayStartDate', 'HolidayEndDate');

        })
    </script>



@endsection
