<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar">
    <a href="index.php" class="logo">Epsi Voyage</a>
    <div class="nav-links">
        <a href="index.php">Accueil</a>
        <a href="circuits.php">Circuits</a>
        
        <?php if (isset($_SESSION['role'])): ?>
            <?php if ($_SESSION['role'] === 'client'): ?>
                <a href="reservations.php">Mes Réservations</a>
                <a href="profil.php">Mon Profil</a>
                <span class="user-info">
                    <?= htmlspecialchars($_SESSION['client_id']) ?> 
                    <?php if(isset($_SESSION['nom']) && isset($_SESSION['prenom'])): ?>
                        - <?= htmlspecialchars($_SESSION['nom'] . " " . $_SESSION['prenom']) ?>
                    <?php endif; ?>
                </span>
            <?php else: ?>
                <a href="admin_dashboard.php">Gestion Circuits</a>
                <a href="#">Étapes</a>
                <a href="#">Clients</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-logout">Déconnexion</a>
        <?php else: ?>
            <a href="login.php">Connexion</a>
            <a href="register.php" class="btn btn-secondary" style="padding: 8px 15px; margin-left: 10px;">Inscription</a>
        <?php endif; ?>
    </div>
</nav>
