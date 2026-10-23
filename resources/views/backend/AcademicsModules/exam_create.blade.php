@extends('backend.layouts.main')

@section('main-container')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />

<div class="main-content">
    <div class="breadcrumb d-flex align-items-center justify-content-between">
        <div>
            <h1 class="mr-2">Exam Master</h1>
            <ul>
                <li><a href="#">Academic</a></li>
                <li>Exam Management</li>
            </ul>
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success'))
        <div class="alert alert-success mt-2">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mt-2">
            {{ session('error') }}
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-2">Copy Exams from Previous Session</h5>
            <form method="GET" action="{{ route('academic.exams.create') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Source Session</label>
                    <select name="source_session" class="form-control" required>
                        <option value="">Select source session</option>
                        @foreach($databaseNames as $db)
                            @if (is_numeric(substr($db, 0, 1)))
                                <option value="{{ $db }}" {{ ($sourceSession ?? '') === $db ? 'selected' : '' }}>{{ $db }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Target Session</label>
                    <input type="text" class="form-control" value="{{ $currentSessionYear ?? '-' }}" disabled>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Load Exams</button>
                </div>
            </form>

            @if(!empty($sourceSession) && isset($sourceExamGroups) && $sourceExamGroups->count())
                <hr>
                <form method="POST" action="{{ route('academic.exams.copyFromSession') }}">
                    @csrf
                    <input type="hidden" name="source_session" value="{{ $sourceSession }}">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:60px;">Select</th>
                                    <th>Exam Name</th>
                                    <th>Exam Type</th>
                                    <th>Classes</th>
                                    <th>Session Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sourceExamGroups as $g)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="source_exam_ids[]" value="{{ $g->seed_id }}">
                                        </td>
                                        <td>{{ $g->exam_name }}</td>
                                        <td>{{ $g->exam_type }}</td>
                                        <td>{{ $g->class_names !== '' ? $g->class_names : '-' }}</td>
                                        <td>{{ $g->session_year ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Copy selected exams into current session?');">Copy Selected</button>
                </form>
            @endif

            <div class="text-muted small mt-2">
                Note: Copy maps classes by <strong>class name</strong> (source → target). If a class name doesn’t exist in target session, that class will be skipped.
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('academic.exams.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="exam_name">Exam Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="exam_name" name="exam_name" required>
                </div>
                <div class="form-group mt-2">
                    <label for="exam_title">Exam Title</label>
                    <input type="text" class="form-control" id="exam_title" name="exam_title" value="{{ old('exam_title') }}" maxlength="150">
                </div>
                @if(!empty($currentSessionYear))
                    <p class="text-muted small mb-2">Session year: <strong>{{ $currentSessionYear }}</strong></p>
                @endif
                <div class="form-group mt-2">
                    <label>Exam Type <span class="text-danger">*</span></label><br>
                    @forelse($examTypes as $type)
                        <label class="me-3">
                            <input
                                type="radio"
                                name="exam_type"
                                value="{{ $type }}"
                                {{ old('exam_type') === $type ? 'checked' : '' }}
                                required
                            >
                            {{ $type }}
                        </label>
                    @empty
                        <div class="alert alert-warning mb-0">
                            No exam types found. Please create one from Exam Type first.
                        </div>
                    @endforelse
                </div>
                <div class="form-group mt-2">
                    <label>Max Marks (Theory)</label>
                    <input type="number" class="form-control" name="max_marks_theory">
                </div>
                <div class="form-group mt-2">
                    <label>Max Marks (Practical)</label>
                    <input type="number" class="form-control" name="max_marks_practical">
                </div>
                <div class="form-group mt-2">
                    <label>Fail %</label>
                    <input type="number" class="form-control" name="fail_percent" step="0.01">
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
            <div class="col-md-6">
                <label>Applicable to these Classes:</label>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="mb-0">
                        <input type="checkbox" id="select_all_classes">
                        Select All Classes
                    </label>
                    <button type="button" class="btn btn-sm btn-secondary" id="clear_all_classes">Clear</button>
                </div>
                <div class="border p-2" style="max-height: 350px; overflow-y: auto;">
                    @php
                        $oldClassIds = old('class_ids', []);
                    @endphp
                    @foreach($classes as $class)
                        <div class="form-check d-flex align-items-center justify-content-between">
                            <div>
                                <input
                                    type="checkbox"
                                    class="form-check-input class-checkbox"
                                    name="class_ids[]"
                                    value="{{ $class->id }}"
                                    id="class_{{ $class->id }}"
                                    {{ in_array($class->id, $oldClassIds) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="class_{{ $class->id }}">{{ $class->class_name }}</label>
                            </div>
                            <!-- Edit/Delete removed from classes list -->
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>

    <style>
        .exam-table-card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            background: #ffffff;
            overflow: hidden;
        }

        .exam-table-card .card-header {
            background: #ffffff;
            border-bottom: 1px solid #edf2f7;
        }

        .exam-table-card .card-footer {
            background: #ffffff;
            border-top: 1px solid #edf2f7;
        }

        #saved_exams_table {
            margin-bottom: 0;
            font-size: 13px;
        }

        #saved_exams_table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            border-top: none;
            padding: 9px 6px;
            vertical-align: middle;
            white-space: nowrap;
        }

        #saved_exams_table tbody td {
            padding: 8px 6px;
            vertical-align: middle;
            border-color: #f1f5f9;
            color: #334155;
        }

        #saved_exams_table tbody tr.exam-row:hover {
            background-color: #f8fafc;
            transition: background-color 0.15s ease-in-out;
        }

        .action-group {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            flex-wrap: nowrap;
        }

        .action-btn {
            border-radius: 4px;
            padding: 3px 5px;
            font-size: 10.5px;
            font-weight: 500;
            transition: all 0.15s ease;
            line-height: 1.25;
            white-space: nowrap;
        }

        .action-btn i {
            font-size: 10px;
            margin-right: 2px;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        }

        .class-chip {
            display: inline-block;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            padding: 1px 4px;
            margin: 1px;
            line-height: 1.25;
            transition: all 0.15s ease;
        }

        .class-chip:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .badge-soft-primary {
            background-color: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .badge-soft-purple {
            background-color: #faf5ff;
            color: #7c3aed;
            border: 1px solid #ddd6fe;
        }

        .badge-soft-teal {
            background-color: #f0fdfa;
            color: #0d9488;
            border: 1px solid #99f6e4;
        }

        .badge-soft-info {
            background-color: #f0f9ff;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }

        .badge-soft-indigo {
            background-color: #eef2ff;
            color: #4f46e5;
            border: 1px solid #c7d2fe;
        }

        .badge-soft-secondary {
            background-color: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .badge-soft-success {
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            font-weight: 600;
            font-size: 11.5px;
        }

        .badge-soft-danger {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            font-weight: 600;
            font-size: 11.5px;
        }

        .search-box-wrap .input-group-text {
            border-color: #cbd5e1;
            font-size: 13px;
        }

        .search-box-wrap .form-control {
            border-color: #cbd5e1;
            font-size: 13px;
        }

        .search-box-wrap .form-control:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.15);
        }

        .pagination .page-item.active .page-link {
            background-color: #7c3aed;
            border-color: #7c3aed;
            color: #fff;
            font-weight: 600;
        }

        .pagination .page-link {
            color: #475569;
            font-size: 12.5px;
            padding: 5px 11px;
            border-color: #e2e8f0;
        }

        .pagination .page-link:hover {
            background-color: #f8fafc;
            color: #7c3aed;
        }
    </style>

    <div class="card exam-table-card mt-4 mb-5">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <div class="icon-shape bg-primary text-white rounded-circle mr-3 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 font-weight-bold text-dark mr-2">List of Saved Exam Names</h5>
                        <span class="badge badge-primary rounded-pill px-2 py-1 font-weight-normal" id="exam_total_badge">
                            {{ count($savedExamGroups) }} {{ count($savedExamGroups) === 1 ? 'Exam' : 'Exams' }}
                        </span>
                    </div>
                    <small class="text-muted">Manage existing exam definitions, locking, and class associations</small>
                </div>
            </div>

            <div class="search-box-wrap" style="min-width: 270px; max-width: 350px;">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0 text-muted"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="exam_table_search" class="form-control border-left-0" placeholder="Search exams by name, type, class..." autocomplete="off">
                    <div class="input-group-append" id="clear_exam_search_wrap" style="display: none;">
                        <button class="btn btn-outline-secondary border-left-0 bg-white text-muted" type="button" id="clear_exam_search" title="Clear search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="saved_exams_table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">S.No.</th>
                            <th style="min-width: 130px;">Exam Name</th>
                            <th style="min-width: 110px;">Exam Title</th>
                            <th class="text-center" style="width: 80px;">Exam Type</th>
                            <th style="min-width: 120px; max-width: 190px;">Classes</th>
                            <th class="text-center" style="width: 85px;">Session</th>
                            <th class="text-center" style="width: 85px;">Status</th>
                            <th class="text-center" style="width: 210px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="saved_exams_tbody">
                        @forelse($savedExamGroups as $i => $exam)
                            @php
                                $typeLower = strtolower(trim($exam->exam_type ?? ''));
                                $badgeClass = 'badge-soft-secondary';
                                if (str_contains($typeLower, 'pt') || str_contains($typeLower, 'periodic')) {
                                    $badgeClass = 'badge-soft-primary';
                                } elseif (str_contains($typeLower, 'term 1') || str_contains($typeLower, 'term-1')) {
                                    $badgeClass = 'badge-soft-purple';
                                } elseif (str_contains($typeLower, 'term 2') || str_contains($typeLower, 'term-2')) {
                                    $badgeClass = 'badge-soft-teal';
                                } elseif (str_contains($typeLower, 'half')) {
                                    $badgeClass = 'badge-soft-info';
                                } elseif (str_contains($typeLower, 'annual') || str_contains($typeLower, 'final')) {
                                    $badgeClass = 'badge-soft-indigo';
                                }

                                $classList = array_filter(array_map('trim', explode(',', $exam->class_names ?? '')));
                            @endphp
                            <tr class="exam-row" data-index="{{ $i + 1 }}">
                                <td class="text-center font-weight-bold text-muted s-no-cell">{{ $i + 1 }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark exam-name-text">{{ $exam->exam_name }}</div>
                                </td>
                                <td>
                                    <span class="text-secondary">{{ $exam->exam_title ?: '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }} px-2 py-1 font-weight-bold rounded-pill">
                                        {{ $exam->exam_type }}
                                    </span>
                                </td>
                                <td>
                                    <div class="classes-wrap d-flex flex-wrap" style="max-width: 210px;">
                                        @forelse($classList as $cls)
                                            <span class="class-chip">{{ $cls }}</span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border text-muted px-2 py-1 font-weight-normal">
                                        {{ $exam->session_year ? str_replace('_', '-', $exam->session_year) : '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(!empty($exam->is_locked))
                                        <span class="badge badge-soft-danger px-2 py-1 rounded-pill d-inline-flex align-items-center">
                                            <i class="fas fa-lock" style="margin-right: 4px;"></i> Locked
                                        </span>
                                    @else
                                        <span class="badge badge-soft-success px-2 py-1 rounded-pill d-inline-flex align-items-center">
                                            <i class="fas fa-unlock-alt" style="margin-right: 4px;"></i> Unlocked
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    <div class="action-group justify-content-center">
                                        @if(Route::has('academic.exams.toggleLock'))
                                            <form action="{{ route('academic.exams.toggleLock', $exam->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ !empty($exam->is_locked) ? 'btn-outline-warning' : 'btn-outline-secondary' }} action-btn" title="{{ !empty($exam->is_locked) ? 'Unlock for staff marks editing' : 'Lock for staff marks editing' }}" onclick="return confirm('{{ !empty($exam->is_locked) ? 'Unlock this exam for staff marks editing?' : 'Lock this exam for staff marks editing?' }}');">
                                                    <i class="fas {{ !empty($exam->is_locked) ? 'fa-unlock-alt' : 'fa-lock' }} mr-1"></i> {{ !empty($exam->is_locked) ? 'Unlock' : 'Lock' }}
                                                </button>
                                            </form>
                                        @endif

                                        @if(Route::has('academic.exams.edit'))
                                            @if(!empty($exam->is_locked))
                                                <button type="button" class="btn btn-sm btn-outline-info action-btn" disabled title="Locked exams cannot be edited">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </button>
                                            @else
                                                <a href="{{ route('academic.exams.edit', $exam->id) }}" class="btn btn-sm btn-outline-info action-btn" title="Edit Exam">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                            @endif
                                        @endif

                                        @if(Route::has('academic.exams.duplicate'))
                                            <form action="{{ route('academic.exams.duplicate', $exam->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary action-btn" title="Duplicate Exam" onclick="return confirm('Duplicate this exam in current session?');">
                                                    <i class="fas fa-copy mr-1"></i> Duplicate
                                                </button>
                                            </form>
                                        @endif

                                        @if(Route::has('academic.exams.destroy'))
                                            @if(!empty($exam->is_locked))
                                                <button type="button" class="btn btn-sm btn-outline-danger action-btn" disabled title="Locked exams cannot be deleted">
                                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                                </button>
                                            @else
                                                <form action="{{ route('academic.exams.destroy', $exam->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger action-btn" title="Delete Exam" onclick="return confirm('Are you sure you want to delete this exam? This will delete the exam across all classes.');">
                                                        <i class="fas fa-trash-alt mr-1"></i> Delete
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="initial_no_data_row">
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                    <h6 class="font-weight-bold text-dark">No exams created yet</h6>
                                    <p class="mb-0 text-muted small">Fill out the form above to define your first examination.</p>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="search_no_data_row" style="display: none;">
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-search fa-3x mb-3 text-muted d-block"></i>
                                <h6 class="font-weight-bold text-dark">No matching exams found</h6>
                                <p class="text-muted small mb-3">We couldn't find any exams matching your search criteria.</p>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="reset_search_btn">
                                    <i class="fas fa-times mr-1"></i> Clear Search
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white py-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2" id="exam_table_footer">
            <div class="text-muted small" id="exam_pagination_info">
                Showing <span class="font-weight-bold text-dark" id="page_start_count">1</span> to <span class="font-weight-bold text-dark" id="page_end_count">{{ min(20, count($savedExamGroups)) }}</span> of <span class="font-weight-bold text-dark" id="page_total_count">{{ count($savedExamGroups) }}</span> entries
            </div>
            <nav aria-label="Exam table pagination">
                <ul class="pagination pagination-sm mb-0 justify-content-end" id="exam_pagination_controls">
                    <!-- Dynamically generated page buttons -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Class checkboxes logic ---
        var selectAll = document.getElementById('select_all_classes');
        var clearAll = document.getElementById('clear_all_classes');
        var classCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.class-checkbox'));

        function syncSelectAllState() {
            if (!selectAll) return;
            var checkedCount = classCheckboxes.filter(function (checkbox) {
                return checkbox.checked;
            }).length;

            selectAll.checked = classCheckboxes.length > 0 && checkedCount === classCheckboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < classCheckboxes.length;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                classCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
                syncSelectAllState();
            });
        }

        if (clearAll) {
            clearAll.addEventListener('click', function () {
                classCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });
                syncSelectAllState();
            });
        }

        classCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', syncSelectAllState);
        });

        syncSelectAllState();

        // --- Exam Table Pagination & Search (20 records per page) ---
        const pageSize = 20;
        let currentPage = 1;
        const searchInput = document.getElementById('exam_table_search');
        const clearBtn = document.getElementById('clear_exam_search');
        const clearWrap = document.getElementById('clear_exam_search_wrap');
        const resetBtn = document.getElementById('reset_search_btn');
        const tbody = document.getElementById('saved_exams_tbody');
        const allRows = tbody ? Array.from(tbody.querySelectorAll('.exam-row')) : [];
        const noResultsRow = document.getElementById('search_no_data_row');
        const paginationControls = document.getElementById('exam_pagination_controls');
        const infoStart = document.getElementById('page_start_count');
        const infoEnd = document.getElementById('page_end_count');
        const infoTotal = document.getElementById('page_total_count');
        const paginationInfo = document.getElementById('exam_pagination_info');
        const footer = document.getElementById('exam_table_footer');

        let filteredRows = allRows.slice();

        function updateTable() {
            const total = filteredRows.length;
            const totalPages = Math.ceil(total / pageSize) || 1;

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }
            if (currentPage < 1) {
                currentPage = 1;
            }

            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = Math.min(startIndex + pageSize, total);

            // Hide all rows first
            allRows.forEach(row => {
                row.style.display = 'none';
            });

            if (total === 0) {
                if (noResultsRow) noResultsRow.style.display = '';
                if (footer) footer.style.display = 'none';
            } else {
                if (noResultsRow) noResultsRow.style.display = 'none';
                if (footer) footer.style.display = 'flex';

                // Show only the rows for current page
                for (let i = startIndex; i < endIndex; i++) {
                    filteredRows[i].style.display = '';
                }

                // Update info text
                if (paginationInfo) {
                    if (filteredRows.length < allRows.length) {
                        paginationInfo.innerHTML = `Showing <span class="font-weight-bold text-dark">${startIndex + 1}</span> to <span class="font-weight-bold text-dark">${endIndex}</span> of <span class="font-weight-bold text-dark">${total}</span> entries (filtered from <span class="font-weight-bold text-dark">${allRows.length}</span> total)`;
                    } else {
                        paginationInfo.innerHTML = `Showing <span class="font-weight-bold text-dark">${startIndex + 1}</span> to <span class="font-weight-bold text-dark">${endIndex}</span> of <span class="font-weight-bold text-dark">${total}</span> entries`;
                    }
                }
            }

            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            if (!paginationControls) return;
            paginationControls.innerHTML = '';

            if (totalPages <= 1) {
                return;
            }

            // Previous button
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `<a class="page-link" href="javascript:void(0)" aria-label="Previous">&laquo; Prev</a>`;
            if (currentPage > 1) {
                prevLi.addEventListener('click', function (e) {
                    e.preventDefault();
                    currentPage--;
                    updateTable();
                });
            }
            paginationControls.appendChild(prevLi);

            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                const firstLi = document.createElement('li');
                firstLi.className = 'page-item';
                firstLi.innerHTML = `<a class="page-link" href="javascript:void(0)">1</a>`;
                firstLi.addEventListener('click', function (e) {
                    e.preventDefault();
                    currentPage = 1;
                    updateTable();
                });
                paginationControls.appendChild(firstLi);

                if (startPage > 2) {
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = `<span class="page-link">&hellip;</span>`;
                    paginationControls.appendChild(dotsLi);
                }
            }

            for (let p = startPage; p <= endPage; p++) {
                const pageLi = document.createElement('li');
                pageLi.className = `page-item ${p === currentPage ? 'active' : ''}`;
                pageLi.innerHTML = `<a class="page-link" href="javascript:void(0)">${p}</a>`;
                (function (targetPage) {
                    pageLi.addEventListener('click', function (e) {
                        e.preventDefault();
                        currentPage = targetPage;
                        updateTable();
                    });
                })(p);
                paginationControls.appendChild(pageLi);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = `<span class="page-link">&hellip;</span>`;
                    paginationControls.appendChild(dotsLi);
                }
                const lastLi = document.createElement('li');
                lastLi.className = 'page-item';
                lastLi.innerHTML = `<a class="page-link" href="javascript:void(0)">${totalPages}</a>`;
                lastLi.addEventListener('click', function (e) {
                    e.preventDefault();
                    currentPage = totalPages;
                    updateTable();
                });
                paginationControls.appendChild(lastLi);
            }

            // Next button
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
            nextLi.innerHTML = `<a class="page-link" href="javascript:void(0)" aria-label="Next">Next &raquo;</a>`;
            if (currentPage < totalPages) {
                nextLi.addEventListener('click', function (e) {
                    e.preventDefault();
                    currentPage++;
                    updateTable();
                });
            }
            paginationControls.appendChild(nextLi);
        }

        function filterRows() {
            const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
            if (clearWrap) {
                clearWrap.style.display = query ? '' : 'none';
            }

            if (!query) {
                filteredRows = allRows.slice();
            } else {
                filteredRows = allRows.filter(function (row) {
                    return (row.textContent || '').toLowerCase().includes(query);
                });
            }

            currentPage = 1;
            updateTable();
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterRows);
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                    filterRows();
                    searchInput.focus();
                }
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                    filterRows();
                    searchInput.focus();
                }
            });
        }

        // Initialize table state
        updateTable();
    });
</script>
@endsection
