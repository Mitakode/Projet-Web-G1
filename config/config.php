<?php


function get_env($key, $default = null) { //Charge les variables d'environnement mises dans le docker compose

    if (isset($_ENV[$key])) {
        $value = $_ENV[$key];

        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        return $value;
    }
}



define('APP_NAME', get_env('APP_NAME', 'CareerQuest'));
define('APP_URL', get_env('APP_URL', 'http://localhost:8000'));

// Base de données
define('DB_HOST', get_env('DB_HOST', 'db'));
define('DB_NAME', get_env('DB_NAME', 'projet_db'));
define('DB_USER', get_env('DB_USER', 'projet_user'));
define('DB_PASS', get_env('DB_PASS', 'projet_pass'));

// Chemins
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('APP_PATH', BASE_PATH . '/app');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Uploads
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);  // 5 MB

// Timezone
define('TIMEZONE', get_env('TIMEZONE','Europe/Paris'));
date_default_timezone_set('Europe/Paris');;

?>