<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon panier</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/styles/cart_style.css">

    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">

    <script>
        //Fonction pour valider la soumission du formulaire (form-quantity) par la touche "Entrée"
        function pressEnter (event) {
            var code=event.which || event.keyCode;
            if (code==13) { //Code de la touche "Entrée"
                document.getElementById("form-quantity").submit();
            }
        }
    </script>

</head>

<body class="body_margin">



<!--------------->
<!------PHP------>
<!--------------->

<?php

// Importer les fichiers
require_once "header.php" ;
require_once 'database.php';
require_once 'files_save.php';
require_once 'cart_class.php';

// Connexion à la base de donnees
$db = new DB();

// Initialisation du panier
$cart = new Cart($db);
?>

<!-- On récupère les produits du panier -->
<?php
    $ids = array_keys($_SESSION['cart']);
if (empty($ids)) {
    $products = array();
} else {
    //Préparation de la requete SELECT
    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $query = "SELECT * FROM ARTICLE WHERE id_article IN ($placeholders)";
    $types = str_repeat("i", count($ids));

    $products = $db->select(
        $query,
        $types,
        $ids
    );
}
?>

<!--------------->
<!------HTML----->
<!--------------->

<div>
<H1>MON PANIER</H1>

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

    <div>
        <button id="shop-button" >
            <a href="shop.php">
                <img src="/assets/fleche_retour.png" alt="Fleche de retour">
                Retourner à la boutique
            </a>
        </button>
    </div>
</div>

<?php if (!empty($_SESSION['cart'])) : ?>
<div id='cart-container'>
    <form method="POST" action="/cart.php" id= "form-quantity">
    <table>
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product) :?>
                <tr>
                    <td id='article-cell'>
                        <img src="/api/files/<?php echo $product['image_article']; ?>" alt="Image de l'article" />
                        <p><?= htmlspecialchars($product['nom_article']) ?></p>
                    </td>
                    <td><?= number_format(htmlspecialchars($product['prix_article']), 2, ',', ' ') ?> €</td>                
                    <td><input type='text' name="cart[quantity][<?=$product['id_article']?>]" value="<?=$_SESSION['cart'][$product['id_article']]?>" onkeydown="pressEnter(event)"></td>
                    <td><?= number_format(htmlspecialchars($product['prix_article'] * $_SESSION['cart'][$product['id_article']]), 2, ',', ' ') ?> €</td>  
                    <td>
                        <a href="/cart.php?del=<?= $product['id_article'] ?>">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Nombre d'articles &nbsp : </th>
                    <td><?=$cart->count()?></td>
                </tr>
                <tr>
                    <th>Total &nbsp : </th>
                    <td><?= number_format($cart->total(), 2, ',', ' ') ?> €</td>
                </tr>
                
                <?php if (!empty($_SESSION['userid'])) {
                    // Vérifie l'adhésion de l'utilisateur
                    $adherant = $db->select(
                        "SELECT * FROM ADHESION 
                        INNER JOIN GRADE ON ADHESION.id_grade = GRADE.id_grade 
                        WHERE ADHESION.id_membre = ? AND reduction_grade > 0",
                        "i",
                        [$_SESSION['userid']]
                    );

                    //récupérer la réduction liée au grade
                    if (!empty($adherant)) {
                        $reductionGrade = floatval($adherant[0]["reduction_grade"] ?? 0);
                        $user_reduction = 1 - ($reductionGrade / 100);
                        $totalWithReduc = 0;

                        // Calcule le total en tenant compte des réductions applicables
                        foreach ($products as $product) {
                            if (!empty($product['reduction_article'])) { // Vérifie si une réduction est applicable
                                $totalWithReduc += $product['prix_article'] * $_SESSION['cart'][$product['id_article']] * $user_reduction;
                            } else {
                                $totalWithReduc += $product['prix_article'] * $_SESSION['cart'][$product['id_article']];
                            }
                        }
                        ?>

                        <tr>
                            <th style="min-width: 400px">Total après réductions &nbsp : </th>
                            <td style="min-width: 50px"><?= number_format($totalWithReduc, 2, ',', ' ') ?> €</td>
                        </tr>

                    <?php }
                }?>
            <tfoot>
        </table>
    </form>
</div>
<div>
    <form class="subscription" action="/order.php" method="post">
        <?php
        if (isset($_SESSION['cart'])) {
            // Encodage du panier entier en JSON et transmission dans un seul champ caché
            echo '<input type="hidden" name="cart" value="' . htmlspecialchars(json_encode($_SESSION['cart'], JSON_UNESCAPED_UNICODE)) . '">';
        }
        ?>
        <button type="submit" id='order-button'>
            Commander
        </button>
    </form>
</div>

<?php else : ?>
    <p id="empty-cart">Votre panier est vide</p>
<?php endif; ?>






<?php require_once "footer.php" ?>

</body>
</html>