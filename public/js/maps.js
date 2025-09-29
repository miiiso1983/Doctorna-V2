/**
 * طبيبك Maps Integration
 * Google Maps functionality for location-based doctor search
 */

class DoctornaMap {
    constructor(options = {}) {
        this.map = null;
        this.userMarker = null;
        this.doctorMarkers = [];
        this.userLocation = null;
        this.infoWindow = null;
        this.directionsService = null;
        this.directionsRenderer = null;
        
        this.options = {
            defaultLocation: { lat: 33.3152, lng: 44.3661 }, // Baghdad
            defaultZoom: 12,
            userZoom: 14,
            ...options
        };
        
        this.init();
    }
    
    init() {
        if (typeof google === 'undefined') {
            console.error('Google Maps API not loaded');
            return;
        }
        
        this.initMap();
        this.initServices();
    }
    
    initMap() {
        const mapElement = document.getElementById('map');
        if (!mapElement) {
            console.error('Map element not found');
            return;
        }
        
        this.map = new google.maps.Map(mapElement, {
            zoom: this.options.defaultZoom,
            center: this.options.defaultLocation,
            mapTypeControl: true,
            streetViewControl: true,
            fullscreenControl: true,
            zoomControl: true,
            styles: this.getMapStyles()
        });
        
        this.infoWindow = new google.maps.InfoWindow();
        
        // Add click listener for map
        this.map.addListener('click', (event) => {
            this.onMapClick(event);
        });
    }
    
