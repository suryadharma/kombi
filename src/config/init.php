<?php
/**
 * Application Initialization
 * 
 * This file is used by CLI scripts to bootstrap the application
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
$debugMode = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
error_reporting(E_ALL);
ini_set('display_errors', $debugMode ? '1' : '0');

// Timezone
$timezone = getenv('APP_TIMEZONE') ?: 'Asia/Jakarta';
if (!in_array($timezone, timezone_identifiers_list(), true)) {
    $timezone = 'Asia/Jakarta';
}
date_default_timezone_set($timezone);

// Define base path (for CLI scripts, we need to set this differently)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/..');
    define('PUBLIC_PATH', BASE_PATH . '/public');
    define('CONTROLLER_PATH', BASE_PATH . '/controllers');
    define('MODEL_PATH', BASE_PATH . '/models');
    define('VIEW_PATH', BASE_PATH . '/views');
    define('CONFIG_PATH', BASE_PATH . '/config');
    define('HELPER_PATH', BASE_PATH . '/helpers');
    define('MIDDLEWARE_PATH', BASE_PATH . '/middleware');
    define('STORAGE_PATH', BASE_PATH . '/storage');
}

// Load core classes
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/helpers/Settings.php';
require_once BASE_PATH . '/helpers/AppSettings.php';
require_once BASE_PATH . '/helpers/DebugLog.php';
