
<style>
/* 
 * Always set the map height explicitly to define the size of the div element
 * that contains the map. 
 */
#map {
  height: 100%;
}

/* 
 * Optional: Makes the sample page fill the window. 
 */
html,
body {
  height: 100%;
  margin: 0;
  padding: 0;
}

#card-box {
    height: 100px; /* Match the height of the .popup-bubble */
    width: 200%; /* Match the width of the .popup-bubble */
    overflow: hidden;
    border: 1px solid #ccc; /* Adding a border for visualization */
    border-radius: 10px;
    /* Add any additional styles you want */
}
/* The popup bubble styling. */
.popup-bubble {
  /* Position the bubble centred-above its parent. */
  position: absolute;
  top: 0;
  left: 0;
  transform: translate(-50%, -100%);
  /* Style the bubble. */
  background-color: white;
  padding: 5px;
  border-radius: 5px;
  font-family: sans-serif;
  overflow-y: auto;
  height: 100px;
 width: 200%;
  box-shadow: 0px 2px 10px 1px rgba(0, 0, 0, 0.5);
}

/* The parent of the bubble. A zero-height div at the top of the tip. */
.popup-bubble-anchor {
  /* Position the div a fixed distance above the tip. */
  position: absolute;
  width: 100%;
  bottom: 8px;
  left: 0;
}

/* This element draws the tip. */
.popup-bubble-anchor::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  /* Center the tip horizontally. */
  transform: translate(-50%, 0);
  /* The tip is a https://css-tricks.com/snippets/css/css-triangle/ */
  width: 0;
  height: 0;
  /* The tip is 8px high, and 12px wide. */
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-top: 8px solid white;
}

/* JavaScript will position this div at the bottom of the popup tip. */
.popup-container {
            cursor: auto;
            height: 0;
            position: absolute;
            /* The max width of the info window. */
            width: 200px;
        }
        #trackCont {
    background-color: #eef3f3;
    border-radius: 25px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding: 10px;
}

#AddLoc {
    background-color: #e4f0fd;
    border-radius: 25px;
    padding: 10px;
    width: 100%;
}

#BusInfo {
    background-color: #ddebda;
    border-radius: 25px;
    padding: 10px;
    width: 100%;
}

#card-box {
    height: 100px; /* Match the height of the .popup-bubble */
    width: 200%; /* Match the width of the .popup-bubble */
    overflow: hidden;
    border: 1px solid #ccc; /* Adding a border for visualization */
    border-radius: 10px;
    /* Add any additional styles you want */
}
</style>
<div id="map"></div>
  <div id="content">
        <div class="row" id="card-box">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <!-- put here -->
                <div class="row" id="trackCont">
                    <div class="col-md-4" id="BusInfo">
                        <h3 id="card-title"></h3>
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th>Running</th>
                                    <td>since 03min</td>
                                </tr>
                                <tr>
                                    <th>Speed</th>
                                    <td id="card-speed"></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td id="card-status"></td>
                                </tr>
                                <tr>
                                    <th>Mobile No.</th>
                                    <td>123465687</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4" id="AddLoc">
                        <h4>Location</h4>
                        <p id="card-description"></p>
                        <h6>Last Update At 31-07-2023 16:31:45</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBa0_Zia458Lqzrwk7PzzpU7JIwJAkITdk&callback=initMap" async defer></script>  
<script>
  let map, popup, Popup;

/** Initializes the map and the custom popup. */
function initMap() {
  map = new google.maps.Map(document.getElementById("map"), {
    center: {  lat: 22.7543887319602, lng: 75.8952665170425 },
    zoom: 14,
  });
  /**
   * A customized popup on the map.
   */
  class Popup extends google.maps.OverlayView {
    position;
    containerDiv;
    constructor(position, content) {
      super();
      this.position = position;
      content.classList.add("popup-bubble");

      // This zero-height div is positioned at the bottom of the bubble.
      const bubbleAnchor = document.createElement("div");

      bubbleAnchor.classList.add("popup-bubble-anchor");
      bubbleAnchor.appendChild(content);
      // This zero-height div is positioned at the bottom of the tip.
      this.containerDiv = document.createElement("div");
      this.containerDiv.classList.add("popup-container");
      this.containerDiv.appendChild(bubbleAnchor);
      // Optionally stop clicks, etc., from bubbling up to the map.
      Popup.preventMapHitsAndGesturesFrom(this.containerDiv);
    }
    /** Called when the popup is added to the map. */
    onAdd() {
      this.getPanes().floatPane.appendChild(this.containerDiv);
    }
    /** Called when the popup is removed from the map. */
    onRemove() {
      if (this.containerDiv.parentElement) {
        this.containerDiv.parentElement.removeChild(this.containerDiv);
      }
    }
    /** Called each frame when the popup needs to draw itself. */
    draw() {
      const divPosition = this.getProjection().fromLatLngToDivPixel(
        this.position,
      );
      // Hide the popup when it is far out of view.
      const display =
        Math.abs(divPosition.x) < 4000 && Math.abs(divPosition.y) < 4000
          ? "block"
          : "none";

      if (display === "block") {
        this.containerDiv.style.left = divPosition.x + "px";
        this.containerDiv.style.top = divPosition.y + "px";
      }

      if (this.containerDiv.style.display !== display) {
        this.containerDiv.style.display = display;
      }
    }
  }

  popup = new Popup(
    new google.maps.LatLng(22.7543887319602, 75.8952665170425),
    document.getElementById("content"),
  );
  popup.setMap(map);
}

function fetchNewLocation() {
      // Use AJAX to fetch new location data from the controller
      $.ajax({
        url: '{{ route("getNewDestination") }}',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
          console.log(response);
          // Update the map with the new location
          const newLocation = { lat: parseFloat(response.Latitude), lng: parseFloat(response.Longitude) };
          updateMap(newLocation);
          map.panTo(newLocation); // Pan to the new location

          // Update the info window with title and description
          updateInfoWindow(response.Vehicle_No, response.Location, response.Status, response.Speed);

          // Update the card box with title and description
          $('#card-title').text(response.Vehicle_No);
          $('#card-description').text(response.Location);
          $('#card-status').text(response.Status);
          $('#card-speed').text(response.Speed);

          // if (response.Status === "RUNNING") {
          //   $('#card-box').css('background-color', 'green');
          // } else {
          //   $('#card-box').css('background-color', 'red');
          // }
          
          $('#card-box').show();
        },
        error: function(error) {
          console.error('Error fetching new location:', error);
        }
      });
    }

window.initMap = initMap;
</script>