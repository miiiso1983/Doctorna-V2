<?php
/**
 * Router Class
 * Handles URL routing and dispatching
 */

class Router {
    private $routes = [];
    private $middlewares = [];
    
    public function get($path, $handler, $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post($path, $handler, $middleware = []) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put($path, $handler, $middleware = []) {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function delete($path, $handler, $middleware = []) {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    private function addRoute($method, $path, $handler, $middleware = []) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = $this->getPath();
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestPath)) {
                // Extract parameters from URL
                $params = $this->extractParams($route['path'], $requestPath);
                
                // Run middleware
                foreach ($route['middleware'] as $middleware) {
                    if (!$this->runMiddleware($middleware)) {
                        return;
                    }
                }
                
                // Handle the route
                $this->handleRoute($route['handler'], $params);
                return;
            }
        }
        
        // No route found
        $this->handleNotFound();
    }
    
    private function getPath() {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $base = parse_url(APP_URL, PHP_URL_PATH) ?: '';
        // Strip base path (e.g., if APP_URL includes a subdirectory like /public)
        if ($base && $base !== '/' && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }
        // Normalize
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }

    private function matchPath($routePath, $requestPath) {
        // Convert route path to regex pattern
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestPath);
    }
    
    private function extractParams($routePath, $requestPath) {
        $params = [];
        
        // Extract parameter names from route path
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Extract parameter values from request path
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches); // Remove full match
            
            for ($i = 0; $i < count($paramNames[1]); $i++) {
                if (isset($matches[$i])) {
                    $params[$paramNames[1][$i]] = $matches[$i];
                }
            }
        }
        
        return $params;
    }
    
    private function runMiddleware($middleware) {
        if (is_string($middleware)) {
            $middlewareClass = $middleware;
            if (class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                return $middlewareInstance->handle();
            }
        } elseif (is_callable($middleware)) {
            return $middleware();
        }
        
        return true;
    }
    
    private function handleRoute($handler, $params = []) {
        if (is_string($handler)) {
            list($controllerName, $methodName) = explode('@', $handler);
            
            $controllerFile = CONTROLLERS_PATH . '/' . $controllerName . '.php';
            
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                
                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    
                    if (method_exists($controller, $methodName)) {
                        // Pass parameters to the method
                        call_user_func_array([$controller, $methodName], $params);
                    } else {
                        $this->handleError("Method {$methodName} not found in {$controllerName}");
                    }
                } else {
                    $this->handleError("Controller {$controllerName} not found");
                }
            } else {
                $this->handleError("Controller file {$controllerFile} not found");
            }
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
        }
    }
    
    private function handleNotFound() {
        http_response_code(404);
        
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            include VIEWS_PATH . '/errors/404.php';
        } else {
            echo "<h1>404 - Page Not Found</h1>";
            echo "<p>الصفحة المطلوبة غير موجودة</p>";
        }
    }
    
    private function handleError($message) {
        http_response_code(500);
        
        if (APP_DEBUG) {
            echo "<h1>Router Error</h1>";
            echo "<p>{$message}</p>";
        } else {
            if (file_exists(VIEWS_PATH . '/errors/500.php')) {
                include VIEWS_PATH . '/errors/500.php';
            } else {
                echo "<h1>500 - Internal Server Error</h1>";
                echo "<p>حدث خطأ في النظام، يرجى المحاولة لاحقاً</p>";
            }
        }
    }
    
    public function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    public static function url($path = '') {
        return APP_URL . '/' . ltrim($path, '/');
    }

    public static function asset($path) {
        $relative = ltrim($path, '/');
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        $publicReal = rtrim(realpath(PUBLIC_PATH) ?: PUBLIC_PATH, '/');
        $docRootReal = $docRoot ? rtrim((realpath($docRoot) ?: $docRoot), '/') : '';
        $isPublicWebroot = ($docRootReal && $publicReal && $docRootReal === $publicReal);
        $prefix = $isPublicWebroot ? '' : 'public/';
        return APP_URL . '/' . $prefix . $relative;
    }
}
