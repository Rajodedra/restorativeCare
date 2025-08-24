<?php
// pharmacy_finder.php
// Simple Pharmacy Finder using City + Geoapify API (no database required)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pharmacy Finder</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="pharmacy_finder.js" defer></script>
  <style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 0; }
    .container { max-width: 800px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .search-box { margin-bottom: 20px; }
    input[type=text] { padding: 10px; width: 250px; border: 1px solid #ccc; border-radius: 4px; }
    button { padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; }
    button.search-btn { background: #007bff; color: white; }
    #pharmacy-results .pharmacy { border-bottom: 1px solid #eee; padding: 10px; }
    #pharmacy-results h4 { margin: 0 0 4px 0; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Find Pharmacies by City</h2>
    <div class="search-box">
      <input type="text" id="city-input" placeholder="Enter City">
      <button id="city-search-btn" class="search-btn"><i class="fas fa-search"></i> Search</button>
    </div>
    <div id="pharmacy-results"></div>
  </div>
</body>
</html>
