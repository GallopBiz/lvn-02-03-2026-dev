@extends('backend.layouts.main')

@section('main-container')
    <div class="main-content">
        <div class="container-fluid">
            <div class="breadcrumb">
                <h1 class="me-2">Shift History Assignment</h1>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card text-start mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('shifts.history') }}" id="employeeFilterForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="department_id">Department</label>
                                <select name="department_id" id="department_id" class="form-control">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                                            {{ $department->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="position_id">Designation</label>
                                <select name="position_id" id="position_id" class="form-control">
                                    <option value="">All Designations</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}" @selected(request('position_id') == $position->id)>
                                            {{ $position->position_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="staff_type_id">Staff Type</label>
                                <select name="staff_type_id" id="staff_type_id" class="form-control">
                                    <option value="">All Staff Types</option>
                                    @foreach ($staffTypes as $staffType)
                                        <option value="{{ $staffType->id }}" @selected(request('staff_type_id') == $staffType->id)>
                                            {{ $staffType->staff_type_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="employee_status">Employment Status</label>
                                <select name="employee_status" id="employee_status" class="form-control">
                                    <option value="">Active / Blank</option>
                                    <option value="active" @selected(request('employee_status') === 'active')>Active</option>
                                    <option value="inactive" @selected(request('employee_status') === 'inactive')>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" id="reset_filters_btn" class="btn btn-secondary">Reset Filters</button>
                            <button type="submit" class="btn btn-primary">Filter Employees</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-title mb-0">Employees</h5>
                    <span id="selected_employee_count_badge" class="badge bg-primary d-none">0 selected</span>
                    <button type="button" id="clear_selection_btn" class="btn btn-sm btn-link text-danger p-0 d-none" style="text-decoration:none;">Clear selection</button>
                </div>
                <form id="employeeSearchForm" class="d-flex gap-2 align-items-center flex-wrap" onsubmit="event.preventDefault();">
                    <input type="search" id="employee_search_input" class="form-control form-control-sm" placeholder="Search employees..." aria-controls="employeeTable" style="max-width: 280px;" value="{{ request('employee_search') }}" autocomplete="off">
                    <button type="button" id="reset_employee_search_btn" class="btn btn-sm btn-secondary">Clear Search</button>
                </form>
            </div>

            <form method="POST" action="{{ route('shifts.history.store') }}" id="assignShiftForm">
                @csrf
                <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                <input type="hidden" name="position_id" value="{{ request('position_id') }}">
                <input type="hidden" name="staff_type_id" value="{{ request('staff_type_id') }}">
                <input type="hidden" name="employee_status" value="{{ request('employee_status') }}">

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card text-start">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Assign Shift</h5>
                                <div class="form-group mb-3">
                                    <label for="shift_id">Shift</label>
                                    <select name="shift_id" id="shift_id" class="form-control" required>
                                        <option value="">Select Shift</option>
                                        @foreach ($shifts as $shift)
                                            <option value="{{ $shift->id }}" @selected(old('shift_id') == $shift->id)>
                                                {{ optional($shift->shiftType)->shift_type_name ?? 'Shift '.$shift->id }}
                                                ({{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} -
                                                {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="effective_from">Effective From</label>
                                    <input type="date" name="effective_from" id="effective_from" class="form-control"
                                        value="{{ old('effective_from') }}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="effective_to">Effective To</label>
                                    <input type="date" name="effective_to" id="effective_to" class="form-control"
                                        value="{{ old('effective_to') }}" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="remarks">Remarks</label>
                                    <textarea name="remarks" id="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Assign Selected Employees</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8 mb-4">
                        <div class="card text-start">
                            <div class="card-body">
                                <div id="employeeTableContainer">
                                    <div class="table-responsive">
                                        <table id="employeeTable" class="table table-bordered table-striped align-middle">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40px;"><input type="checkbox" id="select_all_shift_employees"></th>
                                                    <th>Employee</th>
                                                    <th>Department</th>
                                                    <th>Designation</th>
                                                    <th>Staff Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($employees as $employee)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="shift-employee-checkbox"
                                                                name="employee_ids[]" value="{{ $employee->id }}">
                                                        </td>
                                                        <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                                        <td>{{ optional($employee->department)->department_name }}</td>
                                                        <td>{{ optional($employee->position)->position_name }}</td>
                                                        <td>{{ optional($employee->staffType)->staff_type_name }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No employees found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center mt-3">
                                        {!! $employees->withQueryString()->links('pagination::bootstrap-4') !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="card text-start">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="card-title mb-0">Recent Shift Assignments</h5>
                        <form id="recentSearchForm" class="d-flex gap-2 align-items-center flex-wrap" onsubmit="event.preventDefault();">
                            <input type="search" id="recent_search_input" class="form-control form-control-sm" placeholder="Search assignments..." aria-controls="recentHistoriesTable" style="max-width: 280px;" value="{{ request('recent_search') }}" autocomplete="off">
                            <button type="button" id="reset_recent_search_btn" class="btn btn-sm btn-secondary">Clear Search</button>
                        </form>
                    </div>
                    <div id="recentTableContainer">
                        <div class="table-responsive">
                            <table id="recentHistoriesTable" class="table table-bordered table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Shift</th>
                                        <th>Effective From</th>
                                        <th>Effective To</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentHistories as $history)
                                        <tr>
                                            <td>{{ optional($history->employee)->first_name }} {{ optional($history->employee)->last_name }}</td>
                                            <td>{{ optional(optional($history->shift)->shiftType)->shift_type_name }}</td>
                                            <td>{{ optional($history->effective_from)->format('d-m-Y') }}</td>
                                            <td>{{ optional($history->effective_to)->format('d-m-Y') }}</td>
                                            <td>{{ $history->remarks }}</td>
                                            <td>
                                                <a href="{{ route('shifts.history.edit', $history->id) }}"
                                                    class="btn btn-warning btn-sm">Edit</a>
                                                <form action="{{ route('shifts.history.destroy', $history->id) }}" method="POST"
                                                    style="display:inline-block;" onsubmit="confirmDelete(event)">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No shift assignments found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {!! $recentHistories->withQueryString()->links('pagination::bootstrap-4') !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('shifts.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const assignForm = document.getElementById('assignShiftForm');
            const shiftIdInput = document.getElementById('shift_id');
            const effectiveFromInput = document.getElementById('effective_from');
            const effectiveToInput = document.getElementById('effective_to');
            const remarksInput = document.getElementById('remarks');

            const STORAGE_KEY_FORM = 'shift_history_assign_form_data';
            const STORAGE_KEY_IDS = 'shift_history_selected_employee_ids';

            @if (session('success'))
                sessionStorage.removeItem(STORAGE_KEY_FORM);
                sessionStorage.removeItem(STORAGE_KEY_IDS);
            @endif

            // Restore form data if available and not set by Blade old()
            const savedFormDataRaw = sessionStorage.getItem(STORAGE_KEY_FORM);
            if (savedFormDataRaw) {
                try {
                    const savedFormData = JSON.parse(savedFormDataRaw);
                    if (shiftIdInput && !shiftIdInput.value && savedFormData.shift_id) {
                        shiftIdInput.value = savedFormData.shift_id;
                    }
                    if (effectiveFromInput && !effectiveFromInput.value && savedFormData.effective_from) {
                        effectiveFromInput.value = savedFormData.effective_from;
                    }
                    if (effectiveToInput && !effectiveToInput.value && savedFormData.effective_to) {
                        effectiveToInput.value = savedFormData.effective_to;
                    }
                    if (remarksInput && !remarksInput.value && savedFormData.remarks) {
                        remarksInput.value = savedFormData.remarks;
                    }
                } catch (e) {
                    console.error('Error parsing saved shift form data', e);
                }
            }

            // Save form data on user input
            function saveAssignFormData() {
                const formData = {
                    shift_id: shiftIdInput ? shiftIdInput.value : '',
                    effective_from: effectiveFromInput ? effectiveFromInput.value : '',
                    effective_to: effectiveToInput ? effectiveToInput.value : '',
                    remarks: remarksInput ? remarksInput.value : ''
                };
                sessionStorage.setItem(STORAGE_KEY_FORM, JSON.stringify(formData));
            }

            [shiftIdInput, effectiveFromInput, effectiveToInput, remarksInput].forEach(function (el) {
                if (el) {
                    el.addEventListener('input', saveAssignFormData);
                    el.addEventListener('change', saveAssignFormData);
                }
            });

            // Employee Selections Persistence Across Pages
            function getSelectedEmployeeIds() {
                try {
                    const raw = sessionStorage.getItem(STORAGE_KEY_IDS);
                    return raw ? JSON.parse(raw) : [];
                } catch (e) {
                    return [];
                }
            }

            function setSelectedEmployeeIds(idsArray) {
                const uniqueIds = Array.from(new Set(idsArray.map(id => String(id))));
                sessionStorage.setItem(STORAGE_KEY_IDS, JSON.stringify(uniqueIds));
                syncSelectionUI();
            }

            const selectedBadge = document.getElementById('selected_employee_count_badge');
            const clearSelectionBtn = document.getElementById('clear_selection_btn');

            function syncSelectionUI() {
                const selectedIds = getSelectedEmployeeIds();
                const selectAllCheckbox = document.getElementById('select_all_shift_employees');
                const employeeCheckboxes = document.querySelectorAll('.shift-employee-checkbox');
                
                let visibleCheckedCount = 0;
                let visibleCount = 0;

                employeeCheckboxes.forEach(function (cb) {
                    const isSelected = selectedIds.includes(String(cb.value));
                    cb.checked = isSelected;
                    visibleCount++;
                    if (isSelected) visibleCheckedCount++;
                });

                if (selectAllCheckbox) {
                    if (visibleCount > 0 && visibleCheckedCount === visibleCount) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else if (visibleCheckedCount > 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    }
                }

                if (selectedBadge) {
                    if (selectedIds.length > 0) {
                        selectedBadge.textContent = selectedIds.length + ' employee' + (selectedIds.length > 1 ? 's' : '') + ' selected';
                        selectedBadge.classList.remove('d-none');
                    } else {
                        selectedBadge.classList.add('d-none');
                    }
                }

                if (clearSelectionBtn) {
                    if (selectedIds.length > 0) {
                        clearSelectionBtn.classList.remove('d-none');
                    } else {
                        clearSelectionBtn.classList.add('d-none');
                    }
                }

                if (assignForm) {
                    assignForm.querySelectorAll('.hidden-offpage-employee').forEach(function (el) {
                        el.remove();
                    });

                    const visibleIdsOnPage = Array.from(employeeCheckboxes).map(cb => String(cb.value));
                    selectedIds.forEach(function (id) {
                        if (!visibleIdsOnPage.includes(String(id))) {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'employee_ids[]';
                            hiddenInput.value = id;
                            hiddenInput.className = 'hidden-offpage-employee';
                            assignForm.appendChild(hiddenInput);
                        }
                    });
                }
            }

            function attachCheckboxListeners() {
                const selectAllCheckbox = document.getElementById('select_all_shift_employees');
                const employeeCheckboxes = document.querySelectorAll('.shift-employee-checkbox');

                employeeCheckboxes.forEach(function (cb) {
                    cb.onclick = function () {
                        let selectedIds = getSelectedEmployeeIds();
                        const val = String(this.value);
                        if (this.checked) {
                            if (!selectedIds.includes(val)) selectedIds.push(val);
                        } else {
                            selectedIds = selectedIds.filter(id => id !== val);
                        }
                        setSelectedEmployeeIds(selectedIds);
                    };
                });

                if (selectAllCheckbox) {
                    selectAllCheckbox.onclick = function () {
                        let selectedIds = getSelectedEmployeeIds();
                        const visibleCbs = Array.from(employeeCheckboxes);
                        
                        if (this.checked) {
                            visibleCbs.forEach(function (cb) {
                                const val = String(cb.value);
                                if (!selectedIds.includes(val)) selectedIds.push(val);
                            });
                        } else {
                            const visibleVals = visibleCbs.map(cb => String(cb.value));
                            selectedIds = selectedIds.filter(id => !visibleVals.includes(id));
                        }
                        setSelectedEmployeeIds(selectedIds);
                    };
                }
            }

            if (clearSelectionBtn) {
                clearSelectionBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    setSelectedEmployeeIds([]);
                });
            }

            syncSelectionUI();
            attachCheckboxListeners();

            // Client-side validation before form submission
            if (assignForm) {
                assignForm.addEventListener('submit', function (e) {
                    const selectedIds = getSelectedEmployeeIds();
                    if (selectedIds.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one employee from the list to assign the shift.');
                    }
                });
            }

            // AJAX Data Loading (NO Page Reload)
            function fetchTables(customParams = {}) {
                const params = new URLSearchParams();
                
                const dept = document.getElementById('department_id')?.value;
                const pos = document.getElementById('position_id')?.value;
                const staff = document.getElementById('staff_type_id')?.value;
                const status = document.getElementById('employee_status')?.value;
                const empSearch = document.getElementById('employee_search_input')?.value;
                const recSearch = document.getElementById('recent_search_input')?.value;

                if (dept) params.set('department_id', dept);
                if (pos) params.set('position_id', pos);
                if (staff) params.set('staff_type_id', staff);
                if (status) params.set('employee_status', status);
                if (empSearch) params.set('employee_search', empSearch);
                if (recSearch) params.set('recent_search', recSearch);

                for (const [key, val] of Object.entries(customParams)) {
                    params.set(key, val);
                }

                const url = '{{ route("shifts.history") }}?' + params.toString();

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.html) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(data.html, 'text/html');

                        const newEmpContainer = doc.getElementById('employeeTableContainer');
                        if (newEmpContainer) {
                            document.getElementById('employeeTableContainer').innerHTML = newEmpContainer.innerHTML;
                        }

                        const newRecContainer = doc.getElementById('recentTableContainer');
                        if (newRecContainer) {
                            document.getElementById('recentTableContainer').innerHTML = newRecContainer.innerHTML;
                        }
                    }
                    syncSelectionUI();
                    attachCheckboxListeners();
                })
                .catch(err => console.error('Error fetching tables', err));
            }

            // Employee Search Input on Type
            const employeeSearchInput = document.getElementById('employee_search_input');
            let empDebounce;
            if (employeeSearchInput) {
                employeeSearchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    const tbody = document.querySelector('#employeeTable tbody');
                    if (tbody) {
                        const rows = tbody.querySelectorAll('tr');
                        rows.forEach(function (row) {
                            const text = row.textContent.toLowerCase();
                            if (!query || text.indexOf(query) !== -1) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }

                    clearTimeout(empDebounce);
                    empDebounce = setTimeout(() => {
                        fetchTables({ employees_page: 1 });
                    }, 300);
                });
            }

            document.getElementById('reset_employee_search_btn')?.addEventListener('click', function () {
                if (employeeSearchInput) {
                    employeeSearchInput.value = '';
                    fetchTables({ employees_page: 1, employee_search: '' });
                }
            });

            // Recent Search Input on Type
            const recentSearchInput = document.getElementById('recent_search_input');
            let recDebounce;
            if (recentSearchInput) {
                recentSearchInput.addEventListener('input', function () {
                    clearTimeout(recDebounce);
                    recDebounce = setTimeout(() => {
                        fetchTables({ histories_page: 1 });
                    }, 300);
                });
            }

            document.getElementById('reset_recent_search_btn')?.addEventListener('click', function () {
                if (recentSearchInput) {
                    recentSearchInput.value = '';
                    fetchTables({ histories_page: 1, recent_search: '' });
                }
            });

            // Filter Form Submit & Change
            const filterForm = document.getElementById('employeeFilterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    fetchTables({ employees_page: 1, histories_page: 1 });
                });

                filterForm.querySelectorAll('select').forEach(select => {
                    select.addEventListener('change', function () {
                        fetchTables({ employees_page: 1, histories_page: 1 });
                    });
                });
            }

            document.getElementById('reset_filters_btn')?.addEventListener('click', function () {
                if (filterForm) {
                    filterForm.querySelectorAll('select').forEach(select => select.value = '');
                    fetchTables({ employees_page: 1, histories_page: 1, department_id: '', position_id: '', staff_type_id: '', employee_status: '' });
                }
            });

            // Intercept Pagination Clicks (NO Page Reload)
            document.addEventListener('click', function (e) {
                const link = e.target.closest('.pagination a');
                if (link) {
                    e.preventDefault();
                    const href = link.getAttribute('href');
                    if (href && href !== '#') {
                        const urlObj = new URL(href, window.location.origin);
                        const paramsObj = {};
                        urlObj.searchParams.forEach((v, k) => {
                            paramsObj[k] = v;
                        });
                        fetchTables(paramsObj);
                    }
                }
            });
        });

        function confirmDelete(event) {
            if (!confirm('Are you sure you want to delete this shift assignment?')) {
                event.preventDefault();
            }
        }
    </script>
@endsection



