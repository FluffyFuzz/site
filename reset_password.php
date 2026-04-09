<?php
require_once 'database.php';
require_once 'header.php';

$token   = trim($_GET['token'] ?? '');
$message = '';
$success = false;
$valid   = false;

$db = new DB();

// Vérifie que le token existe et n'est pas expiré
if (!empty($token)) {
    $row = $db->select(
        "SELECT id_membre FROM PASSWORD_RESET WHERE token = ? AND expires_at > NOW()",
        "s", [$token]
    );
    $valid = !empty($row);
}

if (!$valid) {
    $message = "Ce lien est invalide ou a expiré.";
}

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password     = $_POST['password']     ?? '';
    $confirm_password = $_POST['password_confirm'] ?? '';

    if (strlen($new_password) < 8) {
        $message = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        $id_membre = $row[0]['id_membre'];
        $hash      = password_hash($new_password, PASSWORD_BCRYPT);

        $db->query("UPDATE MEMBRE SET password_membre = ? WHERE id_membre = ?", "si", [$hash, $id_membre]);
        $db->query("DELETE FROM PASSWORD_RESET WHERE token = ?", "s", [$token]);

        $success = true;
        $message = "Mot de passe mis à jour. Vous pouvez vous connecter.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/login_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
</head>
<body>
    <?php if ($success): ?>
        <p class="login-success"><?= htmlspecialchars($message) ?></p>
        <p><a href="/login.php">Se connecter</a></p>

    <?php elseif (!$valid): ?>
        <p class="login-error"><?= htmlspecialchars($message) ?></p>
        <p><a href="/forgot_password.php">Demander un nouveau lien</a></p>

    <?php else: ?>
        <form method="POST" class="login-form">
            <h1>Nouveau mot de passe</h1>

            <?php if ($message): ?>
                <p class="login-error"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <label for="password">Nouveau mot de passe :</label>
            <input type="password" name="password" id="password" required minlength="8">

            <label for="password_confirm">Confirmer le mot de passe :</label>
            <input type="password" name="password_confirm" id="password_confirm" required minlength="8">

            <button type="submit">Valider</button>
        </form>
    <?php endif; ?>
</body>
</html>
