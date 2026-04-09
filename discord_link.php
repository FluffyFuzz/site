<?php
session_start();
require_once 'database.php';

// ── Configuration Discord OAuth2 ──────────────────────────────────────────────
// Créer une application sur https://discord.com/developers/applications
// puis ajouter l'URL de redirect dans OAuth2 > Redirects
define('DISCORD_CLIENT_ID',     'VOTRE_CLIENT_ID');
define('DISCORD_CLIENT_SECRET', 'VOTRE_CLIENT_SECRET');
define('DISCORD_REDIRECT_URI',  'http://localhost:8080/discord_link.php');
define('DISCORD_SCOPE',         'identify');
// ─────────────────────────────────────────────────────────────────────────────

if (!isset($_SESSION['userid'])) {
    header('Location: /login.php');
    exit;
}

// ── Étape 2 : Discord renvoie un ?code= ──────────────────────────────────────
if (isset($_GET['code'])) {

    // Échange le code contre un access token
    $response = file_get_contents('https://discord.com/api/oauth2/token', false,
        stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'client_id'     => DISCORD_CLIENT_ID,
                'client_secret' => DISCORD_CLIENT_SECRET,
                'grant_type'    => 'authorization_code',
                'code'          => $_GET['code'],
                'redirect_uri'  => DISCORD_REDIRECT_URI,
            ]),
        ]])
    );

    $token_data = json_decode($response, true);

    if (!isset($token_data['access_token'])) {
        header('Location: /account.php?discord=error');
        exit;
    }

    // Récupère les infos Discord de l'utilisateur
    $user_response = file_get_contents('https://discord.com/api/users/@me', false,
        stream_context_create(['http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $token_data['access_token'],
        ]])
    );

    $discord_user = json_decode($user_response, true);

    if (!isset($discord_user['id'])) {
        header('Location: /account.php?discord=error');
        exit;
    }

    // Sauvegarde l'identifiant Discord en base
    $db = new DB();
    $db->query(
        "UPDATE MEMBRE SET discord_token_membre = ? WHERE id_membre = ?",
        "si", [$discord_user['id'], $_SESSION['userid']]
    );

    header('Location: /account.php?discord=success');
    exit;
}

// ── Étape 1 : Redirection vers Discord ───────────────────────────────────────
$discord_url = 'https://discord.com/api/oauth2/authorize?' . http_build_query([
    'client_id'     => DISCORD_CLIENT_ID,
    'redirect_uri'  => DISCORD_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => DISCORD_SCOPE,
]);

header('Location: ' . $discord_url);
exit;
