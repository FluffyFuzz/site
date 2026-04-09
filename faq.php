<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ</title>
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">
</head>
<body class="body_margin">
<?php
require_once 'database.php';
require_once 'header.php';

$db      = new DB();
$entries = $db->select(
    "SELECT prenom_contact, nom_contact, objet_contact, message_contact, reponse_faq
     FROM CONTACT
     WHERE reponse_faq IS NOT NULL AND reponse_faq != ''
     ORDER BY date_contact DESC"
);
?>

<h1>FAQ</h1>

<?php if (empty($entries)): ?>
    <p>Aucune réponse disponible pour l'instant.</p>
<?php else: ?>
    <?php foreach ($entries as $e): ?>
    <details>
        <summary>
            <strong><?= htmlspecialchars($e['objet_contact']) ?></strong>
            <span> — <?= htmlspecialchars($e['prenom_contact'] . ' ' . $e['nom_contact']) ?></span>
        </summary>
        <p><em>Question :</em> <?= nl2br(htmlspecialchars($e['message_contact'])) ?></p>
        <p><em>Réponse :</em> <?= nl2br(htmlspecialchars($e['reponse_faq'])) ?></p>
    </details>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
</body>
</html>
