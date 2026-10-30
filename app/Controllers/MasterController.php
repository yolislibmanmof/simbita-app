<?php
declare(strict_types=1);

class MasterController
{
    // ============================================================
    // FAKULTAS - FIXED!
    // ============================================================
    public static function faculties(): void
    {
        $faculties = Faculty::all();
        // Perbaikan: pakai indeks array, bukan referensi
        foreach ($faculties as $i => $f) {
            $faculties[$i]['prodi_count'] = Faculty::countStudyPrograms((int)$f['id']);
        }
        require dirname(__DIR__) . '/Views/admin/faculties.php';
    }

    public static function storeFaculty(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            flash_set('error', 'Token tidak valid.');
            redirect('/admin/faculties');
            return;
        }
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        
        if ($code === '' || $name === '') {
            flash_set('error', 'Kode dan nama fakultas wajib diisi.');
            redirect('/admin/faculties');
            return;
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM faculties WHERE code = ? OR name = ?');
        $stmt->execute([$code, $name]);
        if ($stmt->fetch()) {
            flash_set('error', 'Gagal: Kode atau Nama Fakultas tersebut sudah ada. Gunakan nama/kode yang berbeda.');
            redirect('/admin/faculties');
            return;
        }

        try {
            Faculty::create($code, $name);
            flash_set('success', 'Fakultas "' . $name . '" berhasil ditambahkan.');
        } catch (Throwable $e) {
            flash_set('error', 'Gagal: ' . $e->getMessage());
        }
        redirect('/admin/faculties');
    }

    public static function updateFaculty(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            flash_set('error', 'Token tidak valid.');
            redirect('/admin/faculties');
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        
        if ($id <= 0 || $code === '' || $name === '') {
            flash_set('error', 'Data tidak lengkap.');
            redirect('/admin/faculties');
            return;
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM faculties WHERE (code = ? OR name = ?) AND id != ?');
        $stmt->execute([$code, $name, $id]);
        if ($stmt->fetch()) {
            flash_set('error', 'Gagal: Kode atau Nama Fakultas tersebut sudah digunakan oleh fakultas lain.');
            redirect('/admin/faculties');
            return;
        }

        try {
            Faculty::update($id, $code, $name);
            flash_set('success', 'Fakultas berhasil diperbarui.');
        } catch (Throwable $e) {
            flash_set('error', 'Gagal: ' . $e->getMessage());
        }
        redirect('/admin/faculties');
    }

    public static function deleteFaculty(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            flash_set('error', 'Token tidak valid.');
            redirect('/admin/faculties');
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $count = Faculty::countStudyPrograms($id);
        if ($count > 0) {
            flash_set('error', 'Fakultas tidak dapat dihapus karena memiliki ' . $count . ' program studi.');
        } else {
            Faculty::delete($id);
            flash_set('success', 'Fakultas dihapus.');
        }
        redirect('/admin/faculties');
    }

    // ============================================================
    // PROGRAM STUDI - FIXED!
    // ============================================================
    public static function studyPrograms(): void
    {
        $prodis = StudyProgram::all();
        // Perbaikan: pakai indeks array, bukan referensi
        foreach ($prodis as $i => $p) {
            $prodis[$i]['student_count']  = StudyProgram::countStudents((int)$p['id']);
            $prodis[$i]['lecturer_count'] = StudyProgram::countLecturers((int)$p['id']);
        }
        $faculties = Faculty::all();
        require dirname(__DIR__) . '/Views/admin/study_programs.php';
    }

    public static function storeProdi(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            flash_set('error', 'Token tidak valid.');
            redirect('/admin/study-programs');
            return;
        }
        $facultyId = (int)($_POST['faculty_id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $degree = $_POST['degree_level'] ?? 's1';
        if ($facultyId <= 0 || $code === '' || $name === '') {
            flash_set('error', 'Data tidak lengkap.');
        } else {
            try {
                StudyProgram::create($facultyId, $code, $name, $degree);
                flash_set('success', 'Program studi berhasil ditambahkan.');
            } catch (Throwable $e) {
                flash_set('error', 'Gagal: ' . $e->getMessage());
            }
        }
        redirect('/admin/study-programs');
    }

    public static function updateProdi(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            flash_set('error', 'Token tidak valid.');
            redirect('/admin/study-programs');
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $facultyId = (int)($_POST['faculty_id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $degree = $_POST['degree_level'] ?? 's1';
        if ($id <= 0 || $facultyId <= 0 || $code === '' || $name === '') {
            flash_set('error', 'Data tidak lengkap.');
        } else {
            StudyProgram::update($id, $facultyId, $code, $name, $degree);
            flash_set('success', 'Program studi diperbarui.');
        }
        redirect('/admin/study-programs');
    }

    public static function deleteProdi(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            flash_set('error', 'Token tidak valid.');
            redirect('/admin/study-programs');
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $mhs = StudyProgram::countStudents($id);
        $dos = StudyProgram::countLecturers($id);
        if ($mhs > 0 || $dos > 0) {
            flash_set('error', "Prodi tidak dapat dihapus: {$mhs} mahasiswa & {$dos} dosen terdaftar.");
        } else {
            StudyProgram::delete($id);
            flash_set('success', 'Program studi dihapus.');
        }
        redirect('/admin/study-programs');
    }
}