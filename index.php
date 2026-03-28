<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue à Epsi Voyage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php' ?>

    <section class="hero">
        <h1>Bienvenue à Epsi Voyage</h1>
        <p>Explorez les destinations les plus exotiques, vivez des circuits inoubliables et réservez votre prochaine aventure en quelques clics.</p>
        <div style="margin-top:20px;">
            <a href="circuits.php" class="btn btn-primary" style="font-size:1.2rem; padding: 15px 40px;">Visiter les circuits</a>
        </div>
    </section>

    <div class="container">
        <div class="circuit-grid">
            <div class="card">
                <h3>Nos Engagements</h3>
                <p>Des prix transparents, des guides passionnés et une assistance 24/7 pour vos voyages.</p>
            </div>
            <div class="card">
                <h3>Flexibilité</h3>
                <p>Modifiez vos dates ou annulez sans frais jusqu'à 30 jours avant le départ.</p>
            </div>
            <div class="card">
                <h3>Exclusivité</h3>
                <p>Des parcours hors des sentiers battus pour une immersion totale dans la culture locale.</p>
            </div>
        </div>
    </div>
</body>
</html>
