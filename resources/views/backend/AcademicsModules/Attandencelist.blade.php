@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <!-- <h2>hyy</h2> -->
    @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
          <div class="breadcrumb">
          <h2>Attandence list</h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">
                  <form id="progress-form" class="p-4 progress-form" action="#"  novalidate method="post">
                    @csrf
                    <h4 class="card-title mb-3 text-end"><a href="#"><button class="btn btn-outline-primary" type="button">save</button></a></h4>
                  </form>
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>No</th>
                            <th>Class Name</th>
                            <th>Section Name</th>
                            <th>Teachers 1</th>
                            <th>Teachers 2</th>                            
                        </tr>
                      </thead>
                      
                      <tbody>
                        <tr>
                            <td>1</td>
                            <td>12th</td>
                            <td>A</td>
                            <td><select name="Exam_Name" class="form-control" id="Exam_Name">
                                <option value="" selected> -- Please select -- </option>
                                <option value="Teacher 1">Teacher 1</option>
                                <option value="Teacher 2">Teacher 2</option>
                            </select></td>
                            <td><select name="Exam_Name" class="form-control" id="Exam_Name">
                                <option value="" selected> -- Please select -- </option>
                                <option value="Teacher 1">Teacher 1</option>
                                <option value="Teacher 2">Teacher 2</option>
                            </select></td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>12th</td>
                            <td>A</td>
                            <td><select name="Exam_Name" class="form-control" id="Exam_Name">
                                <option value="" selected> -- Please select -- </option>
                                <option value="Teacher 1">Teacher 1</option>
                                <option value="Teacher 2">Teacher 2</option>
                            </select></td>
                            <td><select name="Exam_Name" class="form-control" id="Exam_Name">
                                <option value="" selected> -- Please select -- </option>
                                <option value="Teacher 1">Teacher 1</option>
                                <option value="Teacher 2">Teacher 2</option>
                            </select></td>
                        </tr>
                      </tbody>
                      <tfoot>
                      <tr>
                            <th>No</th>
                            <th>Class Name</th>
                            <th>Section Name</th>
                            <th>Teachers 1</th>
                            <th>Teachers 2</th>                            
                        </tr>
                      </tfoot>
                    </table>

                  </div>
                </div>
              </div>
            </div>
          </div>
    <!-- end of main-content -->
</div>
@endsection