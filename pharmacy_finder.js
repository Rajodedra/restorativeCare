// pharmacy_finder.js
// Global variables for pharmacy locator functionality
let map;
let userMarker;
let radiusCircle;
let pharmacyMarkers = [];
let infoWindow;
let userLocation;
let searchRadius = 2; // Default radius in kilometers

// Initialize the map when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Set default location (central location in your service area)
    const defaultLocation = { lat: 28.6139, lng: 77.2090 }; // Example: New Delhi
    
    // Initialize the map
    initMap(defaultLocation);
    
    // Set up event listeners
    document.getElementById('current-location-btn').addEventListener('click', getUserLocation);
    document.getElementById('search-btn').addEventListener('click', function() {
        const address = document.getElementById('search-location').value;
        if (address) {
            geocodeAddress(address);
        } else {
            alert('Please enter a location to search.');
        }
    });
    document.getElementById('radius-select').addEventListener('change', function() {
        searchRadius = parseFloat(this.value);
        if (userLocation) {
            // Update radius circle if it exists
            if (radiusCircle) {
                radiusCircle.setRadius(searchRadius * 1000); // Convert km to meters
            }
            searchPharmacies();
        }
    });
    
    // Set up search box for addresses
    setupAddressSearch();
});

// Initialize the map
function initMap(center) {
    // Initialize Mappls map
    map = new mappls.Map('pharmacy-map', {
        center: [center.lng, center.lat], // Mappls uses [lng, lat] format
        zoom: 12,
        fullscreenControl: true,
        location: true,
        geolocation: true
    });
    
    infoWindow = new mappls.InfoWindow();

    // Add a radius circle around the user's location
    radiusCircle = new mappls.Circle({
        map: map,
        center: [center.lng, center.lat],
        radius: searchRadius * 1000, // Convert km to meters
        strokeColor: '#3498db',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#3498db',
        fillOpacity: 0.1
    });
}

// Get the user's current location
function getUserLocation() {
    const locationButton = document.getElementById('current-location-btn');
    locationButton.disabled = true;
    locationButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Getting Location...';
    
    document.getElementById('loading').style.display = 'flex';
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                updateUserMarker(userLocation);
                map.setCenter([userLocation.lng, userLocation.lat]);
                
                searchPharmacies();
                
                locationButton.disabled = false;
                locationButton.innerHTML = '<i class="fas fa-location-arrow mr-2"></i> Use My Location';
                document.getElementById('loading').style.display = 'none';
            },
            function(error) {
                console.error('Geolocation error:', error);
                locationButton.disabled = false;
                locationButton.innerHTML = '<i class="fas fa-location-arrow mr-2"></i> Use My Location';
                document.getElementById('loading').style.display = 'none';
                
                let errorMessage = 'Unable to get your location.';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += ' Please enable location permissions in your browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += ' Location information is unavailable.';
                        break;
                    case error.TIMEOUT:
                        errorMessage += ' The request to get your location timed out.';
                        break;
                }
                
                alert(errorMessage);
            }
        );
    } else {
        alert('Geolocation is not supported by your browser.');
        locationButton.disabled = false;
        locationButton.innerHTML = '<i class="fas fa-location-arrow mr-2"></i> Use My Location';
        document.getElementById('loading').style.display = 'none';
    }
}

// Geocode an address to get coordinates
function geocodeAddress(address) {
    document.getElementById('loading').style.display = 'flex';
    
    // Use Mappls Geocoding API
    mappls.geocode({
        address: address,
        key: '68a9fc9f160b4347748742zwr2bce8c'
    }, function(response) {
        document.getElementById('loading').style.display = 'none';
        
        if (response && response.results && response.results.length > 0) {
            const result = response.results[0];
            userLocation = {
                lat: parseFloat(result.latitude),
                lng: parseFloat(result.longitude)
            };
            
            // Update the map
            updateUserMarker(userLocation);
            map.setCenter([userLocation.lng, userLocation.lat]);
            
            // Find nearby pharmacies
            searchPharmacies();
        } else {
            alert('Could not find that address. Please try a different one.');
        }
    });
}

// Update or create the user's location marker
function updateUserMarker(location) {
    // Clear existing user marker if any
    if (userMarker) {
        userMarker.remove();
    }
    
    // Create new marker
    userMarker = new mappls.Marker({
        map: map,
        position: [location.lng, location.lat],
        icon: {
            url: 'https://apis.mappls.com/map_v3/1.png', // Default marker if custom one doesn't exist
            scaledSize: [30, 40]
        },
        draggable: false,
        popupHtml: 'Your Location'
    });
    
    // Update radius circle
    radiusCircle.setCenter([location.lng, location.lat]);
    radiusCircle.setRadius(searchRadius * 1000); // Convert km to meters
}

