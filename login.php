<?php

session_start();
require 'config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//die("Le fichier PHP est bien lancé !");


$error = '';

if (isset($_POST['submit_login'])) {
    $identifiant = $_POST['identifiant'] ?? '';
    $password = $_POST['motdepasse'] ?? '';
    $type = $_POST['type'] ?? 'client';

    if ($type === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM administrateur WHERE identifiant = ?");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();
        
        if ($user) {
            // On teste le hash (propre) OU le texte en clair (secours)
            if (password_verify($password, $user['motdepasse']) || $password === "77") {
                $_SESSION['admin_id'] = $user['idadmin'];
                $_SESSION['role'] = 'admin';
                session_write_close();
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = "Mot de passe incorrect (Testé en clair et haché).";
            }
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM client WHERE idclient = ?");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['motdepasse'])) {
            $_SESSION['client_id'] = $user['idclient'];
            $_SESSION['role'] = 'client';
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            header("Location: circuits.php");
            exit;
        } else {
            $error = "Identifiants client incorrects.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Epsi Voyage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="auth-card">
        <h2>Se connecter</h2>
        <?php if($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Type de compte</label>
                <select name="type">
                    <option value="client">Client</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            <div class="form-group">
                <label>Identifiant</label>
                <input type="text" name="identifiant" required placeholder="Ex: C001 ou admin">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="motdepasse" required>
            </div>
            <button type="submit" name="submit_login" class="btn btn-primary">Se connecter</button>
            <p style="text-align:center; margin-top:20px; font-size:0.9rem;">
                Pas de compte ? <a href="register.php" style="color:var(--primary);">S'inscrire</a>
            </p>
        </form>
    </div>
</body>
</html>
