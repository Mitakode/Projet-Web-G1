<?php

namespace App\Controller;

use App\Model\UtilisateurModel;
use App\Core\View;

class PiloteController
{
    public function index()
    {
        $model = new UtilisateurModel();

        $action = $_POST['action'] ?? null;

        if ($action === 'add') {
            $model->create($_POST);
            header('Location: /account');
            exit;
        }

        if ($action === 'edit') {
            $model->update($_POST['id'], $_POST);
            header('Location: /account');
            exit;
        }

        if ($action === 'delete') {
            $model->delete($_POST['id']);
            header('Location: /account');
            exit;
        }

        $users = $model->getByRoleandestgerepar(0,$_SESSION['user']['Id_user']);

        $editUser = null;
        if (isset($_GET['edit'])) {
            $editUser = $model->getById($_GET['edit']);
        }

        View::render('pilote_liste_eleve.html.twig', [
            'edituser'  => $editUser,
            'users' => $users
        ]);

    }
}