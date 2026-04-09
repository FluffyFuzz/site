<link rel="shortcut icon" href="/admin/ressources/favicon.png" type="image/x-icon">

<?php
    @session_start();
    $isUserLoggedIn = isset($_SESSION['userid']);
    $isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] ;
?>


<!-- HEADER -->
<header>
    <a id="accueil" href="/index.php">
        <img src="/assets/logo.png" alt="Logo de l'ADIIL">
    </a>
    <nav>
        <ul>
            <li>
                <a href="/events.php">Événements</a>
            </li>
            <li>
                <a href="/news.php">Actualités</a>
            </li>
            <li>
                <a href="/shop.php">Boutique</a>
            </li>
            <li>
                <a href="/grade.php">Grades</a>
            </li>
            
            <?php if ($isUserLoggedIn): ?>
                <li>
                    <a href="/agenda.php">Agenda</a>
                </li>
            <?php endif; ?>

            <li>
                <a href="/about.php">À propos</a>
            </li>

            <?php if ($isUserLoggedIn): ?>
                <?php if ($isAdmin): ?>
                <li class="header-dropdown">
                    <span class="header-dropdown-toggle">⚙</span>
                    <ul class="header-dropdown-menu">
                        <li><a href="/account.php">Mon compte</a></li>
                        <li><a href="/admin/admin.php">Panel Admin</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li>
                    <a href="/account.php">Mon compte</a>
                </li>
                <?php endif; ?>

            <?php else: ?>
                <li>
                    <a href="/login.php">Se connecter</a>
                </li>
            <?php endif; ?>

      
        </ul>
    </nav>
</header>
