<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Location</title>
</head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<body>
    <input type="text" name="location_name" id="location_name">
    <button onclick="getLocation()">Get Location</button>
    <p id="location"></p>

    <script>
        function getLocation() {
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;
            var location_name = $("#location_name").val();
            // Display the coordinates on the HTML page
            document.getElementById("location").innerHTML = "Latitude: " + latitude + "<br>Longitude: " + longitude + "<br>location_name: " + location_name;

            // Send the coordinates to the server using AJAX
            var xhttp = new XMLHttpRequest();
            xhttp.open("POST", "process_location.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            // Set up a callback function to handle the AJAX response
            xhttp.onreadystatechange = function () {
                if (xhttp.readyState == 4 && xhttp.status == 200) {
                    var response = JSON.parse(xhttp.responseText);

                    // Check if the response contains a success message
                    if (response.message && response.message === "Data inserted successfully.") {
                        // Show an alert if the data is inserted successfully
                        alert("Data inserted successfully!");
                    } else {
                        // Handle other responses or errors as needed
                        console.log(response.message);
                    }
                }
            };

            // Send the coordinates to the server
            xhttp.send("latitude=" + latitude + "&longitude=" + longitude + "&location_name=" + location_name);

        }
    </script>
</body>
</html>
