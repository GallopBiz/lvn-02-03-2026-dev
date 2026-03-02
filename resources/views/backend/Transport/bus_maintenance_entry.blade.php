@extends('backend.layouts.main')
@section('main-container')

<style>
    .uperletter {
        text-transform: capitalize;
    }
</style>

<div class="main-content pt-4">
    <div class="row">
        <!-- Form Section -->
        <div class="col-md-5 form-group mb-3">
            <div class="form_section1_div">
                <div class="breadcrumb">
                    <h1 class="me-2">Add Bus Maintenance</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>
                <form action="{{ url('store-bus-maintenance') }}" method="post" enctype="multipart/form-data" class="p-4 uperletter">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label>Vehicle Number:</label>
                            <select name="vehicle_no" class="form-control" required>
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->vehicelno }}">{{ $v->vehicelno }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 form-group mb-3">
                            <label>Maintenance Group:</label>
                            <select name="maintenance_group_id" class="form-control" required>
                                <option value="">Select Group</option>
                                @foreach($maintenance_groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->maintenance_group_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 form-group mb-3">
                            <label>Maintenance Head:</label>
                            <select name="maintenance_head_id" class="form-control" required>
                                <option value="">Select Head</option>
                                @foreach($maintenance_heads as $head)
                                    <option value="{{ $head->id }}">{{ $head->maintenance_head_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 form-group mb-3">
                            <label>Invoice Date:</label>
                            <input required type="date" class="form-control" name="invoice_date">
                        </div>

                        <div class="col-md-12 form-group mb-3">
                            <label>Amount:</label>
                            <input required type="number" step="0.01" class="form-control" name="amount">
                        </div>

                        <div class="col-md-12 form-group mb-3">
                            <label>Remarks:</label>
                            <textarea class="form-control" name="remarks" rows="2" placeholder="Remarks..."></textarea>
                        </div>

                        <div class="col-md-12 form-group mb-3">
                            <label>Upload Invoice:</label>
                            <input type="file" class="form-control" name="invoice_file" accept="application/pdf,image/*">
                        </div>

                        <div class="col-md-12">
                            <button class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Maintenance History Table -->
        <div class="col-md-7">
            <div class="form_section1_div">
                <div class="breadcrumb">
                    <h1 class="me-2">Maintenance History</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>

                <div class="table-responsive p-2">
                    <div class="mb-3">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search maintenance history...">
                    </div>

                    <table id="maintenanceTable" class="display table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Vehicle No</th>
                                <th>Group</th>
                                <th>Head</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Invoice</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maintenances as $key => $m)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $m->vehicle_no }}</td>
                                    <td>{{ $m->group_name }}</td>
                                    <td>{{ $m->head_name }}</td>
                                    <td>{{ $m->invoice_date }}</td>
                                    <td>{{ $m->amount }}</td>
                                    <td>
                                        @if($m->invoice_file)
                                            <a href="{{ asset('uploads/invoices/'.$m->invoice_file) }}" target="_blank">View</a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('delete-bus-maintenance/'.$m->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete this entry?')">Delete</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Sr.</th>
                                <th>Vehicle No</th>
                                <th>Group</th>
                                <th>Head</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Invoice</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#maintenanceTable tbody tr');

    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();

        tableRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            row.style.display = rowText.includes(filter) ? '' : 'none';
        });
    });
});
</script>

@endsection
