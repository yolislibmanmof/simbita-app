<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - ACADEMIC WORK MODEL
 * FINAL ULTRA PREMIUM - ENTERPRISE GRADE
 * =====================================================================
 * Menangani semua operasi CRUD tugas akhir (skripsi/tesis/disertasi)
 * beserta relasinya dengan mahasiswa, dosen pembimbing, dan tipe tugas.
 */
class AcademicWork
{
    // ========== STATUS CONSTANTS ==========
    public const STATUS_DRAFT             = 'draft';
    public const STATUS_PROPOSAL          = 'proposal';
    public const STATUS_FEASIBILITY_TEST  = 'feasibility_test';
    public const STATUS_WRITING           = 'writing';
    public const STATUS_PROGRESS_EXAM     = 'progress_exam';
    public const STATUS_REVISION          = 'revision';
    public const STATUS_CLOSED_EXAM       = 'closed_exam';
    public const STATUS_READY_FOR_DEFENSE = 'ready_for_defense';
    public const STATUS_DEFENSE           = 'defense';
    public const STATUS_COMPLETED         = 'completed';
    public const STATUS_CANCELLED         = 'cancelled';

    public const ALL_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PROPOSAL,
        self::STATUS_FEASIBILITY_TEST,
        self::STATUS_WRITING,
        self::STATUS_PROGRESS_EXAM,
        self::STATUS_REVISION,
        self::STATUS_CLOSED_EXAM,
        self::STATUS_READY_FOR_DEFENSE,
        self::STATUS_DEFENSE,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    // Status yang dianggap "aktif / dalam proses"
    public const ACTIVE_STATUSES = [
        self::STATUS_PROPOSAL,
        self::STATUS_FEASIBILITY_TEST,
        self::STATUS_WRITING,
        self::STATUS_PROGRESS_EXAM,
        self::STATUS_CLOSED_EXAM,
        self::STATUS_READY_FOR_DEFENSE,
        self::STATUS_DEFENSE,
    ];

