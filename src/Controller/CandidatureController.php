<?php

namespace App\Controller;

use App\Core\InputValidator;
use App\Core\View;
use App\Core\Auth;
use App\Model\OffreModel;
use App\Model\CandidatureModel;
use App\Model\EntrepriseModel;
use PDOException;

/**
 * Candidature controller.
 *
 * Shows an offer detail page and handles candidature submission with file
 * uploads (CV + letter).
 */
class CandidatureController
{
    /**
     * Show the offer page and rating widgets.
     */
    public function index()
    {
        // Read offer id from the URL query string.
        $id = InputValidator::getInt($_GET, 'id_offre', 0, 1);
        
        $offreModel = new OffreModel();
        $offre = $offreModel->getById($id);

        // If the offer does not exist, redirect to home.
        if (!$offre) {
            header('Location: /');
            exit;
        }

        // Rating data injected into the view:
        // - canRate: whether the student has the right to rate this company
        // - userNote: existing rating by the student (pre-selected value)
        // - ratingStatus: feedback status after submission
        $user = Auth::user();
        $canRate = false;
        $userNote = null;
        $alreadyApplied = false;

        if ($user && (int) ($user['Role'] ?? -1) === 0) {
            $entrepriseModel = new EntrepriseModel();
            $idEntreprise = (int) ($offre['Id_entreprise'] ?? 0);

            if ($idEntreprise > 0) {
                // Student can rate only if they already applied to this company's offers.
                $canRate = $entrepriseModel->canUserRateEntreprise((int) $user['Id_user'], $idEntreprise);
                $userNote = $entrepriseModel->getUserNoteEntreprise((int) $user['Id_user'], $idEntreprise);
            }
        }

        $ratingStatus = InputValidator::getEnum(
            $_GET,
            'rating',
            ['invalid', 'forbidden', 'saved'],
            ''
        );

        View::render('candidature.html.twig', [
            'offre' => $offre,
            'canRate' => $canRate,
            'userNote' => $userNote,
            'ratingStatus' => $ratingStatus,
            'session_role' => $_SESSION['user']['Role'] ?? null,
            'isAuth'       => isset($_SESSION['user']),
            'alreadyApplied' => $alreadyApplied,
        ]);
    }

    /**
     * Handle candidature submission.
     *
     * Requires authentication and student role; stores uploaded files into /uploads.
     */
    public function submit()
    {

        // Ensure the user is authenticated.
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        // Only students can apply.
        if ((int)$_SESSION['user']['Role'] !== 0) {
            header('Location: /');
            exit;
        }


        $idUser = $_SESSION['user']['Id_user'];

        // Ensure the request is a POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        // Read form data.
        $idOffre = InputValidator::getInt($_POST, 'id_offre', 0, 1);

        if ($idOffre <= 0) {
            die("Invalid offer ID.");
        }

        // `__DIR__` is src/Controller; go up to the project root.
        $uploadDir = dirname(__DIR__, 2) . '/uploads/';

        // Ensure upload directory exists and is writable.
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            die("Unable to create the upload directory.");
        }

        if (!is_writable($uploadDir)) {
            die("The upload directory is not writable.");
        }

        $candidatureModel = new CandidatureModel();
        if ($candidatureModel->candidatureExists($idOffre, $idUser)) {
            die("You have already applied to this offer.");
        }
      

        // Helper to generate a unique and safe filename.
        $generateFileName = function($file, $prefix) {
            $extension = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            // Extension: small alphanumeric value + business whitelist.
            if (!InputValidator::regex($extension, '/^[a-z0-9]{2,5}$/') || !in_array($extension, $allowedExtensions, true)) {
                die("Unsupported file format (PDF, DOC, DOCX allowed).");
            }
            
            // Generate a random UUID v4 for collision-resistant names.
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

            return $prefix . '_' . $uuid . '.' . $extension;
        };


        // Validate presence and upload status, then move to the upload directory.
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvName = $generateFileName($_FILES['cv'], 'cv');
            if (!move_uploaded_file($_FILES['cv']['tmp_name'], $uploadDir . $cvName)) {
                die("Error while uploading the CV.");
            }
        } else {
            die("Error with the CV file.");
        }

        if (isset($_FILES['lm']) && $_FILES['lm']['error'] === UPLOAD_ERR_OK) {
            $lmName = $generateFileName($_FILES['lm'], 'lm');
            if (!move_uploaded_file($_FILES['lm']['tmp_name'], $uploadDir . $lmName)) {
                // Best-effort cleanup when the second upload fails.
                @unlink($uploadDir . $cvName);
                die("Error while uploading the cover letter.");
            }
        } else {
            @unlink($uploadDir . $cvName);
            die("Error with the cover letter file.");
        }

        try {
            // Persist the candidature record with stored filenames.
            $candidatureModel->createCandidature($idOffre, $idUser, $cvName, $lmName);
        } catch (PDOException $e) {
            // If DB write fails, remove uploaded files to avoid orphan data.
            @unlink($uploadDir . $cvName);
            @unlink($uploadDir . $lmName);
            die("Unable to save the application.");
        }

        // Success: a redirect could be added here (currently just exits).
        //header("Location: /?uri=/&success=1");
        exit;
    }
}
