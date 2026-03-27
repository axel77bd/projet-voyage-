<?php
// config.php
$db_host = 'localhost'; // A modifier selon Nuage Pédagogique
$db_name = 'AgenceVoyage';
$db_user = 'root'; // A modifier
$db_pass = ''; // A modifier

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
