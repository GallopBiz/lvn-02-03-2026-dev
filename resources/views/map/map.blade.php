<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    #trackCont{
        background-color: #eef3f3;
        border-radius: 25px;
        display: flex;
        /* align-items: center; */
        justify-content: center;
        /* padding: 10px; */
        /* margin:5px; */
    }
    #imgdiv{
        display: flex;
  align-items: center;
    }
    #AddLoc{
        background-color: #e4f0fd;
        border-radius: 25px;
        padding: 10px;
        /* margin-right: 17px;  */
        /* margin:5px; */
    }
    #BusInfo{
        background-color: #ddebda;
        border-radius: 25px;
        padding: 10px;
        /* margin-right: 2px;  */
    }
</style>
<?php
$sessionData = session('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');

?>
    <div id="map" style="height: 100%; width: 100%;"></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBa0_Zia458Lqzrwk7PzzpU7JIwJAkITdk&callback=initMap" async defer></script>
<script>
   var sessionData = <?php echo json_encode($sessionData); ?>;
    console.log(sessionData); // Print the sessionData value in the browser console
    function stuadd(sessionData) {
        console.log(sessionData);
        $.ajax({
            data: { value: sessionData },
            url: "{{ url('filterstuaddre') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            method: "POST",
            datatype: 'json',
            success: function(response) {
                // Handle the AJAX response if needed
                var address = response;
                console.log(address);
                var geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json";
                var apiKey = 'AIzaSyBa0_Zia458Lqzrwk7PzzpU7JIwJAkITdk';
                $.get(geocodeUrl, {
            address: address,
            key: apiKey
        }).done(function(data) {
            if (data.status === "OK" && data.results[0].geometry) {
                var location = data.results[0].geometry.location;
                stulatitude = location.lat;
                stulongitude = location.lng;
                console.log(stulatitude,stulongitude);
                initMap(stulatitude,stulongitude);
            } else {
                console.error("Geocoding failed");
            }
        }).fail(function(error) {
            console.error(error);
        });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    // Call the stuadd function with the sessionData value
    stuadd(sessionData);
  function initMap(stulatitude,stulongitude) {
        // Now you can use stulatitude and stulongitude here
        console.log("Latitude:", stulatitude);
    console.log("Longitude:", stulongitude);
    // Initial location
    var initialLocation = { lat: 22.6934502, lng: 75.8464859 };

    // Create the map
    const map = new google.maps.Map(document.getElementById('map'), {
      zoom: 14,
      center: initialLocation
    });

    const routePoints = [
      { lat: 22.6934502, lng: 75.8464859 }, // Starting point
      { lat: stulatitude, lng: stulongitude } // Ending point
    ];
    
    const directionsService = new google.maps.DirectionsService();
// Initialize the DirectionsRenderer and set its options
const directionsRenderer = new google.maps.DirectionsRenderer({
  map: map,
  suppressMarkers: true,
  polylineOptions: {
    strokeColor: '#0000FF', // Blue color for the route
    strokeOpacity: 1.0,
    strokeWeight: 2
  }
});

// Initialize a polyline for displaying the route
const routePolyline = new google.maps.Polyline({
  map: map,
  strokeColor: '#0000FF', // Blue color for the route
  strokeOpacity: 1.0,
  strokeWeight: 2
});

let dynamicRoutePoints = [];
    const markers = routePoints.map((point, index) => {
      return new google.maps.Marker({
        position: point,
        map: map,
        label: (index === 0) ? 'Start' : 'End'
      });
    });

    const carIcon = {
      url: "{{url('assets/backend')}}/images/faces/car1.png",
      scaledSize: new google.maps.Size(40, 40)
    };

    const marker = new google.maps.Marker({
      position: routePoints[0], // Set initial position to starting point
      map: map,
      icon: carIcon
    });

// Function to update the dynamic route
function updateDynamicRoute(newLocation) {
  dynamicRoutePoints.push(newLocation);

  const request = {
    origin: dynamicRoutePoints[0],
    destination: routePoints[1], // You can use the ending point as the destination
    waypoints: dynamicRoutePoints.slice(1, -1), // Exclude the first and last points
    travelMode: google.maps.TravelMode.DRIVING
  };

  directionsService.route(request, function(result, status) {
    if (status === "OK") {
      directionsRenderer.setDirections(result);
    } else {
      console.error("Directions request failed due to " + status);
    }
  });
}
// Function to update the marker's location
function updateMarkerLocation(newLocation) {
  marker.setPosition(newLocation);
}
    // Show the info window with title and description
    const infoWindow = new google.maps.InfoWindow();

    function updateInfoWindow(title, description, status, speed) {
  const color = (status === "RUNNING") ? "green" : "red";
  
  // Create a table with the data and apply CSS styles
  const content = `
  <div style="position: fixed; bottom: 10px; left: 50%; transform: translateX(-50%); width: 40%; background-color: white; border-radius: 10px; padding: 10px; box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);">
    <div style="color: ${color};">
        <style>
            #trackCont{
                background-color: #eef3f3;
                border-radius: 25px;
                display: flex;
                /* align-items: center; */
                justify-content: center;
                /* padding: 10px; */
                /* margin:5px; */
            }
            #imgdiv{
                display: flex;
                align-items: center;
            }
            #AddLoc{
                background-color: #e4f0fd;
                border-radius: 25px;
                padding: 10px;
                /* margin-right: 17px;  */
                /* margin:5px; */
            }
            #BusInfo{
                background-color: #ddebda;
                border-radius: 25px;
                padding: 10px;
                /* margin-right: 2px;  */
            }
        </style>

        <div class="row" id="card-box" style="display:none;">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <!-- put here -->
                <div class="row" id="trackCont">
                    <div class="col-md-4" id="BusInfo">
                        <h3>${title}</h3>
                        <table class="table table-borderless">
                            <tbody>
                            <tr>
                                <th>Speed</th>
                                <td>${speed}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>${status}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4" id="AddLoc">
                        <h4>Location</h4>
                        <td>${description}</td>
                        <h6>Last Update At 31-07-2023 16:31:45</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>
</div>`;
  
   // Remove previous content before appending the new content
   $('#data-container').remove();

// Append the new content to the body
$('body').append(`<div id="data-container">${content}</div>`);
}


    // Function to update the map with a new location
    function updateMap(newLocation) {
      marker.setPosition(newLocation);
    }

    // Function to fetch new location data every 30 seconds
    function fetchNewLocation() {
      // Use AJAX to fetch new location data from the controller
      var vehical = "<?php echo $vehical; ?>";
      $.ajax({
        url: '{{ route("getNewDestination") }}',
        method: 'GET',
        data : {vehical:vehical},
        dataType: 'json',
        success: function(response) {
          console.log(response);
          // Update the map with the new location
          // const newLocation = { lat: parseFloat(response.Latitude), lng: parseFloat(response.Longitude) };
          const newLocation = { lat: parseFloat(response.latitude), lng: parseFloat(response.longitude) };
          updateDynamicRoute(newLocation); // Update the dynamic route
          updateMarkerLocation(newLocation); // Update the marker's location
          // map.panTo(newLocation); // Pan to the new location

          // Update the info window with title and description
          updateInfoWindow(response.Vehicle_No, response.Location, response.Status, response.Speed);

          // Update the card box with title and description
          // $('#card-title').text(response.Vehicle_No);
          // $('#card-description').text(response.Location);
          // $('#card-status').text(response.Status);
          // $('#card-speed').text(response.Speed);

          // if (response.Status === "RUNNING") {
          //   $('#card-box').css('background-color', 'green');
          // } else {
          //   $('#card-box').css('background-color', 'red');
          // }
          
          // $('#card-box').show();
        },
        error: function(error) {
          console.error('Error fetching new location:', error);
        }
      });
    }

    // Fetch new location every 30 seconds
    setInterval(fetchNewLocation, 25000);
  }
</script>