<?php
declare(strict_types=1);

class SettingController
{
    /** Halaman pengaturan (khusus Admin) */
    public static function index(): void
    {
        $settings = SystemSetting::all();
        require dirname(__DIR__) . '/Views/admin/settings.php';
    }

    /** Simpan perubahan pengaturan (khusus Admin) */
    public static function update(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            flash_set('error', 'Sesi berakhir atau token tidak valid. Silakan coba lagi.');
            redirect('/admin/settings');   // PERBAIKAN: tanpa BASE_URL
        }

        $errors = [];
        $values = [];

        // --- Kumpulkan & validasi nilai numerik ---
        $intRules = [
            'max_upload_size_mb' => [1, 100, 'Batas ukuran upload (MB)'],
            'max_supervisors'    => [1, 5,   'Maksimal jumlah pembimbing'],
        ];
        foreach ($intRules as $key => [$min, $max, $label]) {
            $val = (int)($_POST[$key] ?? 0);
            if ($val < $min || $val > $max) {
                $errors[] = "{$label} harus berada di rentang {$min}–{$max}.";
            } else {
                $values[$key] = (string)$val;
            }
        }

        $threshold = (float)($_POST['plagiarism_threshold'] ?? -1);
        if ($threshold < 0 || $threshold > 100) {
            $errors[] = 'Ambang batas plagiasi harus berada di rentang 0–100.';
        } else {
            $values['plagiarism_threshold'] = number_format($threshold, 2, '.', '');
        }

        // --- Kumpulkan nilai boolean (checkbox) ---
        foreach (['is_registration_open', 'require_plagiarism_check'] as $key) {
            $values[$key] = (isset($_POST[$key]) && $_POST[$key] === '1') ? '1' : '0';
        }

        // --- Simpan hanya jika seluruh validasi lolos ---
        if ($errors) {
            flash_set('error', implode(' ', $errors));
        } else {
            foreach ($values as $key => $val) {
                SystemSetting::set($key, $val);
            }
            flash_set('success', 'Pengaturan sistem berhasil diperbarui.');
        }

        redirect('/admin/settings');
    }
}