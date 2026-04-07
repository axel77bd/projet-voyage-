<?php
session_start();
require 'config.php';

// Vérifier si la personne est connectée et est un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';

// ==========================================
// 1. AJOUTER UN CLIENT
// ==========================================
if (isset($_POST['add_client'])) {
    $idclient = htmlentities($_POST['idclient']);
    $nom = htmlentities($_POST['nom']);
    $prenom = htmlentities($_POST['prenom']);
    $datenaissance = htmlentities($_POST['datenaissance']);
    $motdepasse_clair = htmlentities($_POST['motdepasse']);

    if (empty($idclient) || empty($motdepasse_clair)) {
        $message = "Veuillez remplir l'identifiant et le mot de passe.";
    } else {
        // Hacher le mot de passe pour la sécurité
        $motdepasse_hash = password_hash($motdepasse_clair, PASSWORD_BCRYPT);

        // Préparer la requête SQL
        $sql = "INSERT INTO client (idclient, nom, prenom, datenaissance, motdepasse) 
                VALUES (:idclient, :nom, :prenom, :datenaissance, :motdepasse)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':idclient', $idclient);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':datenaissance', $datenaissance);
        $stmt->bindParam(':motdepasse', $motdepasse_hash);

        // Exécuter et vérifier
        $resultat = $stmt->execute();
        if ($resultat) {
            $message = "<div style='color:green;'>Client ajouté avec succès !</div>";
        } else {
            $message = "<div style='color:red;'>Erreur lors de l'ajout. L'identifiant existe peut-être déjà.</div>";
        }
    }
}

// ==========================================
// 2. MODIFIER UN CLIENT
// ==========================================
if (isset($_POST['edit_client'])) {
    $idclient = htmlentities($_POST['idclient_hidden']);
    $nom = htmlentities($_POST['nom']);
    $prenom = htmlentities($_POST['prenom']);
    $datenaissance = htmlentities($_POST['datenaissance']);
    $motdepasse_clair = htmlentities($_POST['motdepasse']);

    // Si on a tapé un nouveau mot de passe, on modifie tout
    if (!empty($motdepasse_clair)) {
        $motdepasse_hash = password_hash($motdepasse_clair, PASSWORD_BCRYPT);
        
        $sql = "UPDATE client SET nom=:nom, prenom=:prenom, datenaissance=:datenaissance, motdepasse=:motdepasse WHERE idclient=:idclient";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':datenaissance', $datenaissance);
        $stmt->bindParam(':motdepasse', $motdepasse_hash);
        $stmt->bindParam(':idclient', $idclient);
    } else {
        // Sinon, on modifie tout SAUF le mot de passe
        $sql = "UPDATE client SET nom=:nom, prenom=:prenom, datenaissance=:datenaissance WHERE idclient=:idclient";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':datenaissance', $datenaissance);
        $stmt->bindParam(':idclient', $idclient);
    }

    $resultat = $stmt->execute();
    if ($resultat) {
        $message = "<div style='color:green;'>Client modifié avec succès !</div>";
    } else {
        $message = "<div style='color:red;'>Erreur lors de la modification.</div>";
    }
}

// ==========================================
// 3. SUPPRIMER UN CLIENT
// ==========================================
if (isset($_POST['delete_client'])) {
    $id_a_supprimer = htmlentities($_POST['id_to_delete']);
    
    $sql = "DELETE FROM client WHERE idclient = :idclient";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idclient', $id_a_supprimer);
    
    $resultat = $stmt->execute();
    if ($resultat) {
        $message = "<div style='color:green;'>Client supprimé !</div>";
    }
}

// ==========================================
// PREPARATION FORMULAIRE MODIFICATION
// ==========================================
$client_selectionne = null;
if (isset($_GET['edit'])) {
    $id_recherche = htmlentities($_GET['edit']);
    
    $sql = "SELECT * FROM client WHERE idclient = :idclient";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idclient', $id_recherche);
    $stmt->execute();
    
    $client_selectionne = $stmt->fetch();
}

// ==========================================
// LISTER TOUS LES CLIENTS
// ==========================================
$sql = "SELECT idclient, nom, prenom, datenaissance FROM client ORDER BY nom, prenom";
$stmt = $pdo->query($sql);
$les_clients = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Clients</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Clients</h2>
        
        <?php if (!empty($message)) echo $message; ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <?php if ($client_selectionne != null) { ?>
                <h3 style="margin-top:0;">Modifier le client #<?php echo $client_selectionne['idclient']; ?></h3>
            <?php } else { ?>
                <h3 style="margin-top:0;">Créer un nouveau client</h3>
            <?php } ?>

            <form method="POST" action="admin_clients.php" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                
                <?php if ($client_selectionne != null) { ?>
                    <!-- FORMULAIRE MODIFICATION -->
                    <input type="hidden" name="idclient_hidden" value="<?php echo $client_selectionne['idclient']; ?>">
                    <div>
                        <label>Identifiant (Pseudo)</label>
                        <input type="text" value="<?php echo $client_selectionne['idclient']; ?>" disabled>
                    </div>
                    
                    <div><label>Nom</label><input type="text" name="nom" value="<?php echo $client_selectionne['nom']; ?>" required></div>
                    <div><label>Prénom</label><input type="text" name="prenom" value="<?php echo $client_selectionne['prenom']; ?>" required></div>
                    
                    <?php $dateformatee = date('Y-m-d', strtotime($client_selectionne['datenaissance'])); ?>
                    <div><label>Date de Naissance</label><input type="date" name="datenaissance" value="<?php echo $dateformatee; ?>" required></div>
                    
                    <div>
                        <label>Mot de passe (laisser vide pour ne pas modifier)</label>
                        <input type="password" name="motdepasse">
                    </div>
                    
                    <button type="submit" name="edit_client" class="btn" style="height:35px; padding:0; background:var(--primary); border:none;">Mettre à jour</button>
                    <a href="admin_clients.php" class="btn btn-secondary" style="height:35px; padding:0; line-height:35px; text-align:center; text-decoration:none;">Annuler</a>

                <?php } else { ?>
                    <!-- FORMULAIRE AJOUT -->
                    <div><label>Identifiant (Pseudo)</label><input type="text" name="idclient" required></div>
                    <div><label>Nom</label><input type="text" name="nom" required></div>
                    <div><label>Prénom</label><input type="text" name="prenom" required></div>
                    <div><label>Date de Naissance</label><input type="date" name="datenaissance" required></div>
                    <div><label>Mot de passe</label><input type="password" name="motdepasse" required></div>
                    
                    <button type="submit" name="add_client" class="btn" style="height:35px; padding:0;">Créer le client</button>
                <?php } ?>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Identifiant (Pseudo)</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Date de naissance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($les_clients)) { ?>
                        <tr><td colspan="5">Aucun client existant.</td></tr>
                    <?php } else { 
                        foreach($les_clients as $client) { 
                    ?>
                    <tr>
                        <td><strong><?php echo $client['idclient']; ?></strong></td>
                        <td><?php echo $client['nom']; ?></td>
                        <td><?php echo $client['prenom']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($client['datenaissance'])); ?></td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Confirmer la suppression de ce client ?');">
                                <a href="admin_clients.php?edit=<?php echo urlencode($client['idclient']); ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display:inline-block; margin-bottom:5px;">Modifier</a><br>
                                
                                <input type="hidden" name="id_to_delete" value="<?php echo htmlspecialchars($client['idclient']); ?>">
                                <button type="submit" name="delete_client" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php 
                        } 
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