// Search for pharmacies
function searchPharmacies() {
    if (!userLocation) {
        alert('Please enter a location or use your current location first.');
        return;
    }
    
    // Show loading indicator
    document.getElementById('loading').style.display = 'flex';
    
    // Clear existing pharmacy markers
    clearPharmacyMarkers();
    
    // Update the pharmacy list with loading message
    const pharmacyList = document.getElementById('pharmacy-list');
    pharmacyList.innerHTML = '<div class="p-4 text-center"><i class="fas fa-spinner fa-spin mr-2"></i> Finding pharmacies...</div>';
    
    // Make API request to get pharmacies
    fetch(`pharmacy_api.php?latitude=${userLocation.lat}&longitude=${userLocation.lng}&radius=${searchRadius}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('loading').style.display = 'none';
            
            // Update results title
            document.getElementById('results-title').textContent = 
                `Nearby Pharmacies (${data.pharmacies.length})`;
            
            // Display pharmacies
            displayPharmacies(data.pharmacies);
        })
        .catch(error => {
            console.error('Error fetching pharmacies:', error);
            document.getElementById('loading').style.display = 'none';
            pharmacyList.innerHTML = `
                <div class="p-4 text-center text-red-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error retrieving pharmacy data. Please try again.
                </div>
            `;
        });
}

// Display pharmacies on the map and in the list
function displayPharmacies(pharmacies) {
    const pharmacyList = document.getElementById('pharmacy-list');
    pharmacyList.innerHTML = '';
    
    if (pharmacies.length === 0) {
        pharmacyList.innerHTML = `
            <div class="p-4 text-center text-gray-600">
                <i class="fas fa-info-circle mr-2"></i>
                No pharmacies found within ${searchRadius} km. Try increasing your search radius.
            </div>
        `;
        return;
    }
    
    // Pharmacy icon
    const pharmacyIcon = {
        url: 'https://apis.mappls.com/map_v3/2.png', // Default pharmacy icon
        scaledSize: [30, 40]
    };
    
    pharmacies.forEach((pharmacy, index) => {
        // Create marker on map
        const marker = new mappls.Marker({
            map: map,
            position: [parseFloat(pharmacy.longitude), parseFloat(pharmacy.latitude)],
            icon: pharmacyIcon,
            draggable: false,
            popupHtml: pharmacy.name
        });
        
        // Create info window content
        const infoContent = `
            <div class="p-3 max-w-xs">
                <h3 class="font-bold text-lg mb-1">${pharmacy.name}</h3>
                <p class="text-gray-600 mb-2">${pharmacy.address}</p>
                ${pharmacy.distance ? `<p class="text-sm text-blue-600 mb-2"><i class="fas fa-route mr-1"></i> ${pharmacy.distance.toFixed(2)} km away</p>` : ''}
                ${pharmacy.phone ? `<p class="mb-1"><i class="fas fa-phone mr-1"></i> <a href="tel:${pharmacy.phone}" class="text-blue-500 hover:underline">${pharmacy.phone}</a></p>` : ''}
                ${pharmacy.website ? `<p class="mb-1"><i class="fas fa-globe mr-1"></i> <a href="${pharmacy.website}" target="_blank" class="text-blue-500 hover:underline">Website</a></p>` : ''}
                ${pharmacy.hours ? `<p class="mb-1 text-sm"><i class="fas fa-clock mr-1"></i> ${pharmacy.hours}</p>` : ''}
            </div>
        `;
        
        // Add click listener
        marker.on('click', function() {
            // Create and open info window
            if (infoWindow) {
                infoWindow.close();
            }
            
            infoWindow = new mappls.InfoWindow({
                map: map,
                content: infoContent,
                position: [parseFloat(pharmacy.longitude), parseFloat(pharmacy.latitude)]
            });
        });
        
        // Store marker in array
        pharmacyMarkers.push(marker);
        
        // Create list item
        const listItem = document.createElement('div');
        listItem.className = 'pharmacy-card bg-white hover:bg-gray-50 border-b border-gray-200 p-3 cursor-pointer';
        listItem.innerHTML = `
            <div class="flex justify-between">
                <h3 class="font-semibold">${pharmacy.name}</h3>
                ${pharmacy.distance ? `<span class="text-sm text-blue-600"><i class="fas fa-route mr-1"></i> ${pharmacy.distance.toFixed(2)} km</span>` : ''}
            </div>
            <p class="text-sm text-gray-600 mt-1">${pharmacy.address}</p>
            <div class="flex mt-2 text-sm">
                ${pharmacy.phone ? `<a href="tel:${pharmacy.phone}" class="text-blue-500 mr-3"><i class="fas fa-phone mr-1"></i> Call</a>` : ''}
                ${pharmacy.website ? `<a href="${pharmacy.website}" target="_blank" class="text-blue-500 mr-3"><i class="fas fa-globe mr-1"></i> Website</a>` : ''}
                <button class="text-green-500 directions-btn" data-lat="${pharmacy.latitude}" data-lng="${pharmacy.longitude}"><i class="fas fa-directions mr-1"></i> Directions</button>
            </div>
        `;
        
        // Add click event to show on map
        listItem.addEventListener('click', function(e) {
            // Don't trigger if clicking on a link or button
            if (e.target.tagName.toLowerCase() === 'a' || e.target.tagName.toLowerCase() === 'button' || 
                e.target.parentElement.tagName.toLowerCase() === 'a' || e.target.parentElement.tagName.toLowerCase() === 'button') {
                return;
            }
            showPharmacyOnMap(index);
        });
        
        // Add to list
        pharmacyList.appendChild(listItem);
    });
    
    // Add event listeners to directions buttons
    document.querySelectorAll('.directions-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const lat = this.getAttribute('data-lat');
            const lng = this.getAttribute('data-lng');
            window.open(`https://maps.google.com/maps?daddr=${lat},${lng}&navigate=yes`, '_blank');
        });
    });
}

// Show a specific pharmacy on the map
function showPharmacyOnMap(index) {
    if (pharmacyMarkers[index]) {
        const marker = pharmacyMarkers[index];
        
        // Trigger the click event on the marker
        marker.fire('click');
        
        // Pan the map to the marker
        map.flyTo({
            center: [marker.getPosition()[0], marker.getPosition()[1]],
            zoom: 15,
            speed: 1.5
        });
    }
}

// Clear all pharmacy markers from the map
function clearPharmacyMarkers() {
    pharmacyMarkers.forEach(marker => marker.remove());
    pharmacyMarkers = [];
    
    if (infoWindow) {
        infoWindow.close();
    }
}

// Set up the address search functionality
function setupAddressSearch() {
    const searchInput = document.getElementById('search-location');
    
    // Add event listener for the Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            geocodeAddress(this.value);
        }
    });
}