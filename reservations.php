<?php
session_start();
require 'config.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['client_id'];

$query = "SELECT r.identifiant, r.datereservation, r.nbplacedispo, c.descriptif, c.datedepart,
          (c.prixinscription + COALESCE(SUM(l.prixvisite), 0)) AS prix_unitaire
          FROM reservation r
          JOIN Circuit c ON r.identifiant = c.identifiant
          LEFT JOIN etape e ON c.identifiant = e.identifiant
          LEFT JOIN lieuavisiter l ON e.nomlieu = l.nomlieu AND e.ville = l.ville AND e.pays = l.pays
          WHERE r.idclient = ?
          GROUP BY r.identifiant, r.idclient, r.datereservation, r.nbplacedispo, c.descriptif, c.datedepart
          ORDER BY r.datereservation DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$client_id]);
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Réservations - Epsi Voyage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2 style="margin-bottom:30px; font-size:2.5rem;">Mes Réservations</h2>

        <?php if (empty($reservations)): ?>
            <div class="card" style="text-align:center; padding:50px;">
                <p style="color:var(--text-muted); font-size:1.2rem;">Vous n'avez pas encore effectué de réservation.</p>
                <a href="circuits.php" class="btn btn-primary" style="margin-top:20px;">Explorer les circuits</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Circuit</th>
                            <th>Date de Départ</th>
                            <th>Places Réservées</th>
                            <th>Prix Unitaire</th>
                            <th>Total</th>
                            <th>Date de Réservation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reservations as $r): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($r['identifiant']) ?></td>
                            <td><?= htmlspecialchars($r['descriptif']) ?></td>
                            <td><?= date('d/m/Y', strtotime($r['datedepart'])) ?></td>
                            <td><?= $r['nbplacedispo'] ?></td>
                            <td><?= number_format($r['prix_unitaire'], 2) ?> €</td>
                            <td style="font-weight:700; color:var(--primary);"><?= number_format($r['prix_unitaire'] * $r['nbplacedispo'], 2) ?> €</td>
                            <td><?= date('d/m/Y', strtotime($r['datereservation'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
