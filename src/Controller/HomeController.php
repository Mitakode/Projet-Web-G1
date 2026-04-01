<?php

namespace App\Controller;

use App\Core\InputValidator;
use App\Core\View;
use App\Model\OffreModel;

/**
 * Public home controller.
 *
 * Lists offers with search + pagination and exposes JSON endpoints for wishlist
 * actions (student-only).
 */
class HomeController
{
    /**
     * Render the home page with paginated offers.
     */
    public function index()
    {
        $offreModel = new OffreModel();

        // Search input comes from the query string.
        $searchQuery = trim((string) ($_GET['query'] ?? ''));
        $searchInputInvalid = false;
        // Search: restrict allowed characters to keep input predictable/safe.
        if (!InputValidator::regex($searchQuery, '/^[\p{L}\p{N}\s\-\'".,()@]{0,100}$/u')) {
            $searchQuery = '';
            $searchInputInvalid = true;
        }

        // Pagination parameters.
        $elementsParPage = 10;

        $pageActuelle = InputValidator::getInt($_GET, 'page', 1, 1);
        if ($pageActuelle < 1) {
            $pageActuelle = 1;
        }

        // Compute total pages based on DB count.
        $totalElements = $offreModel->getTotalOffres($searchQuery);
        $totalPages = ceil($totalElements / $elementsParPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        if ($pageActuelle > $totalPages) {
            $pageActuelle = $totalPages;
        }

        // Fetch current slice.
        $offset = ($pageActuelle - 1) * $elementsParPage;

        $offres = $offreModel->getOffresPaginated($elementsParPage, $offset, $searchQuery);

        // Determine if current user is a student (Role = 0).
        $userId = $_SESSION['user']['Id_user'] ?? null; 
        
        $userRole = isset($_SESSION['user']['Role']) ? (int)$_SESSION['user']['Role'] : null; 

        $isStudent = ($userId && $userRole === 0);
        
        if ($isStudent) {
            // For students, annotate each offer with a wishlist flag.
            foreach ($offres as &$offre) {
                $offre['is_in_wishlist'] = false; 
                $wishlistEntry = $offreModel->isInWishlist($offre['Id_offre'], $userId);
                
                if ($wishlistEntry) {
                    $offre['is_in_wishlist'] = true;
                }
            }
            unset($offre);
        }

        View::render('home.html.twig', [
            'offres'       => $offres,
            'totalPages'   => $totalPages,
            'pageActuelle' => $pageActuelle,
            'searchQuery'  => $searchQuery,
            'searchInputInvalid' => $searchInputInvalid,
            'isStudent'    => $isStudent,
            'session_role' => $_SESSION['user']['Role'] ?? null,
            'isAuth'       => isset($_SESSION['user']),
        ]);
    }

public function addWishlist()
    {
        // JSON endpoint used by front-end actions.
        header('Content-Type: application/json');
        
        try {
            // Read offer ID from query string and validate it.
            $offerId = InputValidator::getInt($_GET, 'Id_offre', 0, 1);
            $userId = isset($_SESSION['user']['Id_user']) ? (int)$_SESSION['user']['Id_user'] : null;
            $userRole = isset($_SESSION['user']['Role']) ? (int)$_SESSION['user']['Role'] : null;
            
            // Only authenticated students can modify their wishlist.
            if ($offerId > 0 && $userId && $userRole === 0) {
                $offreModel = new \App\Model\OffreModel();
                
                // Idempotent behavior: adding an already-present entry still returns success.
                if (!$offreModel->isInWishlist($offerId, $userId)) {
                    $offreModel->addWishlist($offerId, $userId);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => true]);
                }
                exit;
            }
            
            // Unauthorized or invalid input.
            echo json_encode(['success' => false]);
            exit;
            
        } catch (\Exception $e) {
            // Keep API response simple; errors are not exposed to the client.
            echo json_encode(['success' => false]);
            exit;
        }
    }

    /**
     * Remove an offer from the student's wishlist (JSON endpoint).
     */
    public function deleteWishlist()
    {
        header('Content-Type: application/json');
        
        try {
            $offerId = InputValidator::getInt($_GET, 'Id_offre', 0, 1);
            $userId = isset($_SESSION['user']['Id_user']) ? (int)$_SESSION['user']['Id_user'] : null;
            $userRole = isset($_SESSION['user']['Role']) ? (int)$_SESSION['user']['Role'] : null;

            if ($offerId > 0 && $userId && $userRole === 0) {
                $offreModel = new \App\Model\OffreModel();
                // Delete is also idempotent: removing a missing entry is still "success" at DB layer.
                $offreModel->removeFromWishlist($userId, $offerId);
                echo json_encode(['success' => true]);
                exit;
            }
            
            echo json_encode(['success' => false]);
            exit;

        } catch (\Exception $e) {
            echo json_encode(['success' => false]);
            exit;
        }
    }
}