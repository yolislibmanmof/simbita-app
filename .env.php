<?php
/**
 * SIMBITA - Environment Variables
 * 
 * PENTING: File ini TIDAK BOLEH di-commit ke Git.
 * Pastikan file ini ada di dalam .gitignore
 */

return [
    'APP_DEBUG' => 'true',
    'APP_URL'   => 'http://localhost/simbita-app/public',
    'APP_NAME'  => 'SIMBITA',
    
    'DB_HOST'   => 'localhost',
    'DB_NAME'   => 'simbita_db',
    'DB_USER'   => 'root',
    'DB_PASS'   => '', // Kosongkan jika tidak ada password di lokal
    'DB_PORT'   => '3306',
];