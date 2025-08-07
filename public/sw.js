/**
 * Service Worker for ParkSmart
 * Handles background geolocation, caching, and offline functionality
 */

const CACHE_NAME = 'parksmart-v1';
const CACHE_URLS = [
    '/',
    '/login',
    '/js/geolocation.js',
    '/css/app.css',
    // Add other static assets as needed
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('Service Worker: Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Service Worker: Caching files');
                return cache.addAll(CACHE_URLS);
            })
            .then(() => {
                console.log('Service Worker: Installation complete');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('Service Worker: Installation failed', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activating...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('Service Worker: Deleting old cache', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activation complete');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve cached content when offline
self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }
    
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Return cached version or fetch from network
                return response || fetch(event.request)
                    .then((fetchResponse) => {
                        // Don't cache non-successful responses
                        if (!fetchResponse.ok) {
                            return fetchResponse;
                        }
                        
                        // Clone the response before caching
                        const responseToCache = fetchResponse.clone();
                        
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });
                        
                        return fetchResponse;
                    })
                    .catch(() => {
                        // Return a custom offline page if available
                        if (event.request.destination === 'document') {
                            return caches.match('/offline.html');
                        }
                    });
            })
    );
});

// Background sync for location data
self.addEventListener('sync', (event) => {
    console.log('Service Worker: Sync event', event.tag);
    
    if (event.tag === 'background-location-sync') {
        event.waitUntil(syncLocationData());
    }
});

// Background fetch for location updates
self.addEventListener('backgroundfetch', (event) => {
    console.log('Service Worker: Background fetch', event.tag);
    
    if (event.tag === 'location-update') {
        event.waitUntil(handleBackgroundLocationUpdate(event));
    }
});

// Push notifications for location-based alerts
self.addEventListener('push', (event) => {
    console.log('Service Worker: Push received', event);
    
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: '/icon-192x192.png',
            badge: '/icon-72x72.png',
            tag: data.tag || 'location-alert',
            data: data.data || {},
            actions: data.actions || [],
            requireInteraction: data.requireInteraction || false,
        };
        
        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    console.log('Service Worker: Notification clicked', event);
    
    event.notification.close();
    
    // Handle different notification actions
    if (event.action === 'view-location') {
        event.waitUntil(
            clients.openWindow('/admin/activity-logs')
        );
    } else if (event.action === 'update-location') {
        event.waitUntil(updateLocationPermission());
    } else {
        // Default action - open the app
        event.waitUntil(
            clients.matchAll().then((clients) => {
                if (clients.length > 0) {
                    return clients[0].focus();
                } else {
                    return self.clients.openWindow('/');
                }
            })
        );
    }
});

// Message handling for communication with main thread
self.addEventListener('message', (event) => {
    console.log('Service Worker: Message received', event.data);
    
    const { type, data } = event.data;
    
    switch (type) {
        case 'GET_LOCATION':
            handleGetLocation(event);
            break;
        case 'STORE_LOCATION':
            handleStoreLocation(data);
            break;
        case 'CLEAR_CACHE':
            handleClearCache(event);
            break;
        default:
            console.log('Service Worker: Unknown message type', type);
    }
});

/**
 * Handle location request from main thread
 */
function handleGetLocation(event) {
    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                event.ports[0].postMessage({
                    success: true,
                    location: {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: position.timestamp
                    }
                });
            },
            (error) => {
                event.ports[0].postMessage({
                    success: false,
                    error: error.message
                });
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    } else {
        event.ports[0].postMessage({
            success: false,
            error: 'Geolocation not supported'
        });
    }
}

/**
 * Store location data in IndexedDB for offline use
 */
function handleStoreLocation(locationData) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('ParkSmartDB', 1);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('locations')) {
                const store = db.createObjectStore('locations', { 
                    keyPath: 'id', 
                    autoIncrement: true 
                });
                store.createIndex('timestamp', 'timestamp', { unique: false });
            }
        };
        
        request.onsuccess = (event) => {
            const db = event.target.result;
            const transaction = db.transaction(['locations'], 'readwrite');
            const store = transaction.objectStore('locations');
            
            const locationRecord = {
                ...locationData,
                timestamp: Date.now(),
                synced: false
            };
            
            store.add(locationRecord);
            
            transaction.oncomplete = () => {
                console.log('Service Worker: Location stored successfully');
                resolve();
            };
            
            transaction.onerror = () => {
                console.error('Service Worker: Failed to store location');
                reject(transaction.error);
            };
        };
        
        request.onerror = () => {
            console.error('Service Worker: Failed to open database');
            reject(request.error);
        };
    });
}

/**
 * Sync location data when back online
 */
function syncLocationData() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('ParkSmartDB', 1);
        
        request.onsuccess = (event) => {
            const db = event.target.result;
            const transaction = db.transaction(['locations'], 'readonly');
            const store = transaction.objectStore('locations');
            
            const unsyncedRequest = store.getAll();
            
            unsyncedRequest.onsuccess = () => {
                const unsyncedLocations = unsyncedRequest.result.filter(
                    location => !location.synced
                );
                
                if (unsyncedLocations.length === 0) {
                    resolve();
                    return;
                }
                
                // Send unsynced locations to server
                Promise.all(
                    unsyncedLocations.map(location => 
                        fetch('/api/location-sync', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(location)
                        })
                    )
                )
                .then(() => {
                    // Mark locations as synced
                    const updateTransaction = db.transaction(['locations'], 'readwrite');
                    const updateStore = updateTransaction.objectStore('locations');
                    
                    unsyncedLocations.forEach(location => {
                        location.synced = true;
                        updateStore.put(location);
                    });
                    
                    updateTransaction.oncomplete = () => {
                        console.log('Service Worker: Location data synced successfully');
                        resolve();
                    };
                })
                .catch((error) => {
                    console.error('Service Worker: Failed to sync location data', error);
                    reject(error);
                });
            };
        };
        
        request.onerror = () => {
            console.error('Service Worker: Failed to open database for sync');
            reject(request.error);
        };
    });
}

/**
 * Handle background location updates
 */
function handleBackgroundLocationUpdate(event) {
    return new Promise((resolve) => {
        // Implement background location update logic here
        console.log('Service Worker: Handling background location update');
        resolve();
    });
}

/**
 * Update location permission status
 */
function updateLocationPermission() {
    return new Promise((resolve) => {
        if ('permissions' in navigator) {
            navigator.permissions.query({ name: 'geolocation' })
                .then((permission) => {
                    console.log('Service Worker: Location permission status:', permission.state);
                    
                    // Send message to all clients about permission status
                    return self.clients.matchAll();
                })
                .then((clients) => {
                    clients.forEach(client => {
                        client.postMessage({
                            type: 'LOCATION_PERMISSION_UPDATE',
                            permission: permission.state
                        });
                    });
                    resolve();
                });
        } else {
            resolve();
        }
    });
}

/**
 * Clear all caches
 */
function handleClearCache(event) {
    caches.keys()
        .then((cacheNames) => {
            return Promise.all(
                cacheNames.map(cacheName => caches.delete(cacheName))
            );
        })
        .then(() => {
            event.ports[0].postMessage({ success: true });
        })
        .catch((error) => {
            event.ports[0].postMessage({ success: false, error: error.message });
        });
}
