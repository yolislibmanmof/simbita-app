<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - USER MODEL
 * FINAL ULTRA PREMIUM - ENTERPRISE GRADE
 * =====================================================================
 * Menangani operasi CRUD user beserta profile-nya (student / lecturer).
 * Menggunakan transaksi untuk menjaga integritas data.
 */
class User
{
    // ========== ROLE CONSTANTS ==========
    public const ROLE_ADMIN    = 'admin';
    public const ROLE_LECTURER = 'lecturer';
    public const ROLE_STUDENT  = 'student';

    public const ALL_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_LECTURER,
        self::ROLE_STUDENT,
    ];

    public static function isValidRole(string $role): bool
    {
        return in_array($role, self::ALL_ROLES, true);
    }

    // =================================================================
    // FIND / READ
    // =================================================================

    /**
     * Cari user berdasarkan email (dengan profile lengkap)
     */
    public static function findByEmail(string $email): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT u.id, u.name, u.email, u.password_hash, u.role, u.is_active, 
                       u.last_login_at, u.created_at,
                       sp.student_number, sp.degree_level, sp.study_program,
                       sp.faculty, sp.enrollment_year, sp.phone AS student_phone,
                       lp.lecturer_number, lp.academic_degree, lp.department,
                       lp.expertise, lp.phone AS lecturer_phone
                FROM users u
                LEFT JOIN student_profiles sp ON u.id = sp.user_id
                LEFT JOIN lecturer_profiles lp ON u.id = lp.user_id
                WHERE u.email = ?
                LIMIT 1
            ');
            $stmt->execute([trim($email)]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Throwable $e) {
            error_log('[User::findByEmail] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cari user berdasarkan ID
     */
    public static function findById(int $id): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT u.id, u.name, u.email, u.role, u.is_active, 
                       u.last_login_at, u.created_at,
                       sp.student_number, sp.degree_level, sp.study_program,
                       sp.faculty, sp.enrollment_year, sp.phone AS student_phone,
                       lp.lecturer_number, lp.academic_degree, lp.department,
                       lp.expertise, lp.phone AS lecturer_phone
                FROM users u
                LEFT JOIN student_profiles sp ON u.id = sp.user_id
                LEFT JOIN lecturer_profiles lp ON u.id = lp.user_id
                WHERE u.id = ?
                LIMIT 1
            ');
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Throwable $e) {
            error_log('[User::findById] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check apakah email sudah terdaftar
     */
    public static function existsByEmail(string $email, ?int $excludeUserId = null): bool
    {
        try {
            $db = Database::connection();

            if ($excludeUserId !== null) {
                $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
                $stmt->execute([trim($email), $excludeUserId]);
            } else {
                $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([trim($email)]);
            }

            return $stmt->fetch() !== false;
        } catch (Throwable $e) {
            error_log('[User::existsByEmail] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check apakah NIM / NIDN sudah terdaftar
     * 
     * @param string $number NIM (untuk student) atau NIDN (untuk lecturer)
     * @param string $role 'student' atau 'lecturer'
     */
    public static function existsByNumber(string $number, string $role, ?int $excludeUserId = null): bool
    {
        try {
            $db = Database::connection();

            if ($role === self::ROLE_STUDENT) {
                $sql = 'SELECT sp.user_id FROM student_profiles sp WHERE sp.student_number = ?';
                $params = [$number];
                if ($excludeUserId !== null) {
                    $sql .= ' AND sp.user_id != ?';
                    $params[] = $excludeUserId;
                }
                $stmt = $db->prepare($sql . ' LIMIT 1');
            } elseif ($role === self::ROLE_LECTURER) {
                $sql = 'SELECT lp.user_id FROM lecturer_profiles lp WHERE lp.lecturer_number = ?';
                $params = [$number];
                if ($excludeUserId !== null) {
                    $sql .= ' AND lp.user_id != ?';
                    $params[] = $excludeUserId;
                }
                $stmt = $db->prepare($sql . ' LIMIT 1');
            } else {
                return false;
            }

            $stmt->execute($params);
            return $stmt->fetch() !== false;
        } catch (Throwable $e) {
            error_log('[User::existsByNumber] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifikasi password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        try {
            return password_verify($password, $hash);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Check apakah user aktif
     */
    public static function isActive(int $userId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT is_active FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result && (int) $result['is_active'] === 1;
        } catch (Throwable $e) {
            return false;
        }
    }

    // =================================================================
    // LISTS
    // =================================================================

    /**
     * Ambil semua user dengan filter role (untuk admin)
     * 
     * @return array List of user arrays
     */
    public static function getAllByRole(string $role): array
    {
        if (!self::isValidRole($role)) {
            return [];
        }

        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT u.*, 
                       sp.student_number, sp.degree_level, sp.study_program,
                       sp.faculty, sp.enrollment_year, sp.phone AS student_phone,
                       lp.lecturer_number, lp.academic_degree, lp.department,
                       lp.expertise, lp.phone AS lecturer_phone
                FROM users u
                LEFT JOIN student_profiles sp ON u.id = sp.user_id
                LEFT JOIN lecturer_profiles lp ON u.id = lp.user_id
                WHERE u.role = ?
                ORDER BY u.created_at DESC
            ');
            $stmt->execute([$role]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[User::getAllByRole] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ambil semua user dengan pagination + filter + search
     */
    public static function getAllWithFilters(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $db = Database::connection();

            $where = [];
            $params = [];

            if (!empty($filters['role']) && self::isValidRole($filters['role'])) {
                $where[] = 'u.role = ?';
                $params[] = $filters['role'];
            }

            if (isset($filters['is_active']) && $filters['is_active'] !== '') {
                $where[] = 'u.is_active = ?';
                $params[] = (int) $filters['is_active'];
            }

            if (!empty($filters['search'])) {
                $where[] = '(u.name LIKE ? OR u.email LIKE ?)';
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Count
            $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM users u $whereClause");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetch()['total'];

            // Fetch
            $offset = max(0, ($page - 1) * $perPage);
            $dataStmt = $db->prepare("
                SELECT u.*, 
                       sp.student_number, sp.degree_level, sp.study_program, sp.faculty,
                       lp.lecturer_number, lp.academic_degree, lp.department
                FROM users u
                LEFT JOIN student_profiles sp ON u.id = sp.user_id
                LEFT JOIN lecturer_profiles lp ON u.id = lp.user_id
                $whereClause
                ORDER BY u.created_at DESC
                LIMIT $perPage OFFSET $offset
            ");
            $dataStmt->execute($params);
            $data = $dataStmt->fetchAll();

            return [
                'data'        => $data,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ];
        } catch (Throwable $e) {
            error_log('[User::getAllWithFilters] ' . $e->getMessage());
            return ['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage, 'total_pages' => 0];
        }
    }

    // =================================================================
    // CREATE
    // =================================================================

    /**
     * Buat user baru
     *
     * @param array $data Data wajib: name, email, password, role
     * @return int User ID atau 0 jika gagal
     */
    public static function create(array $data): int
    {
        try {
            $db = Database::connection();
            $db->beginTransaction();

            // Validasi email unik
            if (self::existsByEmail($data['email'] ?? '')) {
                $db->rollBack();
                error_log('[User::create] Email sudah terdaftar: ' . ($data['email'] ?? ''));
                return 0;
            }

            // Validasi role
            $role = $data['role'] ?? self::ROLE_STUDENT;
            if (!self::isValidRole($role)) {
                $role = self::ROLE_STUDENT;
            }

            // Insert user
            $stmt = $db->prepare('
                INSERT INTO users (name, email, password_hash, role, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                trim($data['name'] ?? ''),
                trim($data['email'] ?? ''),
                password_hash($data['password'] ?? 'password123', PASSWORD_BCRYPT, ['cost' => 12]),
                $role,
                (int) ($data['is_active'] ?? 1),
            ]);
            $userId = (int) $db->lastInsertId();

            // Insert profile sesuai role
            if ($role === self::ROLE_STUDENT && !empty($data['student_number'])) {
                self::createStudentProfile($userId, $data);
            } elseif ($role === self::ROLE_LECTURER) {
                self::createLecturerProfile($userId, $data);
            }

            $db->commit();
            return $userId;
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            error_log('[User::create] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Helper: buat student profile
     */
    private static function createStudentProfile(int $userId, array $data): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                INSERT INTO student_profiles 
                    (user_id, student_number, degree_level, study_program, 
                     faculty, enrollment_year, phone)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            return $stmt->execute([
                $userId,
                trim($data['student_number'] ?? ''),
                $data['degree_level'] ?? 's1',
                trim($data['study_program'] ?? ''),
                trim($data['faculty'] ?? ''),
                (int) ($data['enrollment_year'] ?? (int) date('Y')),
                trim($data['phone'] ?? ''),
            ]);
        } catch (Throwable $e) {
            error_log('[User::createStudentProfile] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper: buat lecturer profile
     */
    private static function createLecturerProfile(int $userId, array $data): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                INSERT INTO lecturer_profiles 
                    (user_id, lecturer_number, academic_degree, department, expertise, phone)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            return $stmt->execute([
                $userId,
                trim($data['lecturer_number'] ?? ''),
                trim($data['academic_degree'] ?? ''),
                trim($data['department'] ?? ''),
                trim($data['expertise'] ?? ''),
                trim($data['phone'] ?? ''),
            ]);
        } catch (Throwable $e) {
            error_log('[User::createLecturerProfile] ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // UPDATE
    // =================================================================

    /**
     * Update data user (name, email, is_active)
     */
    public static function update(int $id, array $data): bool
    {
        try {
            $db = Database::connection();
            $db->beginTransaction();

            // Validasi email unik (kecuali dirinya sendiri)
            if (!empty($data['email']) && self::existsByEmail($data['email'], $id)) {
                $db->rollBack();
                error_log('[User::update] Email sudah terdaftar: ' . $data['email']);
                return false;
            }

            $stmt = $db->prepare('
                UPDATE users 
                SET name = ?, email = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                trim($data['name'] ?? ''),
                trim($data['email'] ?? ''),
                (int) ($data['is_active'] ?? 1),
                $id,
            ]);

            // Update profile
            $user = self::findById($id);
            if ($user) {
                if ($user['role'] === self::ROLE_STUDENT) {
                    self::updateStudentProfile($id, $data);
                } elseif ($user['role'] === self::ROLE_LECTURER) {
                    self::updateLecturerProfile($id, $data);
                }
            }

            $db->commit();
            return true;
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            error_log('[User::update] ' . $e->getMessage());
            return false;
        }
    }

    private static function updateStudentProfile(int $userId, array $data): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                UPDATE student_profiles 
                SET student_number = ?, degree_level = ?, study_program = ?,
                    faculty = ?, enrollment_year = ?, phone = ?
                WHERE user_id = ?
            ');
            return $stmt->execute([
                trim($data['student_number'] ?? ''),
                $data['degree_level'] ?? 's1',
                trim($data['study_program'] ?? ''),
                trim($data['faculty'] ?? ''),
                (int) ($data['enrollment_year'] ?? date('Y')),
                trim($data['phone'] ?? ''),
                $userId,
            ]);
        } catch (Throwable $e) {
            error_log('[User::updateStudentProfile] ' . $e->getMessage());
            return false;
        }
    }

    private static function updateLecturerProfile(int $userId, array $data): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                UPDATE lecturer_profiles 
                SET lecturer_number = ?, academic_degree = ?, department = ?,
                    expertise = ?, phone = ?
                WHERE user_id = ?
            ');
            return $stmt->execute([
                trim($data['lecturer_number'] ?? ''),
                trim($data['academic_degree'] ?? ''),
                trim($data['department'] ?? ''),
                trim($data['expertise'] ?? ''),
                trim($data['phone'] ?? ''),
                $userId,
            ]);
        } catch (Throwable $e) {
            error_log('[User::updateLecturerProfile] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update password
     */
    public static function updatePassword(int $id, string $newPassword): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                UPDATE users 
                SET password_hash = ?, updated_at = NOW() 
                WHERE id = ?
            ');
            return $stmt->execute([
                password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
                $id,
            ]);
        } catch (Throwable $e) {
            error_log('[User::updatePassword] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login timestamp
     */
    public static function updateLastLogin(int $userId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
            return $stmt->execute([$userId]);
        } catch (Throwable $e) {
            error_log('[User::updateLastLogin] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aktifkan user
     */
    public static function activate(int $userId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('UPDATE users SET is_active = 1, updated_at = NOW() WHERE id = ?');
            return $stmt->execute([$userId]);
        } catch (Throwable $e) {
            error_log('[User::activate] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Nonaktifkan user
     */
    public static function deactivate(int $userId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = ?');
            return $stmt->execute([$userId]);
        } catch (Throwable $e) {
            error_log('[User::deactivate] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update remember token (untuk fitur "remember me")
     */
    public static function updateRememberToken(int $userId, ?string $tokenHash, ?string $expiry): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                UPDATE users 
                SET remember_token = ?, remember_expiry = ?, updated_at = NOW()
                WHERE id = ?
            ');
            return $stmt->execute([$tokenHash, $expiry, $userId]);
        } catch (Throwable $e) {
            // Kolom mungkin belum ada di DB, abaikan
            return false;
        }
    }

    /**
     * Cari user berdasarkan remember token
     */
    public static function findByRememberToken(string $token): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT * FROM users 
                WHERE remember_token = ? 
                  AND remember_expiry > NOW() 
                  AND is_active = 1
                LIMIT 1
            ');
            $stmt->execute([hash('sha256', $token)]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    // =================================================================
    // DELETE
    // =================================================================

    /**
     * Hapus user beserta semua relasi (cascade)
     */
    public static function delete(int $id): bool
    {
        try {
            $db = Database::connection();
            $db->beginTransaction();

            $user = self::findById($id);
            if (!$user) {
                $db->rollBack();
                return false;
            }

            // Hapus profile
            if ($user['role'] === self::ROLE_STUDENT) {
                $db->prepare('DELETE FROM student_profiles WHERE user_id = ?')->execute([$id]);
            } elseif ($user['role'] === self::ROLE_LECTURER) {
                $db->prepare('DELETE FROM lecturer_profiles WHERE user_id = ?')->execute([$id]);
            }

            // Hapus notifikasi
            $db->prepare('DELETE FROM notifications WHERE user_id = ?')->execute([$id]);

            // Hapus user
            $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
            $result = $stmt->execute([$id]);

            $db->commit();
            return $result;
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            error_log('[User::delete] ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // STATISTICS
    // =================================================================

    /**
     * Hitung total user per role
     * @return array ['admin' => 1, 'lecturer' => 10, 'student' => 100]
     */
    public static function countByRole(): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->query('SELECT role, COUNT(*) AS total FROM users GROUP BY role');
            $result = [];
            foreach ($stmt->fetchAll() as $row) {
                $result[$row['role']] = (int) $row['total'];
            }
            foreach (self::ALL_ROLES as $role) {
                if (!isset($result[$role])) $result[$role] = 0;
            }
            return $result;
        } catch (Throwable $e) {
            return [self::ROLE_ADMIN => 0, self::ROLE_LECTURER => 0, self::ROLE_STUDENT => 0];
        }
    }

    /**
     * Hitung user aktif per role
     */
    public static function countActiveByRole(): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->query('
                SELECT role, COUNT(*) AS total 
                FROM users 
                WHERE is_active = 1 
                GROUP BY role
            ');
            $result = [];
            foreach ($stmt->fetchAll() as $row) {
                $result[$row['role']] = (int) $row['total'];
            }
            foreach (self::ALL_ROLES as $role) {
                if (!isset($result[$role])) $result[$role] = 0;
            }
            return $result;
        } catch (Throwable $e) {
            return [self::ROLE_ADMIN => 0, self::ROLE_LECTURER => 0, self::ROLE_STUDENT => 0];
        }
    }

    /**
     * Hitung total user
     */
    public static function countAll(?string $role = null): int
    {
        try {
            $db = Database::connection();
            if ($role !== null && self::isValidRole($role)) {
                $stmt = $db->prepare('SELECT COUNT(*) AS total FROM users WHERE role = ?');
                $stmt->execute([$role]);
            } else {
                $stmt = $db->query('SELECT COUNT(*) AS total FROM users');
            }
            return (int) $stmt->fetch()['total'];
        } catch (Throwable $e) {
            return 0;
        }
    }

    // =================================================================
    // BULK OPERATIONS
    // =================================================================

    /**
     * Bulk activate users
     *
     * @param array $userIds Array of user IDs
     * @return int Jumlah user yang berhasil diaktifkan
     */
    public static function bulkActivate(array $userIds): int
    {
        if (empty($userIds)) return 0;

        try {
            $db = Database::connection();
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $stmt = $db->prepare("
                UPDATE users SET is_active = 1, updated_at = NOW() 
                WHERE id IN ($placeholders)
            ");
            $stmt->execute(array_map('intval', $userIds));
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('[User::bulkActivate] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Bulk deactivate users
     */
    public static function bulkDeactivate(array $userIds): int
    {
        if (empty($userIds)) return 0;

        try {
            $db = Database::connection();
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $stmt = $db->prepare("
                UPDATE users SET is_active = 0, updated_at = NOW() 
                WHERE id IN ($placeholders)
            ");
            $stmt->execute(array_map('intval', $userIds));
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('[User::bulkDeactivate] ' . $e->getMessage());
            return 0;
        }
    }
}