<?php
require_once 'database.php';
require_once 'header.php';

// ── Configuration Resend ──────────────────────────────────────────────────────
define('RESEND_API_KEY', ''); //Mettre la clé api Resend ici
define('MAIL_FROM',      'onboarding@resend.dev');   // mail de base de resend
define('APP_URL',        'http://localhost:8080');           // Mettre l'URL (:8080 sur le docker)
// ─────────────────────────────────────────────────────────────────────────────

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide.";
    } else {
        $db = new DB();

        // Cherche le membre par email
        $membre = $db->select(
            "SELECT id_membre, prenom_membre FROM MEMBRE WHERE email_membre = ?",
            "s", [$email]
        );

        // On répond toujours la même chose pour ne pas exposer si l'email existe
        $success = true;
        $message = "Si cette adresse est connue, un email a été envoyé.";

        if (!empty($membre)) {
            $id_membre = $membre[0]['id_membre'];
            $prenom    = $membre[0]['prenom_membre'];

            // Génère un token unique valable 1 heure
            $token      = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + 3600);

            // Supprime les anciens tokens de cet utilisateur puis sauvegarde le nouveau
            $db->query("DELETE FROM PASSWORD_RESET WHERE id_membre = ?", "i", [$id_membre]);
            $db->query(
                "INSERT INTO PASSWORD_RESET (token, id_membre, expires_at) VALUES (?, ?, ?)",
                "sis", [$token, $id_membre, $expires_at]
            );

            $reset_link = APP_URL . "/reset_password.php?token=" . $token;
            sendResetEmail($email, $prenom, $reset_link);
        }
    }
}

/**
 * Envoie l'email de réinitialisation via l'API Resend.
 */
function sendResetEmail(string $email, string $prenom, string $link): void
{
    $body = json_encode([
        'from'    => MAIL_FROM,
        'to'      => [$email],
        'subject' => 'Réinitialisation de votre mot de passe',
        'html'    => "
            <p>Bonjour {$prenom},</p>
            <p>Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe.
               Ce lien expire dans 1 heure.</p>
            <p><a href='{$link}'>{$link}</a></p>
            <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
        ",
    ]);

    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", [
                'Content-Type: application/json',
                'Authorization: Bearer ' . RESEND_API_KEY,
            ]),
            'content' => $body,
            'timeout' => 10,
        ],
    ]);

    @file_get_contents('https://api.resend.com/emails', false, $context);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/login_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
</head>
<body>
    <?php if ($success): ?>
        <p class="login-success"><?= htmlspecialchars($message) ?></p>
        <p><a href="/login.php">Retour à la connexion</a></p>
    <?php else: ?>
        <form method="POST" class="login-form">
            <h1>Mot de passe oublié</h1>

            <?php if ($message): ?>
                <p class="login-error"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <label for="email">Adresse email :</label>
            <input type="email" name="email" id="email" required>

            <button type="submit">Envoyer le lien</button>
            <a href="/login.php">Retour à la connexion</a>
        </form>
    <?php endif; ?>
</body>
</html>
