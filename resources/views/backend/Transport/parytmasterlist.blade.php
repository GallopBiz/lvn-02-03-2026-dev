@extends('backend.layouts.main')
@section('main-container')
<style>

.uperletter{
  text-transform: capitalize;
} 

</style>

<div class="main-content pt-4">
    <!-- <h2>hyy</h2> -->
    @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
          <div class="breadcrumb">
          <h2>Party Master </h2>
          </div>
          <div class="separator-breadcrumb border-top"></div>
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card text-start">
                <div class="card-body">

                  <h4 class="card-title mb-3 text-end"><a href="{{url('partycontroller')}}"><button class="btn btn-outline-primary" type="button">Create Party Master</button></a></h4>
                  <div class="card-title mb-3 text-end"><form method="POST" action="{{ route('export.csv') }}">
                      @csrf
                      <input type="hidden" name="column_names[]" value="Party_Name">
                      <input type="hidden" name="column_names[]" value="Address">
                      <input type="hidden" name="column_names[]" value="emailId">
                      <input type="hidden" name="column_names[]" value="City">
                      <input type="hidden" name="column_names[]" value="STDCode">
                      <input type="hidden" name="column_names[]" value="Office_ph_no_1">
                      <input type="hidden" name="column_names[]" value="Mobile">
                      <input type="hidden" name="column_names[]" value="PAN_no_">
                      <input type="hidden" name="column_names[]" value="TIN_no_">
                      <input type="hidden" name="column_names[]" value="GST_no_">
                      <input type="hidden" name="table_name" value="partymaster">
                      <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                  </form></div>
                  @php
                      $i = 0;
                    @endphp
                  <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                      <thead>
                        <tr>
                            <th>No</th>
                            <th>Party Name</th>
                            <th>Address</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>Std Code</th>
                            <th>Office no.</th>
                            <th>Mobile no.</th>
                            <th>PAN no.</th>
                            <th>TIN no.</th>
                            <th>GST no.</th>
                            <th>Action Teken</th>
                            
                        </tr>
                      </thead>
                      
                      <tbody>
                        @if(!empty($listpartymaster))
                        @foreach($listpartymaster as $listP)
                        <tr>
                            <td>{{++$i}}</td>
                            <td class= "uperletter">{{$listP->Party_Name}}</td>
                            <td class= "uperletter">{{$listP->Address}}</td>
                            <td>{{$listP->emailId}}</td>
                            <td class= "uperletter">{{$listP->City}}</td>
                            <td>{{$listP->STDCode}}</td>
                            <td>{{$listP->Office_ph_no_1}}</td>
                            <td>{{$listP->Mobile}}</td>
                            <td>{{$listP->PAN_no_}}</td>
                            <td>{{$listP->TIN_no_}}</td>
                            <td>{{$listP->GST_no_}}</td>
                            <!-- <td>{{$listP->Remark}}</td>    -->
                            <!-- <td>{{$listP->Action}}</td>     -->
                            <!-- <td> <a href="{{url('partymaster-delete')}}/{{$listP->id}}">
                            <button  class="btn btn-danger">Delete</button>
                            </a>
                            <a  href="{{ url('partymaster-view') .'/'.$listP->id}}">
                            <button  class="btn btn-success">Edit</button>
                            </a></td> -->
                            <td class='d-flex'>
                              <a class="btn btn-primary m-1" href="{{ url('partymaster-view') .'/'.$listP->id}}">Edit</a>
                                <!-- <form id="deleteForm" method="post" action="{{url('partymaster-delete')}}">
                                    @csrf
                                    <input type="hidden" name="table_name" value="partymaster">
                                    <input type="hidden" name="delete_id" value="{{ $listP->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="confirmDelete(event)">Delete</button>
                                </form> -->
                                <br>
                                <?php $a = "partymaster"."-".$listP->id ; ?>
                                <a class="btn btn-raised ripple btn-danger m-1" href="{{url('partymaster-delete').'/'.$a}}" onclick="confirmDelete(event)">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                        @else
                        <tr><td colspan="9" class="text-center">No Data Found</td></tr>
                        @endif
                      </tbody>
                      <tfoot>
                      <th>No</th>
                      <th>No</th>
                            <th>Party Name</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>Std Code</th>
                            <th>Office no.</th>
                            <th>Mobile no.</th>
                            <th>PAN no.</th>
                            <th>TIN no.</th>
                            <th>GST no.</th>
                            <!-- <th>Remark</th> -->
                            <th>Action Teken</th>
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
</script>
@endsection