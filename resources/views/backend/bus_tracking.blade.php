@extends('layouts.app')

@section('content')
<style>
    html, body, #map {
        height: 100%;
        margin: 0;
        padding: 0;
    }
    #map {
        width: 100vw;
        height: 100vh;
        min-height: 100vh;
        min-width: 100vw;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1;
    }
    .leaflet-control-zoom {
        z-index: 1000;
    }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<div id="map"></div>
<script>
var map, marker;
var busIcon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/3448/3448339.png', // simple bus icon
    iconSize: [48, 48],
    iconAnchor: [24, 48],
    popupAnchor: [0, -48]
});
function initMap(lat, lng) {
    if (!map) {
        map = L.map('map', { zoomControl: true }).setView([lat, lng], 18); // High zoom for clarity
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 22,
            attribution: '© OpenStreetMap'
        }).addTo(map);
        marker = L.marker([lat, lng], {icon: busIcon}).addTo(map);
    } else {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 18); // Keep zoom high for clarity
    }
}
function updateTracking() {
    fetch(window.location.href, {headers: {"X-Requested-With": "XMLHttpRequest"}})
        .then(r => r.json())
        .then(function(data) {
            if(data.tracking && data.tracking.latitude && data.tracking.longitude) {
                initMap(parseFloat(data.tracking.latitude), parseFloat(data.tracking.longitude));
            }
        });
}
document.addEventListener('DOMContentLoaded', function() {
    var lat = parseFloat(@json($tracking['latitude'] ?? 'null'));
    var lng = parseFloat(@json($tracking['longitude'] ?? 'null'));
    if(!isNaN(lat) && !isNaN(lng)) {
        initMap(lat, lng);
    }
    setInterval(updateTracking, 10000);
});
</script>
@endsection
