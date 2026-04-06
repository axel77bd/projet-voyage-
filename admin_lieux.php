<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';

// Traitement Ajout
if (isset($_POST['add_lieu'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO lieuavisiter (nomlieu, ville, pays, descriptif, prixvisite) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nomlieu'], $_POST['ville'], $_POST['pays'], $_POST['descriptif'], $_POST['prixvisite']
        ]);
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Lieu ajouté avec succès !</div>";
    } catch(PDOException $e) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Erreur d'ajout: Ce lieu existe peut-être déjà.</div>";
    }
}

// Traitement Modification
if (isset($_POST['edit_lieu'])) {
    try {
        $stmt = $pdo->prepare("UPDATE lieuavisiter SET descriptif=?, prixvisite=? WHERE nomlieu=? AND ville=? AND pays=?");
        $stmt->execute([
            $_POST['descriptif'], $_POST['prixvisite'], 
            $_POST['old_nomlieu'], $_POST['old_ville'], $_POST['old_pays']
        ]);
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Lieu modifié avec succès !</div>";
    } catch(PDOException $e) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Erreur de modification.</div>";
    }
}

// Traitement Suppression
if (isset($_POST['delete_lieu'])) {
    $stmt = $pdo->prepare("DELETE FROM lieuavisiter WHERE nomlieu=? AND ville=? AND pays=?");
    $stmt->execute([$_POST['del_nomlieu'], $_POST['del_ville'], $_POST['del_pays']]);
    $message = "<div style='color:var(--success); margin-bottom:15px;'>Lieu supprimé !</div>";
}

// Récupération pour modification
$lieu_to_edit = null;
if (isset($_GET['edit_nom']) && isset($_GET['edit_ville']) && isset($_GET['edit_pays'])) {
    $stmt = $pdo->prepare("SELECT * FROM lieuavisiter WHERE nomlieu=? AND ville=? AND pays=?");
    $stmt->execute([$_GET['edit_nom'], $_GET['edit_ville'], $_GET['edit_pays']]);
    $lieu_to_edit = $stmt->fetch();
}

$lieux = $pdo->query("SELECT * FROM lieuavisiter ORDER BY pays, ville, nomlieu")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Lieux à visiter</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Lieux à Visiter</h2>
        <?= $message ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <h3 style="margin-top:0;"><?= $lieu_to_edit ? 'Modifier le lieu' : 'Créer un nouveau lieu' ?></h3>
            <form method="POST" action="admin_lieux.php" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                <?php if($lieu_to_edit): ?>
                    <input type="hidden" name="old_nomlieu" value="<?= htmlspecialchars($lieu_to_edit['nomlieu']) ?>">
                    <input type="hidden" name="old_ville" value="<?= htmlspecialchars($lieu_to_edit['ville']) ?>">
                    <input type="hidden" name="old_pays" value="<?= htmlspecialchars($lieu_to_edit['pays']) ?>">
                <?php endif; ?>
                
                <div>
                    <label>Nom du lieu</label>
                    <input type="text" name="nomlieu" value="<?= $lieu_to_edit ? htmlspecialchars($lieu_to_edit['nomlieu']) : '' ?>" <?= $lieu_to_edit ? 'disabled' : 'required' ?>>
                </div>
                <div>
                    <label>Ville</label>
                    <input type="text" name="ville" value="<?= $lieu_to_edit ? htmlspecialchars($lieu_to_edit['ville']) : '' ?>" <?= $lieu_to_edit ? 'disabled' : 'required' ?>>
                </div>
                <div>
                    <label>Pays</label>
                    <input type="text" name="pays" value="<?= $lieu_to_edit ? htmlspecialchars($lieu_to_edit['pays']) : '' ?>" <?= $lieu_to_edit ? 'disabled' : 'required' ?>>
                </div>
                <div>
                    <label>Descriptif</label>
                    <input type="text" name="descriptif" value="<?= $lieu_to_edit ? htmlspecialchars($lieu_to_edit['descriptif']) : '' ?>" required>
                </div>
                <div>
                    <label>Prix de la visite (€)</label>
                    <input type="number" step="0.01" name="prixvisite" value="<?= $lieu_to_edit ? $lieu_to_edit['prixvisite'] : '' ?>" required>
                </div>
                
                <?php if($lieu_to_edit): ?>
                    <button type="submit" name="edit_lieu" class="btn" style="height:35px; padding:0; background:var(--primary); border:none;">Mettre à jour</button>
                    <a href="admin_lieux.php" class="btn btn-secondary" style="height:35px; padding:0; line-height:35px; text-align:center; text-decoration:none;">Annuler</a>
                <?php else: ?>
                    <button type="submit" name="add_lieu" class="btn" style="height:35px; padding:0;">Ajouter le lieu</button>
                <?php endif; ?>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nom du lieu</th>
                        <th>Ville</th>
                        <th>Pays</th>
                        <th>Descriptif</th>
                        <th>Prix Visite</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($lieux)): ?><tr><td colspan="6">Aucun lieu existant.</td></tr><?php endif; ?>
                    <?php foreach($lieux as $l): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($l['nomlieu']) ?></strong></td>
                        <td><?= htmlspecialchars($l['ville']) ?></td>
                        <td><?= htmlspecialchars($l['pays']) ?></td>
                        <td><?= htmlspecialchars($l['descriptif']) ?></td>
                        <td><?= number_format($l['prixvisite'], 2) ?> €</td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Supprimer ce lieu (et potentiellement les étapes liées) ?');">
                                <a href="admin_lieux.php?edit_nom=<?= urlencode($l['nomlieu']) ?>&edit_ville=<?= urlencode($l['ville']) ?>&edit_pays=<?= urlencode($l['pays']) ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display:inline-block; margin-bottom:5px;">Modifier</a><br>
                                <input type="hidden" name="del_nomlieu" value="<?= htmlspecialchars($l['nomlieu']) ?>">
                                <input type="hidden" name="del_ville" value="<?= htmlspecialchars($l['ville']) ?>">
                                <input type="hidden" name="del_pays" value="<?= htmlspecialchars($l['pays']) ?>">
                                <button type="submit" name="delete_lieu" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Supprimer</button>
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
