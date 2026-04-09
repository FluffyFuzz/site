<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <title>Actualités</title>
    <link rel="stylesheet" href="/styles/news_style.css">

    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">
</head>
<body class="body_margin">
<?php
    require_once 'header.php';
    require_once 'database.php';
    $db = new DB();
    $show = 5;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['show']) && is_numeric($_GET['show'])) {
    $show = (int) $_GET['show'];
}
?>
<h1>ACTUALITES</h1>
<section>
    <a class="show-more" href="/news.php?show= <?php echo $show + 10?>">Voir plus loin dans le passé</a>
    <div class="events-display">
                <?php
                    $date = getdate();
                    $sql_date = $date["year"] . "-" . $date["mon"] . "-" . $date["mday"];
                    $joursFr = [0 => 'Dimanche', 1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];
                    $moisFr = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
                    $current_date = new DateTime(date("Y-m-d"));

                    $events_to_display = $db->select(
                        "SELECT id_actualite, titre_actualite, date_actualite FROM ACTUALITE WHERE date_actualite <= NOW() ORDER BY date_actualite ASC LIMIT ?;",
                        "i",
                        [$show]
                    );

                    $closest_event_id = "";

                    foreach ($events_to_display as $event) :
                        $eventid = $event["id_actualite"];
                        $event_date = substr($event['date_actualite'], 0, 10);
                        $event_date_info = getdate(strtotime($event_date));
                        $event_date = new DateTime($event_date);

                        if ($event_date == $current_date) {
                            $closest_event_id = "closest-event"; // Marquer l'événement du jour comme le plus proche
                        } else {
                            if (empty($closest_event_id)) {
                                $closest_event_id = "closest-event"; // Marquer le premier événement futur comme le plus proche
                            }
                        }
                        ?>
                    <div class="event-box"  id="<?php echo $closest_event_id ?>">
                        <div class="timeline-event">
                            <h4> <?php echo ucwords($joursFr[$event_date_info['wday']] . " " . $event_date_info["mday"] . " " . $moisFr[$event_date_info['mon']]);?></h4>
                            <div class="vertical-line"></div>
                        </div>
                        <div class="event" event-id="<?php echo $eventid;?>">
                            <div>
                                <h2 style="margin-bottom: 0px;"><?php echo $event['titre_actualite'];?></h2>
                            </div>
                            <h4
                                <?php
                                $event_subscription_color_class = "event-not-subscribed";
                                $event_subscription_label = "Consulter";

                                echo "class=\"$event_subscription_color_class\"";
                                ?>>
                                <?php echo $event_subscription_label;?>
                            </h4>
                        </div>
                    </div>
                        <?php $closest_event_id = "";?>
                    <?php endforeach; ?>
        </div>
</section>

<?php require_once "footer.php" ?>

<script src="/scripts/news_details_redirect.js"></script>
<script src="/scripts/scroll_to_closest_event.js"></script>

</body>
</html>
