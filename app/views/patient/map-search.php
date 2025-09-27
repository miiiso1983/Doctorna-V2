<!-- Map Search Interface -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-map-marked-alt me-2"></i>
                    البحث بالخريطة
                </h5>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>
                    فلاتر البحث
                </h5>
            </div>
            <div class="card-body">
                <form id="map-search-form">
                    <div class="mb-3">
                        <label class="form-label">التخصص</label>
                        <select class="form-select" id="specialization-filter">
                            <option value="">جميع التخصصات</option>
                            <?php foreach ($specializations as $spec): ?>
                                <option value="<?= $spec['id'] ?>">
                                    <?= $this->escape($spec['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">نطاق البحث</label>
                        <select class="form-select" id="radius-filter">
                            <option value="5">5 كم</option>
                            <option value="10" selected>10 كم</option>
                            <option value="15">15 كم</option>
                            <option value="20">20 كم</option>
                            <option value="50">50 كم</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">التقييم الأدنى</label>
                        <select class="form-select" id="rating-filter">
                            <option value="">جميع التقييمات</option>
                            <option value="3">3+ نجوم</option>
                            <option value="4">4+ نجوم</option>
                            <option value="4.5">4.5+ نجوم</option>
                            <option value="5">5 نجوم</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">الحد الأقصى للرسوم</label>
                        <input type="range" class="form-range" id="fee-filter" 
                               min="50" max="500" value="500" step="25">
                        <div class="d-flex justify-content-between">
                            <small><?= $this->formatCurrency(50) ?></small>
                            <small id="fee-value"><?= $this->formatCurrency(500) ?></small>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-primary w-100 mb-2" onclick="searchNearbyDoctors()">
                        <i class="fas fa-search me-2"></i>البحث
                    </button>
                    
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                        <i class="fas fa-times me-2"></i>مسح الفلاتر
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Current Location -->
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-location-arrow me-2"></i>
                    موقعي الحالي
                </h6>
            </div>
            <div class="card-body">
                <div id="current-location">
                    <button class="btn btn-info w-100" onclick="getCurrentLocation()">
                        <i class="fas fa-crosshairs me-2"></i>تحديد موقعي
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Results -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    نتائج البحث
                    <span class="badge bg-light text-dark ms-2" id="results-count">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div id="search-results">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                        <p>استخدم الخريطة للبحث عن الأطباء القريبين منك</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Doctor Details Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الطبيب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="doctor-details">
                <!-- Doctor details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-success" id="book-appointment-btn">
                    <i class="fas fa-calendar-plus me-2"></i>حجز موعد
                </button>
                <button type="button" class="btn btn-primary" id="get-directions-btn">
                    <i class="fas fa-route me-2"></i>الاتجاهات
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let map;
let userMarker;
let doctorMarkers = [];
let userLocation = null;
let infoWindow;

// Initialize map
function initMap() {
    // Default location (Riyadh, Saudi Arabia)
    const defaultLocation = { lat: 24.7136, lng: 46.6753 };
    
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: defaultLocation,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true,
        styles: [
            {
                featureType: 'poi.medical',
                elementType: 'geometry',
                stylers: [{ color: '#28a745' }]
            }
        ]
    });
    
    infoWindow = new google.maps.InfoWindow();
    
    // Try to get user's current location
    getCurrentLocation();
}

