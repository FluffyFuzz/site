<!DOCTYPE html>
<html lang="fr">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <title>Ma Gallerie</title>
    
    <link rel="stylesheet" href="/styles/my_gallery_style.css">
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">

 

</head>
<body>
<?php
        require_once 'header.php';
        require_once 'database.php';
        $db = new DB();

        $isLoggedIn = isset($_SESSION["userid"]);
        $limit = 10;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET["show"]) && ctype_digit($_GET["show"])) {
        $limit = (int) $_GET["show"];
    }

    if (isset($_GET['eventid']) && $isLoggedIn) {
        $eventid = $_GET['eventid'];
        $userid = $_SESSION["userid"];
    } else {
        header("Location: /index.php");
        exit;
    }
}

        $event = $db->select(
            "SELECT `nom_evenement` FROM EVENEMENT WHERE id_evenement = ?",
            "i",
            [$eventid]
        )[0];
        ?>


<section class="user-gallery">

    <a href="/event_details.php?id=<?php echo "$eventid";?>" class="back-arrow">
        &#8592;<span>Retour</span>
    </a>
    <h1>MA GALLERIE</h1>
    <h2><?php echo $event['nom_evenement']?></h2>

    <div class="my-medias">

            <form id="add-media" action="add_media.php" method="post" enctype="multipart/form-data">
                <label for="file-picker">
                    <img src="/assets/add_media.png" alt="Ajouter un média">
                </label>
                <input type="hidden" name="eventid" value="<?php echo $eventid?>">
                <input type="hidden" name="userid" value="<?php echo $_SESSION['userid']?>">

                <input type="file" id="file-picker" name="file" accept="image/jpeg, image/png, image/webp" hidden>
                <button type="submit" style="display:none;">Envoyer</button>
            </form>

           <?php

            $medias = $db->select(
                "SELECT id_media, url_media FROM `MEDIA` WHERE id_membre = ? and id_evenement = ? ORDER by date_media ASC LIMIT ?;",
                "iii",
                [$userid, $eventid, $limit]
            );

            foreach ($medias as $media => $img) :?>
                <div class="media-container">
                    <img src="/api/files/<?php echo trim($img['url_media']); ?>" alt="Image Personnelle de l'événement">
                    <div class="delete-icon">

                        <form class="delete-media" action="delete_media.php" method="post">
                            <label for="del-media">
                                <img src="assets/delete_icon.png" alt="poubelle">
                            </label>
                            <input type="hidden" name="mediaid" value="<?php echo $img['id_media']?>">
                            <input type="hidden" name="eventid" value="<?php echo $eventid?>">

                            <button type="submit" style="display:none;">Envoyer</button>
                        </form>

                    </div>
                </div>
            <?php endforeach;?>

    </div>

</section>


<?php require_once 'footer.php';?>

<script src="/scripts/open_media.js"></script>
<script src="/scripts/add_media.js"></script>
<script src="/scripts/delete_media.js"></script>


</body>
</html>