    // ========== HELPER: Validasi Status ==========
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::ALL_STATUSES, true);
    }

    public static function isActiveStatus(string $status): bool
    {
        return in_array($status, self::ACTIVE_STATUSES, true);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT             => 'Draft',
            self::STATUS_PROPOSAL          => 'Proposal',
            self::STATUS_FEASIBILITY_TEST  => 'Uji Kelayakan',
            self::STATUS_WRITING           => 'Penulisan',
            self::STATUS_PROGRESS_EXAM     => 'Ujian Kemajuan',
            self::STATUS_REVISION          => 'Revisi',
            self::STATUS_CLOSED_EXAM       => 'Ujian Tertutup',
            self::STATUS_READY_FOR_DEFENSE => 'Siap Sidang',
            self::STATUS_DEFENSE           => 'Sidang',
            self::STATUS_COMPLETED         => 'Selesai',
            self::STATUS_CANCELLED         => 'Dibatalkan',
            default                        => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    // =================================================================
    // CREATE
    // =================================================================

    /**
     * Buat academic work baru
     *
     * @param array $data Data wajib: student_id, work_type_id, title
     * @return int ID dari record yang dibuat, atau 0 jika gagal
     */
    public static function create(array $data): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                INSERT INTO academic_works 
                    (student_id, work_type_id, title, abstract, keywords, status, started_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                (int) ($data['student_id'] ?? 0),
                (int) ($data['work_type_id'] ?? 0),
                trim($data['title'] ?? ''),
                trim($data['abstract'] ?? '') ?: null,
                trim($data['keywords'] ?? '') ?: null,
                self::isValidStatus($data['status'] ?? '') ? $data['status'] : self::STATUS_DRAFT,
                $data['started_at'] ?? date('Y-m-d'),
            ]);
            return (int) $db->lastInsertId();
        } catch (Throwable $e) {
            error_log('[AcademicWork::create] ' . $e->getMessage());
            return 0;
        }
    }

    // =================================================================
    // READ - Single
    // =================================================================

    /**
     * Ambil detail academic work lengkap dengan supervisor
     * (Untuk form edit / detail page)
     */
    public static function getWithSupervisors(int $id): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT aw.*, 
                       sp.id AS student_profile_id,
                       sp.user_id AS student_user_id,
                       sp.student_number, sp.study_program, sp.degree_level,
                       sp.faculty, sp.enrollment_year,
                       u.name AS student_name,
                       wt.code AS work_type_code, wt.name AS work_type_name
                FROM academic_works aw
                JOIN student_profiles sp ON aw.student_id = sp.id
                JOIN users u ON sp.user_id = u.id
                JOIN work_types wt ON aw.work_type_id = wt.id
                WHERE aw.id = ?
            ');
            $stmt->execute([$id]);
            $work = $stmt->fetch();

            if (!$work) return null;

            // Ambil supervisor dengan lecturer_id (penting untuk pre-select dropdown)
            $stmt = $db->prepare('
                SELECT sa.lecturer_id, sa.assignment_role, sa.is_active,
                       u.name AS lecturer_name, lp.academic_degree
                FROM supervision_assignments sa
                JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                JOIN users u ON lp.user_id = u.id
                WHERE sa.academic_work_id = ? AND sa.is_active = 1
            ');
            $stmt->execute([$id]);
            $work['supervisors'] = $stmt->fetchAll();

            return $work;
        } catch (Throwable $e) {
            error_log('[AcademicWork::getWithSupervisors] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil academic work berdasarkan ID saja (tanpa join)
     */
    public static function findById(int $id): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT * FROM academic_works WHERE id = ?');
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Throwable $e) {
            error_log('[AcademicWork::findById] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil academic works untuk student tertentu (by student_profile.id)
     */
    public static function getByStudentId(int $studentProfileId): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT aw.*, wt.code AS work_type_code, wt.name AS work_type_name
                FROM academic_works aw
                JOIN work_types wt ON aw.work_type_id = wt.id
                WHERE aw.student_id = ?
                ORDER BY aw.created_at DESC
                LIMIT 1
            ');
            $stmt->execute([$studentProfileId]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Throwable $e) {
            error_log('[AcademicWork::getByStudentId] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil semua academic works milik student (jika lebih dari 1)
     */
    public static function getAllByStudentId(int $studentProfileId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT aw.*, wt.code AS work_type_code, wt.name AS work_type_name
                FROM academic_works aw
                JOIN work_types wt ON aw.work_type_id = wt.id
                WHERE aw.student_id = ?
                ORDER BY aw.created_at DESC
            ');
            $stmt->execute([$studentProfileId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[AcademicWork::getAllByStudentId] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper: cari academic work berdasarkan user_id mahasiswa
     * (karena relasi ke users melalui student_profiles)
     */
    public static function findByStudentUserId(int $userId): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT aw.*, wt.code AS work_type_code, wt.name AS work_type_name
                FROM academic_works aw
                JOIN student_profiles sp ON aw.student_id = sp.id
                JOIN work_types wt ON aw.work_type_id = wt.id
                WHERE sp.user_id = ?
                ORDER BY aw.created_at DESC
                LIMIT 1
            ');
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Throwable $e) {
            error_log('[AcademicWork::findByStudentUserId] ' . $e->getMessage());
            return null;
        }
    }

    // =================================================================
    // READ - Lists
    // =================================================================

    /**
     * Ambil semua academic works dengan info student & work type
     * (Untuk halaman daftar admin)
     */
    public static function getAllWithDetails(): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT aw.*, 
                       sp.student_number, sp.study_program, sp.degree_level,
                       sp.faculty,
                       u.name AS student_name,
                       wt.code AS work_type_code, wt.name AS work_type_name
                FROM academic_works aw
                JOIN student_profiles sp ON aw.student_id = sp.id
                JOIN users u ON sp.user_id = u.id
                JOIN work_types wt ON aw.work_type_id = wt.id
                ORDER BY aw.created_at DESC
            ');
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[AcademicWork::getAllWithDetails] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ambil academic works dengan FILTER + SEARCH + PAGINATION
     * (Untuk halaman daftar dengan fitur advanced)
     *
     * @param array $filters ['status', 'work_type_id', 'degree_level', 'enrollment_year', 'search']
     * @param int $page Halaman saat ini (dimulai dari 1)
     * @param int $perPage Jumlah record per halaman
     * @return array ['data' => [...], 'total' => int, 'page' => int, 'per_page' => int, 'total_pages' => int]
     */
    public static function getAllWithFilters(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $db = Database::connection();

            $where = [];
            $params = [];

            // Filter status
            if (!empty($filters['status']) && self::isValidStatus($filters['status'])) {
                $where[] = 'aw.status = ?';
                $params[] = $filters['status'];
            }

            // Filter work type
            if (!empty($filters['work_type_id'])) {
                $where[] = 'aw.work_type_id = ?';
                $params[] = (int) $filters['work_type_id'];
            }

            // Filter degree level
            if (!empty($filters['degree_level'])) {
                $where[] = 'sp.degree_level = ?';
                $params[] = $filters['degree_level'];
            }

            // Filter enrollment year
            if (!empty($filters['enrollment_year'])) {
                $where[] = 'sp.enrollment_year = ?';
                $params[] = (int) $filters['enrollment_year'];
            }

            // Search (title, student name, student number)
            if (!empty($filters['search'])) {
                $where[] = '(aw.title LIKE ? OR u.name LIKE ? OR sp.student_number LIKE ?)';
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Count total
            $countStmt = $db->prepare("
                SELECT COUNT(*) AS total 
                FROM academic_works aw
                JOIN student_profiles sp ON aw.student_id = sp.id
                JOIN users u ON sp.user_id = u.id
                $whereClause
            ");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetch()['total'];

            // Fetch data with pagination
            $offset = max(0, ($page - 1) * $perPage);
            $dataStmt = $db->prepare("
                SELECT aw.*, 
                       sp.student_number, sp.study_program, sp.degree_level, sp.faculty,
                       u.name AS student_name,
                       wt.code AS work_type_code, wt.name AS work_type_name
                FROM academic_works aw
                JOIN student_profiles sp ON aw.student_id = sp.id
                JOIN users u ON sp.user_id = u.id
                JOIN work_types wt ON aw.work_type_id = wt.id
                $whereClause
                ORDER BY aw.created_at DESC
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
            error_log('[AcademicWork::getAllWithFilters] ' . $e->getMessage());
            return [
                'data'        => [],
                'total'       => 0,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => 0,
            ];
        }
    }

    /**
     * Ambil academic works yang di-assign ke lecturer tertentu
     * (Untuk dashboard dosen)
     */
    public static function getByLecturerId(int $lecturerProfileId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT aw.*, 
                       sp.student_number, sp.study_program, sp.degree_level,
                       u.name AS student_name,
                       wt.code AS work_type_code, wt.name AS work_type_name,
                       sa.assignment_role
                FROM academic_works aw
                JOIN supervision_assignments sa ON aw.id = sa.academic_work_id
                JOIN student_profiles sp ON aw.student_id = sp.id
                JOIN users u ON sp.user_id = u.id
                JOIN work_types wt ON aw.work_type_id = wt.id
                WHERE sa.lecturer_id = ? AND sa.is_active = 1
                ORDER BY aw.created_at DESC
            ');
            $stmt->execute([$lecturerProfileId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[AcademicWork::getByLecturerId] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ambil N tugas akhir terbaru (untuk dashboard)
     */
    public static function getRecent(int $limit = 5): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT aw.*, 
                       sp.student_number, sp.study_program,
                       u.name AS student_name,
                       wt.name AS work_type_name
                FROM academic_works aw
                JOIN student_profiles sp ON aw.student_id = sp.id
                JOIN users u ON sp.user_id = u.id
                JOIN work_types wt ON aw.work_type_id = wt.id
                ORDER BY aw.created_at DESC
                LIMIT ?
            ');
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[AcademicWork::getRecent] ' . $e->getMessage());
            return [];
        }
    }

    // =================================================================
    // COUNT / STATISTICS
    // =================================================================

    /**
     * Hitung total academic works berdasarkan status
     */
    public static function countByStatus(string $status): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM academic_works WHERE status = ?');
            $stmt->execute([$status]);
            return (int) $stmt->fetch()['total'];
        } catch (Throwable $e) {
            error_log('[AcademicWork::countByStatus] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Hitung total semua academic works
     */
    public static function countAll(): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->query('SELECT COUNT(*) AS total FROM academic_works');
            return (int) $stmt->fetch()['total'];
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Hitung academic works aktif (dalam proses)
     */
    public static function countActive(): int
    {
        try {
            $db = Database::connection();
            $placeholders = implode(',', array_fill(0, count(self::ACTIVE_STATUSES), '?'));
            $stmt = $db->prepare("SELECT COUNT(*) AS total FROM academic_works WHERE status IN ($placeholders)");
            $stmt->execute(self::ACTIVE_STATUSES);
            return (int) $stmt->fetch()['total'];
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Hitung total per status (return array associative)
     * @return array ['draft' => 5, 'proposal' => 3, ...]
     */
    public static function countGroupedByStatus(): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->query('SELECT status, COUNT(*) AS total FROM academic_works GROUP BY status');
            $result = [];
            foreach ($stmt->fetchAll() as $row) {
                $result[$row['status']] = (int) $row['total'];
            }
            // Pastikan semua status ada di result (nilai 0 jika tidak ada)
            foreach (self::ALL_STATUSES as $status) {
                if (!isset($result[$status])) {
                    $result[$status] = 0;
                }
            }
            return $result;
        } catch (Throwable $e) {
            error_log('[AcademicWork::countGroupedByStatus] ' . $e->getMessage());
            $result = [];
            foreach (self::ALL_STATUSES as $status) {
                $result[$status] = 0;
            }
            return $result;
        }
    }

    /**
     * Hitung tugas akhir milik lecturer tertentu
     */
    public static function countByLecturerId(int $lecturerProfileId): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT COUNT(*) AS total 
                FROM academic_works aw
                JOIN supervision_assignments sa ON aw.id = sa.academic_work_id
                WHERE sa.lecturer_id = ? AND sa.is_active = 1
            ');
            $stmt->execute([$lecturerProfileId]);
            return (int) $stmt->fetch()['total'];
        } catch (Throwable $e) {
            return 0;
        }
    }

    // =================================================================
    // UPDATE
    // =================================================================

    /**
     * Update academic work (semua field)
     */
    public static function update(int $id, array $data): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                UPDATE academic_works 
                SET title = ?, abstract = ?, keywords = ?, status = ?, 
                    work_type_id = ?, started_at = ?, updated_at = NOW()
                WHERE id = ?
            ');
            return $stmt->execute([
                trim($data['title'] ?? ''),
                trim($data['abstract'] ?? '') ?: null,
                trim($data['keywords'] ?? '') ?: null,
                self::isValidStatus($data['status'] ?? '') ? $data['status'] : self::STATUS_DRAFT,
                (int) ($data['work_type_id'] ?? 0),
                $data['started_at'] ?? date('Y-m-d'),
                $id,
            ]);
        } catch (Throwable $e) {
            error_log('[AcademicWork::update] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update status academic work
     */
    public static function updateStatus(int $id, string $status): bool
    {
        if (!self::isValidStatus($status)) {
            error_log("[AcademicWork::updateStatus] Invalid status: $status");
            return false;
        }

        try {
            $db = Database::connection();
            $stmt = $db->prepare('UPDATE academic_works SET status = ?, updated_at = NOW() WHERE id = ?');
            return $stmt->execute([$status, $id]);
        } catch (Throwable $e) {
            error_log('[AcademicWork::updateStatus] ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // SUPERVISOR MANAGEMENT
    // =================================================================

    /**
     * Tambah dosen pembimbing ke tugas akhir
     *
     * @param int $workId ID academic work
     * @param int $lecturerProfileId ID dari lecturer_profiles (BUKAN users.id)
     * @param string $role 'supervisor_1' atau 'supervisor_2'
     * @param int $assignedBy User ID yang menugaskan
     */
    public static function addSupervisor(int $workId, int $lecturerProfileId, string $role, int $assignedBy): bool
    {
        if (!in_array($role, ['supervisor_1', 'supervisor_2'], true)) {
            error_log("[AcademicWork::addSupervisor] Invalid role: $role");
            return false;
        }

        try {
            $db = Database::connection();

            // Hapus role yang sama terlebih dahulu (jika ada)
            $db->prepare('
                DELETE FROM supervision_assignments 
                WHERE academic_work_id = ? AND assignment_role = ?
            ')->execute([$workId, $role]);

            $stmt = $db->prepare('
                INSERT INTO supervision_assignments 
                    (academic_work_id, lecturer_id, assignment_role, assigned_by, is_active, assigned_at)
                VALUES (?, ?, ?, ?, 1, NOW())
            ');
            return $stmt->execute([$workId, $lecturerProfileId, $role, $assignedBy]);
        } catch (Throwable $e) {
            error_log('[AcademicWork::addSupervisor] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus dosen pembimbing dari tugas akhir
     */
    public static function removeSupervisor(int $workId, int $lecturerProfileId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                UPDATE supervision_assignments 
                SET is_active = 0, removed_at = NOW() 
                WHERE academic_work_id = ? AND lecturer_id = ?
            ');
            return $stmt->execute([$workId, $lecturerProfileId]);
        } catch (Throwable $e) {
            error_log('[AcademicWork::removeSupervisor] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ganti semua supervisor untuk satu tugas akhir (delete & re-insert)
     *
     * @param array $supervisors [1 => lecturerProfileId, 2 => lecturerProfileIdOrNull]
     */
    public static function syncSupervisors(int $workId, array $supervisors, int $assignedBy): bool
    {
        try {
            $db = Database::connection();
            $db->beginTransaction();

            // Hapus semua supervisor aktif
            $db->prepare('
                UPDATE supervision_assignments 
                SET is_active = 0, removed_at = NOW() 
                WHERE academic_work_id = ? AND is_active = 1
            ')->execute([$workId]);

            // Insert supervisor 1
            if (!empty($supervisors[1]) || !empty($supervisors['supervisor_1'])) {
                $sup1 = $supervisors[1] ?? $supervisors['supervisor_1'];
                self::addSupervisor($workId, (int) $sup1, 'supervisor_1', $assignedBy);
            }

            // Insert supervisor 2 (opsional)
            if (!empty($supervisors[2]) || !empty($supervisors['supervisor_2'])) {
                $sup2 = $supervisors[2] ?? $supervisors['supervisor_2'];
                self::addSupervisor($workId, (int) $sup2, 'supervisor_2', $assignedBy);
            }

            $db->commit();
            return true;
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            error_log('[AcademicWork::syncSupervisors] ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // DELETE
    // =================================================================

    /**
     * Hapus academic work beserta semua relasi (cascade)
     */
    public static function delete(int $id): bool
    {
        try {
            $db = Database::connection();
            $db->beginTransaction();

            // Hapus relasi (sesuaikan dengan nama tabel yang ada di database)
            $relatedTables = [
                'supervision_assignments',
                'documents',
                'work_milestones',
                'document_reviews',
                'notifications',
            ];

            foreach ($relatedTables as $table) {
                try {
                    $col = $table === 'notifications' ? 'related_id' : 'academic_work_id';
                    $db->prepare("DELETE FROM $table WHERE $col = ?")->execute([$id]);
                } catch (Throwable $e) {
                    // Table mungkin tidak ada, abaikan
                    continue;
                }
            }

            // Hapus academic work
            $stmt = $db->prepare('DELETE FROM academic_works WHERE id = ?');
            $result = $stmt->execute([$id]);

            $db->commit();
            return $result;
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            error_log('[AcademicWork::delete] ' . $e->getMessage());
            return false;
        }
    }
}