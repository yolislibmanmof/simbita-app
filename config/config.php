<?php
declare(strict_types=1);

define('BASE_URL', 'http://localhost/simbita-app/public');
define('APP_NAME', 'SIMBITA');

define('DB_HOST', 'localhost');
define('DB_NAME', 'simbita_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('STORAGE_PATH', dirname(__DIR__) . '/storage/documents');
define('MAX_UPLOAD_BYTES', 25 * 1024 * 1024);
define('ALLOWED_MIME', ['application/pdf']);
