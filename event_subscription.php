<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/styles/event_subscription_style.css">

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
require_once "header.php";
require_once 'database.php';
require_once 'files_save.php';

// ── Resend ────────────────────────────────────────────────────────────────────
define('RESEND_API_KEY_SUB', ''); //METTTRE LA CLE Resend.com ICI AUSSI
define('MAIL_FROM_SUB',      'onboarding@resend.dev');

function sendConfirmationEmail(array $membre, array $event, float $prix): void
{
    $prenom = $membre['prenom_membre'];
    $nom    = $membre['nom_membre'];
    $email  = $membre['email_membre'];
    $date   = date('d/m/Y à H:i', strtotime($event['date_evenement']));
    $lieu   = $event['lieu_evenement'];
    $titre  = $event['nom_evenement'];
    $prix_str = $prix == 0 ? 'Gratuit' : number_format($prix, 2, ',', ' ') . ' €';

    $body = json_encode([
        'from'    => MAIL_FROM_SUB,
        'to'      => [$email],
        'subject' => "Confirmation d'inscription — {$titre}",
        'html'    => "
            <p>Bonjour {$prenom} {$nom},</p>
            <p>Votre inscription à l'événement <strong>{$titre}</strong> est confirmée.</p>
            <ul>
                <li><strong>Date :</strong> {$date}</li>
                <li><strong>Lieu :</strong> {$lieu}</li>
                <li><strong>Prix payé :</strong> {$prix_str}</li>
            </ul>
            <p>À bientôt à l'ADIIL !</p>
        ",
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", [
                'Content-Type: application/json',
                'Authorization: Bearer ' . RESEND_API_KEY_SUB,
            ]),
            'content' => $body,
            'timeout' => 10,
        ],
    ]);
    @file_get_contents('https://api.resend.com/emails', false, $ctx);
}
// ─────────────────────────────────────────────────────────────────────────────


// Vérifie si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION["userid"]);
if (!$isLoggedIn) {
    header("Location: /login.php");
    exit;
}

$userid = $_SESSION["userid"];

// Vérifie que la requête est POST et contient les données nécessaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_SESSION["userid"];
    $eventid = $_POST["eventid"];

    require_once 'database.php';
    $db = new DB();
    if(isset($_POST["price"], $_POST["eventid"])){
        $inscription_id = $db->query(
            "INSERT INTO `INSCRIPTION` (`id_membre`, `id_evenement`, `date_inscription`, `paiement_inscription`, `prix_inscription`)
            VALUES (?, ?, NOW(), 'WEB', ?);",
            "iid",
            [$userid, $eventid, $_POST["price"]]
        );
        if ($inscription_id > 0) {
            $event_info = $db->select(
                "SELECT xp_evenement, nom_evenement, date_evenement, lieu_evenement FROM EVENEMENT WHERE id_evenement = ?",
                "i", [$eventid]
            )[0];
            $db->query(
                "UPDATE MEMBRE SET xp_membre = xp_membre + ? WHERE id_membre = ?",
                "ii", [$event_info['xp_evenement'], $userid]
            );

            // Envoi du mail de confirmation
            $membre = $db->select(
                "SELECT prenom_membre, nom_membre, email_membre FROM MEMBRE WHERE id_membre = ?",
                "i", [$userid]
            )[0];
            sendConfirmationEmail($membre, $event_info, (float)$_POST["price"]);

            header("Location: /events.php");
        } else {
            $_SESSION['subscription_error'] = "Inscription impossible : l'événement est complet ou vous êtes déjà inscrit(e).";
            header("Location: /event_details.php?id=" . (int)$eventid);
        }
        exit;
    }
    elseif(isset($_POST["eventid"])){
            $event = $db->select(
                "SELECT nom_evenement, xp_evenement, prix_evenement, reductions_evenement, date_evenement, lieu_evenement FROM EVENEMENT WHERE id_evenement = ? ;",
                "i",
                [$eventid]
            );
            if(empty($event)){
                header("Location: /index.php");
                exit;
            }
            $event = $event[0];
            $title = $event["nom_evenement"];
            $xp = $event["xp_evenement"];
            $price = $event["prix_evenement"];

            $membre_info = $db->select(
                "SELECT prenom_membre, nom_membre, email_membre FROM MEMBRE WHERE id_membre = ?",
                "i", [$userid]
            )[0];

            $isDiscounted = boolval($event["reductions_evenement"]);
            $user_reduction = 1;

            if($isDiscounted){
                $user_reduction = $db->select(
                    "SELECT reduction_grade FROM ADHESION 
                    JOIN GRADE ON ADHESION.id_grade = GRADE.id_grade
                    WHERE id_membre = ? AND reduction_grade > 0 order by ADHESION.date_adhesion DESC LIMIT 1",
                    "i",
                    [$userid]
                );
                if(!empty($user_reduction)){
                    $user_reduction = 1 - ($user_reduction[0]["reduction_grade"]/100);
                }else{
                    $user_reduction = 1;
                }
            }
        }else{
            header("Location: /login.php");
            exit;
        }
    }else{
        header("Location: /login.php");
        exit;
    }
