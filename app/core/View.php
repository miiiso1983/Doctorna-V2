<?php
/**
 * View Class
 * Handles view rendering and templating
 */

class View {
    private $viewsPath;
    private $layoutsPath;
    private $data = [];

    public function __construct() {
        $this->viewsPath = VIEWS_PATH;
        $this->layoutsPath = VIEWS_PATH . '/layouts';
    }

    /**
     * Render a view
     */
    public function render($view, $data = []) {
        $this->data = array_merge($this->data, $data);

        $viewFile = $this->viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }

        // Extract data to variables
        extract($this->data);

        // Start output buffering
        ob_start();

        // Include the view file
        include $viewFile;

        // Get the content
        $content = ob_get_clean();

        echo $content;
    }

    /**
     * Render a view with layout
     */
    public function renderWithLayout($view, $data = [], $layout = 'main') {
        $this->data = array_merge($this->data, $data);

        $viewFile = $this->viewsPath . '/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = $this->layoutsPath . '/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }

        if (!file_exists($layoutFile)) {
            throw new Exception("Layout file not found: {$layoutFile}");
        }

        // Extract data to variables
        extract($this->data);

        // Start output buffering for content
        ob_start();

        // Include the view file
        include $viewFile;

        // Get the content
        $content = ob_get_clean();

        // Buffer layout output to ensure no leading BOM/whitespace (prevents Quirks Mode)
        ob_start();
        include $layoutFile;
        $layoutOutput = ob_get_clean();

        // Trim potential UTF-8 BOM and leading whitespace before output
        $bom = "\xEF\xBB\xBF";
        if (strpos($layoutOutput, $bom) === 0) {
            $layoutOutput = substr($layoutOutput, 3);
        }
        echo ltrim($layoutOutput);
    }

    /**
     * Set global data for all views
     */
    public function share($key, $value = null) {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * Include a partial view
     */
    public function partial($view, $data = []) {
        $viewFile = $this->viewsPath . '/partials/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("Partial view file not found: {$viewFile}");
        }

        // Merge with existing data
        $data = array_merge($this->data, $data);

        // Extract data to variables
        extract($data);

        // Include the partial
        include $viewFile;
    }

    /**
     * Escape HTML (null-safe and robust)
     */
    public function escape($string) {
        if ($string === null) {
            return '';
        }
        if (is_bool($string)) {
            $string = $string ? '1' : '0';
        } elseif (is_array($string)) {
            $string = json_encode($string, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Generate URL
     */
    public function url($path = '') {
        $base = rtrim(APP_URL, '/');
        return $base . '/' . ltrim($path, '/');
    }

    /**
     * Generate asset URL (supports both docroots: project root or /public)
     */
    public function asset($path) {
        $relative = ltrim($path, '/');
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        $publicReal = rtrim(realpath(PUBLIC_PATH) ?: PUBLIC_PATH, '/');
        $docRootReal = $docRoot ? rtrim((realpath($docRoot) ?: $docRoot), '/') : '';
        // If the web server's document root equals PUBLIC_PATH, don't prefix with 'public/'
        $isPublicWebroot = ($docRootReal && $publicReal && $docRootReal === $publicReal);
        $prefix = $isPublicWebroot ? '' : 'public/';
        $base = rtrim(APP_URL, '/');
        return $base . '/' . $prefix . $relative;
    }
    /**
     * Build URL with query parameters
     */
    public function buildUrl($path, $params = []) {
        $base = $this->url($path);
        if (empty($params) || !is_array($params)) {
            return $base;
        }
        // Remove null/empty values
        $params = array_filter($params, function ($v) { return !($v === null || $v === ''); });
        if (empty($params)) return $base;
        return $base . '?' . http_build_query($params);
    }

    /**
     * Backward-compat helpers for status label/badge
     */
    public function statusBadgeClass($status) { return $this->getStatusBadge($status); }
    public function statusLabel($status) { return $this->getStatusText($status); }


    /**
     * Get CSRF token
     */
    public function csrf() {
        return CSRF::token();
    }

    /**
     * Get CSRF input field
     */
    public function csrfField() {
        $token = $this->csrf();
        return "<input type='hidden' name='" . CSRF_TOKEN_NAME . "' value='{$token}'>";
    }

    /**
     * Get current user
     */
    public function user() {
        $auth = new Auth();
        return $auth->user();
    }

    /**
     * Check if user is authenticated
     */
    public function auth() {
        $auth = new Auth();
        return $auth->check();
    }

    /**
     * Check if user has role
     */
    public function hasRole($role) {
        $auth = new Auth();
        return $auth->hasRole($role);
    }

    /**
     * Format date
     */
    public function formatDate($date, $format = 'Y-m-d H:i:s') {
        if (!$date) return '';

        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    }

    /**
     * Format Arabic date
     */
    public function formatArabicDate($date) {
        if (!$date) return '';

        $dateTime = new DateTime($date);
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        $day = $dateTime->format('d');
        $month = $months[(int)$dateTime->format('m')];
        $year = $dateTime->format('Y');

        return "{$day} {$month} {$year}";
    }
    /**
     * Currency helpers (Iraqi Dinar)
     */
    public function currencyCode() {
        return 'IQD';
    }
    public function currencySymbol() {
        return 'د.ع';
    }
    public function formatCurrency($amount, $withSymbol = true) {
        $amount = is_numeric($amount) ? (float)$amount : 0.0;
        // Prefer intl if available
        if (class_exists('NumberFormatter')) {
            try {
                $fmt = new NumberFormatter('ar_IQ', NumberFormatter::CURRENCY);
                $formatted = $fmt->formatCurrency($amount, 'IQD');
                if ($withSymbol) return $formatted;
                // Remove currency symbol if requested
                $symbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
                return trim(str_replace($symbol, '', $formatted));
            } catch (Throwable $e) {
                // Fallback below
            }
        }
        // Fallback: no intl extension
        $formatted = number_format($amount, 0, '.', ',');
        return $withSymbol ? ($formatted . ' ' . $this->currencySymbol()) : $formatted;
    }


    /**
     * Human friendly time ago (Arabic)
     */
    public function timeAgo($datetime) {
        if (!$datetime) return '';
        $ts = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
        if ($ts === false) return '';
        $diff = time() - $ts;
        if ($diff < 60) return 'الآن';
        $minutes = floor($diff / 60);
        if ($minutes < 60) return $minutes === 1 ? 'منذ دقيقة' : "منذ {$minutes} دقائق";
        $hours = floor($minutes / 60);
        if ($hours < 24) return $hours === 1 ? 'منذ ساعة' : "منذ {$hours} ساعات";
        $days = floor($hours / 24);
        if ($days < 7) return $days === 1 ? 'أمس' : "منذ {$days} أيام";
        $weeks = floor($days / 7);
        if ($weeks < 5) return $weeks === 1 ? 'منذ أسبوع' : "منذ {$weeks} أسابيع";
        $months = floor($days / 30);
        if ($months < 12) return $months === 1 ? 'منذ شهر' : "منذ {$months} أشهر";
        $years = floor($days / 365);
        return $years === 1 ? 'منذ سنة' : "منذ {$years} سنوات";
    }

    /**
     * Get status color (for CSS accents)
     */
    public function getStatusColor($status) {
        $map = [
            'pending' => '#ffc107',   // warning
            'confirmed' => '#28a745', // success
            'completed' => '#0d6efd', // primary
            'cancelled' => '#dc3545', // danger
        ];
        return $map[$status] ?? '#6c757d'; // secondary default
    }

    /**
     * Get Bootstrap badge class for status
     */
    public function getStatusBadge($status) {
        $map = [
            'pending' => 'warning',
            'confirmed' => 'success',
            'completed' => 'primary',
            'cancelled' => 'danger',
        ];
        return $map[$status] ?? 'secondary';
    }

    /**
     * Get Arabic status text
     */
    public function getStatusText($status) {
        $map = [
            'pending' => 'في الانتظار',
            'confirmed' => 'مؤكد',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
        ];
        return $map[$status] ?? $status;
    }

    /**
     * Truncate text
     */
    public function truncate($text, $length = 100, $suffix = '...') {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . $suffix;
    }

    /**
     * Get flash message
     */
    public function flash($type) {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    /**
     * Check if flash message exists
     */
    public function hasFlash($type) {
        return isset($_SESSION['flash'][$type]);
    }

    /**
     * Get old input value
     */
    public function old($key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }

    /**
     * Get validation error
     */
    public function error($key) {
        return $_SESSION['errors'][$key] ?? null;
    }

    /**
     * Check if validation error exists
     */
    public function hasError($key) {
        return isset($_SESSION['errors'][$key]);
    }

    /**
     * Include component
     */
    public function component($component, $data = []) {
        $componentFile = $this->viewsPath . '/components/' . str_replace('.', '/', $component) . '.php';

        if (!file_exists($componentFile)) {
            throw new Exception("Component file not found: {$componentFile}");
        }

        // Merge with existing data
        $data = array_merge($this->data, $data);

        // Extract data to variables
        extract($data);

        // Include the component
        include $componentFile;
    }

    /**
     * Generate pagination links
     */
    public function paginate($pagination, $url = '') {
        if ($pagination['last_page'] <= 1) {
            return '';
        }

        $currentPage = $pagination['current_page'];
        $lastPage = $pagination['last_page'];
        $url = $url ?: $_SERVER['REQUEST_URI'];

        // Remove existing page parameter
        $url = preg_replace('/[?&]page=\d+/', '', $url);
        $separator = strpos($url, '?') !== false ? '&' : '?';

        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

        // Previous button
        if ($currentPage > 1) {
            $prevUrl = $url . $separator . 'page=' . ($currentPage - 1);
            $html .= "<li class='page-item'><a class='page-link' href='{$prevUrl}'>السابق</a></li>";
        } else {
            $html .= "<li class='page-item disabled'><span class='page-link'>السابق</span></li>";
        }

        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($lastPage, $currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            $pageUrl = $url . $separator . 'page=' . $i;
            $active = $i === $currentPage ? 'active' : '';
            $html .= "<li class='page-item {$active}'><a class='page-link' href='{$pageUrl}'>{$i}</a></li>";
        }

        // Next button
        if ($currentPage < $lastPage) {
            $nextUrl = $url . $separator . 'page=' . ($currentPage + 1);
            $html .= "<li class='page-item'><a class='page-link' href='{$nextUrl}'>التالي</a></li>";
        } else {
            $html .= "<li class='page-item disabled'><span class='page-link'>التالي</span></li>";
        }

        $html .= '</ul></nav>';

        return $html;
    }
}
