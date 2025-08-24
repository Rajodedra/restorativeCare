// pharmacy_finder.js
// Uses Geoapify API to find pharmacies by city

const GEOAPIFY_API_KEY = "0cd77bcc474a4e00804e0f5b8c2eeeef"; // your key

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('city-search-btn').addEventListener('click', function() {
        const city = document.getElementById('city-input').value.trim();
        if (!city) {
            alert("Please enter a city.");
            return;
        }
        searchPharmaciesByCity(city);
    });
});

function searchPharmaciesByCity(city) {
    const resultsDiv = document.getElementById('pharmacy-results');
    resultsDiv.innerHTML = '<p>Searching pharmacies...</p>';

    // Step 1: Geocode city to lat/lon
    fetch(`https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(city)}&filter=countrycode:in&apiKey=${GEOAPIFY_API_KEY}`)
        .then(res => res.json())
        .then(geo => {
            if (!geo.features || geo.features.length === 0) {
                resultsDiv.innerHTML = `<p style="color:red;">Could not locate this city.</p>`;
                return;
            }

            const lat = geo.features[0].properties.lat;
            const lon = geo.features[0].properties.lon;

            // Step 2: Search nearby pharmacies
            return fetch(`https://api.geoapify.com/v2/places?categories=healthcare.pharmacy&filter=circle:${lon},${lat},5000&limit=20&apiKey=${GEOAPIFY_API_KEY}`)
                .then(res => res.json())
                .then(places => {
                    if (!places.features || places.features.length === 0) {
                        resultsDiv.innerHTML = `<p>No pharmacies found in ${city}.</p>`;
                        return;
                    }

                    let html = `<h3>Pharmacies in ${city}</h3>`;
                    places.features.forEach(ph => {
                        const props = ph.properties;
                        html += `
                            <div class="pharmacy" style="border:1px solid #ddd; margin-bottom:12px; padding:12px; border-radius:6px; background:#fafafa;">
                                <h4 style="margin:0; font-size:16px; font-weight:bold;">${props.name || "Unnamed Pharmacy"}</h4>
                                <p style="margin:4px 0; color:#555;">${props.formatted || ""}</p>
                                <p style="margin:2px 0; font-size:14px; color:#777;">
                                    ${props.city ? props.city + "," : ""} ${props.state || ""} ${props.postcode || ""}
                                </p>
                                <button class="details-btn" 
                                    style="margin-top:6px; padding:6px 10px; border:none; background:#007bff; color:white; border-radius:4px; cursor:pointer;" 
                                    data-id="${props.place_id}">
                                    More Details
                                </button>
                            </div>`;
                    });

                    resultsDiv.innerHTML = html;

                    // Step 3: Add listeners for "More Details"
                    document.querySelectorAll('.details-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            fetch(`https://api.geoapify.com/v2/place-details?id=${id}&apiKey=${GEOAPIFY_API_KEY}`)
                                .then(r => r.json())
                                .then(detail => {
                                    if (detail.features && detail.features.length > 0) {
                                        const d = detail.features[0].properties;
                                        alert(
                                            `Name: ${d.name || "N/A"}\n` +
                                            `Address: ${d.formatted || "N/A"}\n` +
                                            `Phone: ${d.contact ? d.contact.phone : "N/A"}\n` +
                                            `Website: ${(d.datasource && d.datasource.raw && d.datasource.raw.website) ? d.datasource.raw.website : "N/A"}`
                                        );
                                    } else {
                                        alert("No extra details available.");
                                    }
                                });
                        });
                    });
                });
        })
        .catch(err => {
            console.error("Error:", err);
            resultsDiv.innerHTML = '<p style="color:red;">Error fetching pharmacy data.</p>';
        });
}
