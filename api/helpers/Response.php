<?php
/**
 * API Response Helper
 */

class Response {
    /**
     * Send success response
     */
    public static function success($data = [], $message = null, $code = 200) {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Send error response
     */
    public static function error($message, $code = 400, $errors = []) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Send paginated response
     */
    public static function paginated($data, $total, $page, $limit, $message = null) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'total' => (int)$total,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'pages' => ceil($total / $limit)
            ],
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Send validation error response
     */
    public static function validationError($errors) {
        self::error(ERROR_MESSAGES['missing_fields'], 422, $errors);
    }
    
    /**
     * Send unauthorized response
     */
    public static function unauthorized($message = null) {
        self::error($message ?? ERROR_MESSAGES['unauthorized'], 401);
    }
    
    /**
     * Send forbidden response
     */
    public static function forbidden($message = null) {
        self::error($message ?? ERROR_MESSAGES['forbidden'], 403);
    }
    
    /**
     * Send not found response
     */
    public static function notFound($message = null) {
        self::error($message ?? ERROR_MESSAGES['not_found'], 404);
    }
}

