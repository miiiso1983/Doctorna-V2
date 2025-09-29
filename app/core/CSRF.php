<?php
/**
 * CSRF Protection Class
 * Handles Cross-Site Request Forgery protection
 */

class CSRF {
    private static $tokenKey = 'csrf_tokens';
    
    /**
     * Generate CSRF token
     */
    public static function token() {
        if (!isset($_SESSION[self::$tokenKey])) {
            $_SESSION[self::$tokenKey] = [];
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::$tokenKey][$token] = time();

        // Clean old tokens (older than 1 hour)
        self::cleanOldTokens();

        // Debug logging
        error_log('CSRF::token() - Generated: ' . substr($token, 0, 16) . '... at ' . date('H:i:s'));
        error_log('CSRF::token() - Session ID: ' . session_id());
        error_log('CSRF::token() - Total tokens in session: ' . count($_SESSION[self::$tokenKey]));

        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public static function validate($token = null) {
        // 1) Try explicit token param
        if ($token === null) {
            $token = $_POST[CSRF_TOKEN_NAME] ?? $_GET[CSRF_TOKEN_NAME] ?? null;
        }
        // 2) Fall back to X-CSRF-TOKEN header (for AJAX/JSON requests)
        if ($token === null) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        // Debug logging
        error_log('CSRF::validate() - Received token: ' . ($token ? substr($token, 0, 16) . '...' : 'NULL'));
        error_log('CSRF::validate() - Session ID: ' . session_id());
        error_log('CSRF::validate() - Tokens in session: ' . count($_SESSION[self::$tokenKey] ?? []));
        error_log('CSRF::validate() - Session data: ' . print_r($_SESSION[self::$tokenKey] ?? [], true));

        if (!$token) {
            error_log('CSRF::validate() - FAILED: No token provided');
            return false;
        }

        if (!isset($_SESSION[self::$tokenKey][$token])) {
            error_log('CSRF::validate() - FAILED: Token not found in session');
            return false;
        }

        // Check if token is not expired (1 hour)
        $tokenTime = $_SESSION[self::$tokenKey][$token];
        if (time() - $tokenTime > 3600) {
            unset($_SESSION[self::$tokenKey][$token]);
            return false;
        }

        // Token consumption policy:
        // - For normal form submissions (token in POST/GET), consume once.
        // - For AJAX header usage (X-CSRF-TOKEN), allow reuse within expiry to avoid breaking background polls.
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $tokenCameFromParam = isset($_POST[CSRF_TOKEN_NAME]) || isset($_GET[CSRF_TOKEN_NAME]);
        if (!$isAjax && $tokenCameFromParam) {
            unset($_SESSION[self::$tokenKey][$token]);
        }

        return true;
    }

    /**
     * Get CSRF input field
     */
    public static function field() {
        $token = self::token();
        return "<input type='hidden' name='" . CSRF_TOKEN_NAME . "' value='{$token}'>";
    }
    
    /**
     * Get CSRF meta tag
     */
    public static function meta() {
        $token = self::token();
        return "<meta name='csrf-token' content='{$token}'>";
    }
    
    /**
     * Clean old tokens
     */
    private static function cleanOldTokens() {
        if (!isset($_SESSION[self::$tokenKey])) {
            return;
        }
        
        $currentTime = time();
        
        foreach ($_SESSION[self::$tokenKey] as $token => $time) {
            if ($currentTime - $time > 3600) { // 1 hour
                unset($_SESSION[self::$tokenKey][$token]);
            }
        }
    }
    
    /**
     * Clear all tokens
     */
    public static function clear() {
        $_SESSION[self::$tokenKey] = [];
    }
    
    /**
     * Middleware for CSRF protection
     */
    public static function middleware() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Only check CSRF for state-changing methods
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            if (!self::validate()) {
                http_response_code(403);
                
                // Check if it's an AJAX request
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false,
                        'message' => 'رمز الحماية غير صحيح. يرجى إعادة تحميل الصفحة والمحاولة مرة أخرى.',
                        'error_code' => 'CSRF_TOKEN_MISMATCH'
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    echo "<h1>403 - Forbidden</h1>";
                    echo "<p>رمز الحماية غير صحيح. يرجى إعادة تحميل الصفحة والمحاولة مرة أخرى.</p>";
                    echo "<a href='javascript:history.back()'>العودة</a>";
                }
                
                exit;
            }
        }
        
        return true;
    }
}
