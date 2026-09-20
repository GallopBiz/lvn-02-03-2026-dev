@extends('backend.layouts.main')
@section('main-container')
<style>
    .fuel-entry-layout {
        display: flex;
        flex-wrap: wrap;
    }

    .fuel-entry-layout > .fuel-entry-form {
        flex: 0 0 55%;
        max-width: 55%;
    }

    .fuel-entry-layout > .fuel-entry-settings {
        flex: 0 0 45%;
        max-width: 45%;
    }

    .fuel-inline-row {
        display: grid;
        gap: 0 15px;
        margin: 0 -7.5px;
    }

    .fuel-inline-row > .fuel-inline-field {
        min-width: 0;
        padding: 0 7.5px;
    }

    .fuel-inline-two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .fuel-inline-three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .fuel-inline-four {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .fuel-search-select {
        position: relative;
    }

    .fuel-search-options {
        position: absolute;
        z-index: 20;
        top: 100%;
        left: 7.5px;
        right: 7.5px;
        display: none;
        max-height: 220px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ced4da;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    }

    .fuel-search-select.is-open .fuel-search-options {
        display: block;
    }

    .fuel-search-option {
        display: block;
        width: 100%;
        padding: 8px 12px;
        border: 0;
        background: #fff;
        text-align: left;
        cursor: pointer;
    }

    .fuel-search-option:hover {
        background: #f1f3f5;
    }

    .fuel-station-badge {
        display: inline-block;
        padding: 6px 10px;
        margin-bottom: 6px;
        background: #e8f1fb;
        border: 1px solid #b8d0e8;
        color: #1f3f5b;
        font-size: 14px;
        font-weight: 600;
    }

    .fuel-filter-row,
    #fuel-filter-form > .form-row.align-items-end {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 10px;
    }

    .fuel-filter-row > .form-group,
    #fuel-filter-form > .form-row.align-items-end > .form-group {
        flex: 1 1 150px;
        max-width: none;
        margin-bottom: 0;
    }

    .fuel-filter-row > .form-group:last-child,
    #fuel-filter-form > .form-row.align-items-end > .form-group:last-child {
        flex: 0 0 auto;
    }

    #fuel-filter-form > .form-group:last-child,
    #fuel-filter-form > .form-row.align-items-end > .form-group:last-child {
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }

    #fuel-filter-form + .table-responsive {
        margin-top: 20px;
    }

    @media (max-width: 991px) {
        .fuel-entry-layout > .fuel-entry-form,
        .fuel-entry-layout > .fuel-entry-settings {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .fuel-inline-two,
        .fuel-inline-three,
        .fuel-inline-four {
            grid-template-columns: 1fr;
        }
    }
</style>
<div class="main-content pt-4">
    <div class="breadcrumb d-flex justify-content-between align-items-center">
        <h1>Fuel Management</h1>
        <a href="{{ route('transport.fuel.report') }}" class="btn btn-outline-primary">Monthly Report</a>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    <div class="row fuel-entry-layout">
        <div class="fuel-entry-form mb-4">
            <div class="card"><div class="card-body">
                <h3>Add Fuel Entry</h3>
                <form method="POST" action="{{ route('transport.fuel.store') }}">
                    @csrf
                    <div class="fuel-inline-row fuel-inline-two">
                        <div class="form-group fuel-inline-field"><label>Fuel Date</label><input type="date" name="fuel_date" class="form-control" value="{{ old('fuel_date', now()->format('Y-m-d')) }}" required></div>
                        <div class="form-group fuel-inline-field fuel-search-select" id="vehicle_select"><label>Vehicle</label><input type="search" id="vehicle_search" class="form-control" placeholder="Search vehicle number" autocomplete="off" required><input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ old('vehicle_id') }}"><div class="fuel-search-options" id="vehicle_options"><button type="button" class="fuel-search-option" data-id="">Select Vehicle</button>@foreach($vehicles as $vehicle)<button type="button" class="fuel-search-option" data-id="{{ $vehicle->id }}" data-previous="{{ $vehicle->fuel_previous_odometer }}">{{ $vehicle->vehicelno }}</button>@endforeach</div></div>
                    </div>
                    <div class="fuel-inline-row fuel-inline-three">
                        <div class="form-group fuel-inline-field"><label>Fuel / Energy Type</label><select name="fuel_type" id="fuel_type" class="form-control" required>@foreach($fuelTypes as $type => $unit)<option value="{{ $type }}" data-unit="{{ $unit }}" @selected(old('fuel_type', 'Diesel') === $type)>{{ $type }}</option>@endforeach</select></div>
                        <div class="form-group fuel-inline-field"><label>Quantity</label><input type="number" name="quantity" id="quantity" class="form-control" step="0.01" min="0.01" value="{{ old('quantity') }}" required></div>
                        <div class="form-group fuel-inline-field"><label>Unit</label><input type="text" id="quantity_unit" class="form-control" readonly></div>
                    </div>
                    <div class="fuel-inline-row fuel-inline-three">
                        <div class="form-group fuel-inline-field"><label id="rate_label">Rate</label><input type="number" name="rate" id="rate" class="form-control" step="0.01" min="0" value="{{ old('rate') }}" required></div>
                        <div class="form-group fuel-inline-field"><label>Total Amount</label><input type="text" id="total_amount" class="form-control" readonly></div>
                        <div class="form-group fuel-inline-field"><label>Previous / Starting KM</label><input type="text" id="previous_odometer" class="form-control" readonly placeholder="Select vehicle"></div>
                    </div>
                    <div class="fuel-inline-row fuel-inline-three">
                        <div class="form-group fuel-inline-field"><label>Current KM</label><input type="number" name="current_odometer" class="form-control" step="0.01" min="0" value="{{ old('current_odometer') }}" required></div>
                        <div class="form-group fuel-inline-field"><label>Fuel Station</label><select name="fuel_station_id" class="form-control" required><option value="">Select Station</option>@foreach($stations as $station)<option value="{{ $station->id }}">{{ $station->name }}</option>@endforeach</select></div>
                        <div class="form-group fuel-inline-field fuel-search-select" id="driver_select"><label>Driver (optional)</label><input type="search" id="driver_search" class="form-control" placeholder="Search driver" autocomplete="off"><input type="hidden" name="driver_id" id="driver_id"><div class="fuel-search-options" id="driver_options"><button type="button" class="fuel-search-option" data-id="">Select Driver</button>@foreach($drivers as $driver)@if(trim((string) $driver->ename) !== '')<button type="button" class="fuel-search-option" data-id="{{ $driver->id }}">{{ $driver->ename }}</button>@endif @endforeach</div></div>
                    </div>
                    <div class="fuel-inline-row fuel-inline-three">
                        <div class="form-group fuel-inline-field"><label>Bill Number</label><input type="text" name="bill_number" class="form-control" value="{{ old('bill_number') }}"></div>
                        <div class="form-group fuel-inline-field"><label>Payment Mode</label><select name="payment_mode" class="form-control"><option value="">Select</option>@foreach(['Cash','UPI','Card','Bank Transfer','Other'] as $mode)<option>{{ $mode }}</option>@endforeach</select></div>
                        <div class="form-group fuel-inline-field"><label>Payment Reference</label><input type="text" name="payment_reference" class="form-control"></div>
                    </div>
                    <div class="form-group mb-3"><label>Remarks</label><textarea name="remarks" class="form-control" rows="2"></textarea></div>
                    <button class="btn btn-primary">Save Fuel Entry</button>
                </form>
            </div></div>
        </div>

        <div class="fuel-entry-settings mb-4">
            <div class="card"><div class="card-body">
                <h3>Vehicle Fuel Opening Odometer</h3>
                <p class="text-muted">Select one vehicle and set its opening KM once.</p>
                <form method="POST" action="{{ route('transport.fuel.opening') }}">
                    @csrf
                    <div class="form-group mb-3"><label>Vehicle</label><select name="vehicle_id" id="opening_vehicle_id" class="form-control" required><option value="">Select Vehicle</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}" data-opening="{{ $vehicle->fuel_opening_odometer }}">{{ $vehicle->vehicelno }}</option>@endforeach</select></div>
                    <div class="form-group mb-3"><label>Opening Odometer (KM)</label><input type="number" name="fuel_opening_odometer" id="opening_odometer" class="form-control" step="0.01" min="0" required></div>
                    <button class="btn btn-secondary">Save Opening KM</button>
                </form>
            </div></div>
            <div class="card mt-4"><div class="card-body">
                <h3>Fuel Stations</h3>
                <form method="POST" action="{{ route('transport.fuel.station.store') }}" class="form-inline mb-3">@csrf<input name="name" class="form-control mr-2" placeholder="Station name" required><button class="btn btn-secondary">Add Station</button></form>
                @foreach($stations as $station)<span class="fuel-station-badge mr-2">{{ $station->name }}</span>@endforeach
            </div></div>
        </div>
    </div>

    <div class="card"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><h3 class="mb-0">Fuel Entries</h3><input type="search" form="fuel-filter-form" name="search" class="form-control" style="max-width:280px" placeholder="Search entries..." value="{{ request('search') }}"></div><form id="fuel-filter-form" method="GET" action="{{ route('transport.fuel.index') }}"><div class="form-row align-items-end"><div class="form-group col-md-2"><label>From Date</label><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div><div class="form-group col-md-2"><label>To Date</label><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div><div class="form-group col-md-3"><label>Vehicle</label><select name="filter_vehicle_id" class="form-control"><option value="">All Vehicles</option>@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}" @selected(request('filter_vehicle_id') == $vehicle->id)>{{ $vehicle->vehicelno }}</option>@endforeach</select></div><div class="form-group col-md-2"><label>Fuel Type</label><select name="filter_fuel_type" class="form-control"><option value="">All Types</option>@foreach($fuelTypes as $type => $unit)<option value="{{ $type }}" @selected(request('filter_fuel_type') === $type)>{{ $type }}</option>@endforeach</select></div><div class="form-group col-md-3"><button class="btn btn-primary mr-2">Filter</button><a href="{{ route('transport.fuel.index') }}" class="btn btn-light mr-2">Clear</a><a href="{{ route('transport.fuel.export', request()->except('page')) }}" class="btn btn-success">Export CSV</a></div></div></form><div class="table-responsive"><table class="table table-striped table-bordered"><thead><tr><th>S.No.</th><th>Date</th><th>Vehicle</th><th>Type</th><th>Quantity</th><th>Amount</th><th>Previous KM</th><th>Current KM</th><th>Distance</th><th>Average</th><th>Actions</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>{{ $entries->firstItem() + $loop->index }}</td><td>{{ \Carbon\Carbon::parse($entry->fuel_date)->format('d-m-Y') }}</td><td>{{ $entry->vehicelno }}</td><td>{{ $entry->fuel_type }}</td><td>{{ number_format($entry->quantity, 2) }} {{ $entry->quantity_unit }}</td><td>{{ number_format($entry->total_amount, 2) }}</td><td>{{ number_format($entry->previous_odometer, 2) }}</td><td>{{ number_format($entry->current_odometer, 2) }}</td><td>{{ number_format($entry->distance, 2) }}</td><td>{{ $entry->efficiency !== null ? number_format($entry->efficiency, 2).' KM/'.$entry->quantity_unit : 'N/A' }}</td><td class="text-nowrap"><a href="{{ route('transport.fuel.edit', $entry->id) }}" class="btn btn-sm btn-primary">Edit</a> <form method="POST" action="{{ route('transport.fuel.destroy', $entry->id) }}" class="d-inline" onsubmit="return confirm('Delete this fuel entry?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger">Delete</button></form></td></tr>@empty<tr><td colspan="11" class="text-center">No fuel entries found.</td></tr>@endforelse</tbody><tfoot><tr class="font-weight-bold"><td colspan="5" class="text-right">Total</td><td>{{ number_format($totals->quantity, 2) }}</td><td>{{ number_format($totals->total_amount, 2) }}</td><td>{{ number_format($totals->previous_odometer, 2) }}</td><td>{{ number_format($totals->current_odometer, 2) }}</td><td>{{ number_format($totals->distance, 2) }}</td><td>{{ $totals->average !== null ? number_format($totals->average, 2).' KM' : 'N/A' }}</td><td></td></tr></tfoot></table></div><div class="d-flex flex-wrap justify-content-between align-items-center mt-3"><small class="text-muted">Showing {{ $entries->firstItem() ?? 0 }} to {{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }} fuel entries (20 per page)</small>{{ $entries->onEachSide(1)->links() }}</div></div></div>
</div>
<script>
(function () {
    const type = document.getElementById('fuel_type');
    const quantity = document.getElementById('quantity');
    const rate = document.getElementById('rate');
    const unit = document.getElementById('quantity_unit');
    const total = document.getElementById('total_amount');
    const label = document.getElementById('rate_label');
    const openingVehicle = document.getElementById('opening_vehicle_id');
    const openingOdometer = document.getElementById('opening_odometer');
    function update() { const option = type.options[type.selectedIndex]; const selectedUnit = option.dataset.unit; unit.value = selectedUnit; label.textContent = 'Rate per ' + selectedUnit; total.value = ((parseFloat(quantity.value) || 0) * (parseFloat(rate.value) || 0)).toFixed(2); }
    const vehicleSearch = document.getElementById('vehicle_search');
    const vehicle = document.getElementById('vehicle_id');
    const previous = document.getElementById('previous_odometer');
    const vehicleSelect = document.getElementById('vehicle_select');
    const vehicleOptions = Array.from(document.querySelectorAll('#vehicle_options .fuel-search-option'));
    const driverSearch = document.getElementById('driver_search');
    const driver = document.getElementById('driver_id');
    const driverSelect = document.getElementById('driver_select');
    const driverOptions = Array.from(document.querySelectorAll('#driver_options .fuel-search-option'));
    type.addEventListener('change', update); quantity.addEventListener('input', update); rate.addEventListener('input', update); update();
    vehicleSearch.addEventListener('input', function () {
        const search = this.value.toLowerCase();
        vehicle.value = '';
        previous.value = '';
        vehicleOptions.forEach(option => {
            option.style.display = option.textContent.toLowerCase().includes(search) ? 'block' : 'none';
        });
        vehicleSelect.classList.add('is-open');
    });
    vehicleSearch.addEventListener('focus', function () { vehicleSelect.classList.add('is-open'); });
    vehicleOptions.forEach(option => option.addEventListener('click', function () {
        vehicleSearch.value = this.textContent.trim() === 'Select Vehicle' ? '' : this.textContent.trim();
        vehicle.value = this.dataset.id;
        previous.value = this.dataset.previous || (this.dataset.id ? 'Set opening KM first' : '');
        vehicleSelect.classList.remove('is-open');
    }));
    driverSearch.addEventListener('input', function () {
        const search = this.value.toLowerCase();
        driver.value = '';
        driverOptions.forEach(option => {
            option.style.display = option.textContent.toLowerCase().includes(search) ? 'block' : 'none';
        });
        driverSelect.classList.add('is-open');
    });
    driverSearch.addEventListener('focus', function () { driverSelect.classList.add('is-open'); });
    driverOptions.forEach(option => option.addEventListener('click', function () {
        driverSearch.value = this.textContent.trim() === 'Select Driver' ? '' : this.textContent.trim();
        driver.value = this.dataset.id;
        driverSelect.classList.remove('is-open');
    }));
    document.addEventListener('click', function (event) {
        if (!driverSelect.contains(event.target)) driverSelect.classList.remove('is-open');
        if (!vehicleSelect.contains(event.target)) vehicleSelect.classList.remove('is-open');
    });
    openingVehicle.addEventListener('change', function () { openingOdometer.value = this.options[this.selectedIndex].dataset.opening || ''; });
}());
</script>
@endsection
