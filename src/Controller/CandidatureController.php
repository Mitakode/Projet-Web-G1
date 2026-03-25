<?php

namespace App\Controller;

use App\Core\View;
use App\Core\Auth;
use App\Model\OffreModel;
use App\Model\CandidatureModel;

class CandidatureController
{
    public function index()
    {
         //Vérification de la session utilisateur
        if (!Auth::check()) {
            header('Location: =/login');
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

        $uploadDir = __DIR__ . '/../../public/uploads/';       
      

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
            move_uploaded_file($_FILES['cv']['tmp_name'], $uploadDir . $cvName);
        } else {
            die("Erreur sur le fichier CV");
        }

        if (isset($_FILES['lm']) && $_FILES['lm']['error'] === UPLOAD_ERR_OK) {
            $lmName = $generateFileName($_FILES['lm'], 'lm');
            move_uploaded_file($_FILES['lm']['tmp_name'], $uploadDir . $lmName);
        } else {
            die("Erreur sur le fichier Lettre de Motivation");
        }

        $candidatureModel = new CandidatureModel();
        $candidatureModel->createCandidature($idOffre, $idUser, $cvName, $lmName);

        // 6. Redirection vers une page de succès
        // On pourrait rediriger vers l'offre avec un message de succès
        //header("Location: /?uri=/&success=1");
        exit;
    }
}
