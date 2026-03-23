<?php
$pdo = new PDO("mysql:host=90.54.20.90;dbname=projet_db;charset=utf8", "projet_user", "projet_pass");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_POST['action'] ?? '';
$id     = $_POST['id']     ?? null;

if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO Utilisateur (Nom, Prenom, Email, Date_naissance, Formation, Description, est_gere_par) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['date_naissance'],
        $_POST['formation'],
        $_POST['description'],
        $_POST['est_gere_par'] ?: null,
    ]);
    header('Location: test.php'); exit;
}

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("UPDATE Utilisateur SET Nom=?, Prenom=?, Email=?, Date_naissance=?, Formation=?, Description=?, est_gere_par=? WHERE Id_user=?");
    $stmt->execute([
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['date_naissance'],
        $_POST['formation'],
        $_POST['description'],
        $_POST['est_gere_par'] ?: null,
        $id,
    ]);
    header('Location: test.php'); exit;
}

if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE Id_user=?");
    $stmt->execute([$id]);
    header('Location: test.php'); exit;
}

$users = $pdo->query("SELECT * FROM Utilisateur ORDER BY Id_user ASC")->fetchAll(PDO::FETCH_ASSOC);

$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE Id_user=?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

$roles = [0 => 'Élève', 1 => 'Pilote', 2 => 'Admin'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>CRUD — Utilisateur</title>
    <style>
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 16px; text-align: center; border-bottom: 1px solid #eee; }
</style>
</head>
<body>

<h1>CRUD — table <code>Utilisateur</code></h1>

<h2><?= $editUser ? 'Modifier l\'utilisateur' : 'Ajouter un utilisateur' ?></h2>
<form method="POST" class="main-form">
    <input type="hidden" name="action" value="<?= $editUser ? 'edit' : 'add' ?>">
    <?php if ($editUser): ?>
        <input type="hidden" name="id" value="<?= $editUser['Id_user'] ?>">
    <?php endif; ?>

    <label>Nom</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($editUser['Nom'] ?? '') ?>" required>

    <label>Prénom</label>
    <input type="text" name="prenom" value="<?= htmlspecialchars($editUser['Prenom'] ?? '') ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($editUser['Email'] ?? '') ?>" required>

    <label>Date de naissance</label>
    <input type="date" name="date_naissance" value="<?= htmlspecialchars($editUser['Date_naissance'] ?? '') ?>" required>

    <label>Formation</label>
    <input type="text" name="formation" value="<?= htmlspecialchars($editUser['Formation'] ?? '') ?>" required>

    <label>Description</label>
    <input type="text" name="description" value="<?= htmlspecialchars($editUser['Description'] ?? '') ?>" required>

    <label>Est géré par </label>
    <input type="text" name="est_gere_par" value="<?= htmlspecialchars($editUser['est_gere_par'] ?? '') ?>" required>


    <button type="submit" class="btn btn-add">
        <?= $editUser ? 'Enregistrer' : 'Ajouter' ?>
    </button>
    <?php if ($editUser): ?>
        <a href="test.php" style="margin-left:10px;">Annuler</a>
    <?php endif; ?>
</form>

<h2>Liste des utilisateurs (<?= count($users) ?>)</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Role</th>
            <th>Date de naissance</th>
            <th>Formation</th>
            <th>Description</th>
            <th>Est géré par </th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['Id_user'] ?></td>
            <td><?= htmlspecialchars($u['Nom']) ?></td>
            <td><?= htmlspecialchars($u['Prenom']) ?></td>
            <td><?= htmlspecialchars($u['Email']) ?></td>
            <td>
                <span class="badge role-<?= $u['Role'] ?>">
                    <?= $roles[$u['Role']] ?? 'Inconnu' ?>
                </span>
            </td>
            <td><?= htmlspecialchars($u['Date_naissance']) ?></td>
            <td><?= htmlspecialchars($u['Formation']) ?></td>
            <td><?= htmlspecialchars($u['Description']) ?></td>
            <td><?= htmlspecialchars($u['est_gere_par']) ?></td>
            <td>
                <a href="?edit=<?= $u['Id_user'] ?>">
                    <button class="btn btn-edit">Modifier</button>
                </a>
                <form method="POST" style="display:inline"
                      onsubmit="return confirm('Supprimer <?= htmlspecialchars($u['Nom']) ?> ?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $u['Id_user'] ?>">
                    <button type="submit" class="btn btn-delete">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>