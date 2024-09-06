// track_geofence.js

navigator.geolocation.watchPosition(function(position) {
    var userLat = position.coords.latitude;
    var userLng = position.coords.longitude;
    var eventLat = <?php echo $event_lat; ?>; // Latitude from the event
    var eventLng = <?php echo $event_lng; ?>; // Longitude from the event
    var distance = getDistance(userLat, userLng, eventLat, eventLng);

    // Assuming a geofence radius of 100 meters
    if (distance <= 100) {
        // User is inside the geofence, record the entry time via AJAX
        recordEntryTime();
    }
});

function getDistance(lat1, lon1, lat2, lon2) {
    var R = 6371e3; // Earth's radius in meters
    var φ1 = lat1 * Math.PI/180;
    var φ2 = lat2 * Math.PI/180;
    var Δφ = (lat2-lat1) * Math.PI/180;
    var Δλ = (lon2-lon1) * Math.PI/180;
    var a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ/2) * Math.sin(Δλ/2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function recordEntryTime() {
    // Make an AJAX call to record the entry time
    fetch('record_entry_time.php?event_id=<?php echo $event_id; ?>&student_id=<?php echo $student_id; ?>')
        .then(response => response.text())
        .then(data => console.log(data));
}
