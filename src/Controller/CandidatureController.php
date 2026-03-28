<?php

namespace App\Controller;

use App\Core\View;
use App\Core\Auth;
use App\Model\OffreModel;
use App\Model\CandidatureModel;
use App\Model\EntrepriseModel;
use PDOException;

class CandidatureController
{
    public function index()
    {
         //Vérification de la session utilisateur
        // Récupérer l'ID de l'offre depuis l'URL
        $id = isset($_GET['id_offre']) ? (int)$_GET['id_offre'] : 0;
        
        $offreModel = new OffreModel();
        $offre = $offreModel->getById($id);

        // Si l'offre n'existe pas, redirection vers l'accueil
        if (!$offre) {
            header('Location: /');
            exit;
        }

        $user = Auth::user();
        $canRate = false;
        $userNote = null;

        if ($user && (int) ($user['Role'] ?? -1) === 0) {
            $entrepriseModel = new EntrepriseModel();
            $idEntreprise = (int) ($offre['Id_entreprise'] ?? 0);

            if ($idEntreprise > 0) {
                $canRate = $entrepriseModel->canUserRateEntreprise((int) $user['Id_user'], $idEntreprise);
                $userNote = $entrepriseModel->getUserNoteEntreprise((int) $user['Id_user'], $idEntreprise);
            }
        }

        $ratingStatus = isset($_GET['rating']) ? (string) $_GET['rating'] : '';

        View::render('candidature.html.twig', [
            'offre' => $offre,
            'canRate' => $canRate,
            'userNote' => $userNote,
            'ratingStatus' => $ratingStatus,
        ]);
    }

    public function submit()
    {

        // vérifie si l'utilisateur est bien connecté
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        if ((int)$_SESSION['user']['Role'] !== 0) {
            header('Location: /');
            exit;
        }


        $idUser = $_SESSION['user']['Id_user'];

        //Vérification de la méthode HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        // 3. Récupération des données du formulaire
        $idOffre = isset($_POST['id_offre']) ? (int)$_POST['id_offre'] : 0;

        if ($idOffre <= 0) {
            die("ID de l'offre invalide.");
        }

        // `__DIR__` = src/Controller, on remonte à la racine du projet.
        $uploadDir = dirname(__DIR__, 2) . '/uploads/';

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            die("Impossible de créer le dossier d'upload.");
        }

        if (!is_writable($uploadDir)) {
            die("Le dossier d'upload n'est pas accessible en écriture.");
        }

        $candidatureModel = new CandidatureModel();
        if ($candidatureModel->candidatureExists($idOffre, $idUser)) {
            die("Vous avez déjà candidaté à cette offre.");
        }
      

        // Fonction pour générer un nom unique et sécurisé
        $generateFileName = function($file, $prefix) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                die("Format de fichier non autorisé (PDF, DOC, DOCX acceptés).");
            }
            
            // Génération UUID v4
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
            $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

            return $prefix . '_' . $uuid . '.' . $extension;
        };


        // Vérification présence et erreurs, Modif nom + "Upload"
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvName = $generateFileName($_FILES['cv'], 'cv');
            if (!move_uploaded_file($_FILES['cv']['tmp_name'], $uploadDir . $cvName)) {
                die("Erreur lors de l'upload du CV.");
            }
        } else {
            die("Erreur sur le fichier CV");
        }

        if (isset($_FILES['lm']) && $_FILES['lm']['error'] === UPLOAD_ERR_OK) {
            $lmName = $generateFileName($_FILES['lm'], 'lm');
            if (!move_uploaded_file($_FILES['lm']['tmp_name'], $uploadDir . $lmName)) {
                @unlink($uploadDir . $cvName);
                die("Erreur lors de l'upload de la lettre de motivation.");
            }
        } else {
            @unlink($uploadDir . $cvName);
            die("Erreur sur le fichier Lettre de Motivation");
        }

        try {
            $candidatureModel->createCandidature($idOffre, $idUser, $cvName, $lmName);
        } catch (PDOException $e) {
            @unlink($uploadDir . $cvName);
            @unlink($uploadDir . $lmName);
            die("Impossible d'enregistrer la candidature.");
        }

        // 6. Redirection vers une page de succès
        // On pourrait rediriger vers l'offre avec un message de succès
        //header("Location: /?uri=/&success=1");
        exit;
    }
}
