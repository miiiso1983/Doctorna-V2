<?php
/**
 * Debug Controller (safe diagnostics)
 * Provides a secure endpoint to inspect session/auth state without exposing secrets.
 * Access controlled via DEBUG_KEY in .env; if not set, defaults to 'dev'.
 */

require_once APP_PATH . '/core/Controller.php';

class DebugController extends Controller {
    private function isAuthorized(): bool {
        $key = $_GET['key'] ?? '';
        $envKey = $_ENV['DEBUG_KEY'] ?? 'dev';
        return hash_equals((string)$envKey, (string)$key);
    }

    public function session() {
        if (!$this->isAuthorized()) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        // Build diagnostics
        $sessionName = session_name();
        $sid = session_id();
        $sidShort = $sid ? (substr($sid, 0, 8) . '…') : '-';
        $savePath = ini_get('session.save_path') ?: '-';
        $sessFile = ($sid && $savePath) ? rtrim($savePath, '/').'/sess_'.$sid : '';
        $sessFileExists = $sessFile ? (file_exists($sessFile) ? 'yes' : 'no') : '-';
        $sessFileSize = ($sessFile && file_exists($sessFile)) ? filesize($sessFile) : 0;
        $cookie = $_COOKIE ?? [];
        $keys = array_keys($_SESSION ?? []);
        $hasUserId = in_array('user_id', $keys, true);
        $hasRole = in_array('user_role', $keys, true);

        // Request/APP URL canonical check context
        $reqScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
        $reqHost = $_SERVER['HTTP_HOST'] ?? '';
        $appParts = parse_url(APP_URL);
        $appScheme = $appParts['scheme'] ?? 'http';
        $appHost = $appParts['host'] ?? '';
        $canonicalMismatch = ($reqHost && $appHost && (strtolower($reqHost) !== strtolower($appHost) || strtolower($reqScheme) !== strtolower($appScheme))) ? 'yes' : 'no';

        // Render simple plaintext
        header('Content-Type: text/plain; charset=utf-8');
        echo "== Session Diagnostics ==\n";
        echo "time: ".date('c')."\n";
        echo "app_env: ".(APP_ENV)."\n";
        echo "session_name: {$sessionName}\n";
        echo "session_id: {$sidShort}\n";
        echo "save_path: {$savePath}\n";
        echo "sess_file: {$sessFile}\n";
        echo "sess_file_exists: {$sessFileExists}\n";
        echo "sess_file_size: {$sessFileSize}\n";
        echo "cookie_present(DOCTORNASESSID): ".(isset($cookie['DOCTORNASESSID']) ? 'yes' : 'no')."\n";
        echo "cookie_present(PHPSESSID): ".(isset($cookie['PHPSESSID']) ? 'yes' : 'no')."\n";
        echo "auth_check: ".($this->auth->check() ? 'true' : 'false')."\n";
        echo "user_id: ".($this->auth->id() ?: '-')."\n";
        echo "session_keys: ".implode(',', $keys)."\n";
        echo "has_user_id_key: ".($hasUserId ? 'yes' : 'no')."\n";
        echo "has_user_role_key: ".($hasRole ? 'yes' : 'no')."\n";
        echo "req_host: {$reqHost}\n";
        echo "req_scheme: {$reqScheme}\n";
        echo "app_url: ".APP_URL."\n";
        echo "canonical_mismatch: {$canonicalMismatch}\n";

        // Optional: poke session to ensure it persists
        if (($this->get('poke', '0')) === '1') {
            $_SESSION['__probe'] = date('c');
            echo "probe_written: yes\n";
        }
    }
}

