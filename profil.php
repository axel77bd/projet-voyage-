<?php
session_start();
require 'config.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['client_id'];

$stmt = $pdo->prepare("SELECT * FROM client WHERE idclient = ?");
$stmt->execute([$client_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - Epsi Voyage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container" style="max-width:800px;">
        <h2 style="margin-bottom:30px; font-size:2.5rem;">Mon Profil</h2>

        <div class="card">
            <div style="margin-bottom:30px; padding-bottom:20px; border-bottom:1px solid var(--border);">
                <h3>Informations personnelles</h3>
                <p style="color:var(--text-muted);">Voici les détails de votre compte client chez Epsi Voyage.</p>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                <div>
                    <label style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em;">Prénom</label>
                    <p style="font-size:1.2rem; font-weight:600;"><?= htmlspecialchars($user['prenom']) ?></p>
                </div>
                <div>
                    <label style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em;">Nom</label>
                    <p style="font-size:1.2rem; font-weight:600;"><?= htmlspecialchars($user['nom']) ?></p>
                </div>
                <div>
                    <label style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em;">Identifiant Client</label>
                    <p style="font-size:1.2rem; font-weight:600;"><?= htmlspecialchars($user['idclient']) ?></p>
                </div>
                <div>
                    <label style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em;">Date de Naissance</label>
                    <p style="font-size:1.2rem; font-weight:600;"><?= date('d F Y', strtotime($user['datenaissance'])) ?></p>
                </div>
            </div>

            <div style="margin-top:40px; display:flex; gap:15px;">
                <a href="reservations.php" class="btn btn-primary" style="flex:1;">Mes Réservations</a>
                <a href="logout.php" class="btn-logout btn btn-secondary" style="flex:1; border-color:var(--danger);">Déconnexion</a>
            </div>
        </div>
    </div>
</body>
</html>
