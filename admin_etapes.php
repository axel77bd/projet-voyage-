<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';

// Helper pour parser la valeur du select lieu
function parseLieuValue($val) {
    return explode('|', $val);
}

// Traitement Ajout
if (isset($_POST['add_etape'])) {
    try {
        list($nomlieu, $ville, $pays) = parseLieuValue($_POST['lieu']);
        $stmt = $pdo->prepare("INSERT INTO etape (identifiant, ordre, duree, nomlieu, ville, pays) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['identifiant'], $_POST['ordre'], $_POST['duree'], $nomlieu, $ville, $pays
        ]);
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Étape ajoutée avec succès !</div>";
    } catch(PDOException $e) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Erreur d'ajout: L'ordre pour ce circuit existe peut-être déjà ou erreur de référence.</div>";
    }
}

// Traitement Modification
if (isset($_POST['edit_etape'])) {
    try {
        list($nomlieu, $ville, $pays) = parseLieuValue($_POST['lieu']);
        $stmt = $pdo->prepare("UPDATE etape SET duree=?, nomlieu=?, ville=?, pays=? WHERE identifiant=? AND ordre=?");
        $stmt->execute([
            $_POST['duree'], $nomlieu, $ville, $pays,
            $_POST['old_identifiant'], $_POST['old_ordre']
        ]);
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Étape modifiée avec succès !</div>";
    } catch(PDOException $e) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Erreur de modification.</div>";
    }
}

// Traitement Suppression
if (isset($_POST['delete_etape'])) {
    $stmt = $pdo->prepare("DELETE FROM etape WHERE identifiant=? AND ordre=?");
    $stmt->execute([$_POST['del_identifiant'], $_POST['del_ordre']]);
    $message = "<div style='color:var(--success); margin-bottom:15px;'>Étape supprimée !</div>";
}

// Récupération pour modification
$etape_to_edit = null;
if (isset($_GET['edit_id']) && isset($_GET['edit_ordre'])) {
    $stmt = $pdo->prepare("SELECT * FROM etape WHERE identifiant=? AND ordre=?");
    $stmt->execute([$_GET['edit_id'], $_GET['edit_ordre']]);
    $etape_to_edit = $stmt->fetch();
}

$etapes = $pdo->query("SELECT e.*, c.descriptif AS circuit_desc FROM etape e JOIN Circuit c ON e.identifiant = c.identifiant ORDER BY e.identifiant, e.ordre")->fetchAll();
$circuits = $pdo->query("SELECT identifiant, descriptif FROM Circuit ORDER BY identifiant DESC")->fetchAll();
$lieux = $pdo->query("SELECT nomlieu, ville, pays FROM lieuavisiter ORDER BY pays, ville, nomlieu")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Étapes</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input, .admin-form select { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Étapes</h2>
        <?= $message ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <h3 style="margin-top:0;"><?= $etape_to_edit ? 'Modifier l\'étape' : 'Créer une nouvelle étape' ?></h3>
            <form method="POST" action="admin_etapes.php" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                <?php if($etape_to_edit): ?>
                    <input type="hidden" name="old_identifiant" value="<?= $etape_to_edit['identifiant'] ?>">
                    <input type="hidden" name="old_ordre" value="<?= $etape_to_edit['ordre'] ?>">
                <?php endif; ?>
                
                <div>
                    <label>Circuit</label>
                    <?php if($etape_to_edit): ?>
                        <select name="identifiant" disabled>
                            <option value="<?= $etape_to_edit['identifiant'] ?>"><?= $etape_to_edit['identifiant'] ?></option>
                        </select>
                    <?php else: ?>
                        <select name="identifiant" required>
                            <option value="">Sélectionner un circuit</option>
                            <?php foreach($circuits as $c): ?>
                                <option value="<?= $c['identifiant'] ?>"><?= $c['identifiant'] . ' - ' . htmlspecialchars($c['descriptif']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div>
                    <label>Ordre (N° de l'étape)</label>
                    <input type="number" name="ordre" value="<?= $etape_to_edit ? $etape_to_edit['ordre'] : '' ?>" <?= $etape_to_edit ? 'disabled' : 'required' ?>>
                </div>

                <div>
                    <label>Durée (jours)</label>
                    <input type="number" name="duree" value="<?= $etape_to_edit ? $etape_to_edit['duree'] : '' ?>" required>
                </div>

                <div>
                    <label>Lieu à visiter</label>
                    <select name="lieu" required>
                        <option value="">Sélectionner un lieu</option>
                        <?php foreach($lieux as $l): ?>
                            <?php 
                                $val = $l['nomlieu'].'|'.$l['ville'].'|'.$l['pays']; 
                                $selected = '';
                                if ($etape_to_edit && $etape_to_edit['nomlieu'] == $l['nomlieu'] && $etape_to_edit['ville'] == $l['ville'] && $etape_to_edit['pays'] == $l['pays']) {
                                    $selected = 'selected';
                                }
                            ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $selected ?>>
                                <?= htmlspecialchars($l['nomlieu']) ?> (<?= htmlspecialchars($l['ville'].', '.$l['pays']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if($etape_to_edit): ?>
                    <button type="submit" name="edit_etape" class="btn" style="height:35px; padding:0; background:var(--primary); border:none;">Mettre à jour</button>
                    <a href="admin_etapes.php" class="btn btn-secondary" style="height:35px; padding:0; line-height:35px; text-align:center; text-decoration:none;">Annuler</a>
                <?php else: ?>
                    <button type="submit" name="add_etape" class="btn" style="height:35px; padding:0;">Ajouter l'étape</button>
                <?php endif; ?>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Circuit (ID)</th>
                        <th>Ordre</th>
                        <th>Durée</th>
                        <th>Lieu</th>
                        <th>Ville, Pays</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($etapes)): ?><tr><td colspan="6">Aucune étape existante.</td></tr><?php endif; ?>
                    <?php foreach($etapes as $e): ?>
                    <tr>
                        <td><strong><?= $e['identifiant'] ?></strong> - <?= htmlspecialchars($e['circuit_desc']) ?></td>
                        <td><?= $e['ordre'] ?></td>
                        <td><?= $e['duree'] ?> jours</td>
                        <td><?= htmlspecialchars($e['nomlieu']) ?></td>
                        <td><?= htmlspecialchars($e['ville'].', '.$e['pays']) ?></td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Confirmer la suppression de cette étape ?');">
                                <a href="admin_etapes.php?edit_id=<?= $e['identifiant'] ?>&edit_ordre=<?= $e['ordre'] ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display:inline-block; margin-bottom:5px;">Modifier</a><br>
                                <input type="hidden" name="del_identifiant" value="<?= $e['identifiant'] ?>">
                                <input type="hidden" name="del_ordre" value="<?= $e['ordre'] ?>">
                                <button type="submit" name="delete_etape" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Supprimer</button>
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
