@extends('backend.layouts.main')
@section('main-container')
<style>
    .fuel-entry-layout { display: flex; flex-wrap: wrap; }
    .fuel-entry-layout > .fuel-entry-form { flex: 0 0 55%; max-width: 55%; }
    .fuel-entry-layout > .fuel-entry-settings { flex: 0 0 45%; max-width: 45%; }
    .fuel-inline-row { display: grid; gap: 0 15px; margin: 0 -7.5px; }
    .fuel-inline-row > .fuel-inline-field { min-width: 0; padding: 0 7.5px; }
    .fuel-inline-two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .fuel-inline-three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .fuel-search-select { position: relative; }
    .fuel-search-options { position: absolute; z-index: 20; top: 100%; left: 7.5px; right: 7.5px; display: none; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15); }
    .fuel-search-select.is-open .fuel-search-options { display: block; }
    .fuel-search-option { display: block; width: 100%; padding: 8px 12px; border: 0; background: #fff; text-align: left; cursor: pointer; }
    .fuel-search-option:hover { background: #f1f3f5; }
    .fuel-station-badge { display: inline-block; padding: 6px 10px; margin-bottom: 6px; background: #e8f1fb; border: 1px solid #b8d0e8; color: #1f3f5b; font-size: 14px; font-weight: 600; }
    @media (max-width: 991px) {
        .fuel-entry-layout > .fuel-entry-form,
        .fuel-entry-layout > .fuel-entry-settings { flex: 0 0 100%; max-width: 100%; }
        .fuel-inline-two, .fuel-inline-three { grid-template-columns: 1fr; }
    }
</style>
<div class="main-content pt-4">
    <div class="breadcrumb d-flex justify-content-between align-items-center">
        <h1>Fuel Management</h1>
        <a href="{{ route('transport.fuel.index') }}" class="btn btn-outline-secondary">Back to Fuel Entries</a>
    </div>
    <div class="separator-breadcrumb border-top"></div>
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    <div class="row fuel-entry-layout">
        <div class="fuel-entry-form mb-4">
            <div class="card"><div class="card-body">
                <h3>Edit Fuel Entry</h3>
                <form method="POST" action="{{ route('transport.fuel.update', $entry->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="fuel-inline-row fuel-inline-two">
                        <div class="form-group fuel-inline-field"><label>Fuel Date</label><input type="date" name="fuel_date" class="form-control" value="{{ old('fuel_date', $entry->fuel_date) }}" required></div>
                        <div class="form-group fuel-inline-field fuel-search-select" id="vehicle_select"><label>Vehicle</label><input type="search" id="vehicle_search" class="form-control" value="{{ old('vehicle_search', optional($vehicles->firstWhere('id', old('vehicle_id', $entry->vehicle_id)))->vehicelno) }}" autocomplete="off" required><input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ old('vehicle_id', $entry->vehicle_id) }}"><div class="fuel-search-options" id="vehicle_options"><button type="button" class="fuel-search-option" data-id="">Select Vehicle</button>@foreach($vehicles as $vehicle)<button type="button" class="fuel-search-option" data-id="{{ $vehicle->id }}">{{ $vehicle->vehicelno }}</button>@endforeach</div></div>
                    </div>
                    <div class="fuel-inline-row fuel-inline-three">
                        <div class="form-group fuel-inline-field"><label>Fuel / Energy Type</label><select name="fuel_type" id="fuel_type" class="form-control" required>@foreach($fuelTypes as $type => $unit)<option value="{{ $type }}" data-unit="{{ $unit }}" @selected(old('fuel_type', $entry->fuel_type) === $type)>{{ $type }}</option>@endforeach</select></div>
                        <div class="form-group fuel-inline-field"><label>Quantity</label><input type="number" name="quantity" id="quantity" class="form-control" step="0.01" min="0.01" value="{{ old('quantity', $entry->quantity) }}" required></div>
                        <div class="form-group fuel-inline-field"><label>Unit</label><input type="text" id="quantity_unit" class="form-control" value="{{ $entry->quantity_unit }}" readonly></div>
                    </div>
                    <div class="fuel-inline-row fuel-inline-three">
                        <div class="form-group fuel-inline-field"><label id="rate_label">Rate</label><input type="number" name="rate" id="rate" class="form-control" step="0.01" min="0" value="{{ old('rate', $entry->rate) }}" required></div>
                        <div class="form-group fuel-inline-field"><label>Total Amount</label><input type="text" id="total_amount" class="form-control" value="{{ number_format($entry->total_amount, 2, '.', '') }}" readonly></div>
                        <div class="form-group fuel-inline-field"><label>Previous / Starting KM</label><input type="text" class="form-control" value="{{ number_format($entry->previous_odometer, 2, '.', '') }}" readonly></div>
                    </div>
                    <div class="fuel-inline-row fuel-inline-three">
                        <div class="form-group fuel-inline-field"><label>Current KM</label><input type="number" name="current_odometer" class="form-control" step="0.01" min="0" value="{{ old('current_odometer', $entry->current_odometer) }}" required></div>
                        <div class="form-group fuel-inline-field"><label>Fuel Station</label><select name="fuel_station_id" class="form-control" required><option value="">Select Station</option>@foreach($stations as $station)<option value="{{ $station->id }}" @selected(old('fuel_station_id', $entry->fuel_station_id) == $station->id)>{{ $station->name }}</option>@endforeach</select></div>
                        <div class="form-group fuel-inline-field fuel-search-select" id="driver_select"><label>Driver (optional)</label><input type="search" id="driver_search" class="form-control" value="{{ old('driver_search', optional($drivers->firstWhere('id', old('driver_id', $entry->driver_id)))->ename) }}" autocomplete="off"><input type="hidden" name="driver_id" id="driver_id" value="{{ old('driver_id', $entry->driver_id) }}"><div class="fuel-search-options" id="driver_options"><button type="button" class="fuel-search-option" data-id="">Select Driver</button>@foreach($drivers as $driver)<button type="button" class="fuel-search-option" data-id="{{ $driver->id }}">{{ $driver->ename }}</button>@endforeach</div></div>
                    </div>
                    <div class="fuel-inline-row fuel-inline-three">
                        <div class="form-group fuel-inline-field"><label>Bill Number</label><input type="text" name="bill_number" class="form-control" value="{{ old('bill_number', $entry->bill_number) }}"></div>
                        <div class="form-group fuel-inline-field"><label>Payment Mode</label><select name="payment_mode" class="form-control"><option value="">Select</option>@foreach(['Cash','UPI','Card','Bank Transfer','Other'] as $mode)<option value="{{ $mode }}" @selected(old('payment_mode', $entry->payment_mode) === $mode)>{{ $mode }}</option>@endforeach</select></div>
                        <div class="form-group fuel-inline-field"><label>Payment Reference</label><input type="text" name="payment_reference" class="form-control" value="{{ old('payment_reference', $entry->payment_reference) }}"></div>
                    </div>
                    <div class="form-group mb-3"><label>Remarks</label><textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $entry->remarks) }}</textarea></div>
                    <button class="btn btn-primary">Update Fuel Entry</button>
                    <a href="{{ route('transport.fuel.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div></div>
        </div>
        <div class="fuel-entry-settings mb-4">
            <div class="card"><div class="card-body">
                <h3>Entry Summary</h3>
                <p class="text-muted mb-2">Saved entry details</p>
                <p class="mb-1"><strong>Distance:</strong> {{ number_format($entry->distance, 2) }} KM</p>
                <p class="mb-1"><strong>Average:</strong> {{ $entry->efficiency !== null ? number_format($entry->efficiency, 2).' KM/'.$entry->quantity_unit : 'N/A' }}</p>
                <p class="mb-0"><strong>Created:</strong> {{ $entry->created_at ? \Carbon\Carbon::parse($entry->created_at)->format('d-m-Y H:i') : 'N/A' }}</p>
            </div></div>
            <div class="card mt-4"><div class="card-body">
                <h3>Fuel Stations</h3>
                @foreach($stations as $station)<span class="fuel-station-badge mr-2">{{ $station->name }}</span>@endforeach
            </div></div>
        </div>
    </div>
