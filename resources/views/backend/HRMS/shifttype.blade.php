@extends('backend.layouts.main')
@section('main-container')

    <style>
        .uperletter {
            text-transform: capitalize;
        }
    </style>

    <div class="main-content">
        <div class="form_section1_div">
            <div class="breadcrumb">
                <h1 class="me-2">Shift Type</h1>
            </div>

            <div class="separator-breadcrumb border-top"></div>

            <form id="progress-form" class="p-4 progress-form"
                action="{{ !empty($stream_master) ? url('store-shifttype') : url('save-shifttype') }}" method="post">

                @csrf

                @if (!empty($stream_master))
                    <input type="hidden" name="id" value="{{ $stream_master->id }}">
                @endif

                <div class="row">
                    <div class="col-md-3 form-group mb-3">
                        <label for="shifttype">Shift Type</label>
                        <input required class="form-control uperletter" id="Type" name="shift_type_name" type="text"
                            value="{{ $stream_master->shift_type_name ?? '' }}" placeholder="Shift Type" />
                        @error('shift_type_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" id="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </div>
            </form><br>
        </div>

        <div class="separator-breadcrumb border-top"></div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">List of Saved Shift Types:</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>

                <div class="card text-start">
                    <div class="card-body">
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search shift types">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="searchReset" class="btn btn-secondary">Reset</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Shift Types</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($stream))
                                        @foreach ($stream as $index => $streams)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="uperletter">{{ $streams->shift_type_name }}</td>
                                                <td class='d-flex'>
                                                    <a class="btn btn-primary m-1"
                                                        href="{{ url('view-shifttype/' . $streams->id) }}">Edit</a>
                                                    <?php $a = $streams->id; ?>
                                                    <a class="btn btn-raised ripple btn-danger m-1"
                                                        href="{{ url('delete-shifttype/' . $a) }}"
                                                        onclick="confirmDelete(event)">Delete</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center">No Data Found</td>
                                        </tr>
                                    @endif
                                </tbody>
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
            var resetButton = document.getElementById('reset');
            if (resetButton) {
                resetButton.addEventListener('click', function() {
                    var typeInput = document.getElementById('Type');
                    if (typeInput) {
                        typeInput.value = '';
                    }
                });
            }

            var searchInput = document.getElementById('searchInput');
            var searchReset = document.getElementById('searchReset');

            function filterTable() {
                if (!searchInput) {
                    return;
                }
                var searchValue = searchInput.value.toLowerCase();
                var rows = document.querySelectorAll('#deafult_ordering_table_wrapper tbody tr');

                rows.forEach(function(row) {
                    var cell = row.querySelector('td:nth-child(2)');
                    if (!cell) {
                        row.style.display = '';
                        return;
                    }
                    var cellText = cell.innerText.toLowerCase();
                    row.style.display = cellText.includes(searchValue) ? '' : 'none';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('keyup', filterTable);
            }

            if (searchReset) {
                searchReset.addEventListener('click', function() {
                    if (searchInput) {
                        searchInput.value = '';
                        filterTable();
                    }
                });
            }
        });
    </script>

@endsection