?>




<!--------------->
<!------HTML----->
<!--------------->

    <h1>INSCRIPTION</h1>

    <div>
        <button id="cart-button">
            <a href="/event_details.php?id=<?php echo $eventid?>">
                <img src="/assets/fleche_retour.png" alt="Flèche de retour">
                Retourner à l'évènement
            </a>
        </button>
    </div>

    <div id="member-info">
        <h3>Vos informations</h3>
        <p><strong>Nom :</strong> <?= htmlspecialchars($membre_info['prenom_membre'] . ' ' . $membre_info['nom_membre']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($membre_info['email_membre']) ?></p>
        <p><strong>Événement :</strong> <?= htmlspecialchars($event['nom_evenement']) ?></p>
        <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($event['date_evenement'])) ?></p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($event['lieu_evenement']) ?></p>
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
                    <tr>
                        <td><?php echo strtoupper(htmlspecialchars($title)); ?></td>
                        <td>1</td>
                        <td><?= number_format($price, 2, ',', ' ') ?> €</td>
                        <td><?= number_format($price, 2, ',', ' ') ?> €</td>
                    </tr>
                </tbody>
            </table>

            <h3>Total &nbsp : &nbsp <?= number_format($price, 2, ',', ' ') ?> €</h3>
            <h3>Total après réductions &nbsp : &nbsp <?= number_format($price*$user_reduction, 2, ',', ' ') ?> €</h3>
                   
        </div>

        <div>
            <?php if ($price * $user_reduction == 0): ?>

            <h3>Événement gratuit</h3>
            <form method="POST" action="/event_subscription.php">
                <input type="hidden" name="eventid" value="<?php echo $eventid; ?>">
                <input type="hidden" name="price" value="0">
                <button type="submit" id="finalise-order-button">Confirmer l'inscription</button>
            </form>

            <?php else: ?>

            <h3>Paiement</h3>

            <label for="mode_paiement">Mode de Paiement :</label>
            <select id="mode_paiement" name="mode_paiement" required>
                <option value="carte_credit">Carte de Crédit</option>
                <option value="paypal">PayPal</option>
                <option value="sur_place">Sur place</option>
            </select><br><br>
            <div id="carte_credit" class="mode_paiement_fields">
                <form method="POST" action="/event_subscription.php">
                    <input type="hidden" name="eventid" value="<?php echo $eventid; ?>">
                    <input type="hidden" name="price" value="<?php echo $price*$user_reduction; ?>">
                    <input type="hidden" name="mode_paiement" value="carte_credit">

                    <label for="numero_carte">Numéro de Carte :</label>
                    <input type="text" id="numero_carte" name="numero_carte" placeholder="XXXX XXXX XXXX XXXX" required><br><br>

                    <label for="expiration">Date d'Expiration :</label>
                    <input type="text" id="expiration" name="expiration" placeholder="MM/AA" required><br><br>

                    <label for="cvv">CVV :</label>
                    <input type="text" id="cvv" name="cvv" placeholder="XXX" required><br><br>

                    <button type="submit" id="finalise-order-button">Valider la commande</button>
                </form>
            </div>
            <div id="paypal" class="mode_paiement_fields" style="display: none;">
                <form method="POST" action="/event_subscription.php">
                    <input type="hidden" name="eventid" value="<?php echo $eventid; ?>">
                    <input type="hidden" name="price" value="<?php echo $price*$user_reduction; ?>">
                    <input type="hidden" name="mode_paiement" value="paypal">

                    <button type="button" id="paypal-button">Se connecter à PayPal</button><br><br>

                    <button type="submit" id="finalise-order-button">Valider la commande</button>
                </form>
            </div>

            <?php endif; ?>
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
        } else if (modePaiement === 'sur_place') {
            document.getElementById('carte_credit').style.display = 'none';
            document.getElementById('paypal').style.display = 'none';
        }
    });
</script>


<?php require_once "footer.php" ?>

</body>
</html>
