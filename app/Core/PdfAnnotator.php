<?php
declare(strict_types=1);

/**
 * SIMBITA - PDF Annotator (DISABLED - Menggunakan Overlay HTML)
 * Anotasi akan ditampilkan sebagai overlay di PDF viewer, bukan embed ke file
 */

class PdfAnnotator
{
    /**
     * Get annotations untuk versi dokumen tertentu
     */
    public static function getAnnotations(int $versionId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT page_number, pos_x_pct, pos_y_pct, comment, priority, author_name, created_at
                FROM document_comments
                WHERE document_version_id = ?
                ORDER BY page_number ASC, created_at ASC
            ');
            $stmt->execute([$versionId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[PdfAnnotator] ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Dummy method untuk kompatibilitas
     */
    public static function getAnnotatedPdf(int $versionId, string $originalPath): ?string
    {
        // Tidak digunakan - return null agar pakai file asli
        return null;
    }
}