</div>
<script>
(function () {
    const type = document.getElementById('fuel_type');
    const quantity = document.getElementById('quantity');
    const rate = document.getElementById('rate');
    const unit = document.getElementById('quantity_unit');
    const total = document.getElementById('total_amount');
    const label = document.getElementById('rate_label');
    function update() {
        const option = type.options[type.selectedIndex];
        unit.value = option.dataset.unit;
        label.textContent = 'Rate per ' + option.dataset.unit;
        total.value = ((parseFloat(quantity.value) || 0) * (parseFloat(rate.value) || 0)).toFixed(2);
    }
    type.addEventListener('change', update);
    quantity.addEventListener('input', update);
    rate.addEventListener('input', update);
    update();
    function setupSearch(selectId, searchId, hiddenId, optionsId, placeholder) {
        const select = document.getElementById(selectId);
        const search = document.getElementById(searchId);
        const hidden = document.getElementById(hiddenId);
        const options = Array.from(document.querySelectorAll('#' + optionsId + ' .fuel-search-option'));
        search.addEventListener('input', function () {
            const value = this.value.toLowerCase();
            hidden.value = '';
            options.forEach(option => { option.style.display = option.textContent.toLowerCase().includes(value) ? 'block' : 'none'; });
            select.classList.add('is-open');
        });
        search.addEventListener('focus', function () { select.classList.add('is-open'); });
        options.forEach(option => option.addEventListener('click', function () {
            search.value = this.textContent.trim() === placeholder ? '' : this.textContent.trim();
            hidden.value = this.dataset.id;
            select.classList.remove('is-open');
        }));
        return select;
    }
    const vehicleSelect = setupSearch('vehicle_select', 'vehicle_search', 'vehicle_id', 'vehicle_options', 'Select Vehicle');
    const driverSelect = setupSearch('driver_select', 'driver_search', 'driver_id', 'driver_options', 'Select Driver');
    document.addEventListener('click', function (event) {
        if (!vehicleSelect.contains(event.target)) vehicleSelect.classList.remove('is-open');
        if (!driverSelect.contains(event.target)) driverSelect.classList.remove('is-open');
    });
}());
</script>
@endsection
