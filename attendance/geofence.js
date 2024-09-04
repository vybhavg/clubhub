const geofenceCenter = { latitude: 12.9715987, longitude: 77.594566 }; // Example coordinates
const geofenceRadius = 200; // Geofence radius in meters

let startTime = null;
let totalTimeInGeofence = 0;

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; 
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    return R * c; 
}

function isInsideGeofence(position) {
    const userLon = position.coords.longitude;

    const distance = calculateDistance(userLat, userLon, geofenceCenter.latitude, geofenceCenter.longitude);
    return distance <= geofenceRadius;
}

function trackLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition((position) => {
            const insideGeofence = isInsideGeofence(position);

            const statusText = insideGeofence ? "You are inside the geofence." : "You are outside the geofence.";
            document.getElementById("status").innerText = statusText;

            if (insideGeofence) {
                if (!startTime) {
                    startTime = new Date();
                }
            } else {
                if (startTime) {
                    const endTime = new Date();
                    const timeSpent = (endTime - startTime) / 1000; // time in seconds
                    totalTimeInGeofence += timeSpent;
                    startTime = null;
                }
            }

            // Update hidden form fields
            document.getElementById("geofenceStatus").value = insideGeofence ? 1 : 0;
            document.getElementById("totalTimeInGeofence").value = Math.floor(totalTimeInGeofence);
        }, 
        (error) => {
            console.error("Error getting location: ", error);
        },
        {
            enableHighAccuracy: true,
          timeout: 10000 // Timeout for fetching location in milliseconds
        });
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}

// Start tracking location
trackLocation();
