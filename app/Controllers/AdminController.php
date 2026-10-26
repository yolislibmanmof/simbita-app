<?php
declare(strict_types=1);

class AdminController
{
    // =================================================================
    // MAHASISWA
    // =================================================================
    public static function students(): void
    {
        Auth::requireRole('admin');
        
        $db = Database::connection();
        $stmt = $db->query('
            SELECT u.*, sp.student_number, sp.degree_level, sp.study_program, 
                   sp.faculty, sp.enrollment_year, sp.phone
            FROM users u
            JOIN student_profiles sp ON u.id = sp.user_id
            ORDER BY u.created_at DESC
        ');
        $students = $stmt->fetchAll();
        
        require dirname(__DIR__) . '/Views/admin/students.php';
    }

    public static function createStudent(): void
    {
        Auth::requireRole('admin');
        require dirname(__DIR__) . '/Views/admin/student_form.php';
    }

    public static function storeStudent(): void
    {
        Auth::requireRole('admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token tidak valid';
            redirect('/admin/students');
        }

        $db = Database::connection();
        
        try {
            $db->beginTransaction();
            
            // Cek duplikat email
            $check = $db->prepare('SELECT id FROM users WHERE email = ?');
            $check->execute([trim($_POST['email'])]);
            if ($check->fetch()) {
                throw new Exception('Email sudah terdaftar');
            }
            
            // Create user
            $stmt = $db->prepare('
                INSERT INTO users (name, email, password_hash, role, is_active)
                VALUES (?, ?, ?, ?, 1)
            ');
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['email']),
                password_hash($_POST['password'] ?? 'password123', PASSWORD_BCRYPT),
                'student'
            ]);
            $userId = (int)$db->lastInsertId();
            
            // Create student profile
            $stmt = $db->prepare('
                INSERT INTO student_profiles 
                (user_id, student_number, degree_level, study_program, faculty, enrollment_year, phone)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $userId,
                trim($_POST['student_number']),
                $_POST['degree_level'],
                trim($_POST['study_program']),
                trim($_POST['faculty'] ?? ''),
                (int)$_POST['enrollment_year'],
                trim($_POST['phone'] ?? '')
            ]);
            
