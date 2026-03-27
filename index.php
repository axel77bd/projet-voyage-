<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = $_POST['identifiant'] ?? '';
    $password = $_POST['motdepasse'] ?? '';
    $type = $_POST['type'] ?? 'client'; // client ou admin

    if ($type === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM administrateur WHERE identifiant = ?");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['motdepasse'])) {
            $_SESSION['admin_id'] = $user['idadmin'];
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Identifiants administrateur incorrects.";
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM client WHERE idclient = ?");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['motdepasse'])) {
            $_SESSION['client_id'] = $user['idclient'];
            $_SESSION['role'] = 'client';
            header("Location: client_dashboard.php");
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
    <title>Connexion - Agence de Voyage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-card">
        <h2>Connexion</h2>
        <?php if($error): ?>
            <div style="color:var(--danger); margin-bottom:15px; text-align:center;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="index.php">
            <div class="form-group">
                <label>Type de compte</label>
                <select name="type">
                    <option value="client">Client</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            <div class="form-group">
                <label>Identifiant (ID Client ou Pseudo)</label>
                <input type="text" name="identifiant" required placeholder="Ex: C001 ou admin">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="motdepasse" required>
            </div>
            <button type="submit" class="btn">Se connecter</button>
            <a href="register.php" class="btn btn-secondary">Créer un compte client</a>
        </form>
    </div>
</body>
</html>
