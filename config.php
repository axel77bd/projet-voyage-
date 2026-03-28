<?php
// config.php
$db_host = 'localhost'; // A modifier selon Nuage Pédagogique
$db_name = 'AgenceVoyage';


try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db_name;charset=utf8", 'login4447', 'jvFWROneSUahYII');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
