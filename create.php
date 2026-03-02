<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">  
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <title>How To Import CSV File In MySQL Using PHP - Techsolutionstuff</title>
        <style>
            .custom-file-input.selected:lang(en)::after {
                content: "" !important;
            }
        
            .custom-file {
                overflow: hidden;
            }
        
            .custom-file-input {
                white-space: nowrap;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="customFileInput" name="file">
                        <label class="custom-file-label" for="customFileInput">Select File:</label>
                    </div>
                    <div class="input-group-append">
                        <input type="submit" name="submit" value="upload" class="btn btn-primary">
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>