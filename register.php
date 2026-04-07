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
    
    <!-- Reprise d'un style simple type Bootstrap -->
    <style>
        .form-label { font-weight: bold; margin-bottom: 5px; display: block; }
        .form-control { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; box-sizing: border-box; }
        .btn-primary { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; width: 100%; display: block; margin-top: 15px;}
        .btn-primary:hover { background-color: #0056b3; }
        .alert-error { color: #842029; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .alert-success { color: #0f5132; background-color: #d1e7dd; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        
        .row { display: flex; gap: 15px; }
        .col { flex: 1; }
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="auth-card" style="max-width: 500px; margin: 40px auto; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px;">
        <h2 style="text-align: center; margin-top: 0;">Créer un compte</h2>
        
        <?php 
        // Affichage des messages d'erreur ou de succès avec des conditions simples
        if (!empty($error)) { 
            echo '<div class="alert-error">' . $error . '</div>';
        }
        if (!empty($success)) { 
            echo '<div class="alert-success">' . $success . '</div>';
        }
        ?>
        
        <form method="POST" action="register.php">
            <div>
                <label class="form-label">Identifiant (Pseudo)</label>
                <input type="text" name="idclient" class="form-control" placeholder="Ex: C003">
            </div>
            
            <div class="row">
                <div class="col">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" placeholder="Entrez votre nom">
                </div>
                <div class="col">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" placeholder="Entrez votre prénom">
                </div>
            </div>
            
            <div>
                <label class="form-label">Date de naissance</label>
                <input type="date" name="datenaissance" class="form-control">
            </div>
            
            <div>
                <label class="form-label">Mot de passe</label>
                <input type="password" name="motdepasse" class="form-control" placeholder="Entrez un mot de passe">
            </div>
            
            <button type="submit" name="valider_inscription" class="btn btn-primary">S'INSCRIRE</button>
            
            <p style="text-align:center; margin-top:20px; font-size:0.9rem;">
                Vous avez déjà un compte ? <a href="login.php" style="color:#007bff; font-weight:bold;">Se connecter</a>
            </p>
        </form>
    </div>
</body>
</html>
