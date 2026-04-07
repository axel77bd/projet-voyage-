<?php
session_start();
require 'config.php';

$error = '';

// Si l'utilisateur clique sur le bouton "Se connecter"
if (isset($_POST['valider_connexion'])) {
    
    // 1. Récupérer les données envoyées par le formulaire et les sécuriser
    $identifiant = htmlentities($_POST['identifiant']);
    $motdepasse = htmlentities($_POST['motdepasse']);
    $type_compte = htmlentities($_POST['type_compte']); // correspond au <select> admin ou client
    
    // 2. Vérifier que les champs ne sont pas vides
    if (empty($identifiant)) {
        $error = "Veuillez saisir votre identifiant.";
    } 
    else if (empty($motdepasse)) {
        $error = "Veuillez saisir votre mot de passe.";
    } 
    else {
        // 3. Vérifier le type de compte pour savoir dans quelle table chercher (admin ou client)
        
        // ==========================================
        // CAS 1: CONNEXION ADMINISTRATEUR
        // ==========================================
        if ($type_compte == 'admin') {
            
            // On cherche l'administrateur dans la base de données
            $sql = "SELECT * FROM administrateur WHERE identifiant = :identifiant";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':identifiant', $identifiant);
            $stmt->execute();
            
            // fetch() récupère l'utilisateur s'il existe
            $admin_trouve = $stmt->fetch();
            
            if ($admin_trouve != false) {
                // L'administrateur existe, on vérifie maintenant le mot de passe
                $vrai_motdepasse = $admin_trouve['motdepasse'];
                
                // On compare le mot de passe tapé avec celui de la base (ou le mot de passe de secours "77")
                if (password_verify($motdepasse, $vrai_motdepasse) || $motdepasse === "77") {
                    
                    // Mot de passe correct ! On enregistre les données de session
                    $_SESSION['admin_id'] = $admin_trouve['idadmin'];
                    $_SESSION['role'] = 'admin';
                    
                    // On redirige vers le tableau de bord admin
                    header("Location: admin_dashboard.php");
                    exit;
                    
                } else {
                    $error = "Mot de passe incorrect.";
                }
            } else {
                $error = "Identifiant administrateur introuvable.";
            }
        } 
        
        // ==========================================
        // CAS 2: CONNEXION CLIENT
        // ==========================================
        else if ($type_compte == 'client') {
            
            // On cherche le client dans la base de données
            $sql = "SELECT * FROM client WHERE idclient = :identifiant";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':identifiant', $identifiant);
            $stmt->execute();
            
            $client_trouve = $stmt->fetch();
            
            if ($client_trouve != false) {
                // Le client existe, on vérifie son mot de passe
                $vrai_motdepasse = $client_trouve['motdepasse'];
                
                if (password_verify($motdepasse, $vrai_motdepasse)) {
                    
                    // Mot de passe correct ! On crée la session client
                    $_SESSION['client_id'] = $client_trouve['idclient'];
                    $_SESSION['role'] = 'client';
                    $_SESSION['nom'] = $client_trouve['nom'];
                    $_SESSION['prenom'] = $client_trouve['prenom'];
                    
                    // On redirige vers la page des circuits
                    header("Location: circuits.php");
                    exit;
                    
                } else {
                    $error = "Mot de passe incorrect.";
                }
            } else {
                $error = "Cet identifiant client n'existe pas.";
            }
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
        
        <?php 
        // Afficher un message d'erreur s'il y en a un
        if (!empty($error)) { 
            echo '<div class="alert alert-error">' . $error . '</div>';
        } 
        ?>
        
        <form method="POST" action="login.php">
            
            <div class="form-group">
                <label>Type de compte</label>
                <select name="type_compte">
                    <option value="client">Client</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Identifiant</label>
                <input name="identifiant" type="text" required placeholder="Ex: C001 ou admin">
            </div>
            
            <div class="form-group">
                <label>Mot de passe</label>
                <input name="motdepasse" type="password" required>
            </div>
            
            <button name="valider_connexion" type="submit" class="btn btn-primary">Se connecter</button>
            
            <p style="text-align:center; margin-top:20px; font-size:0.9rem;">
                Pas de compte ? <a href="register.php" style="color:var(--primary);">S'inscrire</a>
            </p>
        </form>
    </div>
</body>
</html>
