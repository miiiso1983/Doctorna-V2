<?php
/**
 * User Model
 */

require_once APP_PATH . '/core/Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'status',
        'avatar', 'address', 'city', 'country', 'latitude', 'longitude'
    ];
    protected $hidden = ['password'];

    /**
     * Get user by email
     */
    public function findByEmail($email) {
        return $this->first('email = :email', ['email' => $email]);
    }

    /**
     * Get users by role
     */
    public function getByRole($role, $status = 'active') {
        return $this->where(
            'role = :role AND status = :status',
            ['role' => $role, 'status' => $status],
            'name ASC'
        );
    }

    /**
     * Search users
     */
    public function searchUsers($query, $role = null, $page = 1) {
        $conditions = [];
        $params = [];

        // Add search conditions
        $searchFields = ['name', 'email', 'phone', 'city'];
        $searchConditions = [];
        foreach ($searchFields as $field) {
            $searchConditions[] = "{$field} LIKE :query";
        }
        $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
        $params['query'] = "%{$query}%";

        // Add role filter
        if ($role) {
            $conditions[] = 'role = :role';
            $params['role'] = $role;
        }

        $whereClause = implode(' AND ', $conditions);

        return $this->paginate($page, ITEMS_PER_PAGE, $whereClause, $params, 'created_at DESC');
    }

    /**
     * Get user statistics
     */
    public function getStatistics() {
        $stats = [];

        // Total users by role
        $stats['total_users'] = $this->count();
        $stats['total_doctors'] = $this->count('role = :role', ['role' => ROLE_DOCTOR]);
        $stats['total_patients'] = $this->count('role = :role', ['role' => ROLE_PATIENT]);
        $stats['total_admins'] = $this->count('role = :role', ['role' => ROLE_SUPER_ADMIN]);

        // Active users
        $stats['active_users'] = $this->count('status = :status', ['status' => 'active']);
        $stats['inactive_users'] = $this->count('status = :status', ['status' => 'inactive']);

        // Recent registrations (last 30 days)
        $stats['recent_registrations'] = $this->count(
            'created_at >= :date',
            ['date' => date('Y-m-d', strtotime('-30 days'))]
        );

        return $stats;
    }

    /**
     * Update user status
     */
    public function updateStatus($userId, $status) {
        return $this->update($userId, ['status' => $status]);
    }

    /**
     * Get users with location
     */
    public function getUsersWithLocation($role = null) {
        $conditions = 'latitude IS NOT NULL AND longitude IS NOT NULL';
        $params = [];

        if ($role) {
            $conditions .= ' AND role = :role';
            $params['role'] = $role;
        }

        return $this->where($conditions, $params);
    }

    /**
     * Find nearby users
     */
    public function findNearbyUsers($latitude, $longitude, $radius = 50, $role = null) {
        $conditions = 'latitude IS NOT NULL AND longitude IS NOT NULL';
        $params = [
            'lat' => $latitude,
            'lng' => $longitude,
            'radius' => $radius
        ];

        if ($role) {
            $conditions .= ' AND role = :role';
            $params['role'] = $role;
        }

        // Calculate distance using Haversine formula
        $sql = "SELECT *,
                (6371 * acos(cos(radians(:lat)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(:lng)) + sin(radians(:lat)) *
                sin(radians(latitude)))) AS distance
                FROM {$this->table}
                WHERE {$conditions}
                HAVING distance < :radius
                ORDER BY distance ASC";

        return $this->fetchRaw($sql, $params);
    }

    /**
     * Get user's full profile with role-specific data
     */
    public function getFullProfile($userId) {
        $user = $this->find($userId);

        if (!$user) {
            return null;
        }

        // Get role-specific profile
        switch ($user['role']) {
            case ROLE_DOCTOR:
                $doctorModel = new Doctor();
                $doctorProfile = $doctorModel->getByUserId($userId);
                if ($doctorProfile) {
                    $user = array_merge($user, $doctorProfile);
                }
                break;

            case ROLE_PATIENT:
                $patientModel = new Patient();
                $patientProfile = $patientModel->getByUserId($userId);
                if ($patientProfile) {
                    $user = array_merge($user, $patientProfile);
                }
                break;
        }

        return $user;
    }

    /**
     * Update user location
     */
    public function updateLocation($userId, $latitude, $longitude, $address = null, $city = null) {
        $data = [
            'latitude' => $latitude,
            'longitude' => $longitude
        ];

        if ($address) {
            $data['address'] = $address;
        }

        if ($city) {
            $data['city'] = $city;
        }

        return $this->update($userId, $data);
    }

    /**
     * Verify email
     */
    public function verifyEmail($userId) {
        return $this->update($userId, [
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update last login
     */
    public function updateLastLogin($userId) {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Check if email exists (excluding specific user)
     */
    public function emailExistsExcept($email, $userId) {
        return $this->exists('email = :email AND id != :id', [
            'email' => $email,
            'id' => $userId
        ]);
    }

    /**
     * Get users registered in date range
     */
    public function getUsersByDateRange($startDate, $endDate, $role = null) {
        $conditions = 'created_at BETWEEN :start_date AND :end_date';
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($role) {
            $conditions .= ' AND role = :role';
            $params['role'] = $role;
        }

        return $this->where($conditions, $params, 'created_at DESC');
    }

    /**
     * Get distinct cities (optionally only for active doctors)
     */
    public function getCities($onlyDoctors = true) {
        $sql = "SELECT DISTINCT city FROM {$this->table} WHERE city IS NOT NULL AND city != ''";
        $params = [];
        if ($onlyDoctors) {
            $sql .= " AND role = :role AND status = :status";
            $params['role'] = defined('ROLE_DOCTOR') ? ROLE_DOCTOR : 'doctor';
            $params['status'] = 'active';
        }
        $sql .= " ORDER BY city ASC";
        $rows = $this->fetchRaw($sql, $params);
        return array_map(function ($r) { return $r['city']; }, $rows);
    }
}
