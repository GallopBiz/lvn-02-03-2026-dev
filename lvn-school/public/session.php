<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<?php 

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
} else {
    $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

$result = substr($url, 0, strrpos($url, "/"));

?>
<div class="col-md-4 form-group mb-3">
    <form id="progress-form" class="p-4 progress-form" action="<?php echo $result;?>/create_session.php"  novalidate method="post">
        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="lastName1">Create Session Start Year:</label>
                <input type="number" class="form-control" placeholder="Create Session Start Year" 
                name="start_session">
            </div>
            <div class="col-md-6 form-group mb-3">
                <label for="lastName1">Create Session End Year:</label>
                <input type="number" class="form-control" placeholder="Create Session End Year" 
                name="end_session">
            </div>
            <div class="col-md-42">
                <button class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
</div>