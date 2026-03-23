<?php   
$pdo = new PDO("mysql:host=90.54.20.90;dbname=projet_db;charset=utf8", "projet_user", "projet_pass");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_POST['action'] ?? '';
$id     = $_POST['id']     ?? null;

if ($action === 'add') {
    $stmt = $pdo->prepare("INSERT INTO Utilisateur (Nom, Prenom, Email, Password, 'Role') VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password'], $_POST['role']]);
     exit;
}

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("UPDATE Utilisateur SET Nom=?, Prenom=?, Email=?, Password=?, 'Role'=? WHERE Id_user=?");
    $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password'], $_POST['role'], $id]);
     exit;
}

if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE Id_user=?");
    $stmt->execute([$id]);
     exit;
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

    <label>Mot de passe</label>
    <input type="text" name="password" value="<?= htmlspecialchars($editUser['Password'] ?? '') ?>" required>

    <label>Role</label>
    <select name="role">
        <?php foreach ($roles as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($editUser['Role'] ?? 1) == $val ? 'selected' : '' ?>>
                <?= $label ?>
            </option>
        <?php endforeach; ?>
    </select>

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