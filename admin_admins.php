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
// 1. AJOUTER UN ADMINISTRATEUR
// ==========================================
if (isset($_POST['add_admin'])) {
    $identifiant = htmlentities($_POST['identifiant']);
    $motdepasse_clair = htmlentities($_POST['motdepasse']);

    if (empty($identifiant) || empty($motdepasse_clair)) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        // Hacher le mot de passe 
        $motdepasse_hash = password_hash($motdepasse_clair, PASSWORD_BCRYPT);

        // Requête SQL d'insertion
        $sql = "INSERT INTO administrateur (identifiant, motdepasse) VALUES (:identifiant, :motdepasse)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->bindParam(':motdepasse', $motdepasse_hash);

        $resultat = $stmt->execute();
        if ($resultat) {
            $message = "<div style='color:green;'>Administrateur ajouté avec succès !</div>";
        } else {
            $message = "<div style='color:red;'>Erreur : Cet identifiant existe peut-être déjà.</div>";
        }
    }
}

// ==========================================
// 2. MODIFIER UN ADMINISTRATEUR
// ==========================================
if (isset($_POST['edit_admin'])) {
    $idadmin = htmlentities($_POST['idadmin']);
    $identifiant = htmlentities($_POST['identifiant']);
    $motdepasse_clair = htmlentities($_POST['motdepasse']);

    if (!empty($motdepasse_clair)) {
        // Le mot de passe a été changé
        $motdepasse_hash = password_hash($motdepasse_clair, PASSWORD_BCRYPT);
        
        $sql = "UPDATE administrateur SET identifiant=:identifiant, motdepasse=:motdepasse WHERE idadmin=:idadmin";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->bindParam(':motdepasse', $motdepasse_hash);
        $stmt->bindParam(':idadmin', $idadmin);
    } else {
        // On garde l'ancien mot de passe
        $sql = "UPDATE administrateur SET identifiant=:identifiant WHERE idadmin=:idadmin";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->bindParam(':idadmin', $idadmin);
    }

    $resultat = $stmt->execute();
    if ($resultat) {
        $message = "<div style='color:green;'>Administrateur modifié avec succès !</div>";
    } else {
        $message = "<div style='color:red;'>Erreur de modification ou identifiant déjà pris.</div>";
    }
}

// ==========================================
// 3. SUPPRIMER UN ADMINISTRATEUR
// ==========================================
if (isset($_POST['delete_admin'])) {
    $id_a_supprimer = htmlentities($_POST['id_to_delete']);
    $identifiant_a_supprimer = htmlentities($_POST['del_identifiant']);
    
    // Empêcher l'admin de se supprimer lui-même
    if ($identifiant_a_supprimer === $_SESSION['client_id']) {
        $message = "<div style='color:red;'>Vous ne pouvez pas supprimer votre propre compte.</div>";
    } else {
        $sql = "DELETE FROM administrateur WHERE idadmin = :idadmin";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idadmin', $id_a_supprimer);
        
        $resultat = $stmt->execute();
        if ($resultat) {
            $message = "<div style='color:green;'>Administrateur supprimé !</div>";
        }
    }
}

// ==========================================
// PREPARATION FORMULAIRE MODIFICATION
// ==========================================
$admin_selectionne = null;
if (isset($_GET['edit'])) {
    $id_recherche = htmlentities($_GET['edit']);
    
    $sql = "SELECT * FROM administrateur WHERE idadmin = :idadmin";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idadmin', $id_recherche);
    $stmt->execute();
    
    $admin_selectionne = $stmt->fetch();
}

// ==========================================
// LISTER TOUS LES ADMINS
// ==========================================
$sql = "SELECT idadmin, identifiant FROM administrateur ORDER BY idadmin";
$stmt = $pdo->query($sql);
$les_admins = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Administrateurs</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Administrateurs</h2>
        
        <?php if (!empty($message)) echo $message; ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <?php if ($admin_selectionne != null) { ?>
                <h3 style="margin-top:0;">Modifier l'admin #<?php echo $admin_selectionne['idadmin']; ?></h3>
            <?php } else { ?>
                <h3 style="margin-top:0;">Créer un nouveau admin</h3>
            <?php } ?>

            <form method="POST" action="admin_admins.php" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                
                <?php if ($admin_selectionne != null) { ?>
                    <!-- FORMULAIRE MODIFICATION -->
                    <input type="hidden" name="idadmin" value="<?php echo $admin_selectionne['idadmin']; ?>">
                    
                    <div><label>Identifiant (Login)</label><input type="text" name="identifiant" value="<?php echo $admin_selectionne['identifiant']; ?>" required></div>
                    
                    <div>
                        <label>Mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" name="motdepasse">
                    </div>
                    
                    <button type="submit" name="edit_admin" class="btn" style="height:35px; padding:0; background:var(--primary); border:none;">Mettre à jour</button>
                    <a href="admin_admins.php" class="btn btn-secondary" style="height:35px; padding:0; line-height:35px; text-align:center; text-decoration:none;">Annuler</a>

                <?php } else { ?>
                    <!-- FORMULAIRE AJOUT -->
                    <div><label>Identifiant (Login)</label><input type="text" name="identifiant" required></div>
                    <div><label>Mot de passe</label><input type="password" name="motdepasse" required></div>
                    
                    <button type="submit" name="add_admin" class="btn" style="height:35px; padding:0;">Créer admin</button>
                <?php } ?>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Identifiant (Login)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($les_admins)) { ?>
                        <tr><td colspan="3">Aucun administrateur trouvé.</td></tr>
                    <?php } else { 
                        foreach($les_admins as $admin) { 
                    ?>
                    <tr>
                        <td><strong><?php echo $admin['idadmin']; ?></strong></td>
                        <td><?php echo $admin['identifiant']; ?></td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Confirmer la suppression de cet administrateur ?');">
                                <a href="admin_admins.php?edit=<?php echo $admin['idadmin']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display:inline-block; margin-bottom:5px;">Modifier</a><br>
                                
                                <input type="hidden" name="id_to_delete" value="<?php echo $admin['idadmin']; ?>">
                                <input type="hidden" name="del_identifiant" value="<?php echo htmlspecialchars($admin['identifiant']); ?>">
                                <button type="submit" name="delete_admin" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Supprimer</button>
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
