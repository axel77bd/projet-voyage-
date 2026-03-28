<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';

// Traitement Ajout
if (isset($_POST['add_circuit'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO Circuit (identifiant, descriptif, villedepart, villearrivee, paysarrivee, datedepart, nbrplacedisponible, duree, prixinscription) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['identifiant'], $_POST['descriptif'], $_POST['villedepart'], $_POST['villearrivee'], 
            $_POST['paysarrivee'], $_POST['datedepart'], $_POST['nbrplacedisponible'], $_POST['duree'], $_POST['prixinscription']
        ]);
        $message = "<div style='color:var(--success); margin-bottom:15px;'>Circuit ajouté avec succès !</div>";
    } catch(PDOException $e) {
        $message = "<div style='color:var(--danger); margin-bottom:15px;'>Erreur d'ajout: L'identifiant existe peut-être déjà.</div>";
    }
}

// Traitement Suppression
if (isset($_POST['delete_circuit'])) {
    $stmt = $pdo->prepare("DELETE FROM Circuit WHERE identifiant = ?");
    $stmt->execute([$_POST['id_to_delete']]);
    $message = "<div style='color:var(--success); margin-bottom:15px;'>Circuit supprimé !</div>";
}

$circuits = $pdo->query("SELECT * FROM Circuit ORDER BY identifiant DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Agence de Voyage</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Circuits (CRUD)</h2>
        <?= $message ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <h3 style="margin-top:0;">Créer un nouveau circuit</h3>
            <form method="POST" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                <div><label>ID Circuit</label><input type="number" name="identifiant" required></div>
                <div><label>Descriptif</label><input type="text" name="descriptif" required></div>
                <div><label>Ville de départ</label><input type="text" name="villedepart" required></div>
                <div><label>Ville d'arrivée</label><input type="text" name="villearrivee" required></div>
                <div><label>Pays de destination</label><input type="text" name="paysarrivee" required></div>
                <div><label>Date de départ</label><input type="datetime-local" name="datedepart" required></div>
                <div><label>Durée (jours)</label><input type="number" name="duree" required></div>
                <div><label>Places disponibles</label><input type="number" name="nbrplacedisponible" required></div>
                <div><label>Prix Inscription (€)</label><input type="number" step="0.01" name="prixinscription" required></div>
                <button type="submit" name="add_circuit" class="btn" style="height:35px; padding:0;">Ajouter le circuit</button>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descriptif</th>
                        <th>Départ</th>
                        <th>Arrivée</th>
                        <th>Date de départ</th>
                        <th>Places</th>
                        <th>Prix Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($circuits)): ?><tr><td colspan="8">Aucun circuit existant.</td></tr><?php endif; ?>
                    <?php foreach($circuits as $c): ?>
                    <tr>
                        <td><strong><?= $c['identifiant'] ?></strong></td>
                        <td><?= htmlspecialchars($c['descriptif']) ?></td>
                        <td><?= htmlspecialchars($c['villedepart']) ?></td>
                        <td><?= htmlspecialchars($c['villearrivee']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($c['datedepart'])) ?></td>
                        <td><?= $c['nbrplacedisponible'] ?></td>
                        <td><?= number_format($c['prixinscription'], 2) ?> €</td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Attention ! Supprimer ce circuit supprimera également toutes les étapes et réservations qui y sont liées (DELETE CASCADE). Confirmer ?');">
                                <input type="hidden" name="id_to_delete" value="<?= $c['identifiant'] ?>">
                                <button type="submit" name="delete_circuit" class="btn btn-danger">Supprimer</button>
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
