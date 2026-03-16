# Projet-Web-G1

**Contexte**
Les etudiants recherchent des stages via leurs reseaux (LinkedIn, promotions, etc.) et les offres. Ce projet vise a
creer un site web qui regroupe des offres de stage et stocke les donnees des entreprises ayant deja pris un stagiaire ou
en recherchant un.

**Objectifs**

- Centraliser les offres de stage
- Faciliter l orientation des etudiants par competences
- Fournir des interfaces adaptees aux profils (administrateur, pilote, etudiant)
- Respecter les bonnes pratiques de developpement, SEO et securite

**Profils utilisateurs**

- Administrateur
- Pilote de promotion
- Etudiant

**Fonctionnalites attendues**
Gestion d acces

- SFx1 Authentification et gestion des acces

Gestion des entreprises

- SFx2 Rechercher et afficher une entreprise
- SFx3 Creer une entreprise
- SFx4 Modifier une entreprise
- SFx5 Evaluer une entreprise
- SFx6 Supprimer une entreprise

Gestion des offres de stage

- SFx7 Rechercher et afficher une offre
- SFx8 Creer une offre
- SFx9 Modifier une offre
- SFx10 Supprimer une offre
- SFx11 Consulter les statistiques des offres

Gestion des pilotes de promotions

- SFx12 Rechercher et afficher un compte Pilote
- SFx13 Creer un compte Pilote
- SFx14 Modifier un compte Pilote
- SFx15 Supprimer un compte Pilote

Gestion des etudiants

- SFx16 Rechercher et afficher un compte Etudiant
- SFx17 Creer un compte Etudiant
- SFx18 Modifier un compte Etudiant
- SFx19 Supprimer un compte Etudiant

Gestion des candidatures

- SFx20 Postuler a une offre (CV + LM)
- SFx21 Afficher les offres auxquelles l etudiant a postule
- SFx22 Afficher les offres auxquelles les eleves du pilote ont postule

Gestion des wish list

- SFx23 Afficher les offres ajoutees a la wish list
- SFx24 Ajouter une offre a la wish list
- SFx25 Retirer une offre de la wish list

Fonctionnalites transversales

- SFx27 Pagination pour les listes
- SFx28 Mentions legales

Bonus

- Acces mobile via PWA

**Specifications techniques**
Architecture et stack

- Architecture MVC obligatoire
- Serveur Apache
- Frontend HTML5 CSS3 JS
- Backend PHP (POO obligatoire)
- Base de donnees SQL (MySQL, PostgreSQL, MariaDB, ...)
- Moteur de template cote backend

Contraintes de qualite

- HTML semantique et valide W3C
- CSS structure et coherent
- Conventions PSR-12 appreciees
- Controle des champs front (HTML JS) et back (PHP)

Interdictions et autorisations

- CMS interdits (WordPress, Drupal, Joomla, etc.)
- Frameworks interdits (React, Angular, Vue, Laravel, Symfony)
- LESS Sass et jQuery autorises

Securite

- Cookies securises pour les informations de connexion
- Aucune donnee sensible en clair
- Protection contre SQLi, XSS, CSRF
- HTTPS

SEO

- Balises title, meta description, Hn, alt
- Mots cles dans meta
- Temps de chargement < 3 s
- URLs lisibles et coherentes
- sitemap.xml et robots.txt

Autres exigences

- Vhost distinct pour les assets statiques
- Responsive design avec menu burger sur petits ecrans
- Routage d URL cote backend
- Tests unitaires PHPUnit sur au moins un controleur
- Relations BD avec cles etrangeres

**Phases du projet**

- Phase 1 Lancement de projet (Scrum, roles, backlog, sprints, daily)
- Phase 2 Maquettage puis frontend (wireframe, navigation, mobile first)
- Phase 3 Developpement backend (debut)
- Phase 4 Modelisation et mise en place de la base de donnees
- Phase 5 Developpement backend (auth, DB, tests)
- Phase 6 Finalisation (ajouts JavaScript)

**Livrable et soutenance**

- Presentation courte (environ 5 minutes)
- Demonstration technique
- Questions reponses individuelles

**Taille d equipe**

- Projet dimensionne pour 4 eleves
