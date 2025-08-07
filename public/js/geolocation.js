/**
 * Geolocation Service for ParkSmart
 * Handles browser-based geolocation requests to enhance activity logging
 */

class GeolocationService {
    constructor() {
        this.isSupported = 'geolocation' in navigator;
        this.watchId = null;
        this.lastPosition = null;
        this.accuracyThreshold = 100; // meters
        this.timeoutDuration = 10000; // 10 seconds
        this.maxAge = 60000; // 1 minute
        
        // Real-time tracking properties
        this.isRealTimeTracking = false;
        this.trackingInterval = null;
        this.updateFrequency = 30000; // 30 seconds
        this.authToken = null;
        this.consecutiveErrors = 0;
        this.maxConsecutiveErrors = 3;
    }

    /**
     * Check if geolocation is supported
     */
    isGeolocationSupported() {
        return this.isSupported;
    }

    /**
     * Request permission and get current position
     */
    async getCurrentPosition(options = {}) {
        return new Promise((resolve, reject) => {
            if (!this.isSupported) {
                reject(new Error('Geolocation is not supported by this browser'));
                return;
            }

            // Check if running on localhost or secure context
            const isSecureContext = this.isSecureContext();
            if (!isSecureContext && !this.isLocalhost()) {
                reject(new Error('Geolocation requires HTTPS in production'));
                return;
            }

            const defaultOptions = {
                enableHighAccuracy: true,
                timeout: this.timeoutDuration,
                maximumAge: this.maxAge
            };

            const finalOptions = { ...defaultOptions, ...options };

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.lastPosition = position;
                    resolve(this.formatPosition(position));
                },
                (error) => {
                    reject(this.handleGeolocationError(error));
                },
                finalOptions
            );
        });
    }

    /**
     * Request location permission with user-friendly prompts
     */
    async requestLocationPermission() {
        if (!this.isSupported) {
            return { granted: false, error: 'Geolocation not supported' };
        }

        try {
            // Check current permission state first
            if ('permissions' in navigator) {
                const permission = await navigator.permissions.query({ name: 'geolocation' });
                
                if (permission.state === 'granted') {
                    // Test if we can actually get position
                    try {
                        const position = await this.getCurrentPosition({ timeout: 5000 });
                        return { granted: true, position };
                    } catch (error) {
                        // Permission granted but location unavailable
                        return { granted: true, error: 'Location temporarily unavailable' };
                    }
                } else if (permission.state === 'denied') {
                    // Show instructions for enabling location
                    this.showLocationDeniedInstructions();
                    return { granted: false, error: 'Location permission denied' };
                }
            }

            // Show custom permission dialog first
            const userConsent = await this.showPermissionDialog();
            if (!userConsent) {
                return { granted: false, error: 'User declined location access' };
            }

            // Try to get position (this will trigger browser permission request)
            const position = await this.getCurrentPosition({ timeout: 10000 });
            return { granted: true, position };
        } catch (error) {
            if (error.message.includes('denied')) {
                this.showLocationDeniedInstructions();
            }
            return { granted: false, error: error.message };
        }
    }

    /**
     * Watch position changes
     */
    startWatching(callback, options = {}) {
        if (!this.isSupported) {
            callback(null, new Error('Geolocation not supported'));
            return null;
        }

        const defaultOptions = {
            enableHighAccuracy: true,
            timeout: this.timeoutDuration,
            maximumAge: this.maxAge
        };

        const finalOptions = { ...defaultOptions, ...options };

        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                this.lastPosition = position;
                callback(this.formatPosition(position), null);
            },
            (error) => {
                callback(null, this.handleGeolocationError(error));
            },
            finalOptions
        );

        return this.watchId;
    }

    /**
     * Stop watching position changes
     */
    stopWatching() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
    }

    /**
     * Format position object for API consumption
     */
    formatPosition(position) {
        return {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            altitude: position.coords.altitude,
            altitudeAccuracy: position.coords.altitudeAccuracy,
            heading: position.coords.heading,
            speed: position.coords.speed,
            timestamp: position.timestamp
        };
    }

    /**
     * Handle geolocation errors with user-friendly messages
     */
    handleGeolocationError(error) {
        const errorMessages = {
            1: 'Location access denied by user',
            2: 'Location information unavailable',
            3: 'Location request timed out'
        };

        return new Error(errorMessages[error.code] || 'Unknown geolocation error');
    }

    /**
     * Get location with fallback to IP-based location
     */
    async getLocationWithFallback() {
        try {
            // Try to get precise GPS location first
            const position = await this.getCurrentPosition();
            return {
                type: 'gps',
                ...position,
                isAccurate: position.accuracy <= this.accuracyThreshold
            };
        } catch (error) {
            console.warn('GPS location failed, using fallback:', error.message);
            
            // Fallback to IP-based location (server-side)
            return {
                type: 'ip',
                error: error.message,
                isAccurate: false
            };
        }
    }

    /**
     * Send location data to server
     */
    async sendLocationToServer(locationData, endpoint = '/api/location') {
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(locationData)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Failed to send location to server:', error);
            throw error;
        }
    }

    /**
     * Show instructions for enabling location when denied
     */
    showLocationDeniedInstructions() {
        const instructions = document.createElement('div');
        instructions.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 sm:p-6 md:p-8';
        instructions.style.zIndex = '9999';
        instructions.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm sm:max-w-md md:max-w-lg p-4 sm:p-6 md:p-8 transform transition-all duration-300 ease-out scale-95 opacity-0" id="instructions-content">
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-1">Location Access Blocked</h3>
                        <p class="text-sm text-gray-600">Enable location to improve your experience</p>
                    </div>
                </div>
                
                <div class="mb-6 text-sm sm:text-base text-gray-700 leading-relaxed">
                    <p class="mb-3">Location access has been blocked. To enable it:</p>
                    <ol class="list-decimal list-inside space-y-2 text-sm">
                        <li>Click the location icon in your browser's address bar</li>
                        <li>Select "Allow" for location permissions</li>
                        <li>Refresh the page</li>
                    </ol>
                    <p class="text-xs sm:text-sm text-gray-600 mt-3">Or go to your browser settings and enable location access for this site.</p>
                </div>
                
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                    <button id="refresh-page" class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium text-sm sm:text-base">
                        Refresh Page
                    </button>
                    <button id="close-instructions" class="flex-1 bg-gray-200 text-gray-800 py-3 px-4 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium text-sm sm:text-base">
                        Continue Without
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(instructions);

        // Animate dialog appearance
        requestAnimationFrame(() => {
            const instructionsContent = instructions.querySelector('#instructions-content');
            instructionsContent.style.transform = 'scale(1)';
            instructionsContent.style.opacity = '1';
        });

        const refreshBtn = instructions.querySelector('#refresh-page');
        const closeBtn = instructions.querySelector('#close-instructions');

        const closeInstructions = () => {
            const instructionsContent = instructions.querySelector('#instructions-content');
            instructionsContent.style.transform = 'scale(0.95)';
            instructionsContent.style.opacity = '0';
            
            setTimeout(() => {
                if (instructions.parentNode) {
                    document.body.removeChild(instructions);
                }
            }, 200);
        };

        refreshBtn.addEventListener('click', () => {
            window.location.reload();
        });
        
        closeBtn.addEventListener('click', closeInstructions);
        
        // Close on background click
        instructions.addEventListener('click', (e) => {
            if (e.target === instructions) {
                closeInstructions();
            }
        });
    }

    /**
     * Show responsive location permission dialog compatible with auth layouts
     */
    showPermissionDialog() {
        const dialog = document.createElement('div');
        dialog.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 sm:p-6 md:p-8';
        dialog.style.zIndex = '9999';
        dialog.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm sm:max-w-md md:max-w-lg p-4 sm:p-6 md:p-8 transform transition-all duration-300 ease-out scale-95 opacity-0" id="dialog-content">
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-4 mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-1">Enable Location Access</h3>
                        <p class="text-sm text-gray-600">For better activity tracking and experience</p>
                    </div>
                </div>
                
                <div class="mb-6 text-sm sm:text-base text-gray-700 leading-relaxed">
                    <p class="mb-3">We'd like to access your location to provide more accurate activity logs and enhance your experience.</p>
                    <p class="text-xs sm:text-sm text-gray-600">Your location data is only used for logging purposes and is not shared with third parties.</p>
                </div>
                
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                    <button id="allow-location" class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium text-sm sm:text-base">
                        Allow Location
                    </button>
                    <button id="deny-location" class="flex-1 bg-gray-200 text-gray-800 py-3 px-4 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium text-sm sm:text-base">
                        Not Now
                    </button>
                </div>
                
                <p class="text-xs text-gray-500 mt-4 text-center leading-relaxed">
                    You can change this setting anytime in your browser preferences or by clearing your browser data.
                </p>
            </div>
        `;

        document.body.appendChild(dialog);

        // Animate dialog appearance
        requestAnimationFrame(() => {
            const dialogContent = dialog.querySelector('#dialog-content');
            dialogContent.style.transform = 'scale(1)';
            dialogContent.style.opacity = '1';
        });

        return new Promise((resolve) => {
            const allowBtn = dialog.querySelector('#allow-location');
            const denyBtn = dialog.querySelector('#deny-location');

            const closeDialog = (result) => {
                const dialogContent = dialog.querySelector('#dialog-content');
                dialogContent.style.transform = 'scale(0.95)';
                dialogContent.style.opacity = '0';
                
                setTimeout(() => {
                    if (dialog.parentNode) {
                        document.body.removeChild(dialog);
                    }
                    resolve(result);
                }, 200);
            };

            allowBtn.addEventListener('click', () => closeDialog(true));
            denyBtn.addEventListener('click', () => closeDialog(false));
            
            // Close on escape key
            const handleKeyPress = (e) => {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', handleKeyPress);
                    closeDialog(false);
                }
            };
            document.addEventListener('keydown', handleKeyPress);
            
            // Close on background click
            dialog.addEventListener('click', (e) => {
                if (e.target === dialog) {
                    closeDialog(false);
                }
            });
        });
    }

    /**
     * Initialize geolocation for login/activity tracking
     */
    async initializeForAuth() {
        try {
            // Check if we should request permission
            const shouldRequest = !localStorage.getItem('geolocation_permission_asked');
            
            if (shouldRequest) {
                const userConsent = await this.showPermissionDialog();
                localStorage.setItem('geolocation_permission_asked', 'true');
                
                if (!userConsent) {
                    localStorage.setItem('geolocation_denied', 'true');
                    return null;
                }
            }

            // If user previously denied, don't ask again
            if (localStorage.getItem('geolocation_denied') === 'true') {
                return null;
            }

            // Get location
            const location = await this.getLocationWithFallback();
            
            // Store in session for this login session
            sessionStorage.setItem('current_location', JSON.stringify(location));
            
            return location;
        } catch (error) {
            console.error('Geolocation initialization failed:', error);
            return null;
        }
    }

    /**
     * Get stored location from session
     */
    getStoredLocation() {
        const stored = sessionStorage.getItem('current_location');
        return stored ? JSON.parse(stored) : null;
    }

    /**
     * Clear stored location
     */
    clearStoredLocation() {
        sessionStorage.removeItem('current_location');
    }

    /**
     * Check if current context is secure (HTTPS)
     */
    isSecureContext() {
        return window.isSecureContext || window.location.protocol === 'https:';
    }

    /**
     * Check if running on localhost
     */
    isLocalhost() {
        const hostname = window.location.hostname;
        return hostname === 'localhost' || 
               hostname === '127.0.0.1' || 
               hostname === '::1' ||
               hostname.startsWith('192.168.') ||
               hostname.startsWith('10.') ||
               hostname.startsWith('172.16.') ||
               hostname.endsWith('.local');
    }

    /**
     * Show responsive environment warning for insecure contexts
     */
    showInsecureContextWarning() {
        if (!this.isSecureContext() && !this.isLocalhost()) {
            console.warn('Geolocation requires HTTPS in production environments');
            
            // Show user-friendly responsive message
            const warning = document.createElement('div');
            warning.className = 'fixed top-4 left-4 right-4 sm:left-auto sm:right-4 sm:max-w-sm bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg shadow-lg';
            warning.style.zIndex = '9998';
            warning.innerHTML = `
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm text-yellow-800">Location Services Unavailable</p>
                        <p class="text-xs mt-1 text-yellow-700">HTTPS is required for location access in production environments.</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-yellow-600 hover:text-yellow-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 rounded p-1 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(warning);
            
            // Auto-remove after 10 seconds
            setTimeout(() => {
                if (warning.parentNode) {
                    warning.parentNode.removeChild(warning);
                }
            }, 10000);
        }
    }

    /**
     * Start real-time location tracking
     */
    async startRealTimeTracking(options = {}) {
        if (this.isRealTimeTracking) {
            console.log('Real-time tracking already active');
            return;
        }

        const trackingOptions = {
            frequency: options.frequency || this.updateFrequency,
            enableHighAccuracy: options.enableHighAccuracy !== false,
            sendToServer: options.sendToServer !== false
        };

        try {
            // First, ensure we have permission
            const permission = await this.requestLocationPermission();
            if (!permission.granted) {
                throw new Error('Location permission required for real-time tracking');
            }

            this.isRealTimeTracking = true;
            this.consecutiveErrors = 0;
            
            console.log('Starting real-time location tracking...');
            
            // Start continuous tracking
            this.trackingInterval = setInterval(async () => {
                try {
                    const location = await this.getCurrentPosition({
                        enableHighAccuracy: trackingOptions.enableHighAccuracy,
                        timeout: this.timeoutDuration,
                        maximumAge: 0 // Always get fresh location
                    });

                    // Reset error counter on success
                    this.consecutiveErrors = 0;
                    
                    // Send to server if enabled
                    if (trackingOptions.sendToServer) {
                        await this.updateLocationOnServer(location);
                    }

                    // Dispatch custom event for other parts of app
                    window.dispatchEvent(new CustomEvent('locationUpdate', {
                        detail: { location, timestamp: Date.now() }
                    }));

                    console.log('Location updated:', location.latitude, location.longitude);
                } catch (error) {
                    this.consecutiveErrors++;
                    console.error('Real-time tracking error:', error.message);
                    
                    // Stop tracking if too many consecutive errors
                    if (this.consecutiveErrors >= this.maxConsecutiveErrors) {
                        console.warn('Too many consecutive errors, stopping real-time tracking');
                        this.stopRealTimeTracking();
                    }
                }
            }, trackingOptions.frequency);

            // Also use watchPosition for more responsive updates
            this.startWatching((location, error) => {
                if (error) {
                    console.error('Watch position error:', error.message);
                    return;
                }

                // Only update if we have a significant position change
                if (this.hasSignificantLocationChange(location)) {
                    if (trackingOptions.sendToServer) {
                        this.updateLocationOnServer(location).catch(console.error);
                    }

                    window.dispatchEvent(new CustomEvent('locationUpdate', {
                        detail: { location, timestamp: Date.now(), source: 'watch' }
                    }));
                }
            }, {
                enableHighAccuracy: trackingOptions.enableHighAccuracy,
                timeout: this.timeoutDuration,
                maximumAge: 10000 // 10 seconds for watch position
            });

            return { success: true, message: 'Real-time tracking started' };
        } catch (error) {
            this.isRealTimeTracking = false;
            console.error('Failed to start real-time tracking:', error);
            throw error;
        }
    }

    /**
     * Stop real-time location tracking
     */
    stopRealTimeTracking() {
        if (!this.isRealTimeTracking) {
            return;
        }

        this.isRealTimeTracking = false;
        
        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
            this.trackingInterval = null;
        }

        this.stopWatching();
        
        console.log('Real-time location tracking stopped');
        
        // Dispatch stop event
        window.dispatchEvent(new CustomEvent('locationTrackingStopped'));
    }

    /**
     * Update location on server via API
     */
    async updateLocationOnServer(location) {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const authToken = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
            
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            
            if (token) {
                headers['X-CSRF-TOKEN'] = token;
            }
            
            if (authToken) {
                headers['Authorization'] = `Bearer ${authToken}`;
            }

            const locationData = {
                latitude: location.latitude,
                longitude: location.longitude,
                accuracy: location.accuracy,
                altitude: location.altitude,
                speed: location.speed,
                heading: location.heading,
                location_source: 'gps',
                session_id: this.getSessionId()
            };

            const response = await fetch('/api/location/update', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(locationData)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Failed to update location on server:', error);
            throw error;
        }
    }

    /**
     * Check if location change is significant enough to update
     */
    hasSignificantLocationChange(newLocation) {
        if (!this.lastPosition) {
            return true;
        }

        const oldCoords = this.lastPosition.coords;
        const newCoords = newLocation;
        
        // Calculate distance using Haversine formula
        const distance = this.calculateDistance(
            oldCoords.latitude, oldCoords.longitude,
            newCoords.latitude, newCoords.longitude
        );

        // Update if moved more than 10 meters or accuracy improved significantly
        const significantMove = distance > 10;
        const accuracyImproved = newCoords.accuracy < oldCoords.accuracy / 2;
        
        return significantMove || accuracyImproved;
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth's radius in meters
        const dLat = this.toRadians(lat2 - lat1);
        const dLon = this.toRadians(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    /**
     * Convert degrees to radians
     */
    toRadians(degrees) {
        return degrees * Math.PI / 180;
    }

    /**
     * Get or generate session ID
     */
    getSessionId() {
        let sessionId = sessionStorage.getItem('location_session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('location_session_id', sessionId);
        }
        return sessionId;
    }

    /**
     * Get tracking status
     */
    getTrackingStatus() {
        return {
            isTracking: this.isRealTimeTracking,
            lastPosition: this.lastPosition,
            consecutiveErrors: this.consecutiveErrors,
            updateFrequency: this.updateFrequency
        };
    }
}

// Create global instance
window.geolocationService = new GeolocationService();

// Auto-initialize on DOM load for auth pages
document.addEventListener('DOMContentLoaded', function() {
    const service = window.geolocationService;
    
    // Show warnings for insecure contexts
    service.showInsecureContextWarning();
    
    // Initialize on login/register pages or admin/attendant dashboards
    const isAuthPage = window.location.pathname.includes('/login') || window.location.pathname.includes('/register');
    const isDashboard = window.location.pathname.includes('/admin') || window.location.pathname.includes('/attendant');
    
    if (isAuthPage) {
        service.initializeForAuth().then(location => {
            if (location) {
                console.log('Location initialized for auth:', location.type);
                
                // Add location data to login form if it exists
                const loginForm = document.querySelector('form[action*="login"]');
                if (loginForm && location.type === 'gps') {
                    const latInput = document.createElement('input');
                    latInput.type = 'hidden';
                    latInput.name = 'latitude';
                    latInput.value = location.latitude;
                    
                    const lngInput = document.createElement('input');
                    lngInput.type = 'hidden';
                    lngInput.name = 'longitude';
                    lngInput.value = location.longitude;
                    
                    const accuracyInput = document.createElement('input');
                    accuracyInput.type = 'hidden';
                    accuracyInput.name = 'location_accuracy';
                    accuracyInput.value = location.accuracy;
                    
                    loginForm.appendChild(latInput);
                    loginForm.appendChild(lngInput);
                    loginForm.appendChild(accuracyInput);
                }
            }
        }).catch(error => {
            console.error('Failed to initialize geolocation:', error);
        });
    } else if (isDashboard) {
        // For dashboard pages, start real-time tracking if user is authenticated
        service.getLocationWithFallback().then(location => {
            if (location && location.type === 'gps') {
                console.log('Dashboard location available:', location.type);
                // Store for potential use in activity logging
                sessionStorage.setItem('dashboard_location', JSON.stringify(location));
                
                // Start real-time tracking for authenticated users
                service.startRealTimeTracking({
                    frequency: 30000, // Update every 30 seconds
                    enableHighAccuracy: true,
                    sendToServer: true
                }).then(result => {
                    console.log('Real-time tracking started:', result.message);
                }).catch(error => {
                    console.log('Could not start real-time tracking:', error.message);
                });
            }
        }).catch(error => {
            console.log('Dashboard location not available:', error.message);
        });
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GeolocationService;
}
