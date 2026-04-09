<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/styles/account_style.css">
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">

</head>

<body class="body_margin">



<!--------------->
<!------PHP------>
<!--------------->

 <!-- Importer les fichiers -->
<?php
require_once "header.php" ;
require_once 'database.php';
require_once 'files_save.php';

// Connexion à la base de donnees
$db = new DB();

$isLoggedIn = isset($_SESSION["userid"]);
?>


<!-- Deconnecte l'utilisateur si celui-ci le souhaite -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deconnexion']) && $_POST['deconnexion'] === 'true') {
        session_destroy();
        header("Location: /index.php");
        exit();
    }
}
?>


 <!-- Recuperer les informations de l'utilisateur -->
<?php
    $infoUser = $db->select(
        "SELECT pp_membre, xp_membre, prenom_membre, nom_membre, email_membre, tp_membre, discord_token_membre, nom_grade, image_grade FROM MEMBRE LEFT JOIN ADHESION ON MEMBRE.id_membre = ADHESION.id_membre LEFT JOIN GRADE ON ADHESION.id_grade = GRADE.id_grade WHERE MEMBRE.id_membre = ?;",
        "i",
        [$_SESSION['userid']]
    );
    ?>

<!-- Formulaire permettant de modifier la photo de profil de l'utilisateur-->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Appelle saveImage() pour traiter l'image
    $fileName = saveImage();

    if ($fileName !== null) {
        // Suppression de l'ancienne image si elle existe
        if (!empty($infoUser[0]['pp_membre'])) {
            deleteFile($infoUser[0]['pp_membre']);
        }

        // Met à jour la base de données avec le nom du fichier
        $db->query(
            "UPDATE MEMBRE SET pp_membre = ? WHERE id_membre = ?",
            "si",
            [$fileName, $_SESSION['userid']]
        );

            $_SESSION['message'] = "Mise à jour de la photo de profil réussie !";
            $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Erreur : veuillez vérifier le fichier envoyé.";
        $_SESSION['message_type'] = "error";
    }
    // Recharge la page pour afficher la nouvelle image
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!-- Formulaire contenant les données personnelles de l'utilisateur -->
<?php
    // Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['lastName'], $_POST['mail'])) {
    // Charger les informations actuelles de l'utilisateur depuis la base de données
    $currentUserData = $db->select(
        "SELECT prenom_membre, nom_membre, email_membre, tp_membre FROM MEMBRE WHERE id_membre = ?",
        "i",
        [$_SESSION['userid']]
    );

    // Vérifier si les données actuelles existent
    if (!empty($currentUserData)) {
        $currentName = $currentUserData[0]['prenom_membre'];
        $currentLastName = $currentUserData[0]['nom_membre'];
        $currentMail = $currentUserData[0]['email_membre'];
        $currentTp = $currentUserData[0]['tp_membre'];

        // Récupérer les nouvelles valeurs ou conserver les anciennes si aucune modification
        $name = empty($_POST['name']) ? $currentName : htmlspecialchars($_POST['name']);
        $lastName = empty($_POST['lastName']) ? $currentLastName : htmlspecialchars($_POST['lastName']);
        $mail = empty($_POST['mail']) ? $currentMail : htmlspecialchars($_POST['mail']);
        $tp = isset($_POST['tp']) && !empty($_POST['tp']) ? htmlspecialchars($_POST['tp']) : $currentTp;

        // Vérifier si l'adresse e-mail existe déjà (et appartient à un autre utilisateur)
        $existingEmail = $db->select(
            "SELECT id_membre FROM MEMBRE WHERE email_membre = ? AND id_membre != ?",
            "si",
            [$mail, $_SESSION['userid']]
        );

        if (!empty($existingEmail)) {
        // Cas où l'adresse e-mail est déjà utilisée
            $_SESSION['message'] = "Les modifications n'ont pas pu être effectuées car l'adresse e-mail est déjà utilisée par un autre compte.";
            $_SESSION['message_type'] = "error"; // Pour gérer les styles
        } else {
            // Mettre à jour les informations de l'utilisateur
            $db->query(
                "UPDATE MEMBRE SET prenom_membre = ?, nom_membre = ?, email_membre = ?, tp_membre = ? WHERE id_membre = ?",
                "ssssi",
                [$name, $lastName, $mail, $tp, $_SESSION['userid']]
            );

            // Message de succès suite aux modifications
            $_SESSION['message'] = "Vos informations ont été mises à jour avec succès !";
            $_SESSION['message_type'] = "success"; // Pour gérer les styles
        }
    } else {
        // Cas où l'utilisateur actuel n'existe pas dans la base
        $_SESSION['message'] = "Erreur : utilisateur introuvable dans la base de données.";
        $_SESSION['message_type'] = "error";
    }

    // Recharger la page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>


