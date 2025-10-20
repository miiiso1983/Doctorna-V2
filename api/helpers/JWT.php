<?php
/**
 * JWT Helper - Simple JWT implementation
 */

class JWT {
    /**
     * Generate JWT token
     */
    public static function encode($payload, $expiration = null) {
        $expiration = $expiration ?? JWT_EXPIRATION;
        
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGORITHM
        ];
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiration;
        
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET_KEY, true);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }
    
    /**
     * Decode and verify JWT token
     */
    public static function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception(ERROR_MESSAGES['invalid_token']);
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // Verify signature
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", JWT_SECRET_KEY, true);
        $signatureCheck = self::base64UrlEncode($signature);
        
        if ($signatureEncoded !== $signatureCheck) {
            throw new Exception(ERROR_MESSAGES['invalid_token']);
        }
        
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception(ERROR_MESSAGES['token_expired']);
        }
        
        return $payload;
    }
    
    /**
     * Get token from request headers
     */
    public static function getTokenFromRequest() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

