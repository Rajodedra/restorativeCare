<?php
// pharmacy_api.php
// This file is not needed with Geoapify approach, but kept for compatibility
// Just returns an empty JSON to prevent errors if called.

header('Content-Type: application/json');
echo json_encode(["pharmacies" => []]);
?>
