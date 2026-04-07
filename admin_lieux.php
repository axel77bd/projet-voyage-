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
// 1. AJOUTER UN LIEU
// ==========================================
if (isset($_POST['add_lieu'])) {
    $nomlieu = htmlentities($_POST['nomlieu']);
    $ville = htmlentities($_POST['ville']);
    $pays = htmlentities($_POST['pays']);
    $descriptif = htmlentities($_POST['descriptif']);
    $prixvisite = htmlentities($_POST['prixvisite']);

    if (empty($nomlieu) || empty($ville) || empty($pays)) {
        $message = "Veuillez remplir le nom du lieu, la ville et le pays.";
    } else {
        $sql = "INSERT INTO lieuavisiter (nomlieu, ville, pays, descriptif, prixvisite) 
                VALUES (:nomlieu, :ville, :pays, :descriptif, :prixvisite)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':nomlieu', $nomlieu);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':pays', $pays);
        $stmt->bindParam(':descriptif', $descriptif);
        $stmt->bindParam(':prixvisite', $prixvisite);

        $resultat = $stmt->execute();
        if ($resultat) {
            $message = "<div style='color:green;'>Lieu ajouté avec succès !</div>";
        } else {
            $message = "<div style='color:red;'>Erreur d'ajout. Ce lieu existe peut-être déjà.</div>";
        }
    }
}

// ==========================================
// 2. MODIFIER UN LIEU
// ==========================================
if (isset($_POST['edit_lieu'])) {
    $old_nomlieu = htmlentities($_POST['old_nomlieu']);
    $old_ville = htmlentities($_POST['old_ville']);
    $old_pays = htmlentities($_POST['old_pays']);
    
    $descriptif = htmlentities($_POST['descriptif']);
    $prixvisite = htmlentities($_POST['prixvisite']);

    $sql = "UPDATE lieuavisiter 
            SET descriptif=:descriptif, prixvisite=:prixvisite 
            WHERE nomlieu=:old_nomlieu AND ville=:old_ville AND pays=:old_pays";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':descriptif', $descriptif);
    $stmt->bindParam(':prixvisite', $prixvisite);
    $stmt->bindParam(':old_nomlieu', $old_nomlieu);
    $stmt->bindParam(':old_ville', $old_ville);
    $stmt->bindParam(':old_pays', $old_pays);

    $resultat = $stmt->execute();
    if ($resultat) {
        $message = "<div style='color:green;'>Lieu modifié avec succès !</div>";
    } else {
        $message = "<div style='color:red;'>Erreur lors de la modification.</div>";
    }
}

// ==========================================
// 3. SUPPRIMER UN LIEU
// ==========================================
if (isset($_POST['delete_lieu'])) {
    $del_nomlieu = htmlentities($_POST['del_nomlieu']);
    $del_ville = htmlentities($_POST['del_ville']);
    $del_pays = htmlentities($_POST['del_pays']);
    
    $sql = "DELETE FROM lieuavisiter WHERE nomlieu=:nomlieu AND ville=:ville AND pays=:pays";
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':nomlieu', $del_nomlieu);
    $stmt->bindParam(':ville', $del_ville);
    $stmt->bindParam(':pays', $del_pays);
    
    $resultat = $stmt->execute();
    if ($resultat) {
        $message = "<div style='color:green;'>Lieu supprimé !</div>";
    }
}

// ==========================================
// PREPARATION FORMULAIRE MODIFICATION
// ==========================================
$lieu_selectionne = null;
if (isset($_GET['edit_nom']) && isset($_GET['edit_ville']) && isset($_GET['edit_pays'])) {
    $edit_nom = htmlentities($_GET['edit_nom']);
    $edit_ville = htmlentities($_GET['edit_ville']);
    $edit_pays = htmlentities($_GET['edit_pays']);
    
    $sql = "SELECT * FROM lieuavisiter WHERE nomlieu=:nomlieu AND ville=:ville AND pays=:pays";
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':nomlieu', $edit_nom);
    $stmt->bindParam(':ville', $edit_ville);
    $stmt->bindParam(':pays', $edit_pays);
    $stmt->execute();
    
    $lieu_selectionne = $stmt->fetch();
}

