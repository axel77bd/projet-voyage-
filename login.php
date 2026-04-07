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
    <!-- Reprise d'un style simple comme dans l'exemple "Bootstrap" demandé par moment -->
    <style>
        .form-label { font-weight: bold; margin-bottom: 5px; display: block; }
        .form-control { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; box-sizing: border-box; }
        .btn-primary { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
        .btn-primary:hover { background-color: #0056b3; }
        .alert-error { color: #842029; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="auth-card" style="max-width: 400px; margin: 50px auto; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px;">
        <h2 style="text-align: center;">Se connecter</h2>
        
        <?php 
        // Afficher un message d'erreur s'il y en a un
        if (!empty($error)) { 
            echo '<div class="alert-error">' . $error . '</div>';
        } 
        ?>
        
        <form method="POST" action="login.php">
            
            <div>
                <label class="form-label mt-4">Type de compte</label>
                <select name="type_compte" class="form-control">
                    <option value="client">Je suis Client</option>
                    <option value="admin">Je suis Administrateur</option>
                </select>
            </div>
            
            <div>
                <label class="form-label">Identifiant</label>
                <input name="identifiant" type="text" class="form-control" placeholder="Entrez votre identifiant">
            </div>
            
            <div>
                <label class="form-label">Mot de passe</label>
                <input name="motdepasse" type="password" class="form-control" placeholder="Entrez votre mot de passe">
            </div>
            
            <button name="valider_connexion" type="submit" class="btn btn-primary mt-4">VALIDER</button>
            
            <p style="text-align:center; margin-top:20px; font-size:0.9rem;">
                Pas encore de compte client ? <a href="register.php" style="color: #007bff;">S'inscrire</a>
            </p>
        </form>
    </div>
</body>
</html>
