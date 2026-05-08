@extends('backend.layouts.main')
@section('main-container')

<style>
    .uperletter {
        text-transform: uppercase;
    }
</style>

@php
    $editing = !empty($stream_master);
@endphp

<div class="main-content">
    <div class="breadcrumb d-flex justify-content-between align-items-center">
        <h1 class="me-2">Internal Assessment (IA) Master</h1>
        @if($editing)
            <a href="{{ route('internal-assessment-master') }}" class="btn btn-secondary">Back</a>
        @endif
    </div>

    <div class="separator-breadcrumb border-top"></div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">{{ $editing ? 'Edit IA Type' : 'Create IA Type' }}</h4>

                    <form method="post" action="{{ $editing ? url('store-internal-assessment-master') : url('save-internal-assessment-master') }}">
                        @csrf
                        @if($editing)
                            <input type="hidden" name="id" value="{{ $stream_master->id }}">
                        @endif

                        <div class="form-group mb-3">
                            <label for="assessment_code">Code</label>
                            <input required class="form-control uperletter" id="assessment_code" name="assessment_code" type="text" value="{{ old('assessment_code', $stream_master->assessment_code ?? '') }}" placeholder="NB">
                        </div>

                        <div class="form-group mb-3">
                            <label for="assessment_name">Name</label>
                            <input required class="form-control" id="assessment_name" name="assessment_name" type="text" value="{{ old('assessment_name', $stream_master->assessment_name ?? '') }}" placeholder="Notebook">
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional">{{ old('description', $stream_master->description ?? '') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="display_order">Display Order</label>
                                <input class="form-control" id="display_order" name="display_order" type="number" min="0" value="{{ old('display_order', $stream_master->display_order ?? 0) }}">
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="is_active">Status</label>
                                <select class="form-control" id="is_active" name="is_active">
                                    <option value="1" {{ (string) old('is_active', $stream_master->is_active ?? 1) === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ (string) old('is_active', $stream_master->is_active ?? 1) === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">{{ $editing ? 'Update' : 'Submit' }}</button>
                        @if($editing)
                            <a href="{{ route('internal-assessment-master') }}" class="btn btn-primary">Add New</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7 mb-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">List of IA Types</h4>
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stream as $index => $assessment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="uperletter">{{ $assessment->assessment_code }}</td>
                                        <td>{{ $assessment->assessment_name }}</td>
                                        <td>{{ $assessment->display_order }}</td>
                                        <td>
                                            <span class="badge {{ $assessment->is_active ? 'badge-success' : 'badge-danger' }}">
                                                {{ $assessment->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="d-flex">
                                            <a class="btn btn-primary m-1" href="{{ url('view-internal-assessment-master') . '/' . $assessment->id }}">Edit</a>
                                            <a class="btn btn-warning m-1" href="{{ url('toggle-internal-assessment-master') . '/' . $assessment->id }}">
                                                {{ $assessment->is_active ? 'Deactivate' : 'Activate' }}
                                            </a>
                                            <a class="btn btn-danger m-1" href="{{ url('delete-internal-assessment-master') . '/' . $assessment->id }}" onclick="return confirm('Are you sure you want to delete this IA type?')">Delete</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No Data Found</td>
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
