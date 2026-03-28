<?php
session_start();
require 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idclient = $_POST['idclient'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $datenaissance = $_POST['datenaissance'] ?? '';
    $password = $_POST['motdepasse'] ?? '';

    $stmt = $pdo->prepare("SELECT count(*) FROM client WHERE idclient = ?");
    $stmt->execute([$idclient]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Cet identifiant client existe déjà.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("INSERT INTO client (idclient, nom, prenom, datenaissance, motdepasse) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$idclient, $nom, $prenom, $datenaissance, $hash])) {
            $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
        } else {
            $error = "Une erreur est survenue lors de l'inscription.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Epsi Voyage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="auth-card" style="margin-top: 3rem;">
        <h2>Créer un compte</h2>
        <?php if($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Identifiant Client</label>
                <input type="text" name="idclient" required placeholder="Ex: C003">
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" required>
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" required>
                </div>
            </div>
            <div class="form-group">
                <label>Date de naissance</label>
                <input type="date" name="datenaissance" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="motdepasse" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">S'inscrire</button>
            <p style="text-align:center; margin-top:20px; font-size:0.9rem;">
                Déjà un compte ? <a href="login.php" style="color:var(--primary);">Se connecter</a>
            </p>
        </form>
    </div>
</body>
</html>
