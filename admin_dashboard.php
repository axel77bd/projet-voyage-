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
// 1. AJOUTER UN CIRCUIT
// ==========================================
if (isset($_POST['add_circuit'])) {
    // 1. Récupérer et sécuriser les données envoyées par le formulaire
    $identifiant = htmlentities($_POST['identifiant']);
    $descriptif = htmlentities($_POST['descriptif']);
    $villedepart = htmlentities($_POST['villedepart']);
    $villearrivee = htmlentities($_POST['villearrivee']);
    $paysarrivee = htmlentities($_POST['paysarrivee']);
    $datedepart = htmlentities($_POST['datedepart']);
    $nbrplacedisponible = htmlentities($_POST['nbrplacedisponible']);
    $duree = htmlentities($_POST['duree']);
    $prixinscription = htmlentities($_POST['prixinscription']);

    // 2. Vérifier que les champs importants ne sont pas vides
    if (empty($identifiant) || empty($descriptif)) {
        $message = "Veuillez remplir au moins l'identifiant et le descriptif.";
    } else {
        // 3. Préparer la requête SQL pour insérer (ajouter)
        $sql = "INSERT INTO Circuit (identifiant, descriptif, villedepart, villearrivee, paysarrivee, datedepart, nbrplacedisponible, duree, prixinscription) 
                VALUES (:identifiant, :descriptif, :villedepart, :villearrivee, :paysarrivee, :datedepart, :nbrplacedisponible, :duree, :prixinscription)";
        $stmt = $pdo->prepare($sql);
        
        // 4. Lier les données aux paramètres de la requête
        $stmt->bindParam(':identifiant', $identifiant);
        $stmt->bindParam(':descriptif', $descriptif);
        $stmt->bindParam(':villedepart', $villedepart);
        $stmt->bindParam(':villearrivee', $villearrivee);
        $stmt->bindParam(':paysarrivee', $paysarrivee);
        $stmt->bindParam(':datedepart', $datedepart);
        $stmt->bindParam(':nbrplacedisponible', $nbrplacedisponible);
        $stmt->bindParam(':duree', $duree);
        $stmt->bindParam(':prixinscription', $prixinscription);

        // 5. Exécuter la requête et vérifier le résultat
        $resultat = $stmt->execute();
        if ($resultat) {
            $message = "<div style='color:green;'>Circuit ajouté avec succès !</div>";
        } else {
            $message = "<div style='color:red;'>Erreur : impossible d'ajouter le circuit. L'identifiant existe peut-être déjà.</div>";
        }
    }
}

// ==========================================
// 2. MODIFIER UN CIRCUIT
// ==========================================
if (isset($_POST['edit_circuit'])) {
    $identifiant = htmlentities($_POST['identifiant']);
    $descriptif = htmlentities($_POST['descriptif']);
    $villedepart = htmlentities($_POST['villedepart']);
    $villearrivee = htmlentities($_POST['villearrivee']);
    $paysarrivee = htmlentities($_POST['paysarrivee']);
    $datedepart = htmlentities($_POST['datedepart']);
    $nbrplacedisponible = htmlentities($_POST['nbrplacedisponible']);
    $duree = htmlentities($_POST['duree']);
    $prixinscription = htmlentities($_POST['prixinscription']);

    // On met à jour (UPDATE) le circuit correspondant à l'identifiant
    $sql = "UPDATE Circuit SET descriptif=:descriptif, villedepart=:villedepart, villearrivee=:villearrivee, paysarrivee=:paysarrivee, datedepart=:datedepart, nbrplacedisponible=:nbrplacedisponible, duree=:duree, prixinscription=:prixinscription WHERE identifiant=:identifiant";
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':identifiant', $identifiant);
    $stmt->bindParam(':descriptif', $descriptif);
    $stmt->bindParam(':villedepart', $villedepart);
    $stmt->bindParam(':villearrivee', $villearrivee);
    $stmt->bindParam(':paysarrivee', $paysarrivee);
    $stmt->bindParam(':datedepart', $datedepart);
    $stmt->bindParam(':nbrplacedisponible', $nbrplacedisponible);
    $stmt->bindParam(':duree', $duree);
    $stmt->bindParam(':prixinscription', $prixinscription);

    $resultat = $stmt->execute();
    if ($resultat) {
        $message = "<div style='color:green;'>Circuit modifié avec succès !</div>";
    } else {
        $message = "<div style='color:red;'>Erreur lors de la modification.</div>";
    }
}

// ==========================================
// 3. SUPPRIMER UN CIRCUIT
// ==========================================
if (isset($_POST['delete_circuit'])) {
    $id_a_supprimer = htmlentities($_POST['id_to_delete']);
    
    // Supprimer le circuit (DELETE)
    $sql = "DELETE FROM Circuit WHERE identifiant = :identifiant";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':identifiant', $id_a_supprimer);
    
    $resultat = $stmt->execute();
    if ($resultat) {
        $message = "<div style='color:green;'>Circuit supprimé !</div>";
    }
}

// ==========================================
// PREPARATION FORMULAIRE MODIFICATION
// ==========================================
// Si on a cliqué sur le bouton "Modifier", on récupère les infos du circuit pour les mettre dans le formulaire
$circuit_selectionne = null;
if (isset($_GET['edit'])) {
    $id_recherche = htmlentities($_GET['edit']);
    $sql = "SELECT * FROM Circuit WHERE identifiant = :identifiant";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':identifiant', $id_recherche);
    $stmt->execute();
    
    // fetch() récupère une seule ligne
    $circuit_selectionne = $stmt->fetch(); 
}

