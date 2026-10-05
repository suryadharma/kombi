<?php

class BaseController {
    
    protected function render($view, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewPath = VIEW_PATH . '/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "View not found: " . $viewPath;
        }
        
        // Get the content and clean buffer
        $content = ob_get_clean();
        
        // Include layout if exists
        $layoutPath = VIEW_PATH . '/layouts/main.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }
    
    protected function redirect($url) {
        header("Location: " . $url);
        exit();
    }
    
    protected function jsonResponse($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    protected function getUserRole() {
        if (isset($_SESSION['active_role'])) {
            return $_SESSION['active_role'];
        }
        return isset($_SESSION['role']) ? $_SESSION['role'] : null;
    }
    
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            // Check if this is an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            // Also check for JSON Accept header (fetch API)
            $acceptsJson = isset($_SERVER['HTTP_ACCEPT']) &&
                           strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            
            if ($isAjax || $acceptsJson) {
                // Return JSON error for AJAX requests
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'unauthorized',
                    'message' => 'Sesi telah berakhir. Silakan login kembali.'
                ]);
                exit;
            } else {
                // Redirect for regular page requests
                $this->redirect('/login');
            }
        }
    }

    protected function setFlash(string $key, string $message): void {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$key] = $message;
    }

    protected function getFlash(string $key): ?string {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }

    protected function clearFlash(string $key): void {
        if (isset($_SESSION['flash'][$key])) {
            unset($_SESSION['flash'][$key]);
        }
    }
}
