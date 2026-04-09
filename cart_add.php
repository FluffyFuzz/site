<?php

// Importer les fichiers
require_once 'database.php';
require_once 'files_save.php';
require_once 'cart_class.php';

// Connexion à la base de donnees
$db = new DB();

// Initialisation du panier
$cart = new Cart($db);

$json = array('error' => true);

if (isset($_GET['id'])) {
    $product = $db->select(
        "SELECT id_article FROM ARTICLE WHERE id_article = ?",
        "i",
        [$_GET['id']]
    );

    if (empty($product)) {
        $json['message'] = "Ce produit n'existe pas";
    }


    $cart->add($product[0]['id_article']);
    $json['error'] = false;
    $json['total'] = $cart->total();
    $json['count'] = $cart->count();
    $json['message'] = "Le produit a bien été ajouté à votre panier";
} else {
    $json['message'] = "Vous n'avez pas ajouté de produit à ajouter au panier";
}

echo json_encode($json);