// Get current location
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                // Center map on user location
                map.setCenter(userLocation);
                map.setZoom(14);
                
                // Add user marker
                if (userMarker) {
                    userMarker.setMap(null);
                }
                
                userMarker = new google.maps.Marker({
                    position: userLocation,
                    map: map,
                    title: 'موقعي الحالي',
                    icon: {
                        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#007bff">
                                <circle cx="12" cy="12" r="8"/>
                                <circle cx="12" cy="12" r="3" fill="white"/>
                            </svg>
                        `),
                        scaledSize: new google.maps.Size(24, 24)
                    }
                });
                
                // Update location display
                reverseGeocode(userLocation.lat, userLocation.lng);
                
                // Auto search for nearby doctors
                searchNearbyDoctors();
            },
            (error) => {
                console.error('Error getting location:', error);
                document.getElementById('current-location').innerHTML = `
                    <div class="alert alert-warning small">
                        لا يمكن تحديد موقعك الحالي
                    </div>
                `;
            }
        );
    } else {
        document.getElementById('current-location').innerHTML = `
            <div class="alert alert-danger small">
                المتصفح لا يدعم تحديد الموقع
            </div>
        `;
    }
}

// Reverse geocode to get address
function reverseGeocode(lat, lng) {
    fetch('/map/reverse-geocode', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            latitude: lat,
            longitude: lng
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('current-location').innerHTML = `
                <div class="small">
                    <i class="fas fa-map-marker-alt text-info me-1"></i>
                    ${data.data.formatted_address}
                </div>
            `;
        }
    })
    .catch(error => console.error('Error:', error));
}

// Search for nearby doctors
function searchNearbyDoctors() {
    if (!userLocation) {
        alert('يرجى تحديد موقعك أولاً');
        return;
    }
    
    const specialization = document.getElementById('specialization-filter').value;
    const radius = document.getElementById('radius-filter').value;
    const rating = document.getElementById('rating-filter').value;
    const maxFee = document.getElementById('fee-filter').value;
    
    // Clear existing markers
    clearDoctorMarkers();
    
    // Show loading
    document.getElementById('search-results').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">جاري البحث...</span>
            </div>
            <p class="mt-2">جاري البحث عن الأطباء...</p>
        </div>
    `;
    
    fetch('/map/find-nearby-doctors', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            latitude: userLocation.lat,
            longitude: userLocation.lng,
            radius: radius,
            specialization: specialization,
            rating: rating,
            max_fee: maxFee
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayDoctorsOnMap(data.data.doctors);
            displaySearchResults(data.data.doctors);
            document.getElementById('results-count').textContent = data.data.total;
        } else {
            document.getElementById('search-results').innerHTML = `
                <div class="alert alert-warning">
                    ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('search-results').innerHTML = `
            <div class="alert alert-danger">
                حدث خطأ أثناء البحث
            </div>
        `;
    });
}

// Display doctors on map
function displayDoctorsOnMap(doctors) {
    doctors.forEach(doctor => {
        const marker = new google.maps.Marker({
            position: { lat: doctor.latitude, lng: doctor.longitude },
            map: map,
            title: `د. ${doctor.name}`,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="#28a745">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(32, 32)
            }
        });
        
        marker.addListener('click', () => {
            showDoctorInfo(doctor, marker);
        });
        
        doctorMarkers.push(marker);
    });
}

// Show doctor info window
function showDoctorInfo(doctor, marker) {
    const content = `
        <div class="doctor-info-window" style="max-width: 300px;">
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
                <div><i class="fas fa-money-bill text-success me-1"></i> ${doctor.consultation_fee} ر.س</div>
                <div><i class="fas fa-clock text-info me-1"></i> ${doctor.experience_years} سنة خبرة</div>
            </div>
            <div class="d-flex gap-1">
                <button class="btn btn-primary btn-sm flex-fill" onclick="showDoctorDetails(${doctor.id})">
                    تفاصيل
                </button>
                <button class="btn btn-success btn-sm flex-fill" onclick="bookAppointment(${doctor.id})">
                    حجز
                </button>
            </div>
        </div>
    `;
    
    infoWindow.setContent(content);
    infoWindow.open(map, marker);
}

// Display search results
function displaySearchResults(doctors) {
    if (doctors.length === 0) {
        document.getElementById('search-results').innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-search fa-3x mb-3"></i>
                <p>لم يتم العثور على أطباء في النطاق المحدد</p>
                <button class="btn btn-primary" onclick="clearFilters()">توسيع نطاق البحث</button>
            </div>
        `;
        return;
    }
    
    const resultsHtml = doctors.map(doctor => `
        <div class="doctor-result-item border rounded p-3 mb-3">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0 me-3">
                    ${doctor.avatar ? 
                        `<img src="/uploads/profiles/${doctor.avatar}" class="rounded-circle" width="60" height="60">` :
                        `<div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-md text-white fa-lg"></i>
                        </div>`
                    }
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">د. ${doctor.name}</h6>
                        <span class="badge bg-info">${doctor.distance} كم</span>
                    </div>
                    <p class="text-muted mb-1">${doctor.specialization}</p>
                    <div class="rating-stars text-warning mb-2">
                        ${'★'.repeat(Math.floor(doctor.rating))}${'☆'.repeat(5 - Math.floor(doctor.rating))}
                        <span class="text-muted ms-1">(${doctor.total_reviews})</span>
                    </div>
                    <div class="row text-center mb-2">
                        <div class="col-4">
                            <small class="text-muted d-block">الخبرة</small>
                            <strong>${doctor.experience_years} سنة</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">الرسوم</small>
                            <strong class="text-success">${Doctorna.utils.formatCurrency(doctor.consultation_fee)}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">المسافة</small>
                            <strong class="text-info">${doctor.distance} كم</strong>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="showDoctorDetails(${doctor.id})">
                            <i class="fas fa-eye me-1"></i>تفاصيل
                        </button>
                        <button class="btn btn-success btn-sm" onclick="bookAppointment(${doctor.id})">
                            <i class="fas fa-calendar-plus me-1"></i>حجز موعد
                        </button>
                        <button class="btn btn-info btn-sm" onclick="getDirections(${doctor.latitude}, ${doctor.longitude})">
                            <i class="fas fa-route me-1"></i>الاتجاهات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    document.getElementById('search-results').innerHTML = resultsHtml;
}

// Clear doctor markers
function clearDoctorMarkers() {
    doctorMarkers.forEach(marker => marker.setMap(null));
    doctorMarkers = [];
}

// Clear filters
function clearFilters() {
    document.getElementById('specialization-filter').value = '';
    document.getElementById('radius-filter').value = '10';
    document.getElementById('rating-filter').value = '';
    document.getElementById('fee-filter').value = '500';
    document.getElementById('fee-value').textContent = '500 ر.س';
    
    if (userLocation) {
        searchNearbyDoctors();
    }
}

// Show doctor details
function showDoctorDetails(doctorId) {
    // Load doctor details and show modal
    window.location.href = `/patient/doctor/${doctorId}`;
}

// Book appointment
function bookAppointment(doctorId) {
    window.location.href = `/patient/doctor/${doctorId}#book-appointment`;
}

// Get directions
function getDirections(lat, lng) {
    if (!userLocation) {
        alert('يرجى تحديد موقعك أولاً');
        return;
    }
    
    const url = `https://www.google.com/maps/dir/${userLocation.lat},${userLocation.lng}/${lat},${lng}`;
    window.open(url, '_blank');
}

// Fee range slider
document.getElementById('fee-filter').addEventListener('input', function() {
    document.getElementById('fee-value').textContent = this.value + ' ر.س';
});

// Auto search on filter change
document.querySelectorAll('#map-search-form select').forEach(select => {
    select.addEventListener('change', () => {
        if (userLocation) {
            searchNearbyDoctors();
        }
    });
});

// Initialize map when page loads
window.onload = function() {
    if (typeof google !== 'undefined') {
        initMap();
    }
};
</script>

<!-- Google Maps API -->
<script async defer 
        src="https://maps.googleapis.com/maps/api/js?key=<?= $_ENV['GOOGLE_MAPS_API_KEY'] ?? '' ?>&libraries=places&language=ar&region=SA&callback=initMap">
</script>

<style>
.doctor-info-window {
    font-family: 'Cairo', sans-serif;
}

.doctor-result-item {
    transition: all 0.3s ease;
}

.doctor-result-item:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.rating-stars {
    font-size: 0.9rem;
}

#map {
    border-radius: 0 0 10px 10px;
}

.form-range::-webkit-slider-thumb {
    background: #0d6efd;
}

.form-range::-moz-range-thumb {
    background: #0d6efd;
    border: none;
}
</style>
