@extends('layouts.admin')

@section('content')
    <div class="px-6 py-8">
        <div class="flex flex-col space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-2xl font-semibold text-gray-800">Location Monitoring Dashboard</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-blue-900 font-medium">Total Users: {{ $stats['total_users'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-blue-900 font-medium">Users with Location: {{ $stats['users_with_location'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-blue-900 font-medium">Tracking Percentage: {{ $stats['tracking_percentage'] }}%</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-blue-900 font-medium">Online Users (last 5 min): {{ $stats['online_users'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-blue-900 font-medium">Recent Activity (last hour): {{ $stats['recent_activity'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-blue-900 font-medium">Location Sources:</p>
                        <ul class="list-inside list-disc pl-4">
                            @foreach ($stats['location_sources'] as $source => $count)
                                <li>{{ ucfirst($source) }}: {{ $count }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-300" id="refresh-locations">
                    Refresh Locations
                </button>
                <div id="location-map" class="mt-6 rounded-lg shadow-lg border border-gray-200" style="height: 500px;"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha512-pYICAmHnOpK7+09SWtk6nc9wvkqxQF23TQrU5N9TuNJatchedD7Uma4E84rqNt88zLd4CrLN57w5ZdtamDtMh1Ww==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" integrity="sha512-yqDOxofYv9CVxsv1govXAynCB1HaqywqMtYFIjSC2XZJsw9gq58frbVnsdx83RVq0N107hgQODkTABOsYqT5VQ==" crossorigin=""></script>
        function initMap() {
            const map = L.map('location-map').setView([-34.397, 150.644], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            fetchRealTimeLocations(map);

            const refreshButton = document.getElementById('refresh-locations');
            refreshButton.addEventListener('click', function() {
                refreshButton.disabled = true;
                refreshButton.textContent = 'Refreshing...';
                fetchRealTimeLocations(map);
                setTimeout(function() {
                    refreshButton.disabled = false;
                    refreshButton.textContent = 'Refresh Locations';
                }, 2000);
            });
        }

        function fetchRealTimeLocations(map) {
            fetch('/admin/location-monitor/real-time')
                .then(response => response.json())
                .then(locations => {
                    locations.forEach(location => {
                        const position = [parseFloat(location.latitude), parseFloat(location.longitude)];
                        const marker = L.marker(position).addTo(map);

                        marker.bindPopup(`
                            <div>
                                <strong>${location.user_name}</strong><br>
                                ${location.user_email}<br>
                                Coords: ${location.formatted_coordinates}<br>
                                Accuracy: ${location.accuracy_label} (${location.accuracy}m)<br>
                                Last updated: ${location.time_since}
                            </div>
                        `);
                    });
                });
        }

        window.onload = initMap;
    </script>
@endsection

