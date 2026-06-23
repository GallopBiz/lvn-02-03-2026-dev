@extends('backend.layouts.main')
@section('main-container')

{{-- Alert Section --}}
@if(session('success'))
    <script>
        localStorage.removeItem('stepperFormData');
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
@endif

    <style>
        .uperletter {
            text-transform: capitalize;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .stepper-buttons {
            display: flex;
            justify-content: end;
            margin-top: 20px;
        }

        .add-reset-buttons {
            margin-top: 20px;
        }

        .passport-size {
            width: 150px;
            height: 200px;
            border: 2px solid #ddd;
            border-radius: 5px;
            object-fit: cover;
        }

        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        #stepper-form.was-validated .form-control:invalid {
            border-color: #dc3545;
        }

        #stepper-form.was-validated .form-control:invalid:focus {
            box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .25);
        }

        /* .upload-input {
                    position: absolute;
                    font-size: 100px;
                    opacity: 0;
                    right: 0;
                    top: 0;
                } */
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @php
        $i = 0;
        $documents = $stream_master[0]->documents ?? collect();
    @endphp
    <div class="main-content pt-4 employee-page">
        <div class="form_section1_div">
            <div class="breadcrumb d-flex justify-content-between align-items-center">
                <h3 class="me-2">Employees</h3>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    Import Employees
                </button>
                
            </div>
            <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importModalLabel">Import Employees</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="importForm" action="{{ route('employee.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="importFile" class="form-label">Select File (XLSX or CSV)</label>
                                    <input type="file" name="file" id="importFile" class="form-control" accept=".xlsx,.csv" required>
                                    <small class="text-muted">Only .xlsx or .csv files are allowed.</small>
                                </div>
                                <div id="fileError" class="text-danger" style="display: none;">Invalid file type. Please upload an XLSX or CSV file.</div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" id="processImport">Upload & Import</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="separator-breadcrumb border-top mb-3"></div>
            <form id="stepper-form" class="p-4" action="{{ !empty($stream_master) ? url('store-employee') : url('save-employee') }}" method="post" enctype="multipart/form-data" novalidate>
                @csrf
                @if ($errors->any())
                    <div class="alert alert-danger">
                        Please check the required fields highlighted below.
                    </div>
                @endif
                <input type="hidden" name="id" value="{{ $stream_master[0]->id ?? '' }}">
                <input type="hidden" id="save_exit_mode" name="save_exit_mode" value="0">
                <input type="hidden" id="next_step_hash" name="next_step_hash" value="">
                <div id="smartwizard">
                    <ul>
                        <li><a href="#step-1">Step 1<br /><small>Basic Information</small></a></li>
                        <li><a href="#step-hrms-employee-address">Step 2<br /><small>Address</small></a></li>
                        <li><a href="#step-hrms-emergency-contact">Step 3<br /><small>Emergency Contact</small></a></li>
                        <li><a href="#step-hrms-employee-education">Step 4<br /><small>Education</small></a></li>
                        <li><a href="#step-hrms-biometric-detail">Step 5<br /><small>Biometric</small></a></li>
                        <li><a href="#step-hrms-documents">Step 6<br /><small>Documents</small></a></li>
                        <li><a href="#step-hrms-bank-detail">Step 7<br /><small>Bank</small></a></li>
                        <li><a href="#step-hrms-statutory-information">Step 8<br /><small>Statutory</small></a></li>
                        <li><a href="#step-hrms-employee-experience">Step 9<br /><small>Experience</small></a></li>
                    </ul>
                    <div>

                    <!-- Step 1: Basic Information -->
                    <div class="step active" id="step-1">
                        <h5>Basic Information</h5>
                        <div class="row">
                            <div class="col-md-3 form-group mb-3">
                                <label for="FirstName">First Name <span class="text-danger">*</span></label>
                                <input class="form-control uperletter" id="FirstName" name="FirstName" type="text"
                                    value="{{ $stream_master[0]->first_name ?? '' }}" placeholder="First Name" required />
                                @error('FirstName')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label for="LastName">Last Name <span class="text-danger">*</span></label>
                                <input class="form-control uperletter" id="LastName" name="LastName" type="text"
                                    value="{{ $stream_master[0]->last_name ?? '' }}" placeholder="Last Name" required />
                                @error('LastName')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror

                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label for="Father_Name">Father Name</label>
                                <input class="form-control uperletter" id="Father_Name" name="Father_Name" type="text"
                                    value="{{ $stream_master[0]->father_name ?? '' }}" placeholder="Father Name" />
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label for="Email">Email <span class="text-danger">*</span></label>
                                <input class="form-control" id="Email" name="Email" type="text"
                                    value="{{ $stream_master[0]->email ?? '' }}" placeholder="Email" />
                                @error('Email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-3 form-group mb-3">
                                <label for="Gender">Gender <span class="text-danger">*</span></label>
                                <select class="form-control" name="Gender" required>
                                    <option value="">-- Please select --</option>
                                    <option value="male"
                                        {{ isset($stream_master[0]->gender) && $stream_master[0]->gender == 'male' ? 'selected' : '' }}>
                                        Male</option>
                                    <option value="female"
                                        {{ isset($stream_master[0]->gender) && $stream_master[0]->gender == 'female' ? 'selected' : '' }}>
                                        Female</option>
                                    <option value="other"
                                        {{ isset($stream_master[0]->gender) && $stream_master[0]->gender == 'other' ? 'selected' : '' }}>
                                        Other</option>
                                </select>
                                @error('Gender')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label for="DateOfBirth">Date Of Birth <span class="text-danger">*</span></label>
                                <input class="form-control" id="DateOfBirth" name="DateOfBirth" type="date"
                                    value="{{ $stream_master[0]->date_of_birth ?? '' }}" placeholder="Date Of Birth" required />
                                @error('DateOfBirth')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label for="JoiningDate">Joining Date <span class="text-danger">*</span></label>
                                <input class="form-control" id="JoiningDate" name="JoiningDate" type="date"
                                    value="{{ $stream_master[0]->date_of_joining ?? '' }}" placeholder="Joining Date" required />
                                @error('JoiningDate')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
							
                            <div class="col-md-3 form-group mb-3">
                                <label for="contact_number">Contact Number <span class="text-danger">*</span></label>
                                <input class="form-control uperletter" id="contact_number" name="contact_number"
                                    type="text" value="{{ $stream_master[0]->contact_number ?? '' }}"
                                    placeholder="Contact Number" pattern="[6-9][0-9]{9}" onchange="validateContactNumber(this)" required />
                                    <div class="text-danger" id="contact_number_error"></div>
                                @error('contact_number')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>



                        <!-- Shift ID -->
                        <div class="row">
                            <div class="col-md-3 form-group mb-3">
                                <label for="ShiftID">Shift <span class="text-danger">*</span></label>
                                <select name="ShiftID" class="form-control" id="ShiftID" required>
                                    <option value="" selected>-- Please select --</option>
                                    @foreach ($shifts as $shift)
                                        <option value="{{ $shift->id }}"
                                            {{ !empty($stream_master[0]) && $stream_master[0]->shift_id == $shift->id ? 'selected' : '' }}>
                                            {{ $shift->shiftType->shift_type_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ShiftID')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label for="DepartmentID">Department <span class="text-danger">*</span></label>
                                <select name="DepartmentID" class="form-control uperletter" id="DepartmentID" required>
                                    <option value="" selected>-- Please select --</option>
                                    @foreach ($deparments as $deparment)
                                        <option value="{{ $deparment->id }}"
                                            {{ !empty($stream_master[0]) && $stream_master[0]->department_id == $deparment->id ? 'selected' : '' }}>
                                            {{ $deparment->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('DepartmentID')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <label for="PositionID">Position <span class="text-danger">*</span></label>
                                <select name="PositionID" class="form-control uperletter" id="PositionID" required>
                                    <option value="" selected>-- Please select --</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}"
                                            {{ !empty($stream_master[0]) && $stream_master[0]->position_id == $position->id ? 'selected' : '' }}>
                                            {{ $position->position_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('PositionID')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 form-group mb-3">
                                <label for="employment_type">Staff Type <span class="text-danger">*</span></label>
                                <select name="employment_type" class="form-control uperletter" id="employment_type" required>
                                    <option value="" selected>-- Please select --</option>
                                    @foreach ($staffTypes as $staffType)
                                        <option value="{{ $staffType->id }}"
                                            {{ !empty($stream_master[0]) && $stream_master[0]->staff_type_id == $staffType->id ? 'selected' : '' }}>
                                            {{ $staffType->staff_type_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employment_type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">

							<!-- Marital Status -->
                            <div class="col-md-3 form-group mb-3">
                                <label for="marital_status">Marital Status</label>
                                <select class="form-control" id="marital_status" name="marital_status">
                                    <option value="">-- Please select --</option>
                                    <option value="single"
                                        {{ isset($stream_master[0]->marital_status) && $stream_master[0]->marital_status == 'single' ? 'selected' : '' }}>
                                        Single</option>
                                    <option value="married"
                                        {{ isset($stream_master[0]->marital_status) && $stream_master[0]->marital_status == 'married' ? 'selected' : '' }}>
                                        Married</option>
                                    <option value="divorced"
                                        {{ isset($stream_master[0]->marital_status) && $stream_master[0]->marital_status == 'divorced' ? 'selected' : '' }}>
                                        Divorced</option>
                                </select>
                            </div>

                            <!-- Spouse Name (show only if marital status is 'married') -->
                            <div class="col-md-3 form-group mb-3" id="spouse_name_div" style="display:none;">
                                <label for="spouse_name">Spouse Name</label>
                                <input class="form-control uperletter" id="spouse_name" name="spouse_name"
                                    type="text" value="{{ $stream_master[0]->spouse_name ?? '' }}"
                                    placeholder="Spouse Name" />
                            </div>
							<div class="col-md-3 form-group mb-3">
                                <label for="ConfirmationDate">Confirmation Date <span class="text-danger">*</span></label>
                                <input class="form-control" id="ConfirmationDate" name="ConfirmationDate" type="date"
                                    value="{{ $stream_master[0]->confirmation_date ?? '' }}" placeholder="ConfirmationDate" />
                                @error('ConfirmationDate')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 form-group mb-3">
                                <label for="is_vacation">Is Vacation</label>
                                <br>
                                <input id="is_vacation" type="checkbox" name="is_vacation" value="1"
                                    {{ isset($stream_master[0]) && $stream_master[0]->is_vacation ? 'checked' : '' }} />
                                @error('is_vacation')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
							<!-- Employee Status -->
							<div class="col-md-3 form-group mb-3">
								<label for="employee_status">Employee Status <span class="text-danger">*</span></label>
								@php
									$status = old('employee_status', isset($stream_master[0]) ? strtolower(trim($stream_master[0]->employee_status)) : '');
								@endphp
					
								<select class="form-control" id="employee_status" name="employee_status" required>
									<option value="">-- Please select --</option>
									<option value="active"   {{ $status == 'active' ? 'selected' : '' }}>Active</option>
									<option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
									<option value="retired"  {{ $status == 'retired' ? 'selected' : '' }}>Retired</option>
								</select>
								@error('employee_status')
									<div class="text-danger">{{ $message }}</div>
								@enderror
							</div>

							<!-- Unified status date field -->
							<div class="col-md-3 form-group mb-3" id="status_date_wrapper" style="display:none;">
								<label for="employee_status_date">Status Date <span class="text-danger">*</span></label>
								<input type="date" class="form-control" name="employee_status_date" id="employee_status_date"
									value="{{ old('employee_status_date', $stream_master[0]->employee_status_date ?? '') }}">
								@error('employee_status_date')
									<div class="text-danger">{{ $message }}</div>
								@enderror
							</div>
						</div>
                        <div class="row">

                            <div class="col-md-3 form-group mb-4">
                                <label for="profile_picture">Profile Picture </label>
                                <br>
                                <!-- Show existing profile picture if it exists, otherwise show a default placeholder -->
                                <img id="profilePreview"
                                    src="{{ isset($stream_master[0]) && $stream_master[0]->profile_picture
                                        ? asset('/uploads/admin/' . $stream_master[0]->profile_picture)
                                        : asset('assets/frontend/images/default-avatar.png') }}"
                                    alt="Profile Picture" class="passport-size mb-3">
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-3 form-group  upload-btn-wrapper mb-4">
                                <input type="file" name="profile_picture" id="profile_picture" class="form-control"
                                    accept="image/*">
                            </div>
                        </div>
                    </div>

                    {{-- <div class="step" id="step-hrms-employee-address">
                    <h5>Employee Address Details</h5>
                    <div id="address-fields-container">
                        <div class="row address-fields">
                            <div class="col-md-4 form-group mb-3">
                                <label for="address_type">Address Type</label>
                                <input type="text" class="form-control" name="address_type[]"
                                    placeholder="Address Type" />
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="address_line">Address Line</label>
                                <input type="text" class="form-control" name="address_line[]"
                                    placeholder="Address Line" />
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="city">City</label>
                                <input type="text" class="form-control" name="city[]" placeholder="City" />
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="tehsil">Tehsil</label>
                                <input type="text" class="form-control" name="tehsil[]" placeholder="Tehsil" />
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="district">District</label>
                                <input type="text" class="form-control" name="district[]" placeholder="District" />
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="pin_code">Pin Code</label>
                                <input type="text" class="form-control" name="pin_code[]" placeholder="Pin Code" />
                            </div>
                            <div class="col-md-12 form-group mb-3">
                                <button type="button" class="btn btn-raised btn-danger remove-address"
                                    style="display: none;">
                                    Remove Address
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary" id="add-new-address">
                        <i class="i-plus"></i>Add New Address
                    </button>
                </div> --}}

                    <div class="step"  id="step-hrms-employee-address">
                        <h5>Employee Address Details</h5>
                        <div id="address-fields-container">
                            @if (isset($stream_master) && count($stream_master[0]->addresses) > 0)
                                <!-- Loop through existing addresses if any -->
                                @foreach ($stream_master[0]->addresses as $index => $address)
                                    <div class="row address-fields">
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="address_type">Address Type</label>
                                            <input type="text" class="form-control"
                                                name="address_type[{{ $index }}]"
                                                value="{{ old('address_type.' . $index, $address->address_type) }}"
                                                placeholder="Address Type" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="address_line">Address Line</label>
                                            <input type="text" class="form-control"
                                                name="address_line[{{ $index }}]"
                                                value="{{ old('address_line.' . $index, $address->address_line) }}"
                                                placeholder="Address Line" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="city">City</label>
                                            <input type="text" class="form-control" name="city[{{ $index }}]"
                                                value="{{ old('city.' . $index, $address->city) }}" placeholder="City" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="tehsil">Tehsil</label>
                                            <input type="text" class="form-control"
                                                name="tehsil[{{ $index }}]"
                                                value="{{ old('tehsil.' . $index, $address->tehsil) }}"
                                                placeholder="Tehsil" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="district">District</label>
                                            <input type="text" class="form-control"
                                                name="district[{{ $index }}]"
                                                value="{{ old('district.' . $index, $address->district) }}"
                                                placeholder="District" />
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label for="pin_code">Pin Code</label>
                                            <input type="text" class="form-control"
                                                name="pin_code[{{ $index }}]"
                                                value="{{ old('pin_code.' . $index, $address->pin_code) }}"
                                                placeholder="Pin Code" />
                                        </div>
                                        <!-- Remove Button -->
                                        <div class="col-md-12 form-group mb-3">
                                            <button type="button" class="btn btn-raised btn-danger remove-address"
                                                style="display: {{ $index > 0 ? 'block' : 'none' }};">
                                                Remove Address
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <!-- If no addresses, show an empty address form field -->
                                <div class="row address-fields">
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address_type">Address Type</label>
                                        <input type="text" class="form-control" name="address_type[]"
                                            placeholder="Address Type" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="address_line">Address Line</label>
                                        <input type="text" class="form-control" name="address_line[]"
                                            placeholder="Address Line" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="city">City</label>
                                        <input type="text" class="form-control" name="city[]" placeholder="City" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="tehsil">Tehsil</label>
                                        <input type="text" class="form-control" name="tehsil[]"
                                            placeholder="Tehsil" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="district">District</label>
                                        <input type="text" class="form-control" name="district[]"
                                            placeholder="District" />
                                    </div>
                                    <div class="col-md-4 form-group mb-3">
                                        <label for="pin_code">Pin Code</label>
                                        <input type="text" class="form-control" name="pin_code[]"
                                            placeholder="Pin Code" />
                                    </div>
                                    <!-- Remove Button -->
                                    <div class="col-md-12 form-group mb-3">
                                        <button type="button" class="btn btn-raised btn-danger remove-address"
                                            style="display: none;">
                                            Remove Address
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <button type="button" class="btn btn-primary" id="add-new-address">
                            <i class="i-plus"></i>Add New Address
                        </button>
                    </div>




                    <div class="step" id="step-hrms-emergency-contact">
                        <h5>Emergency Contact Details</h5>
                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label for="contact_name">Contact Name</label>
                                <input type="text" class="form-control" id="contact_name" name="contact_name"
                                    value="{{ $stream_master[0]->emergencyContacts[0]->contact_name ?? '' }}"
                                    placeholder="Emergency Contact Name" />
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="relationship">Relationship</label>
                                <input type="text" class="form-control" id="relationship" name="relationship"
                                    value="{{ $stream_master[0]->emergencyContacts[0]->relationship ?? '' }}"
                                    placeholder="Relationship" />
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number"
                                    value="{{ $stream_master[0]->emergencyContacts[0]->phone_number ?? '' }}"
                                    placeholder="Phone Number" onchange="validateContactNumber(this)" />
                                    <div class="text-danger" id="phone_number_error"></div>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="alternative_phone_number">Alternative Phone Number</label>
                                <input type="text" class="form-control" id="alternative_phone_number"
                                    name="alternative_phone_number"
                                    value="{{ $stream_master[0]->emergencyContacts[0]->alternative_phone_number ?? '' }}"
                                    placeholder="Alternative Phone Number"   onchange="validateContactNumber(this)"/>
                                    <div class="text-danger" id="alternative_phone_number_error"></div>
                            </div>
                        </div>
                    </div>


                    <div class="step" id="step-hrms-employee-education">
                        <h5>Employee Educational Qualification</h5>
                        <div id="education-fields-container">
                            <!-- Existing educational qualification form field (will be cloned) -->
                            <div class="row education-fields">
                                <div class="col-md-4 form-group mb-3">
                                    <label for="qualification">Qualification</label>
                                    <input type="text" class="form-control" name="qualification[]"
                                        value="{{ $stream_master[0]->educationalQualifications[0]->qualification ?? '' }}"
                                        placeholder="Qualification" />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="specialization">Specialization</label>
                                    <input type="text" class="form-control" name="specialization[]"
                                        value="{{ $stream_master[0]->educationalQualifications[0]->specialization ?? '' }}"
                                        placeholder="Specialization" />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="institution_name">Institution Name</label>
                                    <input type="text" class="form-control" name="institution_name[]"
                                        value="{{ $stream_master[0]->educationalQualifications[0]->institution_name ?? '' }}"
                                        placeholder="Institution Name" />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="year_of_graduation">Year of Graduation</label>
                                    <input type="text" class="form-control" name="year_of_graduation[]"
                                        value="{{ $stream_master[0]->educationalQualifications[0]->year_of_graduation ?? '' }}"
                                        placeholder="Year of Graduation" />
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label for="certification">Certification</label>
                                    <input type="text" class="form-control" name="certification[]"
                                        value="{{ $stream_master[0]->educationalQualifications[0]->certification ?? '' }}"
                                        placeholder="Certification" />
                                </div>
                                <!-- Remove Button -->
                                <div class="col-md-12 form-group mb-3">
                                    <button type="button" class="btn btn-raised btn-danger  remove-education"
                                        style="display: none;">
                                        Remove Education
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary" id="add-new-education">
                            Add New Education
                        </button>
                    </div>



                    <div class="step" id="step-hrms-biometric-detail">
                        <h5>Biometric Details</h5>
                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label for="card_number">Card Number</label>
                                <input type="text" class="form-control" id="card_number" name="card_number"
                                    value="{{ $stream_master[0]->biometricDetails->card_number ?? '' }}"
                                    placeholder="Card Number" />
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="ess_emp_code">ESS Employee Code</label>
                                <input type="text" class="form-control" id="ess_emp_code" name="ess_emp_code"
                                    value="{{ $stream_master[0]->biometricDetails->ess_emp_code ?? '' }}"
                                    placeholder="ESS Employee Code" />
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="device_code">Device Code</label>
                                <input type="text" class="form-control" id="device_code" name="device_code"
                                    value="{{ $stream_master[0]->biometricDetails->device_code ?? '' }}"
                                    placeholder="Device Code" />
                            </div>
                        </div>
                    </div>
                    <div class="step" id="step-hrms-documents">
                        <h5>Document Details</h5>
                        <br>
                        <div class="row">
                            <div class="col-md-4 form-group mb-3">
                                <label for="id_proof">
                                    <input type="checkbox" id="id_proof" name="id_proof"
                                        {{ $documents->where('document_type', 'ID Proof')->where('is_uploaded', true)->isNotEmpty() ? 'checked' : '' }} />
                                    ID Proof (e.g., Aadhaar Card, Passport, Driving License)
                                </label>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="address_proof">
                                    <input type="checkbox" id="address_proof" name="address_proof"
                                        {{ $documents->where('document_type', 'Address Proof')->where('is_uploaded', true)->isNotEmpty() ? 'checked' : '' }} />
                                    Address Proof (e.g., Utility Bill, Rent Agreement)
                                </label>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="educational_certificate">
                                    <input type="checkbox" id="educational_certificate" name="educational_certificate"
                                        {{ $documents->where('document_type', 'Educational Certificate')->where('is_uploaded', true)->isNotEmpty() ? 'checked' : '' }} />
                                    Educational Certificate (e.g., Degree, Diploma)
                                </label>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="experience_letter">
                                    <input type="checkbox" id="experience_letter" name="experience_letter"
                                        {{ $documents->where('document_type', 'Experience Letter')->where('is_uploaded', true)->isNotEmpty() ? 'checked' : '' }} />
                                    Experience Letter
                                </label>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="passport_size_photo">
                                    <input type="checkbox" id="passport_size_photo" name="passport_size_photo"
                                        {{ $documents->where('document_type', 'Passport Size Photograph')->where('is_uploaded', true)->isNotEmpty() ? 'checked' : '' }} />
                                    Passport Size Photograph
                                </label>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="police_verification">
                                    <input type="checkbox" id="police_verification" name="police_verification"
                                        {{ $documents->where('document_type', 'Police Verification Report')->where('is_uploaded', true)->isNotEmpty() ? 'checked' : '' }} />
                                    Police Verification Report
                                </label>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="medical_certificate">
                                    <input type="checkbox" id="medical_certificate" name="medical_certificate"
                                        {{ $documents->where('document_type', 'Medical Certificate')->where('is_uploaded', true)->isNotEmpty() ? 'checked' : '' }} />
                                    Medical Certificate (if required)
                                </label>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="salary_slip">
                                    <input type="checkbox" id="salary_slip" name="salary_slip"
                                        {{ $documents->where('document_type', 'Last Salary Slip')->where('is_uploaded', true)->isNotEmpty() ? 'checked' : '' }} />
                                    Last Salary Slip (if applicable)
                                </label>
                            </div>
                        </div>
                    </div>


                    {{-- <div class="step" id="step-hrms-employee-documents">
                    <h5>Upload Employee Documents</h5> --}}
                    {{-- <div id="document-fields-container">
                        <!-- Existing document form field (will be cloned) -->
                        <div class="row document-fields">
                            <div class="col-md-5 form-group mb-3">
                                <label for="document_type">Document Type</label>
                                <select class="form-control" name="document_type[]">
                                    <option value="">-- Please select --</option>
                                    <option value="ID Proof">ID Proof</option>
                                    <option value="Address Proof">Address Proof</option>
                                    <option value="Educational Certificate">Educational Certificate</option>
                                    <option value="Experience Letter">Experience Letter</option>
                                    <!-- Add other document types as needed -->
                                </select>
                            </div>
                            <div class="col-md-5 form-group mb-3">
                                <label for="upload">Upload Document</label>
                                <input type="file" class="form-control" name="upload[]" />
                            </div>
                            <!-- Remove Button -->
                            <div class="col-md-2 form-group mb-3">
                                <button type="button" class="btn btn-raised btn-danger  remove-document"
                                    style="display: none;">
                                    Remove Document
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary" id="add-new-document">
                        Add New Document
                    </button> --}}


                    {{-- <div id="document-fields-container">
                        <!-- Existing document form field (will be cloned) -->
                        @if (isset($stream_master) && count($stream_master) > 0)
                            @foreach ($stream_master[0]->documents as $document)
                                <!-- Loop through the existing documents -->
                                <div class="row document-fields">
                                    <div class="col-md-5 form-group mb-3">
                                        <label for="document_type">Document Type</label>
                                        <select class="form-control" name="document_type[]">
                                            <option value="">-- Please select --</option>
                                            <option value="ID Proof"
                                                {{ $document->document_type == 'ID Proof' ? 'selected' : '' }}>ID Proof
                                            </option>
                                            <option value="Address Proof"
                                                {{ $document->document_type == 'Address Proof' ? 'selected' : '' }}>Address
                                                Proof</option>
                                            <option value="Educational Certificate"
                                                {{ $document->document_type == 'Educational Certificate' ? 'selected' : '' }}>
                                                Educational Certificate</option>
                                            <option value="Experience Letter"
                                                {{ $document->document_type == 'Experience Letter' ? 'selected' : '' }}>
                                                Experience Letter</option>
                                            <!-- Add other document types as needed -->
                                        </select>
                                    </div>
                                    <div class="col-md-5 form-group mb-3">
                                        <label for="upload">Upload Document</label>
                                        <input type="file" class="form-control" value=" {{ $document->is_uploaded }}>" name="upload[]" />
                                        <!-- If you want to show the existing document file, you can include the link here -->
                                        @if ($document->file_path)
                                            <a href="{{ asset('/uploads/admin/' . $document->file_path) }}"
                                                class="existing-doc-link" target="_blank">View
                                                Existing Document</a>
                                        @endif
                                    </div>
                                    <!-- Remove Button -->
                                    <div class="col-md-2 form-group mb-3">
                                        <button type="button" class="btn btn-raised btn-danger remove-document">
                                            Remove Document
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                    </div>
                    <button type="button" class="btn btn-primary" id="add-new-document">
                        Add New Document
                    </button>
                @else
                    <!-- Existing document form field (will be cloned) -->
                    <div class="row document-fields">
                        <div class="col-md-5 form-group mb-3">
                            <label for="document_type">Document Type</label>
                            <select class="form-control" name="document_type[]">
                                <option value="">-- Please select --</option>
                                <option value="ID Proof">ID Proof</option>
                                <option value="Address Proof">Address Proof</option>
                                <option value="Educational Certificate">Educational Certificate</option>
                                <option value="Experience Letter">Experience Letter</option>
                                <!-- Add other document types as needed -->
                            </select>
                        </div>
                        <div class="col-md-5 form-group mb-3">
                            <label for="upload">Upload Document</label>
                            <input type="file" class="form-control" name="upload[]" />
                        </div>
                        <!-- Remove Button -->
                        <div class="col-md-2 form-group mb-3">
                            <button type="button" class="btn btn-raised btn-danger  remove-document"
                                style="display: none;">
                                Remove Document
                            </button>
                        </div>
                    </div>

                </div>
                <button type="button" class="btn btn-primary" id="add-new-document">
                    Add New Document
                </button>
                @endif --}}


            



            <div class="step" id="step-hrms-bank-detail">
                <h5>Bank Details</h5>
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name"
                            value="{{ $stream_master[0]->bankDetails[0]->bank_name ?? '' }}" placeholder="Bank Name" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="account_number">Account Number</label>
                        <input type="text" class="form-control" id="account_number" name="account_number"
                            value="{{ $stream_master[0]->bankDetails[0]->account_number ?? '' }}"
                            placeholder="Account Number" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="ifsc_code">IFSC Code</label>
                        <input type="text" class="form-control" id="ifsc_code" name="ifsc_code"
                            value="{{ $stream_master[0]->bankDetails[0]->ifsc_code ?? '' }}" placeholder="IFSC Code" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="branch_name">Branch Name</label>
                        <input type="text" class="form-control" id="branch_name" name="branch_name"
                            value="{{ $stream_master[0]->bankDetails[0]->branch_name ?? '' }}"
                            placeholder="Branch Name" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="pan_number">PAN Number</label>
                        <input type="text" class="form-control" id="pan_number" name="pan_number"
                            value="{{ $stream_master[0]->bankDetails[0]->pan_number ?? '' }}" placeholder="PAN Number" />
                    </div>
                </div>
            </div>

            {{-- <div class="step" id="step-hrms-employee-salary">
            <h5>Employee Salary Details</h5>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="basic_salary">Basic Salary</label>
                    <input type="number" class="form-control" name="basic_salary"
                        value="{{ $stream_master[0]->salaries[0]->basic_salary ?? '' }}" placeholder="Basic Salary" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="allowances">Allowances</label>
                    <input type="number" class="form-control" name="allowances"
                        value="{{ $stream_master[0]->salaries[0]->allowances ?? '' }}" placeholder="Allowances" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="deductions">Deductions</label>
                    <input type="number" class="form-control" name="deductions"
                        value="{{ $stream_master[0]->salaries[0]->deductions ?? '' }}" placeholder="Deductions" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="net_salary">Net Salary</label>
                    <input type="number" class="form-control" name="net_salary"
                        value="{{ $stream_master[0]->salaries[0]->net_salary ?? '' }}" placeholder="Net Salary" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="health_insurance">Health Insurance</label>
                    <input type="number" class="form-control" name="health_insurance"
                        value="{{ $stream_master[0]->salaries[0]->health_insurance ?? '' }}"
                        placeholder="Health Insurance" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="retirement_benefits">Retirement Benefits</label>
                    <input type="number" class="form-control" name="retirement_benefits"
                        value="{{ $stream_master[0]->salaries[0]->retirement_benefits ?? '' }}"
                        placeholder="Retirement Benefits" />
                </div>
            </div>
        </div> --}}


            <div class="step" id="step-hrms-statutory-information">
                <h5>Statutory Information</h5>
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="esic_number">ESIC Number</label>
                        <input type="text" class="form-control" id="esic_number" name="esic_number"
                            value="{{ $stream_master[0]->statutoryInformation->esic_number ?? '' }}"
                            placeholder="ESIC Number" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="epf_number">EPF Number</label>
                        <input type="text" class="form-control" id="epf_number" name="epf_number"
                            value="{{ $stream_master[0]->statutoryInformation->epf_number ?? '' }}"
                            placeholder="EPF Number" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="uan_number">UAN Number</label>
                        <input type="text" class="form-control" id="uan_number" name="uan_number"
                            value="{{ $stream_master[0]->statutoryInformation->uan_number ?? '' }}"
                            placeholder="UAN Number" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="samagra_id">Samagra ID</label>
                        <input type="text" class="form-control" id="samagra_id" name="samagra_id"
                            value="{{ $stream_master[0]->statutoryInformation->samagra_id ?? '' }}"
                            placeholder="Samagra ID" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="aadhar_number">Aadhar Number</label>
                        <input type="text" class="form-control" id="aadhar_number" name="aadhar_number"
                            value="{{ $stream_master[0]->statutoryInformation->aadhar_number ?? '' }}"
                            placeholder="Aadhar Number" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="ayushman_number">Ayushman Number</label>
                        <input type="text" class="form-control" id="ayushman_number" name="ayushman_number"
                            value="{{ $stream_master[0]->statutoryInformation->ayushman_number ?? '' }}"
                            placeholder="Ayushman Number" />
                    </div>
                </div>
            </div>


            <div class="step" id="step-hrms-employee-experience">
                <h5>Employee Experience Details</h5>
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="organization_name">Organization Name</label>
                        <input type="text" class="form-control" id="organization_name" name="organization_name"
                            value="{{ $stream_master[0]->workExperiences[0]->organization_name ?? '' }}"
                            placeholder="Organization Name" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="job_title">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title"
                            value="{{ $stream_master[0]->workExperiences[0]->job_title ?? '' }}"
                            placeholder="Job Title" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date"
                            value="{{ $stream_master[0]->workExperiences[0]->start_date ?? '' }}" name="start_date" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date"
                            value="{{ $stream_master[0]->workExperiences[0]->end_date ?? '' }}" name="end_date" />
                    </div>
                    <div class="col-md-4 form-group mb-3">
                        <label for="roles_responsibilities">Roles and Responsibilities</label>
                        <textarea class="form-control" id="roles_responsibilities" name="roles_responsibilities" rows="3"
                            placeholder="Roles and Responsibilities">{{ $stream_master[0]->workExperiences[0]->roles_responsibilities ?? '' }}</textarea>
                    </div>
					<div class="col-md-4 form-group mb-3">
						<label for="total_experience">Total Experience</label>
						<input type="text" class="form-control" id="total_experience" name="total_experience"
							value="{{ $stream_master[0]->workExperiences[0]->total_experience ?? '' }}"
							placeholder="e.g. 2 years 3 months" />
					</div>
					
                </div>
            </div>

            <div class="add-reset-buttons">
                <button type="button" class="btn btn-secondary" id="reset-button">Reset</button>
                @if (request()->route()->getName() !== 'employee')
                    <a href="{{ url('employee') }}" class="btn btn-primary">Add New</a>
                @endif
            </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">List of Saved Employees :-</h1>
                <form method="GET" action="{{ route('employee') }}" class="d-flex align-items-end gap-2 ms-auto">
                    <div>
                        <label for="employee_status_filter" class="form-label mb-1">Employee Status</label>
                        <select name="employee_status" id="employee_status_filter" class="form-control">
                            <option value="">All</option>
                            <option value="active" @selected(request('employee_status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('employee_status') === 'inactive')>Inactive</option>
                            <option value="retired" @selected(request('employee_status') === 'retired')>Retired</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('employee') }}" class="btn btn-secondary">Reset</a>
                    <a href="{{ route('employees.export.csv', array_filter(request()->only('employee_status'))) }}" class="btn btn-success">Export Employees CSV</a>
                </form>

            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="card text-start">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table"
                            style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Ess Emp Code</th>
                                    <th>Date Of Birth</th>
                                    <th>Joining Date</th>
                                    <th>Status</th>
                                    {{-- <th>Departure Date</th> --}}
                                    <th>Action </th>


                                </tr>
                            </thead>
                            <tbody>

                                @if ($stream->isNotEmpty())
                                    @foreach ($stream as $streams)
                                        <?php $notificationData1 = json_decode($streams->json_str, true); ?>
                                        <tr>
                                            <td>{{ ++$i }}</td>



                                            <td class= "uperletter">{{ $streams->first_name }}</td>
                                            <td class= "uperletter">{{ $streams->last_name }}</td>
                                            <td>{{ $streams->email }}</td>
                                            <td>{{ $streams->contact_number }}</td>
                                            <td class="uperletter">
                                                @empty($streams->addresses[0])
                                                    N/A
                                                @else
                                                    {{ $streams->addresses[0]->address_line ?? 'N/A' }},
                                                    {{ $streams->addresses[0]->city ?? 'N/A' }},
                                                    {{ $streams->addresses[0]->pin_code ?? 'N/A' }}
                                                @endempty
                                            </td>
                                            

                                            <td class= "uperletter">{{ $streams->biometricDetails->ess_emp_code ?? 'N/A' }}
                                            </td>
                                            <td>{{ date('d-m-Y', strtotime($streams->date_of_birth)) }}</td>

                                            <td>{{ date('d-m-Y', strtotime($streams->date_of_joining)) }}</td>
                                            <td class="uperletter">{{ $streams->employee_status ?? 'N/A' }}</td>
                                            <td class='d-flex'>
                                                <a class="btn btn-primary m-1"
                                                    href="{{ url('view-employee') . '/' . $streams->id }}">Edit</a>
                                                <?php $a = $streams->id; ?>
                                                <a class="btn btn-raised ripple btn-danger m-1"
                                                    href="{{ url('delete-employee') . '/' . $streams->id }}"
                                                    onclick="confirmDelete(event)">Delete</a>
                                            </td>


                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="11" class="text-center">No Data Found</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>

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

        document.addEventListener('DOMContentLoaded', function() {
            $("#reset").on("click", function() {
                $("#FirstName").val("");
                $("#LastName").val("");
                $("#Email").val("");
                $("#Phone").val("");
                $("#Address").val("");
                $("#ess_emp_code").val("");
                $("#DateOfBirth").val("");
                $("#JoiningDate").val("");
                $("#DepartureDate").val("");
                $("#DepartmentID").val("");
                $("#PositionID").val("");
                $("#DeviceCode").val("");
                $("#Company").val("");
                $("#Location").val("");
                $("#Designation").val("");
                $("#Grade").val("");
                $("#Team").val("");
                $("#Category").val("");
                $("#EmploymentType").val("");
                $("#Gender").val("");
                $("#DOJ").val("");
                $("#DOC").val("");
                $("#CardNumber").val("");
                $("#ShiftRoaster").val("");
                $("#Status").val("");
                $("#City").val("");
                $("#Taluka").val("");
                $("#District").val("");
                $("#PIN_Code").val("");
                $("#Experience").val("");
                $("#Educational_Qualification").val("");
                $("#Previous_Employer").val("");
                $("#Prevoius_Designation").val("");
                $("#Previous_Salary").val("");
                $("#Duration").val("");
                $("#Maratial_Status").val("");
                $("#Spouse_Name").val("");
                $("#Spouse_Contact_No").val("");
                $("#No_of_Childern").val("");
                $("#Serial_No").val("");
                $("#Joining_Designation").val("");
                $("#Joining_Grade").val("");
                $("#Current_Grade").val("");
                $("#Working_Shift").val("");
                $("#Default_In_Time").val("");
                $("#Default_Out_Time").val("");
                $("#Default_Total_Time").val("");
                $("#Bank_Name").val("");
                $("#Bank_Branch_Name").val("");
                $("#Account_No").val("");
                $("#Do_Not_Apply_EPF_Limit").val("");
                $("#ESI_No").val("");
                $("#PAN_No").val("");
                $("#Ward_Study_in_Institute").val("");
                $("#Is_Discontinued").val("");
                $("#Leaving_Date").val("");
                $("#Adhar_CardNo").val("");
                $("#UAN_No").val("");
                $("#IFSCCODE").val("");
                $("#AyushmanNo").val("");
                $("#IsFirstDoseVaccinated").val("");
                $("#FirstDoseVaccinatedDate").val("");
                $("#IsSecondDoseVaccinated").val("");
                $("#SecondDoseVaccinatedDate").val("");
                $("#IsBoosterDoseVaccinated").val("");
                $("#BoosterDoseVaccinatedDate").val("");
                $("#samagra_id").val("");
                $("#Police_Verification").val("");
                $("#EmpName_as_on_Adhaar").val("");
                $("#PersonalEmail").val("");
                $("#staff_type").val("");
                $("#is_vacatation_staff").val("");
            });

        })
    </script>
    <script>

        // Add new address field
        document.getElementById("add-new-address").addEventListener("click", function() {
            var addressFieldsContainer = document.getElementById("address-fields-container");
            var addressFields = document.querySelector(".address-fields"); // Get the first set of address fields
            var clonedFields = addressFields.cloneNode(true); // Clone the address fields

            // Clear all the input fields in the cloned node (optional, for resetting values)
            var inputs = clonedFields.querySelectorAll("input");
            inputs.forEach(function(input) {
                input.value = ""; // Clear the input fields
            });

            // Add remove button functionality to cloned fields
            var removeButton = clonedFields.querySelector(".remove-address");
            removeButton.style.display = "block"; // Show the remove button in the cloned fields
            removeButton.addEventListener("click", function() {
                clonedFields.remove(); // Remove this specific address fields set
                toggleRemoveAddressButtonVisibility(); // Re-check if only one address remains
            });

            // Append the cloned address fields to the container
            addressFieldsContainer.appendChild(clonedFields);

            toggleRemoveAddressButtonVisibility(); // Re-check visibility of remove button after cloning
        });

        // Add remove button functionality to the first set of address fields
        document.querySelectorAll(".remove-address").forEach(function(removeButton) {
            removeButton.addEventListener("click", function() {
                var parentRow = removeButton.closest(".address-fields"); // Get the closest address row
                parentRow.remove(); // Remove this specific address fields set
                toggleRemoveAddressButtonVisibility(); // Re-check if only one address remains
            });
        });

        // Function to toggle visibility of remove button
        function toggleRemoveAddressButtonVisibility() {
            var addressFieldsCount = document.querySelectorAll(".address-fields").length;
            var removeButtons = document.querySelectorAll(".remove-address");

            if (addressFieldsCount > 1) {
                removeButtons.forEach(function(button) {
                    button.style.display = "block"; // Show remove button if more than one address
                });
            } else {
                removeButtons.forEach(function(button) {
                    button.style.display = "none"; // Hide remove button if only one address
                });
            }
        }

        // Initial check to hide/remove buttons when only one address field is present
        toggleRemoveAddressButtonVisibility();


        // Add new education field
        document.getElementById("add-new-education").addEventListener("click", function() {
            var educationFieldsContainer = document.getElementById("education-fields-container");
            var educationFields = document.querySelector(
                ".education-fields"); // Get the first set of education fields
            var clonedFields = educationFields.cloneNode(true); // Clone the education fields

            // Clear all the input fields in the cloned node (optional, for resetting values)
            var inputs = clonedFields.querySelectorAll("input");
            inputs.forEach(function(input) {
                input.value = ""; // Clear the input fields
            });

            // Add remove button functionality to cloned fields
            var removeButton = clonedFields.querySelector(".remove-education");
            removeButton.style.display = "block"; // Show the remove button in the cloned fields
            removeButton.addEventListener("click", function() {
                clonedFields.remove(); // Remove this specific education fields set
                toggleRemoveEducationButtonVisibility(); // Re-check if only one education remains
            });

            // Append the cloned education fields to the container
            educationFieldsContainer.appendChild(clonedFields);

            toggleRemoveEducationButtonVisibility(); // Re-check visibility of remove button after cloning
        });

        // Add remove button functionality to the first set of education fields
        document.querySelectorAll(".remove-education").forEach(function(removeButton) {
            removeButton.addEventListener("click", function() {
                var parentRow = removeButton.closest(".education-fields"); // Get the closest education row
                parentRow.remove(); // Remove this specific education fields set
                toggleRemoveEducationButtonVisibility(); // Re-check if only one education remains
            });
        });

        // Function to toggle visibility of remove button
        function toggleRemoveEducationButtonVisibility() {
            var educationFieldsCount = document.querySelectorAll(".education-fields").length;
            var removeButtons = document.querySelectorAll(".remove-education");

            if (educationFieldsCount > 1) {
                removeButtons.forEach(function(button) {
                    button.style.display = "block"; // Show remove button if more than one education
                });
            } else {
                removeButtons.forEach(function(button) {
                    button.style.display = "none"; // Hide remove button if only one education
                });
            }
        }

        // Initial check to hide/remove buttons when only one education field is present
        toggleRemoveEducationButtonVisibility();



        //Employee document

        // Add new document field
        // document.getElementById("add-new-document").addEventListener("click", function() {
        //     var documentFieldsContainer = document.getElementById("document-fields-container");
        //     var documentFields = document.querySelector(".document-fields"); // Get the first set of document fields
        //     var clonedFields = documentFields.cloneNode(true); // Clone the document fields

        //     // Clear all the input fields in the cloned node (optional, for resetting values)
        //     var inputs = clonedFields.querySelectorAll("input");
        //     inputs.forEach(function(input) {
        //         input.value = ""; // Clear the input fields
        //     });

        //     // Reset the dropdown selection
        //     var select = clonedFields.querySelector("select");
        //     select.selectedIndex = 0; // Reset dropdown to default option
        //     // Remove the "View Existing Document" link from the cloned fields
        //     var existingDocLink = clonedFields.querySelector('.existing-doc-link');
        //     if (existingDocLink) {
        //         existingDocLink.remove(); // Remove the "View Existing Document" link completely
        //     }
        //     // Add remove button functionality to cloned fields
        //     var removeButton = clonedFields.querySelector(".remove-document");
        //     removeButton.style.display = "block"; // Show the remove button in the cloned fields
        //     removeButton.addEventListener("click", function() {
        //         clonedFields.remove(); // Remove this specific document fields set
        //         toggleRemoveDocumentButtonVisibility(); // Re-check if only one document remains
        //     });

        //     // Append the cloned document fields to the container
        //     documentFieldsContainer.appendChild(clonedFields);

        //     toggleRemoveDocumentButtonVisibility(); // Re-check visibility of remove button after cloning
        // });

        // Add remove button functionality to the first set of document fields
        // document.querySelectorAll(".remove-document").forEach(function(removeButton) {
        //     removeButton.addEventListener("click", function() {
        //         var parentRow = removeButton.closest(".document-fields"); // Get the closest document row
        //         parentRow.remove(); // Remove this specific document fields set
        //         toggleRemoveDocumentButtonVisibility(); // Re-check if only one document remains
        //     });
        // });

        // Function to toggle visibility of remove button
        // function toggleRemoveDocumentButtonVisibility() {
        //     var documentFieldsCount = document.querySelectorAll(".document-fields").length;
        //     var removeButtons = document.querySelectorAll(".remove-document");

        //     if (documentFieldsCount > 1) {
        //         removeButtons.forEach(function(button) {
        //             button.style.display = "block"; // Show remove button if more than one document
        //         });
        //     } else {
        //         removeButtons.forEach(function(button) {
        //             button.style.display = "none"; // Hide remove button if only one document
        //         });
        //     }
        // }

        // Initial check to hide/remove buttons when only one document field is present
        // toggleRemoveDocumentButtonVisibility();


        // Listen for changes on marital status
        document.getElementById('marital_status').addEventListener('change', function() {
            var maritalStatus = this.value;
            var spouseNameDiv = document.getElementById('spouse_name_div');

            if (maritalStatus === 'married') {
                spouseNameDiv.style.display = 'block'; // Show Spouse Name field
            } else {
                spouseNameDiv.style.display = 'none'; // Hide Spouse Name field
            }
        });

        // Initial check to hide/show Spouse Name if already filled
        window.onload = function() {
            var maritalStatus = document.getElementById('marital_status').value;
            if (maritalStatus === 'married') {
                document.getElementById('spouse_name_div').style.display = 'block';
            } else {
                document.getElementById('spouse_name_div').style.display = 'none';
            }
        };


        // document.addEventListener('DOMContentLoaded', function() {
        //     // Select all file input fields
        //     const fileInputs = document.querySelectorAll('input[type="file"]');

        //     // Loop through each file input
        //     fileInputs.forEach(input => {
        //         // Get the existing document link (if any) for the corresponding file input
        //         const existingDocLink = input.closest('.row').querySelector('.existing-doc-link');

        //         // Add an event listener to check when the file is changed
        //         input.addEventListener('change', function() {
        //             // If a file is selected, hide the "View Existing Document" link
        //             if (input.files.length > 0 && existingDocLink) {
        //                 existingDocLink.style.display = 'none'; // Hide the existing document link
        //             } else if (!input.files.length && existingDocLink) {
        //                 existingDocLink.style.display =
        //                     'inline'; // Show the existing document link if no file is selected
        //             }
        //         });

        //         // Check if an existing file is already uploaded and hide the link if the user selects a new file
        //         if (input.files.length > 0 && existingDocLink) {
        //             existingDocLink.style.display = 'none'; // Hide the link if a file is selected
        //         }
        //     });
        // });


        //localstrage code-

        //         document.addEventListener('DOMContentLoaded', function () {
        //     const form = document.getElementById('stepper-form');
        //     const formFields = form.querySelectorAll('input, select, textarea');

        //     // Load saved data on page load
        //     const savedData = JSON.parse(localStorage.getItem('stepperFormData')) || {};
        //     formFields.forEach(field => {
        //         if (savedData[field.name]) {
        //             field.value = savedData[field.name];
        //         }
        //     });

        //     // Save data to localStorage on input change
        //     formFields.forEach(field => {
        //         field.addEventListener('input', () => {
        //             const formData = {};
        //             formFields.forEach(input => {
        //                 formData[input.name] = input.value;
        //             });
        //             localStorage.setItem('stepperFormData', JSON.stringify(formData));
        //         });
        //     });
        // });



        // Function to save form data to localStorage
        function saveFormData() {
            const formData = {};
            const inputs = document.querySelectorAll('#stepper-form input, #stepper-form select, #stepper-form textarea');
            inputs.forEach(input => {
                if (!input.name || input.type === 'file') {
                    return;
                }

                formData[input.name] = input.type === 'checkbox' ? input.checked : input.value;
            });
            localStorage.setItem('stepperFormData', JSON.stringify(formData));
        }

        // Function to load form data from localStorage
        function loadFormData() {
            const savedData = localStorage.getItem('stepperFormData');
            if (savedData) {
                const formData = JSON.parse(savedData);
                for (const name in formData) {
                    const input = document.querySelector(`[name="${name}"]`);
                    if (!input || input.type === "file") {
                        continue;
                    }

                    if (input.type === 'checkbox') {
                        input.checked = Boolean(formData[name]);
                    } else {
                        input.value = formData[name];
                    }
                }
            }
        }

        // Function to reset the form and clear localStorage
        function resetForm() {
            const defaultAvatarUrl = "{{ asset('assets/frontend/images/default-avatar.png') }}";
            const form = document.getElementById('stepper-form');
            Array.from(form.elements).forEach(element => {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        element.checked = false; // Uncheck checkboxes and radio buttons
                    } else {
                        element.value = ''; // Clear text, number, email, etc.
                    }
                } else if (element.tagName === 'SELECT') {
                    element.selectedIndex = 0; // Reset dropdown to the first option
                }
            });
            const profilePreview = document.getElementById('profilePreview');
            profilePreview.src = defaultAvatarUrl;
            localStorage.removeItem('stepperFormData'); // Clear saved data from localStorage

        }

        function validateContactNumber(input) {
    const regex = /^[6-9]\d{9}$/; // Regex for Indian numbers
    if (input.id === 'contact_number') {
        errorDiv = document.getElementById('contact_number_error');
    } else if (input.id === 'phone_number') {
        errorDiv = document.getElementById('phone_number_error');
    } else if (input.id === 'alternative_phone_number') {
        errorDiv = document.getElementById('alternative_phone_number_error');
    }

    if (!regex.test(input.value)) {
        errorDiv.innerText = 'Invalid contact number. Format: +91XXXXXXXXXX';
        input.classList.add('is-invalid'); // Add Bootstrap's error class for better UI
    } else {
        errorDiv.innerText = ''; // Clear error if valid
        input.classList.remove('is-invalid');
    }
}


        // Event listeners for form interactions
        document.addEventListener('DOMContentLoaded', () => {
            // Load saved form data on page load
            loadFormData();

            // Check localStorage to toggle reset button visibility


            // Save form data to localStorage on input change
            document.querySelector('#stepper-form').addEventListener('input', saveFormData);

            document.querySelector('#stepper-form').addEventListener('submit', (event) => {
                const form = event.currentTarget;

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');

                    const firstInvalid = form.querySelector(':invalid');
                    const invalidStep = firstInvalid ? firstInvalid.closest('.step') : null;

                    if (invalidStep) {
                        const stepIndex = Array.from(document.querySelectorAll('#smartwizard > div > .step')).indexOf(invalidStep);

                        if (stepIndex >= 0) {
                            const stepLink = document.querySelectorAll('#smartwizard > ul > li > a')[stepIndex];
                            if (stepLink) {
                                stepLink.click();
                            }
                        }
                    }

                    setTimeout(() => {
                        if (firstInvalid) {
                            firstInvalid.focus();
                            firstInvalid.reportValidity();
                        }
                    }, 150);
                    return;
                }

                saveFormData();
            });

            // Attach event listener to the reset button
            document.getElementById('reset-button').addEventListener('click', resetForm);
        });

        // Show preview of the uploaded image
        document.getElementById('profile_picture').addEventListener('change', function(event) {
            const [file] = this.files;
            if (file) {
                document.getElementById('profilePreview').src = URL.createObjectURL(file);
            }
        });

        document.getElementById('processImport').addEventListener('click', function() {
    let fileInput = document.getElementById('importFile');
    let filePath = fileInput.value;
    let allowedExtensions = /(\.xlsx|\.csv)$/i;

    if (!allowedExtensions.exec(filePath)) {
        document.getElementById('fileError').style.display = 'block';
        fileInput.value = '';
        return false;
    } else {
        document.getElementById('fileError').style.display = 'none';
        document.getElementById('importForm').submit();
    }
});

    </script>
    <script>
        $(document).ready(function() {
            $('#smartwizard').smartWizard({
                selected: 0,
                keyNavigation: false,
                enableAllSteps: false,
                lang: {
                    next: 'Save & Next',
                    previous: 'Previous'
                },
                toolbarSettings: {
                    toolbarPosition: 'bottom',
                    toolbarButtonPosition: 'end',
                    showNextButton: true,
                    showPreviousButton: true,
                    showFinishButton: false,
                    toolbarExtraButtons: [
                        $('<button></button>')
                            .text('Save & Exit')
                            .attr('type', 'submit')
                            .attr('id', 'saveExitBtn')
                            .addClass('btn btn-success ms-2')
                            .on('click', function () {
                                $('#save_exit_mode').val('1');
                            })
                    ]
                },
                anchorSettings: {
                    enableAllAnchors: true,
                    markDoneStep: true,
                    markAllStepsAsDone: false,
                    removeDoneStepOnNavigateBack: true,
                    enableAnchorOnDoneStep: true
                },
                buttonOrder: ['next', 'prev']
            });

            $('.sw-btn-next').text('Save & Next');
            $('.sw-btn-next').off('click').on('click', function (event) {
                event.preventDefault();

                const stepLinks = $('#smartwizard > ul > li > a');
                const currentStepIndex = $('#smartwizard > ul > li.active').index();
                const nextStepHash = stepLinks.eq(currentStepIndex + 1).attr('href') || '';

                $('#save_exit_mode').val('0');
                $('#next_step_hash').val(nextStepHash);
                document.getElementById('stepper-form').requestSubmit();
            });

            $('#saveExitBtn').on('click', function () {
                $('#save_exit_mode').val('1');
                $('#next_step_hash').val('');
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusSelect = document.getElementById('employee_status');
            const dateWrapper = document.getElementById('status_date_wrapper');
            const statusDate = document.getElementById('employee_status_date');

            if (!statusSelect || !dateWrapper || !statusDate) {
                return;
            }

            function toggleDate() {
                const needsStatusDate = statusSelect.value === 'inactive' || statusSelect.value === 'retired';
                dateWrapper.style.display = needsStatusDate ? 'block' : 'none';
                statusDate.required = needsStatusDate;
            }

            statusSelect.addEventListener('change', toggleDate);
            toggleDate();
        });
    </script>



@endsection
