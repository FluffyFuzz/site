<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="/styles/grade_style.css">

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
require_once "header.php" ;
require_once 'database.php';
require_once 'files_save.php';

// Connexion à la base de donnees
$db = new DB();


//Requête SQL
$products = $db->select("SELECT * FROM GRADE WHERE deleted = false ORDER BY prix_grade");

?>



<!--------------->
<!------HTML----->
<!--------------->

<H1>Les grades</H1>

<!-- Affichage du message de succès ou d'erreur -->
<div>
    <?php
        if (isset($_SESSION['message'])) {
            $messageStyle = isset($_SESSION['message_type']) && $_SESSION['message_type'] === "error" ? "error-message" : "success-message";
            echo '<div id="' . $messageStyle . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']); // Supprimer le message après affichage
            unset($_SESSION['message_type']); // Supprimer le type après affichage
        }
    ?>
</div>

<?php if (!empty($products)) : ?>
    <div id="product-list">
        <?php foreach ($products as $product) : ?>
                <div id="one-product">
                    <div>
                        <?php if(empty($product['image_grade'])):?>
                            <img src="/admin/ressources/default_images/grade.webp" alt="Image du grade" />
                        <?php elseif(str_starts_with($product['image_grade'], 'http')):?>
                            <img src="<?= htmlspecialchars($product['image_grade']) ?>" alt="Image du grade" />
                        <?php else:?>
                            <img src="/api/files/<?= htmlspecialchars($product['image_grade']) ?>" alt="Image du grade" />
                        <?php endif?>

                        <h3 title="<?= htmlspecialchars($product['nom_grade']) ?>">
                            <?= htmlspecialchars($product['nom_grade']) ?>
                        </h3>
                        <?php if (!empty($product['description_grade'])) { ?>
                            <p><?= htmlspecialchars($product['description_grade'])?></p>
                        <?php } ?>
                        <p>-- Prix : <?= number_format(htmlspecialchars($product['prix_grade']), 2, ',', ' ') ?> € --</p>
                    </div>
                    <div>
                        <p id="adhesion-status">

                            <?php
                            if (!empty($_SESSION['userid'])) {
                                $unAdherant = $db->select("SELECT * FROM GRADE INNER JOIN ADHESION ON GRADE.id_grade = ADHESION.id_grade INNER JOIN MEMBRE ON ADHESION.id_membre = MEMBRE.id_membre WHERE GRADE.id_grade = ? AND MEMBRE.id_membre = ?;",
                                "ii",
                                [$product['id_grade'], $_SESSION['userid']]
                                );
                                ?>
                            <?php } ?>
                            <?php if (!empty($_SESSION) && !empty($unAdherant)): ?>
                                <button id="detention">Vous détenez ce grade</button>
                            <?php else: ?>
                                <a id="buy-button" href="/grade_subscription.php?id=<?= htmlspecialchars($product['id_grade']) ?>">
                                    Acheter
                                </a>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>Aucun grade trouvé.</p>
<?php endif; ?>




<?php require_once "footer.php" ?>


</body>
</html>