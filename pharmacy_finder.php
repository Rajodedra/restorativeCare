<?php
// pharmacy_finder.php - Main page for pharmacy finder feature
// Include any authentication or session management needed for your application
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Nearby Pharmacies - RestorativeCare</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS for Pharmacy Finder -->
    <style>
        #pharmacy-map {
            width: 100%;
            height: 400px;
            border-radius: 0.5rem;
        }
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255,255,255,0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .pharmacy-card {
            transition: all 0.2s ease;
        }
        .pharmacy-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Loading Indicator -->
    <div id="loading" class="flex">
        <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-3"></div>
            <p class="text-gray-700">Loading...</p>
        </div>
    </div>

    <!-- Navigation (if needed) -->
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <a href="dashboard.php" class="flex-shrink-0 flex items-center">
                        <i class="fas fa-clinic-medical text-2xl mr-2"></i>
                        <span class="font-bold text-xl">RestorativeCare</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Find Nearby Pharmacies</h1>
            <p class="text-gray-600">Locate pharmacies near you or any address to get your prescriptions filled.</p>
        </div>

        <!-- Search Controls -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label for="search-location" class="block text-sm font-medium text-gray-700 mb-1">Enter Location</label>
                    <div class="flex">
                        <input type="text" id="search-location" class="flex-grow p-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter an address or location">
                        <button id="search-btn" class="bg-blue-500 text-white px-4 rounded-r-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                    </div>
                </div>
                <div>
                    <label for="radius-select" class="block text-sm font-medium text-gray-700 mb-1">Search Radius</label>
                    <div class="flex">
                        <select id="radius-select" class="flex-grow p-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1">1 km</option>
                            <option value="2" selected>2 km</option>
                            <option value="5">5 km</option>
                            <option value="10">10 km</option>
                        </select>
                        <button id="current-location-btn" class="bg-green-500 text-white px-4 py-2 rounded-r-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <i class="fas fa-location-arrow mr-1"></i> Use My Location
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map and Results Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Map Container -->
            <div class="lg:col-span-2">
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold text-gray-800 mb-3">Map</h2>
                    <div id="pharmacy-map" class="border border-gray-200"></div>
                </div>
            </div>

            <!-- Results List -->
            <div class="lg:col-span-1">
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h2 id="results-title" class="text-xl font-semibold text-gray-800 mb-3">Nearby Pharmacies</h2>
                    <div id="pharmacy-list" class="overflow-y-auto max-h-[500px]">
                        <div class="p-4 text-center text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            Enter a location or use your current location to find pharmacies.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-100 mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 text-sm">
            &copy; <?php echo date('Y'); ?> RestorativeCare — Pharmacy Finder
        </div>
    </footer>
    
    <!-- Mappls API with Places library -->
    <script src="https://apis.mappls.com/advancedmaps/v1/68a9fc9f160b4347748742zwr2bce8c/map_sdk_plugins"></script>
    
    <!-- Pharmacy Finder JavaScript -->
    <script src="pharmacy_finder.js"></script>
</body>
</html>