<?php
/**
 * Global Error Handler
 * Handles exceptions and errors gracefully with logging
 */

class ErrorHandler {
    private static $logFile;
    
    public static function init() {
        self::$logFile = ROOT_PATH . '/storage/logs/error.log';
        
        // Create logs directory if it doesn't exist
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Set error and exception handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    /**
     * Handle PHP errors
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = self::getErrorType($severity);
        $logMessage = sprintf(
            "[%s] %s: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $errorType,
            $message,
            $file,
            $line
        );
        
        self::log($logMessage);
        
        // Convert errors to exceptions for consistent handling
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    
    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception) {
        $logMessage = sprintf(
            "[%s] EXCEPTION: %s in %s on line %d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        self::log($logMessage);
        
        // Show friendly error page
        self::showErrorPage($exception);
    }
    
    /**
     * Handle fatal errors on shutdown
     */
    public static function handleShutdown() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $logMessage = sprintf(
                "[%s] FATAL: %s in %s on line %d",
                date('Y-m-d H:i:s'),
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            self::log($logMessage);
            
            // Show friendly error page for fatal errors
            self::showErrorPage(new Exception($error['message']));
        }
    }
    
    /**
     * Log error message to file
     */
    private static function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}\n";
        
        // Add request info for web requests
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $requestInfo = sprintf(
                "Request: %s %s | IP: %s | User-Agent: %s\n",
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['REQUEST_URI'] ?? '/',
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            );
            $logEntry .= $requestInfo;
        }
        
        $logEntry .= str_repeat('-', 80) . "\n";
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Show friendly error page
     */
    private static function showErrorPage($exception) {
        // Clear any output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code(500);
        
        // Show detailed error in debug mode, friendly message otherwise
        if (defined('APP_DEBUG') && APP_DEBUG) {
            self::showDebugError($exception);
        } else {
            self::showFriendlyError();
        }
        
        exit;
    }
    
    /**
     * Show debug error page (development)
     */
    private static function showDebugError($exception) {
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>خطأ في التطبيق</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .error-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .error-title { color: #d32f2f; font-size: 24px; margin-bottom: 20px; }
                .error-message { background: #ffebee; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
                .error-details { background: #f5f5f5; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
                .back-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #1976d2; color: white; text-decoration: none; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1 class="error-title">خطأ في التطبيق</h1>
                <div class="error-message">
                    <strong>رسالة الخطأ:</strong> <?= htmlspecialchars($exception->getMessage()) ?>
                </div>
                <div class="error-details">
                    <strong>الملف:</strong> <?= htmlspecialchars($exception->getFile()) ?>
                    <strong>السطر:</strong> <?= $exception->getLine() ?>
                    
                    <strong>تتبع المكدس:</strong>
                    <?= htmlspecialchars($exception->getTraceAsString()) ?>
                </div>
                <a href="/" class="back-link">العودة للصفحة الرئيسية</a>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Show friendly error page (production)
     */
    private static function showFriendlyError() {
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>عذراً، حدث خطأ</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
                .error-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); text-align: center; max-width: 500px; }
                .error-icon { font-size: 64px; color: #ff6b6b; margin-bottom: 20px; }
                .error-title { color: #333; font-size: 28px; margin-bottom: 15px; font-weight: 600; }
                .error-message { color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 30px; }
                .back-link { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; transition: background 0.3s; }
                .back-link:hover { background: #5a6fd8; }
                .support-info { margin-top: 20px; font-size: 14px; color: #888; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <h1 class="error-title">عذراً، حدث خطأ</h1>
                <p class="error-message">
                    نعتذر، حدث خطأ غير متوقع أثناء معالجة طلبك. فريقنا التقني تم إشعاره بالمشكلة وسيتم حلها في أقرب وقت ممكن.
                </p>
                <a href="/" class="back-link">العودة للصفحة الرئيسية</a>
                <div class="support-info">
                    إذا استمرت المشكلة، يرجى التواصل مع الدعم التقني
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Get error type name
     */
    private static function getErrorType($type) {
        switch ($type) {
            case E_ERROR: return 'Fatal Error';
            case E_WARNING: return 'Warning';
            case E_PARSE: return 'Parse Error';
            case E_NOTICE: return 'Notice';
            case E_CORE_ERROR: return 'Core Error';
            case E_CORE_WARNING: return 'Core Warning';
            case E_COMPILE_ERROR: return 'Compile Error';
            case E_COMPILE_WARNING: return 'Compile Warning';
            case E_USER_ERROR: return 'User Error';
            case E_USER_WARNING: return 'User Warning';
            case E_USER_NOTICE: return 'User Notice';
            case E_STRICT: return 'Strict Standards';
            case E_RECOVERABLE_ERROR: return 'Recoverable Error';
            case E_DEPRECATED: return 'Deprecated';
            case E_USER_DEPRECATED: return 'User Deprecated';
            default: return 'Unknown Error';
        }
    }
}
