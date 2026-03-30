<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\InputValidator;
use App\Core\View;
use App\Model\PannelModel;
use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;

class AccountController
{
    public function index()
    {
        Auth::requireAuth();

        $user = Auth::user();
        $id   = $user['Id_user'];       
        $role = $user['Role'];          
 
        switch ($role) {
            case 0: // Eleve normal
                $pannelModel = new PannelModel();

                $infos = $pannelModel->getUserInfos($id);
                View::render('pannel_eleve.html.twig', [
                    'infos' => $infos,
                ]);
                return;

            case 1: // Pilote
                $utilisateurModel = new UtilisateurModel();
                $entrepriseModel  = new EntrepriseModel();
                $offreModel       = new OffreModel();

                View::render('pannel_pilote.html.twig', [
                    'user'        => $user,
                    'eleves'      => $utilisateurModel->getByRoleAndEstGerePar(0,$id),
                    'offres'      => $offreModel->getAll(),
                    'entreprises' => $entrepriseModel->getAll(),
                ]);
                return;

            case 2: // Admin
                $utilisateurModel = new UtilisateurModel();
                $entrepriseModel  = new EntrepriseModel();
                $offreModel       = new OffreModel();

                View::render('pannel_admin.html.twig', [
                    'user'        => $user,
                    'pilotes'     => $utilisateurModel->getByRole(1),
                    'eleves'      => $utilisateurModel->getByRole(0),
                    'entreprises' => $entrepriseModel->getAll(),
                    'offres'      => $offreModel->getAll(),
                ]);
                return;

        }

        
    }

    public function noterEntreprise(): void
    {
        // Si non connecté, on force la connexion avant toute action de notation.
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::user();
        // Seuls les élèves (Role = 0) peuvent noter.
        if ((int) ($user['Role'] ?? -1) !== 0) {
            header('Location: /forbidden');
            exit;
        }

        // La note doit arriver via formulaire POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $idOffre = InputValidator::getInt($_POST, 'id_offre', 0, 1);
        $idEntreprise = InputValidator::getInt($_POST, 'id_entreprise', 0, 1);
        $note = InputValidator::getInt($_POST, 'note', 0, 1, 5);

        // Retour systématique vers la fiche de l'offre concernée.
        $redirectUrl = '/candidater?id_offre=' . max(0, $idOffre);

        // Validation des paramètres reçus.
        if ($idOffre <= 0 || $idEntreprise <= 0 || $note < 1 || $note > 5) {
            header('Location: ' . $redirectUrl . '&rating=invalid');
            exit;
        }

        $entrepriseModel = new EntrepriseModel();

        // On ne peut noter qu'une entreprise à laquelle on a déjà candidaté.
        if (!$entrepriseModel->canUserRateEntreprise((int) $user['Id_user'], $idEntreprise)) {
            header('Location: ' . $redirectUrl . '&rating=forbidden');
            exit;
        }

        // Enregistrement de la note (insert ou mise à jour selon l'existant).
        $entrepriseModel->noterEntreprise((int) $user['Id_user'], $idEntreprise, $note);

        header('Location: ' . $redirectUrl . '&rating=saved');
        exit;
    }
}
