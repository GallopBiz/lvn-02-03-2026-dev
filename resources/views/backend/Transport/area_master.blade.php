@extends('backend.layouts.main')
@section('main-container')

<style>
    .uperletter {
        text-transform: capitalize;
    }
</style>

<div class="main-content pt-4">
    <div class="row">
		<!-- Left Column: Area Master Form -->
		<div class="col-md-6 form-group mb-3">
			<div class="form_section1_div">
				<div class="breadcrumb">
					<h1 class="me-2">Area Master</h1>
				</div>
				<div class="separator-breadcrumb border-top"></div>

				<form id="progress-form" class="p-4 progress-form" action="{{ url('area-master') }}" method="post">
					@csrf
					@if(!empty($area_s))
						<input type="hidden" name="id" value="{{ $area_s->id }}">
					@endif

					<div class="row">
						<div class="col-md-12 form-group mb-3">
							<label for="area_name">Area Name:</label>
							<input required type="text" class="form-control uperletter" placeholder="Area Name" 
								   name="area_name" id="area_name" 
								   value="{{ $area_s->area_name ?? '' }}">
						</div>
						<div class="col-md-12">
							<button class="btn btn-primary">Submit</button>
							<button type="button" id="reset" class="btn btn-primary">Reset</button>
						</div>
					</div>
				</form>
			</div>
		</div>

		<!-- Right Column: Import Area Data -->
		<div class="col-md-6 form-group mb-3">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title mb-3">Import Area Data</h4>
					<form action="{{ route('area.import') }}" method="POST" enctype="multipart/form-data">
						@csrf
						<div class="form-group">
							<label for="csv_file">Upload CSV File:</label>
							<input type="file" name="file" class="form-control" required>

							@if ($errors->any())
								<div class="alert alert-danger mt-2">
									<ul>
										@foreach ($errors->all() as $error)
											<li>{{ $error }}</li>
										@endforeach
									</ul>
								</div>
							@endif
						</div>
						<button type="submit" class="btn btn-raised ripple btn-raised-success m-1">Import CSV</button>
					</form>
				</div>
			</div>
		</div>
	</div>


    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">Area Master</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card text-start">
                        <div class="card-body">
                            <div class="card-title mb-3 text-end">
                                <form method="POST" action="{{ route('export.csv') }}">
                                    @csrf
                                    <input type="hidden" name="column_names[]" value="area_name">
                                    <input type="hidden" name="table_name" value="areamaster">
                                    <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Area Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 1; @endphp
                                        @forelse ($areas as $area)
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ $area->area_name }}</td>
                                                <td class='d-flex'>
                                                    <a class="btn btn-raised ripple btn-raised-primary m-1" href="{{ url('view-area-master/'.$area->id) }}">Edit</a>
                                                    <a class="btn btn-raised ripple btn-danger m-1" href="{{ url('delete-area-master/'.$area->id) }}" onclick="confirmDelete(event)">Delete</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center">No Data Found</td></tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Area Name</th>
                                            <th>Action</th>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function confirmDelete(event) {
        event.preventDefault();

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
                window.location.href = event.target.href;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        $("#reset").on("click", function () {
            $("#area_name").val("");
        });
    });
</script>

@endsection
