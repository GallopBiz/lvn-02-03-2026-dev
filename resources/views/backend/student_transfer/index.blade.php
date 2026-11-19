@extends('backend.layouts.main')
@section('main-container')

<style>
  .ul-widget-app__browser-list::-webkit-scrollbar {
    width: 5px;
  }
  .ul-widget-app__browser-list::-webkit-scrollbar-thumb {
    background-color: #7074b3;
    border-radius: 6px;
  }
  .ul-widget-app__browser-list {
    scrollbar-color: #7074b3 transparent;
    max-height: 200px;
    overflow: auto;
  }
  .card-title {
    margin-bottom: 5px;
    color: #7074b3;
    font-size: 25px;
  }
</style>

<div class="main-content">
    <div class="breadcrumb d-flex justify-content-between align-items-center">
        <h1>Student Transfer & Section Assignment</h1>
        <form action="{{ route('student.allocate_sections') }}" method="POST" onsubmit="return confirm('Run automatic Round Robin section allocation and balancing for the next academic session?');">
            @csrf
            <button type="submit" class="btn btn-warning text-white">
                <i class="fas fa-balance-scale mr-1"></i> Balance / Allocate Next Session Sections
            </button>
        </form>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">Select Class</div>
                    <div class="separator-breadcrumb border-top"></div>
                    <form id="fetchStudentsForm">
                        <div class="form-group">
                            <label for="class_id">Class</label>
                            <select class="form-control" name="class_id" id="class_id">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->class_name }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" id="fetchStudents" class="btn btn-primary">Fetch Students</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row d-none" id="studentsSection">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">Student List</div>
                    <div class="separator-breadcrumb border-top"></div>
                    <form action="{{ route('student.promote') }}" method="POST">
                        @csrf
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select_all"></th>
                                    <th>S. No.</th>
									<th>Student Name</th>
                                    <th>Scholar Number</th>
                                    <th>Current Class</th>
									<th>Section Name</th>
                                    <th>Transition Type</th>
                                </tr>
                            </thead>
                            <tbody id="studentList"></tbody>
                        </table>

                        <button type="submit" class="btn btn-success">Promote / Transfer Selected</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('fetchStudents').addEventListener('click', function () {
    let classId = document.getElementById('class_id').value;
    if (classId) {
        fetch("{{ route('student.fetch') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ class_id: classId })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().catch(() => {
                    throw new Error('Unable to fetch students. Please check the server logs.');
                }).then(errorData => {
                    throw new Error(errorData.message || 'Unable to fetch students.');
                });
            }

            return response.json();
        })
        .then(data => {
            let studentList = document.getElementById('studentList');
            studentList.innerHTML = "";
            data.forEach((student, index) => {  // 'index' provides serial number
                let sectionName = ""; // Default value
                
                // Check if json_str exists and parse it
                if (student.json_str) {
                    try {
                        let parsedJson = JSON.parse(student.json_str);
                        sectionName = parsedJson.section_name ?? "N/A"; // Extract section_name
                    } catch (error) {
                        console.error("Error parsing JSON:", error);
                    }
                }

                let transitionBadge = '<span class="badge bg-primary text-white">Regular (Class Promotion)</span>';
                if (student.next_year == 1) {
                    let upcomingDetails = [];
                    if (student.next_year_section) upcomingDetails.push(`Section: ${student.next_year_section}`);
                    if (student.next_year_stream) upcomingDetails.push(`Stream: ${student.next_year_stream}`);
                    if (student.next_year_subject_combination) upcomingDetails.push(`Comb: ${student.next_year_subject_combination}`);
                    let detailsStr = upcomingDetails.length ? `<div class="small text-muted mt-1">${upcomingDetails.join(', ')}</div>` : '';
                    transitionBadge = `<span class="badge bg-warning text-dark"><i class="fas fa-arrow-right"></i> Next Year (Session Transfer Only - No Class Promotion)</span>${detailsStr}`;
                }

                studentList.innerHTML += `
                    <tr>
                        <td><input type="checkbox" name="selected_students[]" value="${student.id}"></td>
                        <td>${index + 1}</td>  <!-- Serial Number Column -->
                        <td>${student.student_name}</td>
                        <td>${student.scholar_no}</td>
                        <td>${student.class_name}</td>
                        <td>${sectionName}</td> <!-- New Column for Section -->
                        <td>${transitionBadge}</td>
                    </tr>
                `;
            });
            document.getElementById('studentsSection').classList.remove('d-none');
        })
        .catch(error => console.error("Error fetching students:", error));
    }
});

document.getElementById('select_all').addEventListener('click', function () {
    let checkboxes = document.querySelectorAll('input[name="selected_students[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

</script>


@endsection
