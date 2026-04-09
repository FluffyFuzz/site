<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/styles/shop_style.css">
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
require_once 'cart_class.php';

// Connexion à la base de donnees
$db = new DB();

// Initialisation du panier
$cart = new Cart($db);


// Gestion de la recherche, des filtres et tris

//Traitement du formulaire
$filters = [];
$orderBy = "name_asc";
$searchTerm = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        $filters = [];
        $orderBy = "name_asc";
        $searchTerm = "";
    } else {
        if (isset($_POST['category'])) {
            $filters = $_POST['category'];
        }
        if (isset($_POST['sort'])) {
            $orderBy = $_POST['sort'];
        }
        if (!empty($_POST['search'])) {
            $searchTerm = $_POST['search'];
        }
    }
}

//Construction de la requête SQL
$query = "SELECT * FROM ARTICLE";
$whereClauses = ["deleted = false"];
$params = [];
// Ajout de la recherche par nom
if (!empty($searchTerm)) {
    $whereClauses[] = "nom_article LIKE ?";
    $params[] = '%' . $searchTerm . '%';
}
// Ajout des filtres par catégorie
if (!empty($filters)) {
    $placeholders = implode(", ", array_fill(0, count($filters), "?"));
    $whereClauses[] = "categorie_article IN ($placeholders)";
    $params = array_merge($params, $filters);
}
// Ajout des clauses WHERE
if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}
// Ajout du tri
if ($orderBy === "price_asc") {
    $query .= " ORDER BY prix_article ASC";
} elseif ($orderBy === "price_desc") {
    $query .= " ORDER BY prix_article DESC";
} elseif ($orderBy === "name_asc") {
    $query .= " ORDER BY nom_article ASC";
} elseif ($orderBy === "name_desc") {
    $query .= " ORDER BY nom_article DESC";
}
// Exécution de la requête
$products = $db->select($query, str_repeat("s", count($params)), $params);
?>




<!--------------->
<!------HTML----->
<!--------------->

<H1>LA BOUTIQUE</H1>

<div id="principal-section">
    <form method="post" id="filter-form">
        <fieldset>
            <input id = "search-input" type="text" name="search" placeholder="Rechercher un article" value="<?= htmlspecialchars($searchTerm) ?>">
        </fieldset>
        <details>
            <summary>Catégories</summary>
            <fieldset>
                <label><input type="checkbox" name="category[]" value="Sucré" <?= in_array('Sucré', $filters) ? 'checked' : '' ?>> Sucré</label><br>
                <label><input type="checkbox" name="category[]" value="Salé" <?= in_array('Salé', $filters) ? 'checked' : '' ?>> Salé</label><br>
                <label><input type="checkbox" name="category[]" value="Boisson" <?= in_array('Boisson', $filters) ? 'checked' : '' ?>> Boisson</label><br>
                <label><input type="checkbox" name="category[]" value="Merch" <?= in_array('Merch', $filters) ? 'checked' : '' ?>> Merch</label>
            </fieldset>
        </details>
        <div>
            <label>Trier par</label>
            <select name="sort">
                <option value="name_asc" <?= $orderBy === 'name_asc' ? 'selected' : '' ?>>Ordre alphabétique (A-Z)</option>
                <option value="name_desc" <?= $orderBy === 'name_desc' ? 'selected' : '' ?>>Ordre anti-alphabétique (Z-A)</option>
                <option value="price_asc" <?= $orderBy === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="price_desc" <?= $orderBy === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
            </select>
        </div>
        <button type="submit" name="reset">Réinitialiser</button>
    </form>

    <div id='cart-info'>
        <button>
            <a href="cart.php">
                <img src="/assets/logo_caddie.png" alt="Logo du panier">
                <p>Panier (<span id="count"><?=$cart->count();?></span>)</p>
            </a>
        </button>
    </div>
</div>

<p id='message-reduc'>
    * Articles non éligibles aux réductions de grade
</p>
<?php if (!empty($products)) : ?>
    <div id="product-list">
        <?php foreach ($products as $product) : ?>
                <div id="one-product">
                    <div>
                        <?php if(empty($product['image_article'])):?>
                            <img src="/admin/ressources/default_images/boutique.png" alt="Image de l'article" />
                        <?php elseif(str_starts_with($product['image_article'], 'http')):?>
                            <img src="<?= htmlspecialchars($product['image_article']) ?>" alt="Image de l'article" />
                        <?php else:?>
                            <img src="/api/files/<?= htmlspecialchars($product['image_article']) ?>" alt="Image de l'article" />
                        <?php endif?>
                        <h3 title="<?= htmlspecialchars($product['nom_article']) ?>">
                            <?= htmlspecialchars($product['nom_article']) ?>
                        </h3>
                        <p><?= number_format(htmlspecialchars($product['prix_article']), 2, ',', ' ') ?> € </p>
                        <p><?= htmlspecialchars($product['xp_article']) ?> XP
                            <?php if (!(int)$product['reduction_article']) { ?>
                            <span>    * </span>
                            <?php } ?>
                        </p>
                    </div>
                    <div>
                        <?php if ((int)$product['stock_article'] >= 0): ?>
                            <p class="stock-count">Stock : <?= (int)$product['stock_article'] ?></p>
                        <?php endif; ?>
                        <p id="stock-status">
                            <?php if ((int)$product['stock_article'] > 0 || (int)$product['stock_article'] < 0) : ?>
                                <a class="addCart" id="add-to-cart-button" href="/cart_add.php?id=<?= htmlspecialchars($product['id_article']) ?>">
                                    Ajouter au panier
                                </a>
                            <?php else : ?>
                                <button id="out-of-stock">Épuisé</button>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>Aucun produit trouvé pour les critères sélectionnés.</p>
<?php endif; ?>




<?php require_once "footer.php" ?>

<!--Dynamisme du panier-->
    <!--Automatisation de la soumission du formulaire (filter-form)-->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.querySelector("#filter-form");
            
            // Soumission du formulaire lorsqu'on appuie sur "Entrée" dans le champ de recherche
            const searchInput = document.querySelector("input[name='search']");
            searchInput.addEventListener("keydown", function (event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    form.submit();
                }
            });

            // Soumission du formulaire lorsqu'une catégorie est sélectionnée
            const detailsElement = document.querySelector("details");
            // Restaurer l'état ouvert du menu si nécessaire
            if (sessionStorage.getItem("details-open") === "true") {
                detailsElement.open = true;
            }
            // Empêcher la fermeture du menu après soumission
            const categoryCheckboxes = document.querySelectorAll("input[name='category[]']");
            categoryCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener("change", function () {
                    // Sauvegarder l'état du menu
                    sessionStorage.setItem("details-open", "true");
                    // Soumettre le formulaire
                    form.submit();
                });
            });
            // Nettoyer l'état lorsque l'utilisateur ferme manuellement le menu
            detailsElement.addEventListener("toggle", function () {
                if (!detailsElement.open) {
                    sessionStorage.removeItem("details-open");
                }
            });

            // Soumission du formulaire lorsqu'une option de tri est sélectionnée
            const sortSelect = document.querySelector("select[name='sort']");
            sortSelect.addEventListener("change", function () {
                form.submit();
            });
        });
    </script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="/scripts/add_cart.js"></script>

</body>
</html>