            $db->commit();
            $_SESSION['flash_success'] = 'Mahasiswa berhasil ditambahkan';
            redirect('/admin/students');
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal: ' . $e->getMessage();
            redirect('/admin/students/create');
        }
    }

    public static function editStudent(): void
    {
        Auth::requireRole('admin');
        
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT u.*, sp.student_number, sp.degree_level, sp.study_program, 
                   sp.faculty, sp.enrollment_year, sp.phone
            FROM users u
            JOIN student_profiles sp ON u.id = sp.user_id
            WHERE u.id = ?
        ');
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $_SESSION['flash_error'] = 'Mahasiswa tidak ditemukan';
            redirect('/admin/students');
        }
        
        require dirname(__DIR__) . '/Views/admin/student_form.php';
    }

    public static function updateStudent(): void
    {
        Auth::requireRole('admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token tidak valid';
            redirect('/admin/students');
        }

        $id = (int)($_POST['id'] ?? 0);
        $db = Database::connection();
        
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare('
                UPDATE users SET name = ?, email = ?, is_active = ? WHERE id = ?
            ');
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['email']),
                (int)($_POST['is_active'] ?? 1),
                $id
            ]);
            
            $stmt = $db->prepare('
                UPDATE student_profiles 
                SET student_number = ?, degree_level = ?, study_program = ?, 
                    faculty = ?, enrollment_year = ?, phone = ?
                WHERE user_id = ?
            ');
            $stmt->execute([
                trim($_POST['student_number']),
                $_POST['degree_level'],
                trim($_POST['study_program']),
                trim($_POST['faculty'] ?? ''),
                (int)$_POST['enrollment_year'],
                trim($_POST['phone'] ?? ''),
                $id
            ]);
            
            $db->commit();
            $_SESSION['flash_success'] = 'Mahasiswa berhasil diupdate';
            redirect('/admin/students');
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal: ' . $e->getMessage();
            redirect('/admin/students/edit?id=' . $id);
        }
    }

    public static function deleteStudent(): void
    {
        Auth::requireRole('admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token tidak valid';
            redirect('/admin/students');
        }

        $id = (int)($_POST['id'] ?? 0);
        $db = Database::connection();
        
        try {
            $db->beginTransaction();
            // Hapus student_profile terlebih dahulu (jika FK restrict)
            $db->prepare('DELETE FROM student_profiles WHERE user_id = ?')->execute([$id]);
            $stmt = $db->prepare('DELETE FROM users WHERE id = ? AND role = "student"');
            $stmt->execute([$id]);
            $db->commit();
            
            $_SESSION['flash_success'] = 'Mahasiswa berhasil dihapus';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal hapus: ' . $e->getMessage();
        }
        
        redirect('/admin/students');
    }

    // =================================================================
    // DOSEN
    // =================================================================
    public static function lecturers(): void
    {
        Auth::requireRole('admin');
        
        $db = Database::connection();
        $stmt = $db->query('
            SELECT u.*, lp.lecturer_number, lp.academic_degree, lp.department, 
                   lp.expertise, lp.phone
            FROM users u
            JOIN lecturer_profiles lp ON u.id = lp.user_id
            ORDER BY u.created_at DESC
        ');
        $lecturers = $stmt->fetchAll();
        
        require dirname(__DIR__) . '/Views/admin/lecturers.php';
    }

    public static function createLecturer(): void
    {
        Auth::requireRole('admin');
        require dirname(__DIR__) . '/Views/admin/lecturer_form.php';
    }

    public static function storeLecturer(): void
    {
        Auth::requireRole('admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token tidak valid';
            redirect('/admin/lecturers');
        }

        $db = Database::connection();
        
        try {
            $db->beginTransaction();
            
            // Cek duplikat email
            $check = $db->prepare('SELECT id FROM users WHERE email = ?');
            $check->execute([trim($_POST['email'])]);
            if ($check->fetch()) {
                throw new Exception('Email sudah terdaftar');
            }
            
            $stmt = $db->prepare('
                INSERT INTO users (name, email, password_hash, role, is_active)
                VALUES (?, ?, ?, ?, 1)
            ');
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['email']),
                password_hash($_POST['password'] ?? 'password123', PASSWORD_BCRYPT),
                'lecturer'
            ]);
            $userId = (int)$db->lastInsertId();
            
            $stmt = $db->prepare('
                INSERT INTO lecturer_profiles 
                (user_id, lecturer_number, academic_degree, department, expertise, phone)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $userId,
                trim($_POST['lecturer_number'] ?? ''),
                trim($_POST['academic_degree'] ?? ''),
                trim($_POST['department'] ?? ''),
                trim($_POST['expertise'] ?? ''),
                trim($_POST['phone'] ?? '')
            ]);
            
            $db->commit();
            $_SESSION['flash_success'] = 'Dosen berhasil ditambahkan';
            redirect('/admin/lecturers');
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal: ' . $e->getMessage();
            redirect('/admin/lecturers/create');
        }
    }

    public static function editLecturer(): void
    {
        Auth::requireRole('admin');
        
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT u.*, lp.lecturer_number, lp.academic_degree, lp.department, 
                   lp.expertise, lp.phone
            FROM users u
            JOIN lecturer_profiles lp ON u.id = lp.user_id
            WHERE u.id = ?
        ');
        $stmt->execute([$id]);
        $lecturer = $stmt->fetch();
        
        if (!$lecturer) {
            $_SESSION['flash_error'] = 'Dosen tidak ditemukan';
            redirect('/admin/lecturers');
        }
        
        require dirname(__DIR__) . '/Views/admin/lecturer_form.php';
    }

    public static function updateLecturer(): void
    {
        Auth::requireRole('admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token tidak valid';
            redirect('/admin/lecturers');
        }

        $id = (int)($_POST['id'] ?? 0);
        $db = Database::connection();
        
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare('
                UPDATE users SET name = ?, email = ?, is_active = ? WHERE id = ?
            ');
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['email']),
                (int)($_POST['is_active'] ?? 1),
                $id
            ]);
            
            $stmt = $db->prepare('
                UPDATE lecturer_profiles 
                SET lecturer_number = ?, academic_degree = ?, department = ?, 
                    expertise = ?, phone = ?
                WHERE user_id = ?
            ');
            $stmt->execute([
                trim($_POST['lecturer_number'] ?? ''),
                trim($_POST['academic_degree'] ?? ''),
                trim($_POST['department'] ?? ''),
                trim($_POST['expertise'] ?? ''),
                trim($_POST['phone'] ?? ''),
                $id
            ]);
            
            $db->commit();
            $_SESSION['flash_success'] = 'Dosen berhasil diupdate';
            redirect('/admin/lecturers');
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal: ' . $e->getMessage();
            redirect('/admin/lecturers/edit?id=' . $id);
        }
    }

    public static function deleteLecturer(): void
    {
        Auth::requireRole('admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token tidak valid';
            redirect('/admin/lecturers');
        }

        $id = (int)($_POST['id'] ?? 0);
        $db = Database::connection();
        
        try {
            $db->beginTransaction();
            $db->prepare('DELETE FROM lecturer_profiles WHERE user_id = ?')->execute([$id]);
            $stmt = $db->prepare('DELETE FROM users WHERE id = ? AND role = "lecturer"');
            $stmt->execute([$id]);
            $db->commit();
            
            $_SESSION['flash_success'] = 'Dosen berhasil dihapus';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal hapus: ' . $e->getMessage();
        }
        
        redirect('/admin/lecturers');
    }

    // =================================================================
    // TUGAS AKHIR
    // =================================================================
    public static function works(): void
    {
        Auth::requireRole('admin');
        $works = AcademicWork::getAllWithDetails();
        require dirname(__DIR__) . '/Views/admin/works.php';
    }

    public static function createWork(): void
    {
        Auth::requireRole('admin');
        
        $db = Database::connection();
        
        // PERBAIKAN FINAL: Pakai users.id agar konsisten dengan dropdown & FK student_profiles.user_id
        $students = $db->query('
            SELECT u.id, u.name, sp.student_number 
            FROM users u 
            JOIN student_profiles sp ON u.id = sp.user_id 
            ORDER BY u.name
        ')->fetchAll();
        
        $workTypes = $db->query('SELECT * FROM work_types ORDER BY name')->fetchAll();
        
        // PERBAIKAN FINAL: Pakai lp.id (lecturer_profiles.id) sebagai value karena 
        // FK supervision_assignments.lecturer_id mengarah ke lecturer_profiles.id, BUKAN users.id
        $lecturers = $db->query('
            SELECT lp.id, u.name, lp.academic_degree 
            FROM users u 
            JOIN lecturer_profiles lp ON u.id = lp.user_id 
            ORDER BY u.name
        ')->fetchAll();
        
        require dirname(__DIR__) . '/Views/admin/work_form.php';
    }

    public static function storeWork(): void
    {
        Auth::requireRole('admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token tidak valid';
            redirect('/admin/works');
        }

        $db = Database::connection();
        
        try {
            $db->beginTransaction();
            
            // Get student_profile_id dari users.id (sesuai value dropdown)
            $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
            $stmt->execute([(int)$_POST['student_id']]);
            $studentProfile = $stmt->fetch();
            
            if (!$studentProfile) {
                throw new Exception('Profil mahasiswa tidak ditemukan');
            }
            
            $stmt = $db->prepare('
                INSERT INTO academic_works 
                (student_id, work_type_id, title, abstract, keywords, status, started_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $studentProfile['id'],
                (int)$_POST['work_type_id'],
                trim($_POST['title']),
                trim($_POST['abstract'] ?? ''),
                trim($_POST['keywords'] ?? ''),
                $_POST['status'] ?? 'draft',
                $_POST['started_at'] ?? date('Y-m-d')
            ]);
            $workId = (int)$db->lastInsertId();
            
            // Add supervisors (supervisor_1 & supervisor_2 sekarang adalah lecturer_profiles.id)
            if (!empty($_POST['supervisor_1'])) {
                $stmt = $db->prepare('
                    INSERT INTO supervision_assignments 
                    (academic_work_id, lecturer_id, assignment_role, assigned_by)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$workId, (int)$_POST['supervisor_1'], 'supervisor_1', Auth::id()]);
            }
            if (!empty($_POST['supervisor_2'])) {
                $stmt->execute([$workId, (int)$_POST['supervisor_2'], 'supervisor_2', Auth::id()]);
            }
            
            $db->commit();
            $_SESSION['flash_success'] = 'Tugas akhir berhasil ditambahkan';
            redirect('/admin/works');
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal: ' . $e->getMessage();
            redirect('/admin/works/create');
        }
    }

    public static function editWork(): void
    {
        Auth::requireRole('admin');
        
        $id = (int)($_GET['id'] ?? 0);
        $work = AcademicWork::getWithSupervisors($id);
        
        if (!$work) {
            $_SESSION['flash_error'] = 'Tugas akhir tidak ditemukan';
            redirect('/admin/works');
        }
        
        $db = Database::connection();
        
        $students = $db->query('
            SELECT u.id, u.name, sp.student_number 
            FROM users u 
            JOIN student_profiles sp ON u.id = sp.user_id 
            ORDER BY u.name
        ')->fetchAll();
        
        $workTypes = $db->query('SELECT * FROM work_types ORDER BY name')->fetchAll();
        
        // PENTING: Pakai lp.id agar pre-select di view bekerja dengan benar
        $lecturers = $db->query('
            SELECT lp.id, u.name, lp.academic_degree 
            FROM users u 
            JOIN lecturer_profiles lp ON u.id = lp.user_id 
            ORDER BY u.name
        ')->fetchAll();
        
        require dirname(__DIR__) . '/Views/admin/work_form.php';
    }

    public static function updateWork(): void
    {
        Auth::requireRole('admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token tidak valid';
            redirect('/admin/works');
        }

        $id = (int)($_POST['id'] ?? 0);
        $db = Database::connection();
        
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare('
                UPDATE academic_works 
                SET title = ?, abstract = ?, keywords = ?, status = ?
                WHERE id = ?
            ');
            $stmt->execute([
                trim($_POST['title']),
                trim($_POST['abstract'] ?? ''),
                trim($_POST['keywords'] ?? ''),
                $_POST['status'],
                $id
            ]);
            
            // Update supervisors (delete & re-insert)
            $db->prepare('DELETE FROM supervision_assignments WHERE academic_work_id = ?')
               ->execute([$id]);
            
            if (!empty($_POST['supervisor_1'])) {
                $stmt = $db->prepare('
                    INSERT INTO supervision_assignments 
                    (academic_work_id, lecturer_id, assignment_role, assigned_by, is_active)
                    VALUES (?, ?, ?, ?, 1)
                ');
                $stmt->execute([$id, (int)$_POST['supervisor_1'], 'supervisor_1', Auth::id()]);
            }
            if (!empty($_POST['supervisor_2'])) {
                $stmt->execute([$id, (int)$_POST['supervisor_2'], 'supervisor_2', Auth::id()]);
            }
            
            $db->commit();
            $_SESSION['flash_success'] = 'Tugas akhir berhasil diupdate';
            redirect('/admin/works');
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal: ' . $e->getMessage();
            redirect('/admin/works/edit?id=' . $id);
        }
    }

    public static function deleteWork(): void
    {
        Auth::requireRole('admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token tidak valid';
            redirect('/admin/works');
        }

        $id = (int)($_POST['id'] ?? 0);
        $db = Database::connection();
        
        try {
            $db->beginTransaction();
            // Hapus relasi supervisi dulu
            $db->prepare('DELETE FROM supervision_assignments WHERE academic_work_id = ?')->execute([$id]);
            $db->prepare('DELETE FROM documents WHERE academic_work_id = ?')->execute([$id]);
            $db->prepare('DELETE FROM work_milestones WHERE academic_work_id = ?')->execute([$id]);
            $db->prepare('DELETE FROM academic_works WHERE id = ?')->execute([$id]);
            $db->commit();
            
            $_SESSION['flash_success'] = 'Tugas akhir berhasil dihapus';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal hapus: ' . $e->getMessage();
        }
        
        redirect('/admin/works');
    }

    public static function showWork(): void
    {
        Auth::requireRole('admin');
        
        $id = (int)($_GET['id'] ?? 0);
        $work = AcademicWork::getWithSupervisors($id);
        
        if (!$work) {
            $_SESSION['flash_error'] = 'Tugas akhir tidak ditemukan';
            redirect('/admin/works');
        }
        
        require dirname(__DIR__) . '/Views/admin/work_detail.php';
    }

       // =================================================================
    // AUDIT LOGS
    // =================================================================
    public static function logs(): void
    {
        Auth::requireRole('admin');
        $db = Database::connection();
        
        $search = trim($_GET['search'] ?? '');
        $action = trim($_GET['action'] ?? '');
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        
        $where = ['1=1'];
        $params = [];
        
        if ($search !== '') {
            $where[] = '(u.name LIKE ? OR al.description LIKE ? OR al.ip_address LIKE ?)';
            $likeSearch = '%' . $search . '%';
            $params[] = $likeSearch; $params[] = $likeSearch; $params[] = $likeSearch;
        }
        if ($action !== '') {
            $where[] = 'al.action = ?';
            $params[] = $action;
        }
        if ($dateFrom !== '') {
            $where[] = 'al.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $where[] = 'al.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id WHERE $whereClause";
        $stmtCount = $db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetch()['total'];
        
        // Fetch data
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;
        
        $dataSql = "
            SELECT al.*, u.name as user_name, u.role as user_role
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE $whereClause
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmtData = $db->prepare($dataSql);
        $stmtData->execute($params);
        $logs = $stmtData->fetchAll();
        
        // Get unique actions for filter dropdown
        $stmtActions = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action ASC");
        $actions = $stmtActions->fetchAll(PDO::FETCH_COLUMN);
        
        require dirname(__DIR__) . '/Views/admin/logs.php';
    }

    public static function exportLogs(): void
    {
        Auth::requireRole('admin');
        $db = Database::connection();
        
        $search = trim($_GET['search'] ?? '');
        $action = trim($_GET['action'] ?? '');
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        
        $where = ['1=1'];
        $params = [];
        
        if ($search !== '') {
            $where[] = '(u.name LIKE ? OR al.description LIKE ? OR al.ip_address LIKE ?)';
            $likeSearch = '%' . $search . '%';
            $params[] = $likeSearch; $params[] = $likeSearch; $params[] = $likeSearch;
        }
        if ($action !== '') {
            $where[] = 'al.action = ?';
            $params[] = $action;
        }
        if ($dateFrom !== '') {
            $where[] = 'al.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== '') {
            $where[] = 'al.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $dataSql = "
            SELECT al.created_at, u.name as user_name, u.role as user_role, al.action, al.related_table, al.description, al.ip_address
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE $whereClause
            ORDER BY al.created_at DESC
        ";
        $stmt = $db->prepare($dataSql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Waktu', 'User', 'Role', 'Aksi', 'Tabel Terkait', 'Deskripsi', 'IP Address']);
        
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['created_at'],
                $log['user_name'] ?? 'System',
                $log['user_role'] ?? 'N/A',
                $log['action'],
                $log['related_table'] ?? '-',
                $log['description'] ?? '-',
                $log['ip_address'] ?? '-'
            ]);
        }
        fclose($output);
        exit;
    }

}