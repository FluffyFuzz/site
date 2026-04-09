<?php

class Cart
{
    private $db;

    public function __construct($db)
    {

        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        $this->db = $db;

        if (isset($_GET['del'])) {
            $this->del($_GET['del']);
        }

        if (isset($_POST['cart']['quantity'])) {
            $this->recalc();
        }
    }


    public function recalc()
    {
        // Récupérer tous les ids des produits présents dans le panier
        $product_ids = array_keys($_SESSION['cart']);
        if (empty($product_ids)) {
            return;
        }

        // Requête pour récupérer les stocks
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $query = "SELECT id_article, stock_article FROM ARTICLE WHERE id_article IN ($placeholders)";
        $products = $this->db->select($query, str_repeat("i", count($product_ids)), $product_ids);

        $stocks = [];
        foreach ($products as $product) {
            $stocks[$product['id_article']] = $product['stock_article'];
        }

        foreach ($_SESSION['cart'] as $product_id => $current_quantity) {
            if (isset($_POST['cart']['quantity'][$product_id])) {
                $new_quantity = $_POST['cart']['quantity'][$product_id];

                // Vérifier si la quantité est un nombre entier positif (> 0)
                if (!is_numeric($new_quantity) || intval($new_quantity) != $new_quantity || $new_quantity <= 0) {
                    // Si non valide, restaurer la quantité actuelle
                    $_SESSION['cart'][$product_id] = $current_quantity;
                    continue;
                }

                $new_quantity = intval($new_quantity);

                // Vérifier si le stock est suffisant
                if (isset($stocks[$product_id]) && $new_quantity > $stocks[$product_id]) {
                    $_SESSION['cart'][$product_id] = $stocks[$product_id];
                } else {
                    $_SESSION['cart'][$product_id] = $new_quantity;
                }
            }
        }
    }



    public function total()
    {
        $total = 0;

        $ids = array_keys($_SESSION['cart']);
        if (empty($ids)) {
            $products = array();
        } else {
            $placeholders = implode(",", array_fill(0, count($ids), "?"));
            $query = "SELECT id_article, prix_article FROM ARTICLE WHERE id_article IN ($placeholders)";
            $types = str_repeat("i", count($ids));

            $products = $this->db->select(
                $query,
                $types,
                $ids
            );
        }

        foreach ($products as $product) {
            $total += $product['prix_article'] * $_SESSION['cart'][$product['id_article'] ];
        }
        return $total;
    }

    public function count()
    {
        return array_sum($_SESSION['cart']);
    }

    public function add($product_id)
    {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }
    }

    public function del($product_id)
    {
        unset($_SESSION['cart'][$product_id]);
    }
}
