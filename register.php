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

    // Vérification si le client existe déjà
    $stmt = $pdo->prepare("SELECT count(*) FROM client WHERE idclient = ?");
    $stmt->execute([$idclient]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Cet identifiant client existe déjà.";
    } else {
        // Hash BCRYPT du mot de passe
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
    <title>Inscription - Agence de Voyage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-card" style="margin-top: 5vh;">
        <h2>Inscription Client</h2>
        <?php if($error): ?><div style="color:var(--danger); margin-bottom:15px; text-align:center;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if($success): ?><div style="color:var(--success); margin-bottom:15px; text-align:center;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <label>Identifiant (Ex: C003)</label>
                <input type="text" name="idclient" required>
            </div>
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" required>
            </div>
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" required>
            </div>
            <div class="form-group">
                <label>Date de naissance</label>
                <input type="date" name="datenaissance" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="motdepasse" required>
            </div>
            <button type="submit" class="btn">S'inscrire</button>
            <a href="index.php" class="btn btn-secondary">Retour à la connexion</a>
        </form>
    </div>
</body>
</html>
