@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Route - Vehicle Mapping</h1>
        </div>
    </div>
    <br>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card text-start">
                <div class="card-body">

                    {{-- Search Input --}}
                    <div class="mb-3" style="max-width: 300px;">
                        <input type="text" id="search_vehicle" class="form-control" placeholder="Search Vehicle No" value="{{ old('search_vehicle', $searchVehicle ?? '') }}">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr. No</th>
                                    <th>Route Name</th>
                                    <th>Vehicle No</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="route_vehicle_table_body">
                                @include('backend.Transport.partials.route_vehicle_table', ['routes' => $routes])
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- jQuery CDN --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- AJAX Search --}}
<script>
$(document).ready(function() {
    let delayTimer;

    $('#search_vehicle').on('keyup', function() {
        clearTimeout(delayTimer);
        let query = $(this).val();

        delayTimer = setTimeout(function() {
            $.ajax({
                url: "{{ url()->current() }}",
                type: 'GET',
                data: { search_vehicle: query },
                dataType: 'json',
                success: function(data) {
                    $('#route_vehicle_table_body').html(data.html);
                },
                error: function(xhr) {
                    alert('Failed to fetch data.');
                    console.error(xhr.responseText);
                }
            });
        }, 300); // debounce delay
    });
});
</script>
@endsection
