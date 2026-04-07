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
// 1. AJOUTER UNE ÉTAPE
// ==========================================
if (isset($_POST['add_etape'])) {
    $identifiant = htmlentities($_POST['identifiant']);
    $ordre = htmlentities($_POST['ordre']);
    $duree = htmlentities($_POST['duree']);
    
    // Le lieu est envoyé sous la forme "nomlieu|ville|pays" depuis le <select>
    // On utilise explode() pour séparer ces 3 parties
    $donnees_lieu = explode('|', $_POST['lieu']);
    $nomlieu = $donnees_lieu[0];
    $ville = $donnees_lieu[1];
    $pays = $donnees_lieu[2];

    if (empty($identifiant) || empty($ordre)) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        $sql = "INSERT INTO etape (identifiant, ordre, duree, nomlieu, ville, pays) 
                VALUES (:identifiant, :ordre, :duree, :nomlieu, :ville, :pays)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->bindParam(':ordre', $ordre);
        $stmt->bindParam(':duree', $duree);
        $stmt->bindParam(':nomlieu', $nomlieu);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':pays', $pays);

        $resultat = $stmt->execute();
        if ($resultat) {
            $message = "<div style='color:green;'>Étape ajoutée avec succès !</div>";
        } else {
            $message = "<div style='color:red;'>Erreur d'ajout. Vérifiez que l'ordre n'existe pas déjà pour ce circuit.</div>";
        }
    }
}

// ==========================================
// 2. MODIFIER UNE ÉTAPE
// ==========================================
if (isset($_POST['edit_etape'])) {
    $old_identifiant = htmlentities($_POST['old_identifiant']);
    $old_ordre = htmlentities($_POST['old_ordre']);
    $duree = htmlentities($_POST['duree']);
    
    $donnees_lieu = explode('|', $_POST['lieu']);
    $nomlieu = $donnees_lieu[0];
    $ville = $donnees_lieu[1];
    $pays = $donnees_lieu[2];

    // On modifie l'étape en utilisant l'ancien identifiant et l'ancien ordre comme repère (WHERE)
    $sql = "UPDATE etape 
            SET duree=:duree, nomlieu=:nomlieu, ville=:ville, pays=:pays 
            WHERE identifiant=:old_identifiant AND ordre=:old_ordre";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':duree', $duree);
    $stmt->bindParam(':nomlieu', $nomlieu);
    $stmt->bindParam(':ville', $ville);
    $stmt->bindParam(':pays', $pays);
    $stmt->bindParam(':old_identifiant', $old_identifiant);
    $stmt->bindParam(':old_ordre', $old_ordre);

    $resultat = $stmt->execute();
    if ($resultat) {
        $message = "<div style='color:green;'>Étape modifiée avec succès !</div>";
    } else {
        $message = "<div style='color:red;'>Erreur lors de la modification.</div>";
    }
}

// ==========================================
// 3. SUPPRIMER UNE ÉTAPE
// ==========================================
if (isset($_POST['delete_etape'])) {
    $del_identifiant = htmlentities($_POST['del_identifiant']);
    $del_ordre = htmlentities($_POST['del_ordre']);
    
    $sql = "DELETE FROM etape WHERE identifiant=:identifiant AND ordre=:ordre";
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':identifiant', $del_identifiant);
    $stmt->bindParam(':ordre', $del_ordre);
    
    $resultat = $stmt->execute();
    if ($resultat) {
        $message = "<div style='color:green;'>Étape supprimée !</div>";
    }
}

// ==========================================
// PREPARATION FORMULAIRE MODIFICATION
// ==========================================
$etape_selectionnee = null;
if (isset($_GET['edit_id']) && isset($_GET['edit_ordre'])) {
    $edit_id = htmlentities($_GET['edit_id']);
    $edit_ordre = htmlentities($_GET['edit_ordre']);
    
    $sql = "SELECT * FROM etape WHERE identifiant=:identifiant AND ordre=:ordre";
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':identifiant', $edit_id);
    $stmt->bindParam(':ordre', $edit_ordre);
    $stmt->execute();
    
    $etape_selectionnee = $stmt->fetch();
}

// ==========================================
// LISTER TOUTES LES DONNEES UTILES
// ==========================================
// 1. Liste des étapes avec le nom du circuit
$sql_etapes = "SELECT e.*, c.descriptif AS circuit_desc FROM etape e JOIN Circuit c ON e.identifiant = c.identifiant ORDER BY e.identifiant, e.ordre";
$les_etapes = $pdo->query($sql_etapes)->fetchAll();

// 2. Liste de tous les circuits pour le menu déroulant
$sql_circuits = "SELECT identifiant, descriptif FROM Circuit ORDER BY identifiant DESC";
$les_circuits = $pdo->query($sql_circuits)->fetchAll();

