<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';

// Traitement Ajout
if (isset($_POST['add_client'])) {
    try {
        $hashed_password = password_hash($_POST['motdepasse'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO client (idclient, nom, prenom, datenaissance, motdepasse) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['idclient'], $_POST['nom'], $_POST['prenom'], $_POST['datenaissance'], $hashed_password
        ]);
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Client ajouté avec succès !</div>";
    } catch(PDOException $e) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Erreur d'ajout: L'identifiant (pseudo) existe peut-être déjà.</div>";
    }
}

// Traitement Modification
if (isset($_POST['edit_client'])) {
    try {
        if (!empty($_POST['motdepasse'])) {
            $hashed_password = password_hash($_POST['motdepasse'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE client SET nom=?, prenom=?, datenaissance=?, motdepasse=? WHERE idclient=?");
            $stmt->execute([
                $_POST['nom'], $_POST['prenom'], $_POST['datenaissance'], $hashed_password, $_POST['idclient_hidden']
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE client SET nom=?, prenom=?, datenaissance=? WHERE idclient=?");
            $stmt->execute([
                $_POST['nom'], $_POST['prenom'], $_POST['datenaissance'], $_POST['idclient_hidden']
            ]);
        }
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Client modifié avec succès !</div>";
    } catch(PDOException $e) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Erreur de modification.</div>";
    }
}

// Traitement Suppression
if (isset($_POST['delete_client'])) {
    $stmt = $pdo->prepare("DELETE FROM client WHERE idclient = ?");
    $stmt->execute([$_POST['id_to_delete']]);
    $message = "<div style='color:var(--success); margin-bottom:15px;'>Client supprimé !</div>";
}

// Récupération pour modification
$client_to_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM client WHERE idclient = ?");
    $stmt->execute([$_GET['edit']]);
    $client_to_edit = $stmt->fetch();
}

$clients = $pdo->query("SELECT idclient, nom, prenom, datenaissance FROM client ORDER BY nom, prenom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Clients</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Clients</h2>
        <?= $message ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <h3 style="margin-top:0;"><?= $client_to_edit ? 'Modifier le client #'.htmlspecialchars($client_to_edit['idclient']) : 'Créer un nouveau client' ?></h3>
            <form method="POST" action="admin_clients.php" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                
                <?php if($client_to_edit): ?>
                    <input type="hidden" name="idclient_hidden" value="<?= htmlspecialchars($client_to_edit['idclient']) ?>">
                    <div>
                        <label>Identifiant (Pseudo)</label>
                        <input type="text" value="<?= htmlspecialchars($client_to_edit['idclient']) ?>" disabled>
                    </div>
                <?php else: ?>
                    <div>
                        <label>Identifiant (Pseudo)</label>
                        <input type="text" name="idclient" required>
                    </div>
                <?php endif; ?>

                <div>
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= $client_to_edit ? htmlspecialchars($client_to_edit['nom']) : '' ?>" required>
                </div>
                <div>
                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?= $client_to_edit ? htmlspecialchars($client_to_edit['prenom']) : '' ?>" required>
                </div>
                <div>
                    <label>Date de Naissance</label>
                    <input type="date" name="datenaissance" value="<?= $client_to_edit ? date('Y-m-d', strtotime($client_to_edit['datenaissance'])) : '' ?>" required>
                </div>
                
                <div>
                    <label>Mot de passe <?= $client_to_edit ? '(laisser vide pour ne pas modifier)' : '*' ?></label>
                    <input type="password" name="motdepasse" <?= $client_to_edit ? '' : 'required' ?>>
                </div>
                
                <?php if($client_to_edit): ?>
                    <button type="submit" name="edit_client" class="btn" style="height:35px; padding:0; background:var(--primary); border:none;">Mettre à jour</button>
                    <a href="admin_clients.php" class="btn btn-secondary" style="height:35px; padding:0; line-height:35px; text-align:center; text-decoration:none;">Annuler</a>
                <?php else: ?>
                    <button type="submit" name="add_client" class="btn" style="height:35px; padding:0;">Créer le client</button>
                <?php endif; ?>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Identifiant (Pseudo)</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Date de naissance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($clients)): ?><tr><td colspan="5">Aucun client existant.</td></tr><?php endif; ?>
                    <?php foreach($clients as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['idclient']) ?></strong></td>
                        <td><?= htmlspecialchars($c['nom']) ?></td>
                        <td><?= htmlspecialchars($c['prenom']) ?></td>
                        <td><?= date('d/m/Y', strtotime($c['datenaissance'])) ?></td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Confirmer la suppression de ce client (et ses réservations) ?');">
                                <a href="admin_clients.php?edit=<?= urlencode($c['idclient']) ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display:inline-block; margin-bottom:5px;">Modifier</a><br>
                                <input type="hidden" name="id_to_delete" value="<?= htmlspecialchars($c['idclient']) ?>">
                                <button type="submit" name="delete_client" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Supprimer</button>
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
