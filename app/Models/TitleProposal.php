<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - TITLE PROPOSAL MODEL
 * Multi-Source Proposal System (Student <-> Lecturer)
 * =====================================================================
 */
class TitleProposal
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public static function create(array $d): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                INSERT INTO title_proposals
                    (proposer_role, proposer_user_id, target_user_id, title, description, keywords, work_type_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $d['proposer_role'],
                (int) $d['proposer_user_id'],
                (int) $d['target_user_id'],
                mb_substr(trim($d['title'] ?? ''), 0, 300),
                trim($d['description'] ?? '') ?: null,
                trim($d['keywords'] ?? '') ?: null,
                ((int) ($d['work_type_id'] ?? 0)) ?: null,
            ]);
            return (int) $db->lastInsertId();
        } catch (Throwable $e) {
            error_log('[TitleProposal::create] ' . $e->getMessage());
            return 0;
        }
    }

    public static function findById(int $id): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT * FROM title_proposals WHERE id = ?');
            $stmt->execute([$id]);
            $r = $stmt->fetch();
            return $r ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public static function getIncomingPending(int $userId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                SELECT tp.*, u.name AS proposer_name, wt.name AS work_type_name
                FROM title_proposals tp
                JOIN users u ON tp.proposer_user_id = u.id
                LEFT JOIN work_types wt ON tp.work_type_id = wt.id
                WHERE tp.target_user_id = ? AND tp.status = 'pending'
                ORDER BY tp.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[TitleProposal::getIncomingPending] ' . $e->getMessage());
            return [];
        }
    }

    public static function getOutgoing(int $userId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                SELECT tp.*, u.name AS target_name, wt.name AS work_type_name
                FROM title_proposals tp
                JOIN users u ON tp.target_user_id = u.id
                LEFT JOIN work_types wt ON tp.work_type_id = wt.id
                WHERE tp.proposer_user_id = ?
                ORDER BY tp.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[TitleProposal::getOutgoing] ' . $e->getMessage());
            return [];
        }
    }

    public static function respond(int $id, string $status, int $by, string $note = ''): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                UPDATE title_proposals
                SET status = ?, response_note = ?, responded_by = ?, responded_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$status, $note ?: null, $by, $id]);
        } catch (Throwable $e) {
            error_log('[TitleProposal::respond] ' . $e->getMessage());
            return false;
        }
    }

    public static function rejectOtherPendingForStudent(int $studentUserId, int $exceptId): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                UPDATE title_proposals
                SET status = 'rejected',
                    response_note = 'Otomatis ditutup: mahasiswa sudah memiliki tugas akhir',
                    responded_at = NOW(), updated_at = NOW()
                WHERE status = 'pending' AND id <> ?
                  AND (proposer_user_id = ? OR target_user_id = ?)
            ");
            $stmt->execute([$exceptId, $studentUserId, $studentUserId]);
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('[TitleProposal::rejectOtherPendingForStudent] ' . $e->getMessage());
            return 0;
        }
    }

    public static function countPendingForUser(int $userId): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("SELECT COUNT(*) AS total FROM title_proposals WHERE target_user_id = ? AND status = 'pending'");
            $stmt->execute([$userId]);
            return (int) $stmt->fetch()['total'];
        } catch (Throwable $e) {
            return 0;
        }
    }

    public static function hasPendingOutgoing(int $userId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("SELECT COUNT(*) AS total FROM title_proposals WHERE proposer_user_id = ? AND status = 'pending'");
            $stmt->execute([$userId]);
            return (int) $stmt->fetch()['total'] > 0;
        } catch (Throwable $e) {
            return false;
        }
    }
}