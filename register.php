<?php
session_start();
require 'config.php';

$error = '';
$success = '';

// Si l'utilisateur clique sur le bouton "S'inscrire"
if (isset($_POST['valider_inscription'])) {
    
    // 1. On récupère et on protège les données envoyées par le formulaire
    $idclient = htmlentities($_POST['idclient']);
    $nom = htmlentities($_POST['nom']);
    $prenom = htmlentities($_POST['prenom']);
    $datenaissance = htmlentities($_POST['datenaissance']);
    $motdepasse = htmlentities($_POST['motdepasse']);

    // 2. On vérifie que les champs obligatoires ne sont pas vides
    if (empty($idclient)) {
        $error = "Veuillez saisir un identifiant client.";
    } 
    else if (empty($nom)) {
        $error = "Veuillez saisir votre nom.";
    } 
    else if (empty($prenom)) {
        $error = "Veuillez saisir votre prénom.";
    } 
    else if (empty($motdepasse)) {
        $error = "Veuillez saisir un mot de passe.";
    } 
    else {
        // 3. On vérifie si l'identifiant existe déjà dans la base de données
        $sql_verification = "SELECT * FROM client WHERE idclient = :idclient";
        $stmt_verif = $pdo->prepare($sql_verification);
        $stmt_verif->bindParam(':idclient', $idclient);
        $stmt_verif->execute();
        
        $client_existant = $stmt_verif->fetch();
        
        // Si la requête nous renvoie quelque chose, c'est que le client existe déjà
        if ($client_existant != false) {
            $error = "Cet identifiant client est déjà utilisé, veuillez en choisir un autre.";
        } 
        else {
            // L'identifiant est libre, on peut procéder à l'inscription
            
            // 4. On chiffre (hash) le mot de passe pour qu'il soit illisible dans la base
            $motdepasse_hash = password_hash($motdepasse, PASSWORD_BCRYPT);
            
            // 5. On prépare la requête d'insertion (INSERT INTO)
            $sql_inscription = "INSERT INTO client (idclient, nom, prenom, datenaissance, motdepasse) 
                                VALUES (:idclient, :nom, :prenom, :datenaissance, :motdepasse)";
            
            $stmt_insert = $pdo->prepare($sql_inscription);
            $stmt_insert->bindParam(':idclient', $idclient);
            $stmt_insert->bindParam(':nom', $nom);
            $stmt_insert->bindParam(':prenom', $prenom);
            $stmt_insert->bindParam(':datenaissance', $datenaissance);
            $stmt_insert->bindParam(':motdepasse', $motdepasse_hash);

            // 6. On exécute la requête et on vérifie si elle a réussi
            $resultat = $stmt_insert->execute();
            
            if ($resultat == true) {
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Une erreur est survenue lors de l'inscription.";
            }
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
        
        <?php 
        // Affichage des messages d'erreur ou de succès avec des conditions simples
        if (!empty($error)) { 
            echo '<div class="alert alert-error">' . $error . '</div>';
        }
        if (!empty($success)) { 
            echo '<div class="alert alert-success">' . $success . '</div>';
        }
        ?>
        
        <form method="POST" action="register.php">
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
            
            <button type="submit" name="valider_inscription" class="btn btn-primary" style="width:100%;">S'inscrire</button>
            
            <p style="text-align:center; margin-top:20px; font-size:0.9rem;">
                Déjà un compte ? <a href="login.php" style="color:var(--primary);">Se connecter</a>
            </p>
        </form>
    </div>
</body>
</html>
