<!DOCTYPE html>
<html lang="fr">
<?php 
        require_once 'database.php';
        $db = new DB();

        $show = 8;

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $eventid = $_GET['id'];
            $event = $db->select(
                "SELECT `nom_evenement`, `xp_evenement`, `places_evenement`, `prix_evenement`, `reductions_evenement`, `lieu_evenement`, `date_evenement`, `image_evenement`, `description_evenement`
                FROM EVENEMENT WHERE id_evenement = ?",
                "i",
                [$eventid]
            );
            if(empty($event) || is_null($event)){
                header("Location: /index.php");
                exit;
            }
            $event = $event[0];

            if (isset($_GET['show']) && is_numeric($_GET['show']) && $_GET['show']) {
                $show = (int) $_GET['show'];
            }

        }else{
            header("Location: /index.php");
            exit;
        }
    ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <title><?php echo $event['nom_evenement']?></title>

    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">

    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/event_details_style.css">



</head>

<body>
    <?php
    require_once 'header.php';
    $isLoggedIn = isset($_SESSION["userid"]);
?>
    <?php if (isset($_SESSION['subscription_error'])): ?>
        <p style="color:#c0392b; background:#fdecea; border:1px solid #c0392b; border-radius:8px; padding:12px 16px; margin-bottom:16px;">
            <?= htmlspecialchars($_SESSION['subscription_error']) ?>
        </p>
        <?php unset($_SESSION['subscription_error']); ?>
    <?php endif; ?>

    <section class="event-details">
        <?php if($event['image_evenement'] == null):?>
            <img src="/admin/ressources/default_images/event.jpg" alt="Image de l'événement">
        <?php else:?>
            <img src="/api/files/<?php echo $event['image_evenement']; ?>" alt="Image de l'événement">
        <?php endif?>

        <h1><?php echo strtoupper($event['nom_evenement']); ?></h1>

        <div>
            <h2>
                <?php
                    $current_date = new DateTime(date("Y-m-d"));
                    $event_date = new DateTime(substr($event['date_evenement'], 0, 10));
                    echo date('d/m/Y', strtotime($event['date_evenement']));
                ?>
            </h2>
            <?php if($event_date < $current_date):?>
                <button class="subscription" id="passed_subscription">Passé</button>
            <?php else:
                @$a = $db->select("SELECT * FROM INSCRIPTION WHERE id_evenement = ? AND id_membre = ?;","ii",[$_GET['id'], $_SESSION['userid']]);
                $isSubscribed = !empty($a);
                if($isSubscribed):?>
                    <form class="subscription" action="/event_unsubscribe.php" method="post">
                        <input type="hidden" name="eventid" value="<?php echo $eventid ?>">
                        <button type="submit" id="passed_subscription">Se désinscrire</button>
                    </form>
                <?php
                else:?>
                    <form class="subscription" action="event_subscription.php" method="post">
                        <input type="text" name="eventid" value="<?php echo $eventid?>" hidden>
                        <button type="submit">Inscription</a></button>
                    </form>
                <?php endif;?>
            <?php endif;?>
        </div>

        <ul>
            <li>
                <div>📍<h3><?php echo $event['lieu_evenement']; ?></h3>
                </div>
            </li>
            <li>
                <div>💸<h3><?php echo $event['prix_evenement']; ?>€ par personne</h3>
                </div>
            </li>
            <?php if(boolval($event['reductions_evenement'])){echo "<li><div>💎<h3>-10% pour les membres Diamants</h3></div></li>";} ?>
        </ul>

        <p>
            <?php echo nl2br(htmlspecialchars($event['description_evenement'])); ?>
        </p>

    </section>


    <section class="gallery">
        <h2>GALLERIE</h2>
        <?php if($isLoggedIn):?>

        <h3>Mes photos</h3>
        <div class="my-medias">
            <?php
            $medias = $db->select(
                "SELECT url_media FROM `MEDIA` WHERE id_membre = ? and id_evenement = ? ORDER by date_media ASC LIMIT 4;",
                "ii",
                [$_SESSION["userid"], $eventid]
            );
            foreach($medias as $img):
                $url = '/api/files/' . trim($img['url_media']);
                $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                if (in_array($ext, ['mp4','webm','ogg','mov'])): ?>
                    <video src="<?= $url ?>" controls style="width:100%; border-radius:8px;"></video>
                <?php else: ?>
                    <img src="<?= $url ?>" alt="Image personnelle de l'événement">
                <?php endif;
            endforeach;?>

            <form id="add-media" action="/add_media.php" method="post" enctype="multipart/form-data">
                <label for="file-picker">
                    <img src="/assets/add_media.png" alt="Ajouter un média">
                </label>
                <input type="hidden" name="eventid" value="<?php echo $eventid?>">
                <input type="hidden" name="userid" value="<?php echo $_SESSION['userid']?>">

                <input type="file" id="file-picker" name="file" accept="image/jpeg, image/png, image/webp, video/mp4, video/webm, video/ogg, video/quicktime" hidden>
                <button type="submit" style="display:none;">Envoyer</button>
            </form>

            <form id="open-gallery" action="/my_gallery.php" method="get">
                <label for="open-gallery-button">
                    <img src="/assets/explore_gallery.png" alt="Voir ma galerie entière">
                </label>
                <input type="hidden" name="eventid" value="<?php echo $eventid ?>">
                <button id="open-gallery-button" type="submit" style="display:none;">Envoyer</button>
            </form>



        </div>
        <?php endif;?>
        <h3>Collection Generale</h3>

        <div class="general-medias">

            <?php $medias = $db->select(
                "SELECT url_media FROM `MEDIA` WHERE id_evenement = ? ORDER by date_media ASC LIMIT ? ;",
                "ii",
                [$eventid, $show]
            );
            foreach($medias as $media => $img):?>
            <img src="/api/files/<?php echo trim($img['url_media']);?>" alt="Image de l'événement">
            <?php endforeach;?>


        </div>
        <div class="show-more">
            <form action="" method="GET" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $eventid?>">
                <input type="hidden" name="show" value="<?php echo $show + 8?>">

                <button type="submit">Voir plus</button>
            </form>

            <form action="" method="GET" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $eventid?>">
                <?php if($show >= 20): ?>
                <input type="hidden" name="show" value="<?php echo $show - 10?>">
                <?php endif;?>
                <button type="submit">Voir Moins</button>
            </form>
        </div>


    </section>


    <?php require_once 'footer.php';?>
    <script src="/scripts/open_media.js"></script>
    <script src="/scripts/add_media.js"></script>
    <script src="/scripts/open_gallery.js"></script>

</body>

</html>