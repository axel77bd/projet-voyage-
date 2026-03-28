<?php
require 'config.php';

$password_tapé = "77";
$identifiant_test = "admin";

$stmt = $pdo->prepare("SELECT * FROM administrateur WHERE identifiant = ?");
$stmt->execute([$identifiant_test]);
$user = $stmt->fetch();

echo "<h3>Résultat du test :</h3>";
if ($type === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM administrateur WHERE identifiant = ?");
    $stmt->execute([$identifiant]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die("ERREUR : L'identifiant '" . htmlspecialchars($identifiant) . "' n'existe pas dans la table 'administrateur'.");
    }

    if (password_verify($password, $user['motdepasse'])) {
        die("SUCCÈS : Le mot de passe est bon !");
    } else {
        echo "ERREUR : Le mot de passe est faux.<br>";
        echo "Mot de passe tapé : " . htmlspecialchars($password) . "<br>";
        echo "Hash trouvé en base : " . $user['motdepasse'];
        die();
    }
}