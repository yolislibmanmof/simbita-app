<?php
declare(strict_types=1);

class SystemSetting
{
    private static array $cache = [];
    private static bool $loaded = false;

    /** Muat seluruh pengaturan sekali per request (cache memori). */
    public static function all(): array
    {
        if (!self::$loaded) {
            $db = Database::connection();
            $stmt = $db->query('SELECT setting_key, setting_value FROM system_settings');
            foreach ($stmt->fetchAll() as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
            self::$loaded = true;
        }
        return self::$cache;
    }

    public static function get(string $key, $default = null)
    {
        $all = self::all();
        return array_key_exists($key, $all) && $all[$key] !== null ? $all[$key] : $default;
    }

    public static function set(string $key, $value, ?string $description = null): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            INSERT INTO system_settings (setting_key, setting_value, description)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ');
        $stmt->execute([$key, (string)$value, $description]);
        self::$cache[$key] = (string)$value;
    }
}