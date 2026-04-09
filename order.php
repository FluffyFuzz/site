<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commander</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/styles/order_style.css">

    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">

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
$cart = new cart($db);



$isLoggedIn = isset($_SESSION["userid"]);
if (!$isLoggedIn) {
    header("Location: /login.php");
    exit;
}

$userid = $_SESSION["userid"];

$membre_info = $db->select(
    "SELECT prenom_membre, nom_membre, email_membre FROM MEMBRE WHERE id_membre = ?",
    "i", [$userid]
)[0];

// Récupérer le panier
if (empty($_SESSION['cart'])) {
    header("Location: /cart.php");
    exit;
}

// Calculer le total de la commande
$total = 0;
$cart = $_SESSION['cart'];
$product_ids = array_keys($cart);
$placeholders = implode(",", array_fill(0, count($product_ids), "?"));
$query = "SELECT * FROM ARTICLE WHERE id_article IN ($placeholders)";
$types = str_repeat("i", count($product_ids));
$products = $db->select($query, $types, $product_ids);

$cart_items = [];
$total_xp = 0;
foreach ($products as $product) {
    if(
        $product['stock_article'] > 0 && $_SESSION['cart'][$product['id_article']] > $product['stock_article']
    ){
        $cart[$product['id_article']] = $product['stock_article'];
    }
    $qte = $cart[$product['id_article']];
    $total_xp += ($product['xp_article'] ?? 0) * $qte;
    $cart_items[$product['id_article']] = [
        'nom_article' => $product['nom_article'],
        'prix_article' => $product['prix_article'],
        'quantite' => $qte,
    ];
    $total += $product['prix_article'] * $cart[$product['id_article']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['mode_paiement']) && !empty($_POST['mode_paiement'])) {
        $mode_paiement = $_POST['mode_paiement'];

        // Enregistrer la commande dans la base de données
        foreach ($cart_items as $product_id => $item) {
            $db->query(
                "CALL achat_article(?, ?, ?, ?)",
                "iiis",
                [$userid, $product_id, $item['quantite'], $mode_paiement]
            );
        }
        $_SESSION['cart'] = [];
        
        $_SESSION['message'] = "Commande réalisée avec succès !";
        $_SESSION['message_type'] = "success";

        header("Location: /cart.php"); // Rediriger vers le panier
        exit;
    } else {
    }
}
?>



<!--------------->
<!------HTML----->
<!--------------->

<h1>MA COMMANDE</h1>

<div id="member-info">
    <h3>Vos informations</h3>
    <p><strong>Nom :</strong> <?= htmlspecialchars($membre_info['prenom_membre'] . ' ' . $membre_info['nom_membre']) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($membre_info['email_membre']) ?></p>
</div>

<div>
    <button id="cart-button" >
        <a href="/cart.php">
            <img src="/assets/fleche_retour.png" alt="Fleche de retour">
            Retourner au panier
        </a>
    </button>
</div>

<div>
    <div>
        <table>
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $product_id => $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['nom_article']); ?></td>
                        <td><?php echo $item['quantite']; ?></td>
                        <td><?php echo number_format($item['prix_article'], 2, ',', ' ') . " €"; ?></td>
                        <td><?php echo number_format($item['prix_article'] * $item['quantite'], 2, ',', ' ') . " €"; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Total &nbsp : &nbsp<?php echo number_format($total, 2, ',', ' '); ?> €</h3>
        <p>🎯 Cette commande vous rapportera <strong><?= (int)$total_xp ?> XP</strong></p>
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
                        
                        <h3>Total après réductions &nbsp : &nbsp <?= number_format($totalWithReduc, 2, ',', ' ') ?> €</h3>
                        
                    <?php }
                }?>
    </div>

    <div>    
        <h3>Paiement</h3>

        <label for="mode_paiement">Mode de Paiement :</label>
        <select id="mode_paiement" name="mode_paiement" required>
            <option value="carte_credit">Carte de Crédit</option>
            <option value="paypal">PayPal</option>
            <option value="sur_place">Sur place</option>
        </select><br><br>
        <div id="carte_credit" class="mode_paiement_fields">
            <form method="POST" action="/order.php">
                <input type="hidden" name="mode_paiement" value="carte_credit">

                <label for="numero_carte">Numéro de Carte :</label>
                <input type="text" id="numero_carte" name="numero_carte" placeholder="XXXX XXXX XXXX XXXX" pattern="\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}" maxlength="19" title="16 chiffres (ex: 1234 5678 9012 3456)" required><br><br>

                <label for="expiration">Date d'Expiration :</label>
                <input type="text" id="expiration" name="expiration" placeholder="MM/AA" pattern="(0[1-9]|1[0-2])\/\d{2}" maxlength="5" title="Format MM/AA" required><br><br>

                <label for="cvv">CVV :</label>
                <input type="text" id="cvv" name="cvv" placeholder="XXX" pattern="\d{3,4}" maxlength="4" title="3 ou 4 chiffres" required><br><br>

                <button type="submit" id="finalise-order-button">Valider la commande</button>
            </form>
        </div>
        <div id="paypal" class="mode_paiement_fields" style="display: none;">
            <form method="POST" action="/order.php">
                <input type="hidden" name="mode_paiement" value="paypal">

                <button type="button" id="paypal-button">Se connecter à PayPal</button><br><br>

                <button type="submit" id="finalise-order-button">Valider la commande</button>
            </form>
        </div>
        <div id="sur_place" class="mode_paiement_fields" style="display: none;">
            <form method="POST" action="/order.php">
                <input type="hidden" name="mode_paiement" value="sur_place">
                <p>Votre commande sera réglée directement au bureau de l'ADIIL.</p>
                <button type="submit" id="finalise-order-button">Confirmer la commande</button>
            </form>
        </div>
    </div>
</div>



<script>
    document.getElementById('mode_paiement').addEventListener('change', function() {
        var modePaiement = this.value;
        if (modePaiement === 'carte_credit') {
            document.getElementById('carte_credit').style.display = 'block';
            document.getElementById('paypal').style.display = 'none';
        } else if (modePaiement === 'paypal') {
            document.getElementById('carte_credit').style.display = 'none';
            document.getElementById('paypal').style.display = 'block';
            document.getElementById('sur_place').style.display = 'none';
        } else if (modePaiement === 'sur_place') {
            document.getElementById('carte_credit').style.display = 'none';
            document.getElementById('paypal').style.display = 'none';
            document.getElementById('sur_place').style.display = 'block';
        }
    });
</script>


<?php require_once "footer.php" ?>

</body>
</html>
