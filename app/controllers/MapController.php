<?php
/**
 * Map Controller
 * Handles geolocation and maps functionality
 */

require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/Doctor.php';
require_once APP_PATH . '/models/Patient.php';
require_once APP_PATH . '/models/User.php';


class MapController extends Controller {
    private $userModel;

    private $doctorModel;
    private $patientModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->doctorModel = new Doctor();
        $this->patientModel = new Patient();
    }

    /**
     * Find nearby doctors
     */
    public function findNearbyDoctors() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $latitude = $this->post('latitude');
        $longitude = $this->post('longitude');
        $radius = $this->post('radius', 10); // Default 10km
        $specialization = $this->post('specialization', '');

        if (!$latitude || !$longitude) {
            $this->error('الموقع الجغرافي مطلوب');
        }

        // Validate coordinates
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            $this->error('إحداثيات غير صحيحة');
        }

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $this->error('إحداثيات خارج النطاق المسموح');
        }

        try {
            $doctors = $this->findDoctorsWithinRadius($latitude, $longitude, $radius, $specialization);

            $this->success('تم العثور على الأطباء بنجاح', [
                'doctors' => $doctors,
                'total' => count($doctors)
            ]);

        } catch (Exception $e) {
            $this->error('حدث خطأ أثناء البحث عن الأطباء');
        }
    }

    /**
     * Get doctor location
     */
    public function getDoctorLocation() {
        $doctorId = $this->get('doctor_id');

        if (!$doctorId) {
            $this->error('معرف الطبيب مطلوب');
        }

        $doctor = $this->doctorModel->getDoctorWithLocation($doctorId);

        if (!$doctor) {
            $this->error('الطبيب غير موجود');
        }

        if (!$doctor['latitude'] || !$doctor['longitude']) {
            $this->error('موقع الطبيب غير محدد');
        }

        $this->success('تم جلب موقع الطبيب بنجاح', [
            'doctor' => [
                'id' => $doctor['id'],
                'name' => $doctor['name'],
                'specialization' => $doctor['specialization_name'],
                'latitude' => (float)$doctor['latitude'],
                'longitude' => (float)$doctor['longitude'],
                'address' => $doctor['clinic_address'] ?: $doctor['address'],
                'phone' => $doctor['clinic_phone'] ?: $doctor['phone'],
                'rating' => (float)$doctor['rating'],
                'consultation_fee' => (float)$doctor['consultation_fee']
            ]
        ]);
    }

    /**
     * Calculate distance between two points
     */
    public function calculateDistance() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $lat1 = $this->post('lat1');
        $lon1 = $this->post('lon1');
        $lat2 = $this->post('lat2');
        $lon2 = $this->post('lon2');

        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            $this->error('جميع الإحداثيات مطلوبة');
        }

        $distance = $this->calculateDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2);

        $this->success('تم حساب المسافة بنجاح', [
            'distance' => round($distance, 2),
            'unit' => 'km'
        ]);
    }

    /**
     * Get directions between two points
     */
    public function getDirections() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $origin = $this->post('origin');
        $destination = $this->post('destination');

        if (!$origin || !$destination) {
            $this->error('نقطة البداية والوجهة مطلوبتان');
        }

        // Use Google Directions API
        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';

        if (!$apiKey) {
            $this->error('مفتاح Google Maps API غير متوفر');
        }

        $url = "https://maps.googleapis.com/maps/api/directions/json?" . http_build_query([
            'origin' => $origin,
            'destination' => $destination,
            'key' => $apiKey,
            'language' => 'ar',
            'region' => 'IQ'
        ]);

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data['status'] !== 'OK') {
            $this->error('لا يمكن الحصول على الاتجاهات');
        }

        $route = $data['routes'][0];
        $leg = $route['legs'][0];

        $this->success('تم الحصول على الاتجاهات بنجاح', [
            'distance' => $leg['distance']['text'],
            'duration' => $leg['duration']['text'],
            'steps' => array_map(function($step) {
                return [
                    'instruction' => strip_tags($step['html_instructions']),
                    'distance' => $step['distance']['text'],
                    'duration' => $step['duration']['text']
                ];
            }, $leg['steps'])
        ]);
    }

    /**
     * Geocode address
     */
    public function geocodeAddress() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $address = $this->post('address');

        if (!$address) {
            $this->error('العنوان مطلوب');
        }

        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';

        if (!$apiKey) {
            $this->error('مفتاح Google Maps API غير متوفر');
        }

        $url = "https://maps.googleapis.com/maps/api/geocode/json?" . http_build_query([
            'address' => $address,
            'key' => $apiKey,
            'language' => 'ar',
            'region' => 'IQ'
        ]);

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data['status'] !== 'OK' || empty($data['results'])) {
            $this->error('لا يمكن العثور على الموقع');
        }

        $result = $data['results'][0];
        $location = $result['geometry']['location'];

        $this->success('تم العثور على الموقع بنجاح', [
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
            'formatted_address' => $result['formatted_address'],
            'place_id' => $result['place_id']
        ]);
    }

    /**
     * Reverse geocode coordinates
     */
    public function reverseGeocode() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $latitude = $this->post('latitude');
        $longitude = $this->post('longitude');

        if (!$latitude || !$longitude) {
            $this->error('الإحداثيات مطلوبة');
        }

        $apiKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';

        if (!$apiKey) {
            $this->error('مفتاح Google Maps API غير متوفر');
        }

        $url = "https://maps.googleapis.com/maps/api/geocode/json?" . http_build_query([
            'latlng' => "{$latitude},{$longitude}",
            'key' => $apiKey,
            'language' => 'ar',
            'region' => 'IQ'
        ]);

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data['status'] !== 'OK' || empty($data['results'])) {
            $this->error('لا يمكن تحديد العنوان');
        }

        $result = $data['results'][0];

        $this->success('تم تحديد العنوان بنجاح', [
            'formatted_address' => $result['formatted_address'],
            'place_id' => $result['place_id'],
            'address_components' => $result['address_components']
        ]);
    }

    /**
     * Update user location
     */
    public function updateUserLocation() {
        if (!$this->isPost() || !$this->isAjax()) {
            $this->error('طريقة الطلب غير صحيحة');
        }

        $this->validateCSRF();

        $latitude = $this->post('latitude');
        $longitude = $this->post('longitude');
        $address = $this->post('address', '');

        if (!$latitude || !$longitude) {
            $this->error('الإحداثيات مطلوبة');
        }

        try {
            $userId = $this->auth->id();

            // Update location based on user role
            if ($this->auth->isDoctor()) {
                $doctor = $this->doctorModel->getByUserId($userId);
                if ($doctor) {
                    $this->doctorModel->update($doctor['id'], [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'clinic_address' => $address
                    ]);
                }
            } elseif ($this->auth->isPatient()) {
                $patient = $this->patientModel->getByUserId($userId);
                if ($patient) {
                    $this->patientModel->update($patient['id'], [
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]);
                }
            }

            // Update user address
            $this->userModel->update($userId, ['address' => $address]);

            $this->success('تم تحديث الموقع بنجاح');

        } catch (Exception $e) {
            $this->error('حدث خطأ أثناء تحديث الموقع');
        }
    }

    /**
     * Find doctors within radius
     */
    private function findDoctorsWithinRadius($latitude, $longitude, $radius, $specialization = '') {
        $conditions = ['d.status = :status'];
        $params = ['status' => 'approved'];

        if ($specialization) {
            $conditions[] = 'd.specialization_id = :specialization';
            $params['specialization'] = $specialization;
        }

        $whereClause = implode(' AND ', $conditions);

        // Use Haversine formula to calculate distance
        $sql = "SELECT d.*, s.name as specialization_name, s.icon as specialization_icon,
                       u.name, u.email, u.phone, u.city, u.avatar, u.address,
                       d.clinic_address, d.clinic_phone,
                       (6371 * acos(cos(radians(:lat)) * cos(radians(d.latitude)) *
                        cos(radians(d.longitude) - radians(:lng)) +
                        sin(radians(:lat)) * sin(radians(d.latitude)))) AS distance
                FROM doctors d
                LEFT JOIN specializations s ON d.specialization_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE {$whereClause}
                AND d.latitude IS NOT NULL
                AND d.longitude IS NOT NULL
                HAVING distance <= :radius
                ORDER BY distance ASC, d.rating DESC";

        $params['lat'] = $latitude;
        $params['lng'] = $longitude;
        $params['radius'] = $radius;

        $results = $this->doctorModel->fetchRaw($sql, $params);

        // Format results for map display
        return array_map(function($doctor) {
            return [
                'id' => $doctor['id'],
                'name' => $doctor['name'],
                'specialization' => $doctor['specialization_name'],
                'specialization_icon' => $doctor['specialization_icon'],
                'latitude' => (float)$doctor['latitude'],
                'longitude' => (float)$doctor['longitude'],
                'address' => $doctor['clinic_address'] ?: $doctor['address'],
                'phone' => $doctor['clinic_phone'] ?: $doctor['phone'],
                'city' => $doctor['city'],
                'rating' => (float)$doctor['rating'],
                'total_reviews' => (int)$doctor['total_reviews'],
                'consultation_fee' => (float)$doctor['consultation_fee'],
                'experience_years' => (int)$doctor['experience_years'],
                'distance' => round((float)$doctor['distance'], 2),
                'avatar' => $doctor['avatar']
            ];
        }, $results);
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }
}
