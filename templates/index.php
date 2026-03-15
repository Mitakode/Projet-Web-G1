<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Career Quest</title>
    <link href="main-stylesheet.css" rel="stylesheet" />
    <link href="accueil_stylesheet.css" rel="stylesheet"/>
</head>
<body>
    <header class="navbar">
        <div class="logo">
            <img src="images/logo.png" alt="Logo de Career Quest"/>
        </div>
        <div class="search-container">
            <input type="text" placeholder="Rechercher" class="search-bar"/>
        </div>
        <div class="account">
            <div class="user-icon">
                <img src="images/user_icon.svg" alt="Icône de compte utilisateur"/>
            </div>
            <span class="account-text">Mon compte</span>
        </div>
    </header>

    <main class="main-container">
        <aside class="sidebar">
            
            <!-- AFFICHAGE DES OFFRES -->
            <?php if (!empty($offres)): ?>
                <?php foreach ($offres as $offre): ?>
                    <div class="list-offer">
                        <h3><?= htmlspecialchars($offre['titre'] ?? 'Titre non défini') ?></h3>
                        <p><?= htmlspecialchars($offre['lieu'] ?? 'Lieu non défini') ?></p>
                        <small><?= htmlspecialchars($offre['description'] ?? 'Pas de description') ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-offer">
                    <h3>Aucune offre trouvée</h3>
                </div>
            <?php endif; ?>

            <!-- PAGINATION -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination-container" style="display:flex; justify-content:center; gap:10px; margin-top:20px;">
                    
                    <!-- Bouton Précédent -->
                    <?php if ($pageActuelle > 1): ?>
                        <a href="/?page=<?= $pageActuelle - 1 ?>" style="padding:10px; background:#17496E; color:white; text-decoration:none; border-radius:5px;">Précédent</a>
                    <?php endif; ?>

                    <!-- Numéros -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="/?page=<?= $i ?>" style="padding:10px; text-decoration:none; border-radius:5px; <?= ($i === $pageActuelle) ? 'background:#17496E; color:white;' : 'background:#e0e0e0; color:black;' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Bouton Suivant -->
                    <?php if ($pageActuelle < $totalPages): ?>
                        <a href="/?page=<?= $pageActuelle + 1 ?>" style="padding:10px; background:#17496E; color:white; text-decoration:none; border-radius:5px;">Suivant</a>
                    <?php endif; ?>
                    
                </div>
            <?php endif; ?>

        </aside>

        <section class="details">
            <div class="detail-offer">
                <h2>Titre de l'offre</h2>
                <p>Les détails s'afficheront ici...</p>
                <button class="btn-candidate">Candidater</button>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p>
            © Career Quest | <a href="page-mentions-legales.html" class="footer-link">Mentions Légales</a>
        </p>
    </footer>
</body>
</html>