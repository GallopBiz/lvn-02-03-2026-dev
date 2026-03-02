<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if ($message = Session::get('success'))
<!-- <div class="alert alert-dismissible fade show alert-card alert-success" role="alert">
    <strong class="text-capitalize">Success!</strong> {{ $message }}
  <button class="btn btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
</div> -->
<!-- Content -->
<div class="card-body">
    <button id="myButton" style="display:none;"></button>
</div>

<!-- Custom script -->
<script>
    window.onload = function() {
        toastr.success("{{ $message }}", "Success");
    };

    document.getElementById("myButton").addEventListener("click", function() {
        toastr.success("{{ $message }}", "Success");
    });
</script>
@endif 
    
@if ($message = Session::get('error'))
<!-- <div class="alert alert-dismissible fade show alert-card alert-danger" role="alert">
    <strong class="text-capitalize">Error!</strong> {{ $message }}
  <button class="btn btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
</div> -->
<!-- Content -->
<div class="card-body">
    <button id="errButton" style="display:none;">Click me</button>
</div>
<!-- Custom script -->
<script>
    window.onload = function() {
        // toastr.success("Toastr success message", "Success");
        toastr.error("{{ $message }}", "Error");
    };

    document.getElementById("errButton").addEventListener("click", function() {
        // toastr.success("Toastr success message", "Success");
        toastr.error("{{ $message }}", "Error");
    });
</script>
@endif
     
@if ($message = Session::get('warning'))
<!-- <div class="alert alert-warning alert-dismissible fade show" role="alert">
  <strong>{{ $message }}</strong>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div> -->
<div class="card-body">
    <button id="warButton" style="display:none;">Click me</button>
</div>
<!-- Custom script -->
<script>
    window.onload = function() {
        // toastr.success("Toastr success message", "Success");
        toastr.warning("{{ $message }}", "Warning");
    };

    document.getElementById("warButton").addEventListener("click", function() {
        // toastr.success("Toastr success message", "Success");
        toastr.warning("{{ $message }}", "Warning");
    });
</script>
@endif
     
@if ($message = Session::get('info'))
<!-- <div class="alert alert-info alert-dismissible fade show" role="alert">
  <strong>{{ $message }}</strong>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div> -->
<div class="card-body">
    <button id="infoButton" style="display:none;">Click me</button>
</div>
<!-- Custom script -->
<script>
    window.onload = function() {
        // toastr.success("Toastr success message", "Success");
        toastr.info("{{ $message }}", "Info");
    };

    document.getElementById("infoButton").addEventListener("click", function() {
        // toastr.success("Toastr success message", "Success");
        toastr.info("{{ $message }}", "Info");
    });
</script>
@endif
    
@if ($errors->any())
<!-- <div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Please check the form below for errors</strong>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div> -->
<div class="card-body">
    <button id="anyButton" style="display:none;">Click me</button>
</div>
<!-- Custom script -->
<script>
    window.onload = function() {
        // toastr.success("Toastr success message", "Success");
        toastr.warning("Please check the form below for errors", "Warning");
    };

    document.getElementById("anyButton").addEventListener("click", function() {
        // toastr.success("Toastr success message", "Success");
        toastr.warning("Please check the form below for errors", "Warning");
    });
</script>
@endif
