<?php
// pharmacy_api.php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restorativecare";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// If no data was posted, check GET parameters
if (empty($data)) {
    $data = [
        'latitude' => isset($_GET['latitude']) ? floatval($_GET['latitude']) : null,
        'longitude' => isset($_GET['longitude']) ? floatval($_GET['longitude']) : null,
        'radius' => isset($_GET['radius']) ? intval($_GET['radius']) : 5
    ];
}

// Validate input
if (!isset($data['latitude']) || !isset($data['longitude']) || !isset($data['radius'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$userLat = $data['latitude'];
$userLng = $data['longitude'];
$radius = $data['radius']; // in kilometers

// Prepare SQL query to find nearby pharmacies from the database
// Using Haversine formula to calculate distance
$sql = "SELECT 
            id, name, address, phone, website, latitude, longitude, hours,
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
        FROM pharmacies
        HAVING distance <= ?
        ORDER BY distance
        LIMIT 50";

$stmt = $conn->prepare($sql);
$stmt->bind_param("dddd", $userLat, $userLng, $userLat, $radius);
$stmt->execute();
$result = $stmt->get_result();

$pharmacies = [];

// Get results from database
while ($row = $result->fetch_assoc()) {
    $pharmacies[] = $row;
}

// If no results from database, try geocoding API to find pharmacies
if (count($pharmacies) === 0 && $radius <= 10) {
    $geocodingApiKey = "68a9fc9f160b4347748742zwr2bce8c"; // API key for geocoding
    
    // Call external geocoding API to find pharmacies
    $url = "https://apis.mappls.com/advancedmaps/v1/{$geocodingApiKey}/nearby/json?keywords=pharmacy&refLocation={$userLat},{$userLng}&radius={$radius}000";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    
    if ($response) {
        $results = json_decode($response, true);
        
        if (isset($results['results']) && is_array($results['results'])) {
            foreach ($results['results'] as $place) {
                $pharmacy = [
                    'id' => 'api_' . md5($place['placeId'] ?? uniqid()),
                    'name' => $place['name'] ?? 'Unknown Pharmacy',
                    'address' => $place['formattedAddress'] ?? '',
                    'phone' => $place['phone'] ?? '',
                    'website' => $place['website'] ?? '',
                    'latitude' => $place['latitude'] ?? $place['lat'] ?? 0,
                    'longitude' => $place['longitude'] ?? $place['lng'] ?? 0,
                    'distance' => $place['distance'] ? $place['distance']/1000 : 0, // Convert to km
                    'hours' => isset($place['openingHours']) ? implode(', ', $place['openingHours']) : '',
                    'api_source' => true
                ];
                
                $pharmacies[] = $pharmacy;
                
                // Optionally save to database for future queries
                // insertPharmacyToDB($conn, $pharmacy);
            }
        }
    }
    curl_close($ch);
    
    // If still no results, add some dummy pharmacies for testing purposes
    if (count($pharmacies) === 0) {
        // Generate random points around the user location
        for ($i = 0; $i < 5; $i++) {
            // Random distance within the radius (in km)
            $distance = (mt_rand(1, $radius * 10) / 10);
            
            // Random angle
            $angle = mt_rand(0, 360);
            
            // Convert distance and angle to lat/lng offset
            // Approximate 1 degree of latitude = 111 km
            // Approximate 1 degree of longitude = 111 km * cos(latitude)
            $latOffset = $distance * sin(deg2rad($angle)) / 111;
            $lngOffset = $distance * cos(deg2rad($angle)) / (111 * cos(deg2rad($userLat)));
            
            $lat = $userLat + $latOffset;
            $lng = $userLng + $lngOffset;
            
            $pharmacies[] = [
                'id' => 'dummy_' . ($i + 1),
                'name' => 'Pharmacy ' . ($i + 1),
                'address' => 'Near ' . round($distance, 1) . ' km from your location',
                'phone' => '+91 ' . mt_rand(9000000000, 9999999999),
                'website' => 'https://example.com/pharmacy' . ($i + 1),
                'latitude' => $lat,
                'longitude' => $lng,
                'distance' => $distance,
                'hours' => 'Mon-Sat: 9AM-9PM, Sun: 10AM-6PM',
                'api_source' => false
            ];
        }
    }
}

// Close database connection
$stmt->close();
$conn->close();

// Function to insert a pharmacy into the database
function insertPharmacyToDB($conn, $pharmacy) {
    // Only insert if we have valid coordinates
    if (!empty($pharmacy['latitude']) && !empty($pharmacy['longitude'])) {
        $sql = "INSERT IGNORE INTO pharmacies 
                (name, address, phone, website, latitude, longitude, hours) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssdds', 
            $pharmacy['name'], 
            $pharmacy['address'], 
            $pharmacy['phone'], 
            $pharmacy['website'], 
            $pharmacy['latitude'], 
            $pharmacy['longitude'], 
            $pharmacy['hours']
        );
        $stmt->execute();
        $stmt->close();
    }
}

// Return results
echo json_encode(['pharmacies' => $pharmacies]);
?>