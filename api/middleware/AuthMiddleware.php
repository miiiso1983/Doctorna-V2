<?php
/**
 * Authentication Middleware
 */

class AuthMiddleware {
    private static $currentUser = null;
    
    /**
     * Authenticate request
     */
    public static function authenticate() {
        try {
            $token = JWT::getTokenFromRequest();
            
            if (!$token) {
                Response::unauthorized();
            }
            
            $payload = JWT::decode($token);
            
            // Store current user data
            self::$currentUser = $payload;
            
            return $payload;
            
        } catch (Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
    
    /**
     * Get current authenticated user
     */
    public static function user() {
        return self::$currentUser;
    }
    
    /**
     * Get current user ID
     */
    public static function userId() {
        return self::$currentUser['user_id'] ?? null;
    }
    
    /**
     * Get current user role
     */
    public static function userRole() {
        return self::$currentUser['role'] ?? null;
    }
    
    /**
     * Check if user has role
     */
    public static function hasRole($role) {
        return self::userRole() === $role;
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($role) {
        if (!self::hasRole($role)) {
            Response::forbidden('ليس لديك صلاحية للوصول إلى هذا المورد');
        }
    }
    
    /**
     * Require one of multiple roles
     */
    public static function requireAnyRole($roles) {
        if (!in_array(self::userRole(), $roles)) {
            Response::forbidden('ليس لديك صلاحية للوصول إلى هذا المورد');
        }
    }
}

