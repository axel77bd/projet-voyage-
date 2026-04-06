<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';

// Traitement Ajout
if (isset($_POST['add_admin'])) {
    try {
        $hashed_password = password_hash($_POST['motdepasse'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO administrateur (identifiant, motdepasse) VALUES (?, ?)");
        $stmt->execute([
            $_POST['identifiant'], $hashed_password
        ]);
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Administrateur ajouté avec succès !</div>";
    } catch(PDOException $e) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Erreur d'ajout: Cet identifiant existe peut-être déjà.</div>";
    }
}

// Traitement Modification
if (isset($_POST['edit_admin'])) {
    try {
        if (!empty($_POST['motdepasse'])) {
            $hashed_password = password_hash($_POST['motdepasse'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE administrateur SET identifiant=?, motdepasse=? WHERE idadmin=?");
            $stmt->execute([
                $_POST['identifiant'], $hashed_password, $_POST['idadmin']
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE administrateur SET identifiant=? WHERE idadmin=?");
            $stmt->execute([
                $_POST['identifiant'], $_POST['idadmin']
            ]);
        }
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Administrateur modifié avec succès !</div>";
    } catch(PDOException $e) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Erreur de modification ou identifiant déjà pris.</div>";
    }
}

// Traitement Suppression
if (isset($_POST['delete_admin'])) {
    // Éviter de s'auto-supprimer ou de supprimer le dernier admin (optionnel mais recommandé)
    if ($_POST['del_identifiant'] === $_SESSION['client_id']) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Vous ne pouvez pas supprimer votre propre compte admin depuis cette interface.</div>";
    } else {
        $stmt = $pdo->prepare("DELETE FROM administrateur WHERE idadmin = ?");
        $stmt->execute([$_POST['id_to_delete']]);
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Administrateur supprimé !</div>";
    }
}

// Récupération pour modification
$admin_to_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM administrateur WHERE idadmin = ?");
    $stmt->execute([$_GET['edit']]);
    $admin_to_edit = $stmt->fetch();
}

$admins = $pdo->query("SELECT idadmin, identifiant FROM administrateur ORDER BY idadmin")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Administrateurs</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Administrateurs</h2>
        <?= $message ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <h3 style="margin-top:0;"><?= $admin_to_edit ? 'Modifier l\'admin #'.$admin_to_edit['idadmin'] : 'Créer un nouveau admin' ?></h3>
            <form method="POST" action="admin_admins.php" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                
                <?php if($admin_to_edit): ?>
                    <input type="hidden" name="idadmin" value="<?= $admin_to_edit['idadmin'] ?>">
                <?php endif; ?>

                <div>
                    <label>Identifiant (Login)</label>
                    <input type="text" name="identifiant" value="<?= $admin_to_edit ? htmlspecialchars($admin_to_edit['identifiant']) : '' ?>" required>
                </div>
                
                <div>
                    <label>Mot de passe <?= $admin_to_edit ? '(laisser vide pour ne pas modifier)' : '*' ?></label>
                    <input type="password" name="motdepasse" <?= $admin_to_edit ? '' : 'required' ?>>
                </div>
                
                <?php if($admin_to_edit): ?>
                    <button type="submit" name="edit_admin" class="btn" style="height:35px; padding:0; background:var(--primary); border:none;">Mettre à jour</button>
                    <a href="admin_admins.php" class="btn btn-secondary" style="height:35px; padding:0; line-height:35px; text-align:center; text-decoration:none;">Annuler</a>
                <?php else: ?>
                    <button type="submit" name="add_admin" class="btn" style="height:35px; padding:0;">Créer admin</button>
                <?php endif; ?>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Identifiant (Login)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($admins)): ?><tr><td colspan="3">Aucun administrateur trouvé.</td></tr><?php endif; ?>
                    <?php foreach($admins as $a): ?>
                    <tr>
                        <td><strong><?= $a['idadmin'] ?></strong></td>
                        <td><?= htmlspecialchars($a['identifiant']) ?></td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Confirmer la suppression de cet administrateur ?');">
                                <a href="admin_admins.php?edit=<?= $a['idadmin'] ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display:inline-block; margin-bottom:5px;">Modifier</a><br>
                                <input type="hidden" name="id_to_delete" value="<?= $a['idadmin'] ?>">
                                <input type="hidden" name="del_identifiant" value="<?= htmlspecialchars($a['identifiant']) ?>">
                                <button type="submit" name="delete_admin" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
