<?php
/**
 * CSRF Protection Class
 * Handles Cross-Site Request Forgery protection
 */

class CSRF {
    private static $tokenKey = 'csrf_tokens';
    private static $tokenFile = null;

    /**
     * Get or create CSRF session identifier
     */
    private static function getCsrfSessionId() {
        $cookieName = 'csrf_sid';

        // Check if cookie exists
        if (isset($_COOKIE[$cookieName]) && preg_match('/^[a-zA-Z0-9]{32}$/', $_COOKIE[$cookieName])) {
            return $_COOKIE[$cookieName];
        }

        // Create new identifier
        $csrfSid = bin2hex(random_bytes(16));

        // Set cookie (1 day expiry)
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                  (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        setcookie($cookieName, $csrfSid, [
            'expires' => time() + 86400,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        $_COOKIE[$cookieName] = $csrfSid;

        return $csrfSid;
    }

    /**
     * Get token storage file path
     */
    private static function getTokenFile() {
        if (self::$tokenFile === null) {
            $csrfSid = self::getCsrfSessionId();
            $dir = ROOT_PATH . '/storage/csrf';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            self::$tokenFile = $dir . '/tokens_' . $csrfSid . '.json';
        }
        return self::$tokenFile;
    }

    /**
     * Get tokens from storage
     */
    private static function getTokens() {
        $file = self::getTokenFile();
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    /**
     * Save tokens to storage
     */
    private static function saveTokens($tokens) {
        $file = self::getTokenFile();
        file_put_contents($file, json_encode($tokens));
    }

    /**
     * Generate CSRF token
     */
    public static function token() {
        $tokens = self::getTokens();

        $token = bin2hex(random_bytes(32));
        $tokens[$token] = time();

        // Clean old tokens (older than 1 hour)
        self::cleanOldTokens($tokens);

        self::saveTokens($tokens);

        // Debug logging
        error_log('CSRF::token() - Generated: ' . substr($token, 0, 16) . '... at ' . date('H:i:s'));
        error_log('CSRF::token() - CSRF SID: ' . self::getCsrfSessionId());
        error_log('CSRF::token() - PHP Session ID: ' . session_id());
        error_log('CSRF::token() - Total tokens: ' . count($tokens));
        error_log('CSRF::token() - File: ' . self::getTokenFile());

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

        $tokens = self::getTokens();

        // Debug logging
        error_log('CSRF::validate() - Received token: ' . ($token ? substr($token, 0, 16) . '...' : 'NULL'));
        error_log('CSRF::validate() - CSRF SID: ' . self::getCsrfSessionId());
        error_log('CSRF::validate() - PHP Session ID: ' . session_id());
        error_log('CSRF::validate() - Total tokens: ' . count($tokens));
        error_log('CSRF::validate() - File: ' . self::getTokenFile());
        error_log('CSRF::validate() - Tokens: ' . json_encode(array_keys($tokens)));

        if (!$token) {
            error_log('CSRF::validate() - FAILED: No token provided');
            return false;
        }

        if (!isset($tokens[$token])) {
            error_log('CSRF::validate() - FAILED: Token not found');
            return false;
        }

        // Check if token is not expired (1 hour)
        $tokenTime = $tokens[$token];
        if (time() - $tokenTime > 3600) {
            unset($tokens[$token]);
            self::saveTokens($tokens);
            error_log('CSRF::validate() - FAILED: Token expired');
            return false;
        }

        // Token consumption policy:
        // - For normal form submissions (token in POST/GET), consume once.
        // - For AJAX header usage (X-CSRF-TOKEN), allow reuse within expiry to avoid breaking background polls.
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $tokenCameFromParam = isset($_POST[CSRF_TOKEN_NAME]) || isset($_GET[CSRF_TOKEN_NAME]);
        if (!$isAjax && $tokenCameFromParam) {
            unset($tokens[$token]);
            self::saveTokens($tokens);
        }

        error_log('CSRF::validate() - SUCCESS');
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
    private static function cleanOldTokens(&$tokens) {
        $currentTime = time();

        foreach ($tokens as $token => $time) {
            if ($currentTime - $time > 3600) { // 1 hour
                unset($tokens[$token]);
            }
        }
    }
    
    /**
     * Clear all tokens
     */
    public static function clear() {
        $file = self::getTokenFile();
        if (file_exists($file)) {
            @unlink($file);
        }
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
