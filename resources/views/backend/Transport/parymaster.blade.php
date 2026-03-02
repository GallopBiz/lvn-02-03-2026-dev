@extends('backend.layouts.main')
@section('main-container')
<style>

.uperletter{
  text-transform: capitalize;
} 


</style>

<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Party Master Registration</h1>
        </div>
        @if(!empty($listrtopaper))
                  <form action="{{url('partymaster-store')}}" novalidate enctype='multipart/form-data' method="post">
                  <input required type="hidden" 
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value=" {{ $listP->id }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    name="id"
                  >
                  @else
                  <!-- <form id="progress-form" class="p-4 progress-form" action="{{url('bus-stop-create')}}"  novalidate method="post"> -->
                  <form action="{{url('save-addpartymaster')}}" enctype='multipart/form-data' method="post">
                  @endif
        <!-- <form action="save-addpartymaster" method="post"> -->
            @csrf
            <div class="row">
            <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Party Name:</label>
                    <input required name="Party_Name" class="form-control uperletter" id="Challan" type="text" required  
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->Party_Name }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                    placeholder="Party Name" />
                </div>
                <?php 
                    if(!empty($listpartymaster)){ 
                        foreach($listpartymaster as $listP){
                            // echo $listP->Address; 
                            // echo"<pre>";
                            // print_r($listP);
                        }
                    } 
                    ?>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Address :</label>
                    <textarea id="Address" name="Address" rows="3" cols="50" 
                    placeholder="Address" required ><?php 
                    if(!empty($listpartymaster)){ 
                        foreach($listpartymaster as $listP){
                            echo $listP->Address; 
                        }
                    } 
                    ?></textarea>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Tax Type</label>
                    <select id="Tax" class="form-control" name="Tax" autocomplete="shipping address-level1" required>
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                              value=" {{ $listP->Tax }}"
                              <option value=" {{ $listP->Tax }}">{{ $listP->Tax }}</option>
                            @endforeach
                          @else
                          <option>select option</option>
                          @endif
                              <option value="op1">option 1</option>
                              <option value="op2">option 2</option>
                              <option value="op3">option 3</option>
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">City </label>
                    <input name="City" class="form-control" id="Amount" type="text"
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->City }}"
                      @endforeach
                    @else
                      value=""
                    @endif
                        
                        placeholder="City" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">State</label>
                    <select id="State" class="form-control" name="State" autocomplete="shipping address-level1" required>
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                              value=" {{ $listP->Tax }}"
                              <option value=" {{ $listP->State }}">{{ $listP->State }}</option>
                            @endforeach
                          @else
                          <option>select option</option>
                          @endif
                              <option value="op1">option 1</option>
                              <option value="op2">option 2</option>
                              <option value="op3">option 3</option>
                           </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <br>
                    <label for="picker2">locality</label>
                    <label class="radio-inline">
                    </label>
                    <label class="radio-inline">
                    <?php 
                        if(!empty($listpartymaster)){
                          foreach($listpartymaster as $listP){
                              if ($listP->locality == "local"){
                                    echo '<input type="radio" name="locality" checked value="local"> local ';
                                    echo '<input type="radio" name="locality" value="Out of local"> Out of local ';
                                } else {
                                    echo '<input type="radio" name="locality" value="local"> local ';
                                    echo '<input type="radio" name="locality" checked value="Out of local"> Out of local ';
                                }
                            }
                        }else{
                            echo '<input type="radio" name="locality" checked value="local"> local ';
                            echo '<input type="radio" name="locality" value="Out of local"> Out of local ';
                        }
                    
                    ?>    
                    

                    
                    </label>
                </div>
            <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Pin Code</label>
                    <input type="text" class="form-control" id="picker2" @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->PinCode }}"
                      @endforeach
                    @else
                      value=""
                    @endif name="PinCode" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">STD Code</label>
                    <input type="text" class="form-control" id="picker21" @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->STDCode }}"
                      @endforeach
                    @else
                      value=""
                    @endif name="STDCode" />
                </div>
                
                <!-- <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Challan Date</label>
                    <input type="date" class="form-control" id="picker2" name="CDate" />
                </div> -->
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Residence ph. no.1</label>
                    <input type="number" class="form-control" id="picker22" @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->REsidence_ph_no_1 }}"
                      @endforeach
                    @else
                      value=""
                    @endif name="REsidence_ph_no.1" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Office ph. no.1</label>
                    <input type="number" class="form-control" id="picker23" name="Office_ph_no.1"
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->Office_ph_no_1}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Residence ph. no.2</label>
                    <input type="number" class="form-control" id="picker24" name="REsidence_ph_no.2"
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->REsidence_ph_no_2}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Office ph. no.2</label>
                    <input type="number" class="form-control" id="picker25" name="Office_ph_no.2" @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->Office_ph_no_2}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Mobile No.</label>
                    <input type="number" class="form-control" id="picker27" name="Mobile" @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->	Mobile}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Email ID</label>
                    <input type="email" class="form-control" id="picker28" name="emailId" @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->	emailId}}"
                      @endforeach
                    @else
                      value=""
                    @endif/>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Fax no.</label>
                    <input type="text" class="form-control" id="picker211" name="Fax_no."  @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->	Fax_no_}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Service Tax no.</label>
                    <input type="text" class="form-control" id="picker222" name="Service_Tax_no."  @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->Service_Tax_no_}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">PAN no.</label>
                    <input type="text" class="form-control" id="picker233" name="PAN_no." @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->PAN_no_}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">CST no.</label>
                    <input type="text" class="form-control" id="picker244" name="CST_no." 
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->CST_no_}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">TIN no.</label>
                    <input type="text" class="form-control" id="picker255" name="TIN_no." 
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->TIN_no_}}"
                      @endforeach
                    @else
                      value=""
                    @endif/>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">TAN no.</label>
                    <input type="text" class="form-control" id="picker277" name="TAN_no." @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->TAN_no_}}"
                      @endforeach
                    @else
                      value=""
                    @endif/>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">GST no.</label>
                    <input type="text" class="form-control" id="picker288" name="GST_no." @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->GST_no_}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <br>
                    <label for="picker2">Party Flag</label><br>
                    <!-- <input type="radio" id="css" name="Party_Flag" value="ok">
                    <label for="css">OK</label>
                    <input type="radio" id="css" name="Party_Flag" value="becautious">
                    <label for="css">Be Cautious</label>
                    <input type="radio" id="css" name="Party_Flag" value="nottodeal">
                    <label for="css">Not to Deal</label> -->
                    <?php 
                        if(!empty($listpartymaster)){
                            foreach($listpartymaster as $listP){
                                if ($listP->Party_Flag == "ok"){
                                    echo '<input type="radio" name="Party_Flag" checked value="ok"> ok ';
                                } elseif($listp->Party_Flag == "becautious") {
                                    echo '<input type="radio" name="Party_Flag"  value="ok"> ok ';
                                    echo '<input type="radio" name="Party_Flag" checked value="becautious"> becautious ';
                                    echo '<input type="radio" name="Party_Flag" value="nottodeal"> nottodeal ';
                                } else {
                                  echo '<input type="radio" name="Party_Flag"  value="ok"> ok ';
                                  echo '<input type="radio" name="Party_Flag" value="becautious"> becautious ';
                                  echo '<input type="radio" name="Party_Flag" checked value="nottodeal"> nottodeal ';
                              }
                            }
                        }else{
                            echo '<input type="radio" name="Party_Flag" checked value="ok"> ok ';
                            echo '<input type="radio" name="Party_Flag" value="becautious"> becautious ';
                            echo '<input type="radio" name="Party_Flag" value="nottodeal"> nottodeal ';
                        }
                    
                    ?> 
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Remarks :</label>
                    <textarea id="Remarks" name="Remarks" rows="3" cols="50"   placeholder="Remarks">
                    <?php 
                    if(!empty($listpartymaster)){ 
                        foreach($listpartymaster as $listP){
                            echo $listP->Remarks; 
                        }
                    } 
                    else{} 
                    ?></textarea>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Contact no.(If Any)</label>
                    <input type="number" class="form-control" id="picker2a" name="Contactif" @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->Contactif}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="picker2">Valid Upto</label>
                    <input type="text" class="form-control" id="picker2b" name="validUpto" 
                     @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->validUpto}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <h4>Enter Contact Persons Details of this Party </h4>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Person Name :</label>
                    <input name="Person_Name" class="form-control" id="Cadd" type="text" placeholder="Person Name " 
                    @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->Person_Name}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Mobile NO. :</label>
                    <input name="Mobile_NO." class="form-control" id="Cadd1" type="number" placeholder="Mobile NO." @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->Mobile_NO_}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Department :</label>
                    <input name="Department." class="form-control" id="Cadd2" type="text" placeholder="Department" @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->Department_}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Post :</label>
                    <input name="Post" class="form-control" id="Cadd3" type="text" placeholder="Post"  @if(!empty($listpartymaster))
                      @foreach($listpartymaster as $listP)
                        value="{{ $listP->Post}}"
                      @endforeach
                    @else
                      value=""
                    @endif />
                </div> 
                <div class="col-md-42">
                    <button class="btn btn-primary">Submit</button>
                   <button type="button" id="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button>


                </div>
            </div>
        </form>
    </div>
    <!-- end of main-content -->
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    $("#reset").on("click", function () {                
        $("#Challan").val("");
        $("#Address").val("");
        $("#Tax").val("");
        $("#Amount").val("");
        $("#State").val("");
        $("#picker2").val("");
        $("#picker21").val("");
        $("#picker22").val("");
        $("#picker23").val("");
        $("#picker24").val("");
        $("#picker25").val("");
        $("#picker27").val("");
        $("#picker28").val("");
        $("#picker211").val("");
        $("#picker222").val("");
        $("#picker233").val("");
        $("#picker244").val("");
        $("#picker255").val("");
        $("#picker277").val("");
        $("#picker288").val("");
        $("#Remarks").val("");
        $("#picker2a").val("");
        $("#picker2b").val("");
        $("#Cadd").val("");
        $("#Cadd1").val("");
        $("#Cadd2").val("");
        $("#Cadd3").val("");
            
    });

})

</script>

@endsection