// ==========================================
// LISTER TOUS LES CIRCUITS
// ==========================================
$sql = "SELECT * FROM Circuit ORDER BY identifiant DESC";
$stmt = $pdo->query($sql);
// fetchAll() récupère tous les circuits pour les afficher dans le tableau
$les_circuits = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Circuits</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-form input { padding: 8px; background: #0d1117; color: #fff; border: 1px solid var(--border-color); border-radius: 4px; width: 100%; box-sizing: border-box; }
        .admin-form label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;}
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>

    <div class="container">
        <h2>Gestion des Circuits</h2>
        
        <?php 
        // Afficher le message d'erreur ou de succès s'il y en a un
        if (!empty($message)) { 
            echo $message; 
        } 
        ?>

        <div class="auth-card" style="max-width: 100%; margin: 20px 0; padding:20px;">
            <?php if ($circuit_selectionne != null) { ?>
                <h3 style="margin-top:0;">Modifier le circuit #<?php echo $circuit_selectionne['identifiant']; ?></h3>
            <?php } else { ?>
                <h3 style="margin-top:0;">Créer un nouveau circuit</h3>
            <?php } ?>

            <form method="POST" action="admin_dashboard.php" class="admin-form" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                
                <?php if ($circuit_selectionne != null) { 
                    // FORMULAIRE EN MODE "MODIFICATION"
                ?>
                    <input type="hidden" name="identifiant" value="<?php echo $circuit_selectionne['identifiant']; ?>">
                    <div><label>ID Circuit</label><input type="number" value="<?php echo $circuit_selectionne['identifiant']; ?>" disabled></div>
                    
                    <div><label>Descriptif</label><input type="text" name="descriptif" value="<?php echo $circuit_selectionne['descriptif']; ?>" required></div>
                    <div><label>Ville de départ</label><input type="text" name="villedepart" value="<?php echo $circuit_selectionne['villedepart']; ?>" required></div>
                    <div><label>Ville d'arrivée</label><input type="text" name="villearrivee" value="<?php echo $circuit_selectionne['villearrivee']; ?>" required></div>
                    <div><label>Pays de destination</label><input type="text" name="paysarrivee" value="<?php echo $circuit_selectionne['paysarrivee']; ?>" required></div>
                    
                    <?php 
                        // Formater la date pour le champ 'datetime-local'
                        $dateformate = date('Y-m-d\TH:i', strtotime($circuit_selectionne['datedepart'])); 
                    ?>
                    <div><label>Date de départ</label><input type="datetime-local" name="datedepart" value="<?php echo $dateformate; ?>" required></div>
                    
                    <div><label>Durée (jours)</label><input type="number" name="duree" value="<?php echo $circuit_selectionne['duree']; ?>" required></div>
                    <div><label>Places disponibles</label><input type="number" name="nbrplacedisponible" value="<?php echo $circuit_selectionne['nbrplacedisponible']; ?>" required></div>
                    <div><label>Prix Inscription (€)</label><input type="number" step="0.01" name="prixinscription" value="<?php echo $circuit_selectionne['prixinscription']; ?>" required></div>
                    
                    <button type="submit" name="edit_circuit" class="btn" style="height:35px; padding:0; background:var(--primary); border:none;">Mettre à jour</button>
                    <a href="admin_dashboard.php" class="btn btn-secondary" style="height:35px; padding:0; line-height:35px; text-align:center; text-decoration:none;">Annuler</a>

                <?php } else { 
                    // FORMULAIRE EN MODE "NOUVEAU"
                ?>
                    <div><label>ID Circuit</label><input type="number" name="identifiant" required></div>
                    <div><label>Descriptif</label><input type="text" name="descriptif" required></div>
                    <div><label>Ville de départ</label><input type="text" name="villedepart" required></div>
                    <div><label>Ville d'arrivée</label><input type="text" name="villearrivee" required></div>
                    <div><label>Pays de destination</label><input type="text" name="paysarrivee" required></div>
                    <div><label>Date de départ</label><input type="datetime-local" name="datedepart" required></div>
                    <div><label>Durée (jours)</label><input type="number" name="duree" required></div>
                    <div><label>Places disponibles</label><input type="number" name="nbrplacedisponible" required></div>
                    <div><label>Prix Inscription (€)</label><input type="number" step="0.01" name="prixinscription" required></div>
                    
                    <button type="submit" name="add_circuit" class="btn" style="height:35px; padding:0;">Ajouter le circuit</button>
                <?php } ?>
            </form>
        </div>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descriptif</th>
                        <th>Départ</th>
                        <th>Arrivée</th>
                        <th>Date de départ</th>
                        <th>Places</th>
                        <th>Prix Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($les_circuits)) { ?>
                        <tr><td colspan="8">Aucun circuit existant.</td></tr>
                    <?php } else { 
                        // Boucle pour afficher chaque circuit ligne par ligne
                        foreach($les_circuits as $circuit) { 
                    ?>
                    <tr>
                        <td><strong><?php echo $circuit['identifiant']; ?></strong></td>
                        <td><?php echo $circuit['descriptif']; ?></td>
                        <td><?php echo $circuit['villedepart']; ?></td>
                        <td><?php echo $circuit['villearrivee']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($circuit['datedepart'])); ?></td>
                        <td><?php echo $circuit['nbrplacedisponible']; ?></td>
                        <td><?php echo number_format($circuit['prixinscription'], 2); ?> €</td>
                        <td>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Voulez-vous vraiment supprimer ce circuit ?');">
                                <a href="admin_dashboard.php?edit=<?php echo $circuit['identifiant']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display:inline-block; margin-bottom:5px;">Modifier</a><br>
                                
                                <input type="hidden" name="id_to_delete" value="<?php echo $circuit['identifiant']; ?>">
                                <button type="submit" name="delete_circuit" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Supprimer</button>
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