// ==========================================
// LISTER TOUS LES LIEUX
// ==========================================
$sql = "SELECT * FROM lieuavisiter ORDER BY pays, ville, nomlieu";
$stmt = $pdo->query($sql);
$les_lieux = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Lieux à visiter</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Lieux à Visiter</h2>
        
        <?php if (!empty($message)) echo $message; ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <?php if ($lieu_selectionne != null) { ?>
                <h3 style="margin-top:0;">Modifier le lieu</h3>
            <?php } else { ?>
                <h3 style="margin-top:0;">Créer un nouveau lieu</h3>
            <?php } ?>

            <form method="POST" action="admin_lieux.php" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                
                <?php if ($lieu_selectionne != null) { ?>
                    <!-- FORMULAIRE MODIFICATION -->
                    <input type="hidden" name="old_nomlieu" value="<?php echo $lieu_selectionne['nomlieu']; ?>">
                    <input type="hidden" name="old_ville" value="<?php echo $lieu_selectionne['ville']; ?>">
                    <input type="hidden" name="old_pays" value="<?php echo $lieu_selectionne['pays']; ?>">
                    
                    <div><label>Nom du lieu</label><input type="text" name="nomlieu" value="<?php echo $lieu_selectionne['nomlieu']; ?>" disabled></div>
                    <div><label>Ville</label><input type="text" name="ville" value="<?php echo $lieu_selectionne['ville']; ?>" disabled></div>
                    <div><label>Pays</label><input type="text" name="pays" value="<?php echo $lieu_selectionne['pays']; ?>" disabled></div>
                    
                    <div><label>Descriptif</label><input type="text" name="descriptif" value="<?php echo $lieu_selectionne['descriptif']; ?>" required></div>
                    <div><label>Prix de la visite (€)</label><input type="number" step="0.01" name="prixvisite" value="<?php echo $lieu_selectionne['prixvisite']; ?>" required></div>
                    
                    <button type="submit" name="edit_lieu" class="btn" style="height:35px; padding:0; background:var(--primary); border:none;">Mettre à jour</button>
                    <a href="admin_lieux.php" class="btn btn-secondary" style="height:35px; padding:0; line-height:35px; text-align:center; text-decoration:none;">Annuler</a>

                <?php } else { ?>
                    <!-- FORMULAIRE AJOUT -->
                    <div><label>Nom du lieu</label><input type="text" name="nomlieu" required></div>
                    <div><label>Ville</label><input type="text" name="ville" required></div>
                    <div><label>Pays</label><input type="text" name="pays" required></div>
                    <div><label>Descriptif</label><input type="text" name="descriptif" required></div>
                    <div><label>Prix de la visite (€)</label><input type="number" step="0.01" name="prixvisite" required></div>
                    
                    <button type="submit" name="add_lieu" class="btn" style="height:35px; padding:0;">Ajouter le lieu</button>
                <?php } ?>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nom du lieu</th>
                        <th>Ville</th>
                        <th>Pays</th>
                        <th>Descriptif</th>
                        <th>Prix Visite</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($les_lieux)) { ?>
                        <tr><td colspan="6">Aucun lieu existant.</td></tr>
                    <?php } else { 
                        foreach($les_lieux as $l) { 
                    ?>
                    <tr>
                        <td><strong><?php echo $l['nomlieu']; ?></strong></td>
                        <td><?php echo $l['ville']; ?></td>
                        <td><?php echo $l['pays']; ?></td>
                        <td><?php echo $l['descriptif']; ?></td>
                        <td><?php echo number_format($l['prixvisite'], 2); ?> €</td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Confirmer la suppression de ce lieu ?');">
                                <a href="admin_lieux.php?edit_nom=<?php echo urlencode($l['nomlieu']); ?>&edit_ville=<?php echo urlencode($l['ville']); ?>&edit_pays=<?php echo urlencode($l['pays']); ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display:inline-block; margin-bottom:5px;">Modifier</a><br>
                                
                                <input type="hidden" name="del_nomlieu" value="<?php echo $l['nomlieu']; ?>">
                                <input type="hidden" name="del_ville" value="<?php echo $l['ville']; ?>">
                                <input type="hidden" name="del_pays" value="<?php echo $l['pays']; ?>">
                                <button type="submit" name="delete_lieu" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Supprimer</button>
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
