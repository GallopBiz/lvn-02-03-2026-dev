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
  let map;
  let marker;
  let vehical = "<?php echo $vehical; ?>";

  function initMap() {
    // Start by fetching the initial location after API is loaded
    fetchInitialLocationAndInitMap();
  }

  function fetchInitialLocationAndInitMap() {
    $.ajax({
      url: '{{ route("getNewDestination") }}',
      method: 'GET',
      data: { vehical: vehical },
      dataType: 'json',
      success: function (response) {
        const initialLocation = {
          lat: parseFloat(response.latitude),
          lng: parseFloat(response.longitude),
        };
        initMapWithLocation(initialLocation);
      },
      error: function (error) {
        console.error("Error fetching initial location:", error);
      }
    });
  }

  function initMapWithLocation(location) {
    map = new google.maps.Map(document.getElementById('map'), {
      zoom: 18,
      center: location,
    });

    const carIcon = {
      url: "{{url('assets/backend')}}/images/faces/car1.png",
      scaledSize: new google.maps.Size(40, 40)
    };

    marker = new google.maps.Marker({
      position: location,
      map: map,
      title: "Vehicle Location",
      icon: carIcon,
    });

    setInterval(fetchNewLocation, 25000);
  }

  function fetchNewLocation() {
    $.ajax({
      url: '{{ route("getNewDestination") }}',
      method: 'GET',
      data: { vehical: vehical },
      dataType: 'json',
      success: function (response) {
        const newLatLng = new google.maps.LatLng(
          parseFloat(response.latitude),
          parseFloat(response.longitude)
        );
        moveMarkerSmoothly(marker, newLatLng);
        map.setCenter(newLatLng);
      },
      error: function (error) {
        console.error("Error fetching new location:", error);
      }
    });
  }

  function moveMarkerSmoothly(marker, newPosition) {
    const duration = 1000;
    const framesPerSecond = 60;
    const frameCount = (duration / 1000) * framesPerSecond;

    let count = 0;

    const startLatLng = marker.getPosition();
    const deltaLat = (newPosition.lat() - startLatLng.lat()) / frameCount;
    const deltaLng = (newPosition.lng() - startLatLng.lng()) / frameCount;

    function animate() {
      count++;
      const lat = startLatLng.lat() + deltaLat * count;
      const lng = startLatLng.lng() + deltaLng * count;
      const nextPosition = new google.maps.LatLng(lat, lng);
      marker.setPosition(nextPosition);
      if (count < frameCount) {
        requestAnimationFrame(animate);
      }
    }

    animate();
  }
</script>