// 3. Liste de tous les lieux pour le menu déroulant
$sql_lieux = "SELECT nomlieu, ville, pays FROM lieuavisiter ORDER BY pays, ville, nomlieu";
$les_lieux = $pdo->query($sql_lieux)->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Étapes</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input, .admin-form select { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Étapes</h2>
        
        <?php if (!empty($message)) echo $message; ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <?php if ($etape_selectionnee != null) { ?>
                <h3 style="margin-top:0;">Modifier l'étape</h3>
            <?php } else { ?>
                <h3 style="margin-top:0;">Créer une nouvelle étape</h3>
            <?php } ?>

            <form method="POST" action="admin_etapes.php" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                
                <?php if ($etape_selectionnee != null) { ?>
                    <!-- FORMULAIRE MODIFICATION -->
                    <input type="hidden" name="old_identifiant" value="<?php echo $etape_selectionnee['identifiant']; ?>">
                    <input type="hidden" name="old_ordre" value="<?php echo $etape_selectionnee['ordre']; ?>">
                    
                    <div>
                        <label>Circuit</label>
                        <select name="identifiant" disabled>
                            <option value="<?php echo $etape_selectionnee['identifiant']; ?>"><?php echo $etape_selectionnee['identifiant']; ?></option>
                        </select>
                    </div>

                    <div>
                        <label>Ordre (N° de l'étape)</label>
                        <input type="number" name="ordre" value="<?php echo $etape_selectionnee['ordre']; ?>" disabled>
                    </div>

                    <div>
                        <label>Durée (jours)</label>
                        <input type="number" name="duree" value="<?php echo $etape_selectionnee['duree']; ?>" required>
                    </div>

                    <div>
                        <label>Lieu à visiter</label>
                        <select name="lieu" required>
                            <option value="">Sélectionner un lieu</option>
                            <?php foreach($les_lieux as $l) { 
                                // On crée la valeur combi "nom|ville|pays"
                                $valeur_option = $l['nomlieu'].'|'.$l['ville'].'|'.$l['pays'];
                                
                                // On vérifie si c'est le lieu de notre étape pour le pré-sélectionner
                                $est_selectionne = "";
                                if ($etape_selectionnee['nomlieu'] == $l['nomlieu'] && $etape_selectionnee['ville'] == $l['ville'] && $etape_selectionnee['pays'] == $l['pays']) {
                                    $est_selectionne = "selected";
                                }
                            ?>
                                <option value="<?php echo $valeur_option; ?>" <?php echo $est_selectionne; ?>>
                                    <?php echo $l['nomlieu'] . " (" . $l['ville'] . ", " . $l['pays'] . ")"; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="edit_etape" class="btn" style="height:35px; padding:0; background:var(--primary); border:none;">Mettre à jour</button>
                    <a href="admin_etapes.php" class="btn btn-secondary" style="height:35px; padding:0; line-height:35px; text-align:center; text-decoration:none;">Annuler</a>

                <?php } else { ?>
                    <!-- FORMULAIRE AJOUT -->
                    <div>
                        <label>Circuit</label>
                        <select name="identifiant" required>
                            <option value="">Sélectionner un circuit</option>
                            <?php foreach($les_circuits as $c) { ?>
                                <option value="<?php echo $c['identifiant']; ?>"><?php echo $c['identifiant'] . ' - ' . $c['descriptif']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div><label>Ordre (N° de l'étape)</label><input type="number" name="ordre" required></div>
                    <div><label>Durée (jours)</label><input type="number" name="duree" required></div>

                    <div>
                        <label>Lieu à visiter</label>
                        <select name="lieu" required>
                            <option value="">Sélectionner un lieu</option>
                            <?php foreach($les_lieux as $l) { 
                                $valeur_option = $l['nomlieu'].'|'.$l['ville'].'|'.$l['pays'];
                            ?>
                                <option value="<?php echo $valeur_option; ?>">
                                    <?php echo $l['nomlieu'] . " (" . $l['ville'] . ", " . $l['pays'] . ")"; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_etape" class="btn" style="height:35px; padding:0;">Ajouter l'étape</button>
                <?php } ?>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Circuit (ID)</th>
                        <th>Ordre</th>
                        <th>Durée</th>
                        <th>Lieu</th>
                        <th>Ville, Pays</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($les_etapes)) { ?>
                        <tr><td colspan="6">Aucune étape existante.</td></tr>
                    <?php } else { 
                        foreach($les_etapes as $e) { 
                    ?>
                    <tr>
                        <td><strong><?php echo $e['identifiant']; ?></strong> - <?php echo $e['circuit_desc']; ?></td>
                        <td><?php echo $e['ordre']; ?></td>
                        <td><?php echo $e['duree']; ?> jours</td>
                        <td><?php echo $e['nomlieu']; ?></td>
                        <td><?php echo $e['ville'] . ", " . $e['pays']; ?></td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Confirmer la suppression de cette étape ?');">
                                <a href="admin_etapes.php?edit_id=<?php echo $e['identifiant']; ?>&edit_ordre=<?php echo $e['ordre']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display:inline-block; margin-bottom:5px;">Modifier</a><br>
                                
                                <input type="hidden" name="del_identifiant" value="<?php echo $e['identifiant']; ?>">
                                <input type="hidden" name="del_ordre" value="<?php echo $e['ordre']; ?>">
                                <button type="submit" name="delete_etape" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Supprimer</button>
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
