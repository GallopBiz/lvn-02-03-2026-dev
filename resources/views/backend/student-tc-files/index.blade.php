@extends('backend.layouts.main')

@section('main-container')
<style>
    .tc-student-search { position: relative; }
    .tc-student-results { position: absolute; z-index: 1060; width: calc(100% - 30px); max-height: 230px; overflow-y: auto; }
    .tc-student-results button { width: 100%; text-align: left; background: #fff; border: 0; }
</style>
<div class="main-content">
    <div class="breadcrumb"><h1>Student TC PDF Upload</h1></div>
    <div class="separator-breadcrumb border-top"></div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-3"><div class="card-body">
        <form method="POST" enctype="multipart/form-data" action="{{ route('student-tc-files.store') }}" class="row">
            @csrf
            <div class="col-md-4 form-group tc-student-search">
                <label>Scholar No. / Student Search</label>
                <input id="scholar_no" class="form-control" name="scholar_no" value="{{ old('scholar_no') }}" autocomplete="off" placeholder="Type Scholar No. or student name" required>
                <div id="student-results" class="tc-student-results list-group d-none"></div>
                @error('scholar_no')<div class="text-danger">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 form-group"><label>Student Name</label><input id="student_name" class="form-control" readonly></div>
            <div class="col-md-2 form-group"><label>Class</label><input id="class_name" class="form-control" readonly></div>
            <div class="col-md-2 form-group"><label>Section</label><input id="section_name" class="form-control" readonly></div>
            <div class="col-md-4 form-group">
                <label>TC PDF (maximum 500 KB)</label>
                <input class="form-control" type="file" name="tc_file" accept="application/pdf,.pdf" required>
                @error('tc_file')<div class="text-danger">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2 form-group d-flex align-items-end"><button class="btn btn-primary btn-block">Upload PDF</button></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <input id="tc-record-search" type="search" class="form-control form-control-sm" placeholder="Search Scholar No." aria-controls="zero_configuration_table" style="max-width:260px">
        </div>
        <table id="zero_configuration_table" class="table table-bordered"><thead><tr><th>Sr. No.</th><th>Scholar No.</th><th>Student Name</th><th>Class</th><th>Section</th><th>Session</th><th>Uploaded</th><th>Action</th></tr></thead><tbody id="tc-record-results">
            @forelse ($files as $file)
                <tr><td>{{ $files->firstItem() + $loop->index }}</td><td>{{ $file->scholar_no }}</td><td>{{ $file->student_name ?: '--' }}</td><td>{{ $file->class_name ?: '--' }}</td><td>{{ $file->section_name ?: '--' }}</td><td>{{ $file->session_name }}</td><td>{{ optional($file->uploaded_at)->format('d-m-Y H:i') }}</td><td>
                    <a class="btn btn-sm btn-info" target="_blank" rel="noopener" href="{{ route('student-tc-files.view', $file->id) }}">View</a>
                    <a class="btn btn-sm btn-success" href="{{ route('student-tc-files.download', $file->id) }}">Download</a>
                    <form class="d-inline" method="POST" action="{{ route('student-tc-files.delete', $file->id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
                </td></tr>
            @empty
                <tr><td colspan="8" class="text-center">No uploaded PDFs for this session.</td></tr>
            @endforelse
        </tbody></table>
        @if ($files->hasPages())
            <div class="d-flex justify-content-end">
                <ul class="pagination">
                    <li class="paginate_button page-item previous {{ $files->onFirstPage() ? 'disabled' : '' }}">
                        <a href="{{ $files->previousPageUrl() ?: '#' }}" aria-controls="zero_configuration_table" class="page-link">Previous</a>
                    </li>
                    @if ($files->currentPage() > 3)
                        <li class="paginate_button page-item"><a href="{{ $files->url(1) }}" aria-controls="zero_configuration_table" class="page-link">1</a></li>
                        <li class="paginate_button page-item disabled"><a href="#" aria-controls="zero_configuration_table" class="page-link">…</a></li>
                    @endif
                    @foreach ($files->getUrlRange(max(1, $files->currentPage() - 2), min($files->lastPage(), $files->currentPage() + 2)) as $page => $url)
                        <li class="paginate_button page-item {{ $page == $files->currentPage() ? 'active' : '' }}"><a href="{{ $url }}" aria-controls="zero_configuration_table" class="page-link">{{ $page }}</a></li>
                    @endforeach
                    @if ($files->currentPage() < $files->lastPage() - 2)
                        <li class="paginate_button page-item disabled"><a href="#" aria-controls="zero_configuration_table" class="page-link">…</a></li>
                        <li class="paginate_button page-item"><a href="{{ $files->url($files->lastPage()) }}" aria-controls="zero_configuration_table" class="page-link">{{ $files->lastPage() }}</a></li>
                    @endif
                    <li class="paginate_button page-item next {{ $files->hasMorePages() ? '' : 'disabled' }}">
                        <a href="{{ $files->nextPageUrl() ?: '#' }}" aria-controls="zero_configuration_table" class="page-link">Next</a>
                    </li>
                </ul>
            </div>
        @endif
    </div></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var scholar = document.getElementById('scholar_no');
    var results = document.getElementById('student-results');
    var searchUrl = @json(route('student-tc-files.student-search'));

    function clearDetails() {
        document.getElementById('student_name').value = '';
        document.getElementById('class_name').value = '';
        document.getElementById('section_name').value = '';
    }
    function selectStudent(student) {
        scholar.value = student.scholar_no;
        document.getElementById('student_name').value = student.student_name || '';
        document.getElementById('class_name').value = student.class_name || '';
        document.getElementById('section_name').value = student.section_name || '';
        results.classList.add('d-none');
        results.innerHTML = '';
    }
    scholar.addEventListener('input', function () {
        var query = scholar.value.trim();
        clearDetails();
        if (query.length < 2) { results.innerHTML = ''; results.classList.add('d-none'); return; }
        fetch(searchUrl + '?q=' + encodeURIComponent(query), { credentials: 'same-origin' })
            .then(function (response) { return response.ok ? response.json() : []; })
            .then(function (students) {
                results.innerHTML = '';
                students.forEach(function (student) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'list-group-item list-group-item-action';
                    button.textContent = student.scholar_no + ' - ' + student.student_name + ' (' + student.class_name + (student.section_name ? ' / ' + student.section_name : '') + ')';
                    button.addEventListener('click', function () { selectStudent(student); });
                    results.appendChild(button);
                });
                results.classList.toggle('d-none', students.length === 0);
            });
    });
    scholar.addEventListener('blur', function () { setTimeout(function () { results.classList.add('d-none'); }, 150); });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('tc-record-search');
    var body = document.getElementById('tc-record-results');
    var originalRows = body.innerHTML;
    var timer;
    var endpoint = @json(route('student-tc-files.record-search'));
    var viewUrl = @json(route('student-tc-files.view', '__id__'));
    var downloadUrl = @json(route('student-tc-files.download', '__id__'));
    var deleteUrl = @json(route('student-tc-files.delete', '__id__'));
    var csrf = @json(csrf_token());

    function cell(row, value) { var td = document.createElement('td'); td.textContent = value || ''; row.appendChild(td); }
    function action(row, item) {
        var td = document.createElement('td');
        [['View', 'btn-info', viewUrl], ['Download', 'btn-success', downloadUrl]].forEach(function (link) {
            var a = document.createElement('a'); a.className = 'btn btn-sm ' + link[1]; a.textContent = link[0]; a.href = link[2].replace('__id__', item.id);
            if (link[0] === 'View') { a.target = '_blank'; a.rel = 'noopener'; }
            td.appendChild(a); td.appendChild(document.createTextNode(' '));
        });
        var form = document.createElement('form'); form.className = 'd-inline'; form.method = 'POST'; form.action = deleteUrl.replace('__id__', item.id);
        form.innerHTML = '<input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="DELETE"><button class="btn btn-sm btn-danger">Delete</button>';
        td.appendChild(form); row.appendChild(td);
    }
    input.addEventListener('input', function () {
        clearTimeout(timer); var query = input.value.trim();
        if (!query) { body.innerHTML = originalRows; return; }
        timer = setTimeout(function () {
            fetch(endpoint + '?q=' + encodeURIComponent(query), {credentials: 'same-origin'})
                .then(function (response) { return response.ok ? response.json() : []; })
                .then(function (items) {
                    body.innerHTML = '';
                    if (!items.length) { body.innerHTML = '<tr><td colspan="8" class="text-center">No matching records found.</td></tr>'; return; }
                    items.forEach(function (item, index) {
                        var row = document.createElement('tr'); cell(row, index + 1); cell(row, item.scholar_no); cell(row, item.student_name || '--'); cell(row, item.class_name || '--'); cell(row, item.section_name || '--'); cell(row, item.session_name);
                        cell(row, item.uploaded_at ? new Date(item.uploaded_at).toLocaleString() : ''); action(row, item); body.appendChild(row);
                    });
                });
        }, 250);
    });
});
</script>
@endsection
