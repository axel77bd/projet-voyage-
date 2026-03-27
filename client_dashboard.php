<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: index.php");
    exit;
}

$client_id = $_SESSION['client_id'];
$message = '';

// Traitement de la réservation
if (isset($_POST['reserver'])) {
    $idcircuit = $_POST['idcircuit'];
    $nbplaces = $_POST['nbplaces'];

    $stmt = $pdo->prepare("SELECT nbrplacedisponible FROM Circuit WHERE identifiant = ?");
    $stmt->execute([$idcircuit]);
    $dispo = $stmt->fetchColumn();

    if ($dispo && $dispo >= $nbplaces) {
        try {
            $pdo->beginTransaction();
            // Insertion Réservation
            $stmt = $pdo->prepare("INSERT INTO reservation (identifiant, idclient, datereservation, nbplacedispo) VALUES (?, ?, CURDATE(), ?)");
            $stmt->execute([$idcircuit, $client_id, $nbplaces]);

            // Mise à jour des places dans le Circuit
            $stmt = $pdo->prepare("UPDATE Circuit SET nbrplacedisponible = nbrplacedisponible - ? WHERE identifiant = ?");
            $stmt->execute([$nbplaces, $idcircuit]);
            
            $pdo->commit();
            $message = "<div style='color:var(--success); margin-bottom:15px; background:rgba(46,160,67,0.1); padding:10px; border-radius:5px;'>Réservation réussie pour $nbplaces place(s) !</div>";
        } catch(Exception $e) {
            $pdo->rollBack();
            $message = "<div style='color:var(--danger); margin-bottom:15px; background:rgba(248,81,73,0.1); padding:10px; border-radius:5px;'>Erreur: Vous avez peut-être déjà réservé ce circuit, ou une erreur SQL est survenue.</div>";
        }
    } else {
        $message = "<div style='color:var(--danger);'>Pas assez de places disponibles !</div>";
    }
}

// Recherche de circuits avec calcul de prix complet
$circuits = [];
if (isset($_GET['search']) && !empty($_GET['budget'])) {
    $budget = floatval($_GET['budget']);
    
    // Requête similaire à la Question 6 (Filtre par Budget Max)
    $query = "SELECT c.identifiant, c.descriptif, c.villedepart, c.villearrivee, c.datedepart, c.duree, c.nbrplacedisponible,
              (c.prixinscription + COALESCE(SUM(l.prixvisite), 0)) AS prix_total
              FROM Circuit c
              LEFT JOIN etape e ON c.identifiant = e.identifiant
              LEFT JOIN lieuavisiter l ON e.nomlieu = l.nomlieu AND e.ville = l.ville AND e.pays = l.pays
              WHERE c.nbrplacedisponible > 0
              GROUP BY c.identifiant
              HAVING prix_total <= ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$budget]);
    $circuits = $stmt->fetchAll();
} else {
    // Tous les circuits ayant encore des places
    $query = "SELECT c.identifiant, c.descriptif, c.villedepart, c.villearrivee, c.datedepart, c.duree, c.nbrplacedisponible,
              (c.prixinscription + COALESCE(SUM(l.prixvisite), 0)) AS prix_total
              FROM Circuit c
              LEFT JOIN etape e ON c.identifiant = e.identifiant
              LEFT JOIN lieuavisiter l ON e.nomlieu = l.nomlieu AND e.ville = l.ville AND e.pays = l.pays
              GROUP BY c.identifiant
              ORDER BY c.datedepart ASC";
    $stmt = $pdo->query($query);
    $circuits = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Client - Agence de Voyage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <div class="logo">Bonjour, Client <?= htmlspecialchars($client_id) ?></div>
        <div>
            <a href="client_dashboard.php">Catalogue des Circuits</a>
            <a href="logout.php" class="logout">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <h2>Découvrez nos circuits</h2>
        <?= $message ?>
        
        <div class="auth-card" style="margin: 20px 0; max-width:100%; display:flex; gap:15px; padding:20px;">
            <form method="GET" style="display:flex; gap:15px; width:100%; align-items:flex-end;">
                <div class="form-group" style="margin:0; flex:1;">
                    <label>Quel est votre budget maximum ? (€)</label>
                    <input type="number" name="budget" placeholder="Ex: 1500" value="<?= $_GET['budget'] ?? '' ?>" style="background:#0d1117; color:#fff; border:1px solid var(--border-color); padding:10px; width:100%; border-radius:5px;">
                </div>
                <button type="submit" name="search" class="btn" style="width:150px;">Rechercher</button>
                <a href="client_dashboard.php" class="btn btn-secondary" style="width:100px; margin-top:0;">Tout voir</a>
            </form>
        </div>

        <div class="circuit-grid">
            <?php foreach($circuits as $c): ?>
            <div class="circuit-card">
                <h3><?= htmlspecialchars($c['descriptif']) ?></h3>
                <div class="price-badge"><?= number_format($c['prix_total'], 2) ?> € / pers</div>
                <p><strong>De:</strong> <?= htmlspecialchars($c['villedepart']) ?> ➔ <strong>À:</strong> <?= htmlspecialchars($c['villearrivee']) ?></p>
                <p><strong>Départ le:</strong> <?= date('d/m/Y', strtotime($c['datedepart'])) ?> (<?= $c['duree'] ?> jours)</p>
                <p><strong>Disponibilité:</strong> <?= $c['nbrplacedisponible'] ?> places restantes</p>
                
                <?php if ($c['nbrplacedisponible'] > 0): ?>
                <form method="POST" style="margin-top:15px; display:flex; gap:10px; align-items:center;">
                    <input type="hidden" name="idcircuit" value="<?= $c['identifiant'] ?>">
                    <input type="number" name="nbplaces" min="1" max="<?= $c['nbrplacedisponible'] ?>" value="1" style="width:70px; padding:10px; background:#0d1117; color:#fff; border:1px solid var(--border-color); border-radius:5px;">
                    <button type="submit" name="reserver" class="btn" style="padding:10px; flex:1;">Réserver</button>
                </form>
                <?php else: ?>
                    <div style="margin-top:20px; color:var(--danger); font-weight:bold;">COMPLET</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($circuits)): ?>
                <p style="grid-column: 1 / -1; text-align:center; color:var(--text-muted);">Aucun circuit ne correspond à vos critères.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
