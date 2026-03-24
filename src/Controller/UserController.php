<?php

namespace App\Controller;

use App\Core\View;
use App\Model\UtilisateurModel;

class UserController
{
    public function index()
    {
        $model = new UtilisateurModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            if ($action === 'edit') {
                $model->update((int)$_POST['id'], $_POST);
                header('Location: /student_list');
                exit;
            }

            if ($action === 'add') {
                $model->create($_POST);
                header('Location: /student_list');
                exit;
            }

            if ($action === 'delete') {
                $model->delete((int)$_POST['id']);
                header('Location: /student_list');
                exit;
            }
        }

        $editUser = null;
        if (isset($_GET['id'])) {
            $editUser = $model->getById((int)$_GET['id']);
        }

        View::render('user.html.twig', [
            'editUser' => $editUser,
            'pilote_id' => $_SESSION['user']['Id_user'] ?? null
        ]);
    }
}