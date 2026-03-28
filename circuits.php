<?php
session_start();
require 'config.php';

$message = '';

// Traitement de la réservation (Nécessite connexion)
if (isset($_POST['reserver'])) {
    if (!isset($_SESSION['client_id'])) {
        header("Location: login.php?msg=Veuillez vous connecter pour réserver.");
        exit;
    }

    $client_id = $_SESSION['client_id'];
    $idcircuit = $_POST['idcircuit'];
    $nbplaces = intval($_POST['nbplaces']);

    $stmt = $pdo->prepare("SELECT nbrplacedisponible FROM Circuit WHERE identifiant = ?");
    $stmt->execute([$idcircuit]);
    $dispo = $stmt->fetchColumn();

    if ($dispo && $dispo >= $nbplaces) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO reservation (identifiant, idclient, datereservation, nbplacedispo) VALUES (?, ?, CURDATE(), ?)");
            $stmt->execute([$idcircuit, $client_id, $nbplaces]);

            $stmt = $pdo->prepare("UPDATE Circuit SET nbrplacedisponible = nbrplacedisponible - ? WHERE identifiant = ?");
            $stmt->execute([$nbplaces, $idcircuit]);
            
            $pdo->commit();
            $message = "<div class='alert alert-success'>Réservation effectuée avec succès !</div>";
        } catch(Exception $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-error'>Une erreur est survenue lors de la réservation.</div>";
        }
    } else {
        $message = "<div class='alert alert-error'>Désolé, plus assez de places disponibles.</div>";
    }
}

// Recherche de circuits
$budget = isset($_GET['budget']) ? floatval($_GET['budget']) : 0;
$query = "SELECT c.identifiant, c.descriptif, c.villedepart, c.villearrivee, c.datedepart, c.duree, c.nbrplacedisponible,
          (c.prixinscription + COALESCE(SUM(l.prixvisite), 0)) AS prix_total
          FROM Circuit c
          LEFT JOIN etape e ON c.identifiant = e.identifiant
          LEFT JOIN lieuavisiter l ON e.nomlieu = l.nomlieu AND e.ville = l.ville AND e.pays = l.pays
          GROUP BY c.identifiant";

if ($budget > 0) {
    $query .= " HAVING prix_total <= ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$budget]);
} else {
    $stmt = $pdo->query($query);
}
$circuits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Circuits - Epsi Voyage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2 style="margin-bottom:30px; font-size:2.5rem;">Explorez nos circuits</h2>
        
        <?= $message ?>

        <div class="card" style="margin-bottom:40px;">
            <form method="GET" style="display:flex; gap:20px; align-items:flex-end;">
                <div class="form-group" style="margin:0; flex:1;">
                    <label>Filtrer par budget max (€)</label>
                    <input type="number" name="budget" value="<?= $budget > 0 ? htmlspecialchars($budget) : '' ?>" placeholder="Ex: 2000">
                </div>
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="circuits.php" class="btn btn-secondary">Réinitialiser</a>
            </form>
        </div>

        <div class="circuit-grid">
            <?php foreach($circuits as $c): ?>
            <div class="card circuit-card">
                <h3><?= htmlspecialchars($c['descriptif']) ?></h3>
                <div class="price-tag"><?= number_format($c['prix_total'], 2) ?> €</div>
                <p style="color:var(--text-muted); margin-bottom:15px;"><?= $c['duree'] ?> jours de voyage</p>
                
                <div style="margin-bottom:20px;">
                    <span class="badge"><?= htmlspecialchars($c['villedepart']) ?> ➔ <?= htmlspecialchars($c['villearrivee']) ?></span>
                </div>
                
                <p><strong>Départ :</strong> <?= date('d M Y', strtotime($c['datedepart'])) ?></p>
                <p><strong>Places :</strong> <?= $c['nbrplacedisponible'] ?> disponibles</p>
                
                <form method="POST" style="margin-top:20px; display:flex; gap:10px;">
                    <input type="hidden" name="idcircuit" value="<?= $c['identifiant'] ?>">
                    <input type="number" name="nbplaces" min="1" max="<?= $c['nbrplacedisponible'] ?>" value="1" style="width:65px;">
                    <?php if ($c['nbrplacedisponible'] > 0): ?>
                        <button type="submit" name="reserver" class="btn btn-primary" style="flex:1;">Réserver</button>
                    <?php else: ?>
                        <button disabled class="btn btn-secondary" style="flex:1; cursor:not-allowed;">Complet</button>
                    <?php endif; ?>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
