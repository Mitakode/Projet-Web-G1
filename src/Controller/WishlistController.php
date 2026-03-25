<?php

namespace App\Controller;

use App\Core\Auth;
use App\Model\WishlistModel;

class WishlistController
{
    public function add(): void
    {
        Auth::requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            return;
        }

        $offreId = (int) ($_POST['offre_id'] ?? 0);
        if ($offreId <= 0) {
            $_SESSION['wishlist_error'] = 'Offre invalide.';
            $this->redirectBack();
        }

        $user = Auth::user();
        if (!is_array($user) || !isset($user['Id_user'])) {
            http_response_code(401);
            return;
        }

        $model = new WishlistModel();
        $model->add((int) $user['Id_user'], $offreId);

        $this->redirectBack();
    }

    public function remove(): void
    {
        Auth::requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            return;
        }

        $offreId = (int) ($_POST['offre_id'] ?? 0);
        if ($offreId <= 0) {
            $_SESSION['wishlist_error'] = 'Offre invalide.';
            $this->redirectBack();
        }

        $user = Auth::user();
        if (!is_array($user) || !isset($user['Id_user'])) {
            http_response_code(401);
            return;
        }

        $model = new WishlistModel();
        $model->remove((int) $user['Id_user'], $offreId);

        $this->redirectBack();
    }

    private function redirectBack(): void
    {
        $target = $_SERVER['HTTP_REFERER'] ?? '/account';
        header('Location: ' . $target);
        exit;
    }
}
