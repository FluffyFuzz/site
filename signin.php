<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="/styles/login_style.css">
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">

</head>
    <body>
        <?php
            require_once 'header.php';
            require_once 'database.php';
            $db = new DB();

        function format_input($text)
        {
            return htmlspecialchars(trim($text));
        }
        ?>

        <form method="POST" action="" class="login-form">
            <h1>S'inscrire</h1>

            <label for="mail">Prénom :</label>
            <input type="text" name="fname">

            <label for="mail">Nom :</label>
            <input type="text" name="lname">
        
            <label for="mail">Adresse Mail :*</label>
            <input type="email" name="mail" required>

            <label for="password">Mot de passe :*</label>
            <input type="password" name="password" required>

            <label for="password">Confirmez le Mot de passe :*</label>
            <input type="password" name="password_verif" required>

            <button type="submit">Confirmer</button>
        </form>

        <!-- Gestion de l'inscription -->
        <?php

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mail = htmlspecialchars(trim($_POST['mail']));

            $selection_db = $db->select(
                "SELECT id_membre FROM MEMBRE WHERE email_membre = ?",
                "s",
                [$mail]
            );

            if (empty($selection_db)) {
                $password = format_input($_POST['password']);
                $password_verif = format_input($_POST['password_verif']);

                if($password !== $password_verif){
                    echo '<p class="login-error">Les mots de passe ne correspondent pas.</p>';
                } else {
                    $fname = isset($_POST['fname']) ? format_input($_POST['fname']) : 'N/A';
                    $lname = isset($_POST['lname']) ? format_input($_POST['lname']) : 'N/A';

                    $db->query(
                        "INSERT INTO MEMBRE (nom_membre, prenom_membre, email_membre, password_membre, pp_membre) VALUES (?, ?, ?, ?, '')",
                        "ssss",
                        [$lname, $fname, $mail, password_hash($password, PASSWORD_DEFAULT)]
                    );
                    header("Location: /login.php");
                    exit;
                }
            }else{
                echo '<p class="login-error">Cette adresse email est déjà utilisée.</p>';
            }
        }
        ?>
    </body>
</html>