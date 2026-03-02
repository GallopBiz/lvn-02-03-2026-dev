@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">All Routes</h1>
        </div>
    </div>
    <br>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card text-start">
                <div class="card-body">
                    <div class="card-title mb-3 text-end">
                        <!-- You can add buttons or filters here -->
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    @if($routes->isNotEmpty())
                                        @foreach(array_keys((array) $routes->first()) as $column)
                                            <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                                        @endforeach
                                    @else
                                        <th>No routes found</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($routes as $route)
                                    <tr>
                                        @foreach((array) $route as $value)
                                            <td>{{ $value }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center">No routes available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
