<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="/styles/about_style.css">

    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">

</head>


<body class="body_margin">



<!--------------->
<!------PHP------>
<!--------------->


<!-- Importer les fichiers -->
<?php
require_once "header.php";
?>





<!--------------->
<!------HTML----->
<!--------------->


<!-- CORPS DE LA PRESENTATION -->
<H1 class='titre_section_apropos'>LE BUREAU</H1>
<section id="bureau">
    <img src="assets/photo_bureau_ADIIL.png" alt="Photo de groupe du bureau du BDE 2024" />
    <p>Le Bureau des Étudiants "ADIIL" du département informatique de l'IUT de Laval a pour ambition de créer un environnement stimulant et convivial pour tous les étudiants. Notre motivation principale est de favoriser les échanges, de créer des opportunités d’apprentissage et de renforcer les liens entre les membres de notre communauté. Nous organisons des événements variés, allant des ateliers techniques aux soirées conviviales, pour répondre aux intérêts de chacun.</p>
</section>

<H2 class='titre_section_apropos'>LES MEMBRES</H2>
<section>
    
    <div>
        <p>Enzo, notre Président :  <br>Passionné par l’informatique et le travail d’équipe, Enzo est un leader naturel. Il est motivé par l’idée de créer un cadre où chaque étudiant peut s’épanouir. Toujours à l'écoute, il s'assure que toutes les voix sont entendues et que nos projets reflètent les attentes de nos membres.</p>
        <img src="assets/photo_enzo.png" alt="Photo de Enzo Rynders--Vitu" />
    </div>

    <div>
    <img src="assets/photo_tom.png" alt="Photo de Tom Ysope" />

        <p>Tom, le Comptable :  <br>Avec un œil attentif aux détails et une grande rigueur, Tom gère nos finances avec sérieux. Sa motivation vient de son désir d’optimiser les ressources pour que chaque événement soit un succès. Il sait que la bonne gestion est la clé de notre développement.</p>
    </div>

    <div>
        <p>Mathis, Chargé de communication :  <br>Toujours à la recherche de nouvelles façons de communiquer, Mathis a un talent pour la cuisine et la création de contenu. Sa motivation est de faire briller notre BDE et d'informer les étudiants sur nos activités. Il utilise les réseaux sociaux pour créer une véritable communauté en ligne.</p>
        <img src="assets/photo_mathis.png" alt="Photo de Mathis Le Nôtre" />
    </div>

    <div>
    <img src="assets/photo_gemino.png" alt="Photo de Gemino Ruffault--Ravenel" />

        <p>Gémino, Secrétaire : <br>
        Gémino joue un rôle essentiel dans le bon fonctionnement du BDE. Sa motivation réside dans le fait de s'assurer que tout est bien structuré, de la prise de notes lors des réunions à la gestion des documents administratifs. Il aime voir les choses s'organiser efficacement.</p>
    </div>

    <div>
        <p>
        Axel & Julien, Membres : <br>
        Axel et Julien sont les nouvelles recrues pleines d'énergie et d'idées innovantes. Ils sont motivés par le désir d’apporter une touche fraîche à nos événements et de fédérer les étudiants autour de projets communs. Leur enthousiasme contagieux aide à dynamiser notre équipe et à créer une atmosphère joyeuse.</p>
    </div>
    <div class="photo-membres">
        <img src="assets/photo_axel.png" alt="Photo de Enzo Rynders--Vitu" />
        <img src="assets/photo_julien.png" alt="Photo de Julien Dauvergne" />
    </div>

</section>



<H2 class='titre_section_apropos'>DEVENIR ADHÉRENT</H2>
<section id="adhesion">
    <p>Rejoindre l'ADIIL en tant qu'adhérent vous permet de bénéficier de réductions sur les événements et les articles de la boutique, et de soutenir les activités du bureau.</p>
    <h3>Conditions pour devenir adhérent</h3>
    <ul>
        <li>Être étudiant au département informatique de l'IUT de Laval.</li>
        <li>Régler la cotisation annuelle correspondant à votre grade.</li>
        <li>S'inscrire via l'onglet <a href="/grades.php">Grades</a> de votre espace membre.</li>
    </ul>
</section>

<H2 class='titre_section_apropos'>REJOINDRE LE BUREAU</H2>
<section id="bureau-eligibilite">
    <p>Vous souhaitez vous impliquer davantage dans la vie étudiante ? Le bureau de l'ADIIL recrute chaque année de nouveaux membres motivés.</p>
    <h3>Conditions d'éligibilité</h3>
    <ul>
        <li>Être étudiant au département informatique de l'IUT de Laval.</li>
        <li>Être à jour de sa cotisation (être adhérent).</li>
        <li>Se présenter lors de l'Assemblée Générale annuelle ou contacter un membre du bureau actuel.</li>
        <li>Obtenir la majorité des votes des adhérents présents lors de l'élection.</li>
    </ul>
    <p>Pour toute question, n'hésitez pas à nous contacter via la page <a href="/contact.php">Contact</a>.</p>
</section>

<?php require_once "footer.php" ?>

</body>
</html>