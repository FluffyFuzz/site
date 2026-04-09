<?php
require_once 'database.php';
require_once 'header.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $prenom  = trim($_POST['prenom']  ?? '');
    $email   = trim($_POST['email']   ?? '');
    $objet   = trim($_POST['objet']   ?? '');
    $contenu = trim($_POST['message'] ?? '');

    if (empty($nom) || empty($prenom) || empty($email) || empty($objet) || empty($contenu)) {
        $message = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide.";
    } else {
        $db = new DB();
        $db->query(
            "INSERT INTO CONTACT (nom_contact, prenom_contact, email_contact, objet_contact, message_contact)
             VALUES (?, ?, ?, ?, ?)",
            "sssss", [$nom, $prenom, $email, $objet, $contenu]
        );
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nous contacter</title>
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">
    <link rel="stylesheet" href="/styles/login_style.css">
</head>
<body class="body_margin">

    <?php if ($success): ?>

        <div class="login-form">
            <h1>Message envoyé</h1>
            <p>Merci, nous vous répondrons à <strong><?= htmlspecialchars($email) ?></strong> dans les plus brefs délais.</p>
            <a href="/index.php">Retour à l'accueil</a>
        </div>

    <?php else: ?>

        <form method="POST" class="login-form">
            <h1>Nous contacter</h1>

            <?php if ($message): ?>
                <p class="login-error"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" required
                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">

            <label for="prenom">Prénom :</label>
            <input type="text" name="prenom" id="prenom" required
                   value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">

            <label for="email">Adresse email :</label>
            <input type="email" name="email" id="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label for="objet">Objet :</label>
            <input type="text" name="objet" id="objet" required
                   value="<?= htmlspecialchars($_POST['objet'] ?? '') ?>">

            <label for="message">Message :</label>
            <textarea name="message" id="message" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

            <button type="submit">Envoyer</button>
        </form>

    <?php endif; ?>

    <?php require_once 'footer.php'; ?>
</body>
</html>