<!-- Formulaire permettant à l'utilisateur de modifier son mot de passe-->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mdp'], $_POST['newMdp'], $_POST['newMdpVerif'])) {
    $currentPassword = htmlspecialchars(trim($_POST['mdp']));
    $newPassword = htmlspecialchars(trim($_POST['newMdp']));
    $newPasswordVerif = htmlspecialchars(trim($_POST['newMdpVerif']));

    // Récupérer l'utilisateur et le mot de passe actuel depuis la base de données
    $user = $db->select(
        "SELECT password_membre FROM MEMBRE WHERE id_membre = ?",
        "i",
        [$_SESSION['userid']]
    );

    if ($user[0]['password_membre'] == null && $currentPassword == "") {
        $password_ok = true;
    } else {
        $password_ok = password_verify($currentPassword, $user[0]['password_membre']);
    }

    if (!empty($user)) {
        // Vérifier la correspondance des nouveaux mots de passe
        if ($password_ok && $newPassword == $newPasswordVerif) {
            // Mettre à jour le mot de passe dans la base de données
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->query(
                "UPDATE MEMBRE SET password_membre = ? WHERE id_membre = ?",
                "si",
                [$hashedPassword, $_SESSION['userid']]
            );

            $_SESSION['message'] = "Mot de passe mis à jour avec succès !";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Les nouveaux mots de passe ne correspondent pas.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Mot de passe actuel incorrect.";
        $_SESSION['message_type'] = "error";
    }

    // Redirection pour éviter le double envoi du formulaire
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>





<!--------------->
<!------HTML----->
<!--------------->


<!-- PARTIE MON COMPTE -->

<H2>MON COMPTE</H2>



<!-- Affichage du message de succès ou d'erreur -->
<?php
if (isset($_SESSION['message'])) {
    $messageStyle = isset($_SESSION['message_type']) && $_SESSION['message_type'] === "error" ? "error-message" : "success-message";
    echo '<div id="' . $messageStyle . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']); // Supprimer le message après affichage
    unset($_SESSION['message_type']); // Supprimer le type après affichage
}
?>



<section> <!-- Ensemble des différents formulaires du compte -->
    

    <!-- Partie contenant les informations générales sur le compte de l'utilisateur -->
    <div id="account-generalInfo">
    <div>
        <form method="POST" enctype="multipart/form-data" id="pp-form">


            <label id="cadre-pp" for="profilePictureInput">
                <?php if ($infoUser[0]['pp_membre'] == null) :?>
                    <img src="/admin/ressources/default_images/user.jpg" alt="Photo de profil de l'utilisateur" />
                <?php else :?>
                    <img src="/api/files/<?php echo $infoUser[0]['pp_membre']; ?>" alt="Photo de profil de l'utilisateur" />
                <?php endif?>
            </label>

            <input type="file" id="profilePictureInput" name="file" accept="image/jpeg, image/png, image/webp" style="display: none;" onchange="this.form.submit()">

            <button type="button" id="edit-icon" onclick="document.getElementById('profilePictureInput').click()">
                <img src="/assets/edit_logo.png" alt="Icone éditer la photo de profil" />
            </button>
        </form>
    </div>
    <div>
        <p><?php echo $infoUser[0]['xp_membre']; ?></p>
        <p>XP</p>
    </div>
    <div id="cadre-grade">
        <?php if (empty($infoUser[0]['nom_grade'])) : ?>
            <p>Vous n'avez pas de grade</p>
        <?php else : ?>
            <p><?php echo $infoUser[0]['nom_grade']; ?></p>
            <?php if ($infoUser[0]['image_grade'] == null) :?>
                <img src="/admin/ressources/default_images/grade.webp" alt="Image du grade" />
            <?php else :?>
                <img src="/api/files/<?php echo $infoUser[0]['image_grade']; ?>" alt="Illustration du grade de l'utilisateur" />
            <?php endif?>
            <div >
            </div>
        <?php endif; ?>
    </div>
</div>




 <!-- Formulaire contenant les données personnelles de l'utilisateur -->
    <form method="POST" action="" id="account-personalInfo-form">
        <div>
            <div>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Prénom" 
                    value="<?php echo htmlspecialchars($infoUser[0]['prenom_membre']); ?>" 
                    required>
                <input 
                    type="text" 
                    id="lastName" 
                    name="lastName" 
                    placeholder="Nom de famille" 
                    value="<?php echo htmlspecialchars($infoUser[0]['nom_membre']); ?>" 
                    required>
            </div>
            <div>
                <input 
                    type="email" 
                    id="mail" 
                    name="mail" 
                    placeholder="Adresse mail" 
                    value="<?php echo htmlspecialchars($infoUser[0]['email_membre']); ?>" 
                    required>
                
                <?php if (!empty($infoUser[0]['tp_membre'])) : ?>
                <select id="tp" name="tp">
                    <option value="11A" <?php echo $infoUser[0]['tp_membre'] === '11A' ? 'selected' : ''; ?>>TP 11 A</option>
                    <option value="11B" <?php echo $infoUser[0]['tp_membre'] === '11B' ? 'selected' : ''; ?>>TP 11 B</option>
                    <option value="12C" <?php echo $infoUser[0]['tp_membre'] === '12C' ? 'selected' : ''; ?>>TP 12 C</option>
                    <option value="12D" <?php echo $infoUser[0]['tp_membre'] === '12D' ? 'selected' : ''; ?>>TP 12 D</option>
                    <option value="21A" <?php echo $infoUser[0]['tp_membre'] === '21A' ? 'selected' : ''; ?>>TP 21 A</option>
                    <option value="21B" <?php echo $infoUser[0]['tp_membre'] === '21B' ? 'selected' : ''; ?>>TP 21 B</option>
                    <option value="22C" <?php echo $infoUser[0]['tp_membre'] === '22C' ? 'selected' : ''; ?>>TP 22 C</option>
                    <option value="22D" <?php echo $infoUser[0]['tp_membre'] === '22D' ? 'selected' : ''; ?>>TP 22 D</option>
                    <option value="31A" <?php echo $infoUser[0]['tp_membre'] === '31A' ? 'selected' : ''; ?>>TP 31 A</option>
                    <option value="31B" <?php echo $infoUser[0]['tp_membre'] === '31B' ? 'selected' : ''; ?>>TP 31 B</option>
                    <option value="32C" <?php echo $infoUser[0]['tp_membre'] === '32C' ? 'selected' : ''; ?>>TP 32 C</option>
                    <option value="32D" <?php echo $infoUser[0]['tp_membre'] === '32D' ? 'selected' : ''; ?>>TP 32 D</option>
                </select>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit">
            <img src="/assets/save_logo.png" alt="Logo enregistrer les modifications"/>
        </button>
    </form>




    <!-- Formulaire permettant à l'utilisateur de modifier son mot de passe-->
    <form method="POST" action="" id="account-editPass-form">
        <div>
            <div>
                <p>Modifier mon mot de passe :</p>
                <input type="password" id="mdp" name="mdp" placeholder="Mot de passe actuel">
            </div>
            <div>
                <input type="password" id="newMdp" name="newMdp" placeholder="Nouveau mot de passe" required>
                <input type="password" id="newMdpVerif" name="newMdpVerif" placeholder="Confirmation du nouveau mot de passe" required>
            </div>
        </div>

        <button type="submit"><img src="/assets/save_logo.png" alt="Logo editer la photo de profil"/></button>
    </form>
</section>






<section> <!-- Ensemble des différents boutons du compte -->

    <div id="buttons-section">
        <!--Discord-->
        <button type="button">
            <a href="https://discord.com/login" target="_blank">
                <img src="/assets/logo_discord.png" alt="Logo de Discord">
                Associer mon compte à Discord
            </a>
        </button>

        <!--Deconnexion-->
        <form action="" method="post">
            <input type="hidden" name="deconnexion" value="true">
            <button type="submit">
                    <img src="/assets/logOut_icon.png" alt="icone de deconnexion">
                    Déconnexion
            </button>
        </form>

        <!--Supprimer son compte-->
        <form action="delete_account.php" method="post">
            <input type="hidden" name="delete_account" value="true">
            <button type="submit">
                <img src="/assets/delete_icon.png" alt="icone de suppression">
                Supprimer mon compte
            </button>
        </form>
    </div>
</section>



<!-- PARTIE MES ACHATS -->
<section id="section-mesAchats">

<?php
// Vérifie si "viewAll" est défini et vaut "1" dans l'URL
$viewAll = isset($_GET['viewAll']) && $_GET['viewAll'] === '1';
?>

    <h2>MES ACHATS</h2>

    <?php
    // Préparer la requête SQL avec ou sans LIMIT
    $sql = "
        SELECT type_transaction, element, quantite, montant, mode_paiement, date_transaction, 
        CASE 
        WHEN recupere = 1 THEN 'Récupéré'
        ELSE 'Non récupéré'
        END AS statut 
        FROM HISTORIQUE_COMPLET WHERE id_membre=? ORDER BY date_transaction DESC";


    // Ajouter LIMIT si "viewAll" n'est pas activé
    if (!$viewAll) {
        $sql .= " LIMIT 6";
    }

    // Exécuter la requête
    $historiqueAchats = $db->select(
        $sql,
        "i",
        [$_SESSION['userid']]
    );

    ?>

    <!--Zone du tableau-->

    <div id=historique-achats>

        <!-- Bouton pour afficher tout ou afficher moins -->
        <form method="GET" action="#section-mesAchats" id="viewAll-form">
            <?php if ($viewAll) : ?>
                <button type="submit" name="viewAll" value="0">Afficher moins</button>
            <?php else : ?>
                <button type="submit" name="viewAll" value="1">Afficher tout</button>
            <?php endif; ?>
         </form>

        <?php
        if (!empty($historiqueAchats)) : ?>
            <table id="tab-historique-achats">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix</th>
                    <th>Mode de paiement</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historiqueAchats as $achat) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($achat['date_transaction']); ?></td>
                        <td><?php echo htmlspecialchars($achat['type_transaction']); ?></td>
                        <td><?php echo htmlspecialchars($achat['element']); ?></td>
                        <td><?php echo htmlspecialchars($achat['quantite']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($achat['montant'], 2, ',', ' ')) . " €"; ?></td>
                        <td><?php echo htmlspecialchars($achat['mode_paiement']); ?></td>
                        <td><?php echo htmlspecialchars($achat['statut']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        <?php else : ?>
            <p>Vous n'avez effectué aucun achat pour le moment.</p>
        <?php endif; ?>
    </div>
</section>



<?php require_once "footer.php" ?>
</body>
</html>