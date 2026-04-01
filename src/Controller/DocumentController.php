<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\InputValidator;
use App\Model\CandidatureModel;
use App\Model\UtilisateurModel;

class DocumentController
{
    public function download(): void
    {
        Auth::requireAuth();

        $currentUser = Auth::user() ?? [];
        $currentRole = (int) ($currentUser['Role'] ?? 0);
        $currentUserId = (int) ($currentUser['Id_user'] ?? 0);

        $type = InputValidator::requireEnum($_GET, 'type', ['cv', 'lm']);
        $idUser = InputValidator::getInt($_GET, 'id_user', 0, 1);
        $idOffre = InputValidator::getInt($_GET, 'id_offre', 0, 1);

        if ($idUser <= 0 || $idOffre <= 0) {
            http_response_code(400);
            echo 'Requête invalide.';
            return;
        }

        if (!$this->canAccessUserDocuments($currentRole, $currentUserId, $idUser)) {
            http_response_code(403);
            return;
        }

        $candidatureModel = new CandidatureModel();
        $fileName = $candidatureModel->getCandidatureDocumentName($idOffre, $idUser, $type);
        if (!is_string($fileName) || $fileName === '') {
            http_response_code(404);
            return;
        }

        $fileName = basename($fileName);

        // Défense en profondeur: refuse tout nom inattendu (pas de sous-dossiers, pas de traversal).
        $safePattern = '/^' . preg_quote($type, '/') . '_[A-Fa-f0-9-]{36}\.[A-Za-z0-9]{2,5}$/';
        if (!InputValidator::regex($fileName, $safePattern)) {
            http_response_code(404);
            return;
        }

        $baseDir = defined('UPLOADS_PATH') ? (string) UPLOADS_PATH : (dirname(__DIR__, 2) . '/uploads');
        $fullPath = rtrim($baseDir, '/') . '/' . $fileName;

        if (!is_file($fullPath)) {
            http_response_code(404);
            return;
        }

        $mime = 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = @finfo_file($finfo, $fullPath);
                if (is_string($detected) && $detected !== '') {
                    $mime = $detected;
                }
                finfo_close($finfo);
            }
        }

        $ext = (string) pathinfo($fileName, PATHINFO_EXTENSION);
        $downloadBase = ($type === 'cv') ? 'CV' : 'Lettre_de_motivation';
        $downloadName = $downloadBase . '_' . $idUser . '_' . $idOffre . '.' . $ext;

        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string) filesize($fullPath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Content-Disposition: inline; filename="' . $downloadName . '"');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        readfile($fullPath);
        exit;
    }

    private function canAccessUserDocuments(int $currentRole, int $currentUserId, int $targetUserId): bool
    {
        // Admin: OK
        if ($currentRole === 2) {
            return true;
        }

        // Élève: uniquement ses propres documents
        if ($currentRole === 0) {
            return $currentUserId > 0 && $currentUserId === $targetUserId;
        }

        // Pilote: uniquement les élèves qu'il gère
        if ($currentRole === 1) {
            $userModel = new UtilisateurModel();
            $target = $userModel->getById($targetUserId);
            if (!$target) {
                return false;
            }

            return (int) ($target['Role'] ?? -1) === 0
                && (int) ($target['est_gere_par'] ?? 0) === $currentUserId;
        }

        return false;
    }
}
