<?php
/**
 * SIMBITA - Cron Job Reminder Deadline
 * 
 * Jalankan via CLI atau cron:
 *   php cron_reminder.php
 * 
 * Cron schedule (Linux):
 *   0 8 * * * cd /path/to/simbita-app/public && php cron_reminder.php >> /tmp/simbita-cron.log 2>&1
 * 
 * Windows Task Scheduler:
 *   Trigger: Daily at 08:00 AM
 *   Action: php.exe C:\laragon\www\simbita-app\public\cron_reminder.php
 */

declare(strict_types=1);

// Security: hanya bisa dijalankan via CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Akses ditolak. Script ini hanya bisa dijalankan via CLI.');
}

// Load core
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/Core/Helpers.php';
require_once dirname(__DIR__) . '/app/Core/Csrf.php';
require_once dirname(__DIR__) . '/app/Core/Auth.php';
require_once dirname(__DIR__) . '/app/Models/User.php';
require_once dirname(__DIR__) . '/app/Models/AcademicWork.php';
require_once dirname(__DIR__) . '/app/Models/Notification.php';
require_once dirname(__DIR__) . '/app/Controllers/MilestoneController.php';

// Jalankan scan
echo "[" . date('Y-m-d H:i:s') . "] Starting milestone deadline scan...\n";

try {
    $result = MilestoneController::runDeadlineScan();
    echo sprintf(
        "[%s] ✅ Scan selesai. Scanned: %d, Notifications sent: %d\n",
        date('Y-m-d H:i:s'),
        $result['scanned'],
        $result['sent']
    );
    
    // Log detail
    foreach ($result['details'] as $d) {
        echo sprintf(
            "  - %s (%s): %d hari lagi [%s]\n",
            $d['milestone'],
            $d['student'],
            $d['days_left'],
            $d['urgency']
        );
    }
    
    exit(0);
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}