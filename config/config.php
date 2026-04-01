<?php

function get_env($key, $default = null) {
    if (isset($_ENV[$key])) {
        $value = $_ENV[$key];

        if ($value === 'true') return true;
        if ($value === 'false') return false;
        if (is_numeric($value)) return (int)$value;

        return $value;
    }
    return $default;
}



define('APP_NAME', get_env('APP_NAME', 'CareerQuest'));
define('APP_URL', get_env('APP_URL', 'http://localhost:8000'));

// Database
define('DB_HOST', get_env('DB_HOST', '90.54.20.90'));
define('DB_NAME', get_env('DB_NAME', 'projet_db'));      
define('DB_USER', get_env('DB_USER', 'projet_user'));   
define('DB_PASS', get_env('DB_PASS', 'projet_pass'));   

// Paths
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('APP_PATH', BASE_PATH . '/app');
// Stockage des uploads hors du dossier public pour éviter l'accès direct.
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// Uploads
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);  // 5 MB

// Timezone
define('TIMEZONE', get_env('TIMEZONE','Europe/Paris'));
date_default_timezone_set(TIMEZONE);

?>