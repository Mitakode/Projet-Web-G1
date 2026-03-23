<?php

namespace App\Controller;

use App\Core\View;
use App\Model\OffreModel;
use App\Model\CandidatureModel;

class CandidatureController
{
    public function index()
    {
        // Vérification de la session utilisateur
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: /?uri=/login');
            exit;
        }

        // Récupérer l'ID de l'offre depuis l'URL
        $id = isset($_GET['id_offre']) ? (int)$_GET['id_offre'] : 0;
        
        $offreModel = new OffreModel();
        $offre = $offreModel->getOffreById($id);

        // Si l'offre n'existe pas, redirection vers l'accueil
        if (!$offre) {
            header('Location: /');
            exit;
        }

        View::render('candidature.html.twig', [
            'offre' => $offre
        ]);
    }

    public function submit()
    {
        // 1. Vérification de la session utilisateur
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Redirection si l'utilisateur n'est pas connecté
        //if (!isset($_SESSION['user']) || !isset($_SESSION['user']['Id_user'])) {
        //    header('Location: /?uri=/login');
        //    exit;
        //}
        $idUser = $_SESSION['user']['Id_user'];

        // 2. Vérification de la méthode HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        // 3. Récupération des données du formulaire
        $idOffre = isset($_POST['id_offre']) ? (int)$_POST['id_offre'] : 0;

        if ($idOffre <= 0) {
            // Gérer l'erreur : ID offre invalide
            die("ID de l'offre invalide.");
        }

        // 4. Gestion des fichiers (CV et Lettre de Motivation)
        // Vérification présence et erreurs
        if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK ||
            !isset($_FILES['lm']) || $_FILES['lm']['error'] !== UPLOAD_ERR_OK) {
             // Gérer l'erreur (rediriger avec message flash par exemple)
             die("Erreur lors du téléchargement des fichiers. Veuillez réessayer.");
        }

        // Définition du répertoire de destination
        // Chemin absolu vers /public/assets/uploads/
        $uploadDir = __DIR__ . '/../../public/assets/uploads/';
        
        // Création du dossier s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Fonction pour générer un nom unique et sécurisé
        $generateFileName = function($file, $prefix) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            // Vérifier l'extension (pdf, doc, docx uniquement)
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                die("Format de fichier non autorisé (PDF, DOC, DOCX acceptés).");
            }
            
            // Génération UUID v4
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

            return $prefix . '_' . $uuid . '.' . $extension;
        };

        $cvName = $generateFileName($_FILES['cv'], 'cv');
        $lmName = $generateFileName($_FILES['lm'], 'lm');

        // Déplacement des fichiers temporaires vers le dossier final
        if (!move_uploaded_file($_FILES['cv']['tmp_name'], $uploadDir . $cvName) ||
            !move_uploaded_file($_FILES['lm']['tmp_name'], $uploadDir . $lmName)) {
            die("Erreur lors de la sauvegarde des fichiers sur le serveur.");
        }

        // 5. Enregistrement en base de données via le modèle
        $candidatureModel = new CandidatureModel();
        // createCandidature($idOffre, $idUser, $cvName, $lmName)
        $candidatureModel->createCandidature($idOffre, $idUser, $cvName, $lmName);

        // 6. Redirection vers une page de succès
        // On pourrait rediriger vers l'offre avec un message de succès
        //header("Location: /?uri=/&success=1");
        exit;
    }
}