    initServices() {
        this.directionsService = new google.maps.DirectionsService();
        this.directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#007bff',
                strokeWeight: 4
            }
        });
        this.directionsRenderer.setMap(this.map);
    }
    
    getMapStyles() {
        return [
            {
                featureType: 'poi.medical',
                elementType: 'geometry',
                stylers: [{ color: '#28a745' }]
            },
            {
                featureType: 'poi.medical',
                elementType: 'labels',
                stylers: [{ visibility: 'on' }]
            }
        ];
    }
    
    // Get user's current location
    getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation not supported'));
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    this.updateUserMarker();
                    this.centerMapOnUser();
                    resolve(this.userLocation);
                },
                (error) => {
                    reject(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000 // 5 minutes
                }
            );
        });
    }
    
    // Update user marker on map
    updateUserMarker() {
        if (!this.userLocation) return;
        
        if (this.userMarker) {
            this.userMarker.setMap(null);
        }
        
        this.userMarker = new google.maps.Marker({
            position: this.userLocation,
            map: this.map,
            title: 'موقعي الحالي',
            icon: this.getUserIcon(),
            zIndex: 1000
        });
        
        this.userMarker.addListener('click', () => {
            this.showUserInfo();
        });
    }
    
    // Center map on user location
    centerMapOnUser() {
        if (!this.userLocation) return;
        
        this.map.setCenter(this.userLocation);
        this.map.setZoom(this.options.userZoom);
    }
    
    // Add doctors to map
    addDoctorsToMap(doctors) {
        this.clearDoctorMarkers();
        
        doctors.forEach(doctor => {
            const marker = new google.maps.Marker({
                position: { lat: doctor.latitude, lng: doctor.longitude },
                map: this.map,
                title: `د. ${doctor.name}`,
                icon: this.getDoctorIcon(doctor),
                zIndex: 500
            });
            
            marker.addListener('click', () => {
                this.showDoctorInfo(doctor, marker);
            });
            
            this.doctorMarkers.push(marker);
        });
        
        // Fit map to show all markers
        this.fitMapToMarkers();
    }
    
    // Clear all doctor markers
    clearDoctorMarkers() {
        this.doctorMarkers.forEach(marker => marker.setMap(null));
        this.doctorMarkers = [];
    }
    
    // Fit map to show all markers
    fitMapToMarkers() {
        if (this.doctorMarkers.length === 0) return;
        
        const bounds = new google.maps.LatLngBounds();
        
        // Include user location if available
        if (this.userLocation) {
            bounds.extend(this.userLocation);
        }
        
        // Include all doctor markers
        this.doctorMarkers.forEach(marker => {
            bounds.extend(marker.getPosition());
        });
        
        this.map.fitBounds(bounds);
        
        // Ensure minimum zoom level
        google.maps.event.addListenerOnce(this.map, 'bounds_changed', () => {
            if (this.map.getZoom() > 15) {
                this.map.setZoom(15);
            }
        });
    }
    
    // Show doctor information window
    showDoctorInfo(doctor, marker) {
        const content = this.createDoctorInfoContent(doctor);
        this.infoWindow.setContent(content);
        this.infoWindow.open(this.map, marker);
    }
    
    // Show user information window
    showUserInfo() {
        if (!this.userLocation) return;
        
        const content = `
            <div class="user-info-window">
                <h6><i class="fas fa-user-circle text-primary me-2"></i>موقعي الحالي</h6>
                <p class="mb-2 small">
                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                    ${this.userLocation.lat.toFixed(6)}, ${this.userLocation.lng.toFixed(6)}
                </p>
                <button class="btn btn-primary btn-sm" onclick="doctornaMap.searchNearbyDoctors()">
                    <i class="fas fa-search me-1"></i>البحث هنا
                </button>
            </div>
        `;
        
        this.infoWindow.setContent(content);
        this.infoWindow.open(this.map, this.userMarker);
    }
    
    // Create doctor info window content
    createDoctorInfoContent(doctor) {
        return `
            <div class="doctor-info-window" style="max-width: 300px; font-family: 'Cairo', sans-serif;">
                <div class="d-flex align-items-center mb-2">
                    ${doctor.avatar ? 
                        `<img src="/uploads/profiles/${doctor.avatar}" class="rounded-circle me-2" width="40" height="40">` :
                        `<div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                            <i class="fas fa-user-md text-white"></i>
                        </div>`
                    }
                    <div>
                        <h6 class="mb-0">د. ${doctor.name}</h6>
                        <small class="text-muted">${doctor.specialization}</small>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="rating-stars text-warning">
                        ${'★'.repeat(Math.floor(doctor.rating))}${'☆'.repeat(5 - Math.floor(doctor.rating))}
                        <span class="text-muted">(${doctor.total_reviews})</span>
                    </div>
                </div>
                <div class="small mb-2">
                    <div><i class="fas fa-map-marker-alt text-danger me-1"></i> ${doctor.distance} كم</div>
                    <div><i class="fas fa-money-bill text-success me-1"></i> ${doctor.consultation_fee} د.ع</div>
                    <div><i class="fas fa-clock text-info me-1"></i> ${doctor.experience_years} سنة خبرة</div>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-primary btn-sm flex-fill" onclick="window.location.href='/patient/doctor/${doctor.id}'">
                        تفاصيل
                    </button>
                    <button class="btn btn-success btn-sm flex-fill" onclick="window.location.href='/patient/doctor/${doctor.id}#book'">
                        حجز
                    </button>
                    <button class="btn btn-info btn-sm" onclick="doctornaMap.getDirections(${doctor.latitude}, ${doctor.longitude})">
                        <i class="fas fa-route"></i>
                    </button>
                </div>
            </div>
        `;
    }
    
    // Get user icon
    getUserIcon() {
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#007bff">
                    <circle cx="12" cy="12" r="8"/>
                    <circle cx="12" cy="12" r="3" fill="white"/>
                </svg>
            `),
            scaledSize: new google.maps.Size(24, 24),
            anchor: new google.maps.Point(12, 12)
        };
    }
    
    // Get doctor icon
    getDoctorIcon(doctor) {
        const color = this.getDoctorIconColor(doctor.specialization);
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="${color}">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
            `),
            scaledSize: new google.maps.Size(32, 32),
            anchor: new google.maps.Point(16, 32)
        };
    }
    
    // Get doctor icon color based on specialization
    getDoctorIconColor(specialization) {
        const colors = {
            'طب عام': '#28a745',
            'طب الأطفال': '#17a2b8',
            'طب النساء والولادة': '#e83e8c',
            'طب القلب': '#dc3545',
            'طب العيون': '#6f42c1',
            'طب الأسنان': '#fd7e14',
            'طب الجلدية': '#20c997',
            'طب العظام': '#6c757d'
        };
        
        return colors[specialization] || '#28a745';
    }
    
    // Get directions to doctor
    getDirections(doctorLat, doctorLng) {
        if (!this.userLocation) {
            alert('يرجى تحديد موقعك أولاً');
            return;
        }
        
        const request = {
            origin: this.userLocation,
            destination: { lat: doctorLat, lng: doctorLng },
            travelMode: google.maps.TravelMode.DRIVING,
            language: 'ar',
            region: 'SA'
        };
        
        this.directionsService.route(request, (result, status) => {
            if (status === 'OK') {
                this.directionsRenderer.setDirections(result);
                this.showDirectionsInfo(result);
            } else {
                console.error('Directions request failed:', status);
                // Fallback to Google Maps
                const url = `https://www.google.com/maps/dir/${this.userLocation.lat},${this.userLocation.lng}/${doctorLat},${doctorLng}`;
                window.open(url, '_blank');
            }
        });
    }
    
    // Show directions information
    showDirectionsInfo(result) {
        const route = result.routes[0];
        const leg = route.legs[0];
        
        const content = `
            <div class="directions-info">
                <h6><i class="fas fa-route text-primary me-2"></i>الاتجاهات</h6>
                <p class="mb-1"><strong>المسافة:</strong> ${leg.distance.text}</p>
                <p class="mb-2"><strong>الوقت المتوقع:</strong> ${leg.duration.text}</p>
                <button class="btn btn-primary btn-sm" onclick="doctornaMap.clearDirections()">
                    إخفاء الاتجاهات
                </button>
            </div>
        `;
        
        this.infoWindow.setContent(content);
        this.infoWindow.setPosition(leg.end_location);
        this.infoWindow.open(this.map);
    }
    
    // Clear directions
    clearDirections() {
        this.directionsRenderer.setDirections({ routes: [] });
        this.infoWindow.close();
    }
    
    // Search nearby doctors
    async searchNearbyDoctors(filters = {}) {
        if (!this.userLocation) {
            throw new Error('User location not available');
        }
        
        const data = {
            latitude: this.userLocation.lat,
            longitude: this.userLocation.lng,
            radius: filters.radius || 10,
            specialization: filters.specialization || '',
            rating: filters.rating || '',
            max_fee: filters.max_fee || ''
        };
        
        try {
            const response = await fetch('/map/find-nearby-doctors', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.addDoctorsToMap(result.data.doctors);
                return result.data.doctors;
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error searching doctors:', error);
            throw error;
        }
    }
    
    // Geocode address
    async geocodeAddress(address) {
        try {
            const response = await fetch('/map/geocode-address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ address })
            });
            
            const result = await response.json();
            
            if (result.success) {
                return {
                    lat: result.data.latitude,
                    lng: result.data.longitude,
                    address: result.data.formatted_address
                };
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error geocoding address:', error);
            throw error;
        }
    }
    
    // Reverse geocode coordinates
    async reverseGeocode(lat, lng) {
        try {
            const response = await fetch('/map/reverse-geocode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            });
            
            const result = await response.json();
            
            if (result.success) {
                return result.data.formatted_address;
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error reverse geocoding:', error);
            throw error;
        }
    }
    
    // Handle map click
    onMapClick(event) {
        const lat = event.latLng.lat();
        const lng = event.latLng.lng();
        
        // Update user location to clicked position
        this.userLocation = { lat, lng };
        this.updateUserMarker();
        
        // Reverse geocode to get address
        this.reverseGeocode(lat, lng).then(address => {
            console.log('Clicked location:', address);
        }).catch(error => {
            console.error('Error getting address:', error);
        });
    }
    
    // Calculate distance between two points
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth's radius in kilometers
        const dLat = this.toRadians(lat2 - lat1);
        const dLng = this.toRadians(lng2 - lng1);
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        
        return R * c;
    }
    
    // Convert degrees to radians
    toRadians(degrees) {
        return degrees * (Math.PI / 180);
    }
}

// Global instance
let doctornaMap;

// Initialize map when Google Maps API is loaded
function initDoctornaMap() {
    doctornaMap = new DoctornaMap();
    
    // Try to get user location automatically
    doctornaMap.getCurrentLocation().then(() => {
        console.log('User location obtained');
    }).catch(error => {
        console.warn('Could not get user location:', error);
    });
}

// Export for use in other scripts
window.DoctornaMap = DoctornaMap;
window.initDoctornaMap = initDoctornaMap;
