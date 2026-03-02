@extends('backend.layouts.main')

@section('main-container')

<!-- Include Quill.js CDN -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<div class="main-content pt-4">
    <div class="breadcrumb">
        <h1 class="me-2">Bonafide Certificate</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="form_section1_div">
            <form method="post" action="{{ route('bonafide.generateCertificate') }}" novalidate="novalidate" target="_blank">
                @csrf

                <!-- Scholar Search Field -->
                <div class="row">
                    <div class="form-outline w-auto p-4 progress-form">
                        <label class="form-label" for="form1">Student Search</label>
                        <select class="form-control" onchange="select_data(this);" name="scholar_no" id="search_student" data-live-search="true">
                            <option data-tokens="china">Select The Student</option>
                            <?php if(!empty($data_student_name)) {
                                foreach($data_student_name as $name){
                                    $selected = "";
                                    if(!empty($student_data)){
                                        if($student_data->id == $name->id){
                                            $selected = "selected";
                                        }
                                    }
                                    echo '<option '.$selected.' value="'.$name->scholar_no.'" data-tokens="'.$name->student_name.' '.$name->scholar_no.'">'.$name->student_name.' '.$name->scholar_no.'</option>';
                                        
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Full-Width Text Editor -->
                <div class="row">
                    <div class="col-md-12 form-group mb-3">
                        <label for="remarks">Certificate Content</label>
                        <div id="editor" style="height: 300px;"></div>  <!-- Quill editor will be here -->
                        <input type="hidden" name="remarks" id="remarks">  <!-- Hidden input to store editor content -->
                        <span class="validation_err">{{ $errors->first('remarks') }}</span>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-12 text-left">
                        <button type="submit" class="btn btn-primary">Generate Certificate</button>
                        <input type="reset" class="btn btn-danger text-white" value="Reset">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="separator-breadcrumb border-top mt-4"></div>
</div>

<script>
    // Initialize Quill editor for full-width
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link']
            ]
        },
        placeholder: 'Enter certificate content here...',
        readOnly: false,
    });

    // Set the default content in the editor
    var defaultText = ` This certificate is being issued for passport application, visa processing, school admission, or any official requirement.`;

    // Set the default text in the Quill editor
    quill.root.innerHTML = defaultText;

    // On form submit, copy the content from Quill to the hidden input
    $('form').submit(function() {
        var content = quill.root.innerHTML;  // Get content from Quill editor
        $('#remarks').val(content);  // Set content to hidden input
    });
</script>
<script type="text/javascript">
   $.noConflict();
        jQuery(document).ready(function($){
  $("#search_student").select2();
}); </script>
@endsection
