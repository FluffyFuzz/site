<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <title>Evenements</title>
    <link rel="stylesheet" href="/styles/events_style.css">
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">
</head>
<body class="body_margin">
<?php
    require_once 'header.php';
    require_once 'database.php';
    $db = new DB();
    $isLoggedIn = isset($_SESSION["userid"]);
    $show = 5;

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['show']) && is_numeric($_GET['show'])) {
        $show = (int) $_GET['show'];
    }
?>
<h1>LES EVENEMENTS</h1>
<section>
    <div class="events-filters">
        <label><input type="checkbox" id="filter-passe" checked> Passés</label>
        <label><input type="checkbox" id="filter-avenir" checked> À venir</label>
        <input type="text" id="filter-lieu" placeholder="Filtrer par lieu...">
    </div>
    <div class="events-display">
                <?php
                    $date = getdate();
                    $sql_date = $date["year"]."-".$date["mon"]."-".$date["mday"];
                    $joursFr = [0 => 'Dimanche', 1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];
                    $moisFr = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
                    $current_date = new DateTime(date("Y-m-d"));

                    $upcoming_events = $db->select(
                        "SELECT id_evenement, nom_evenement, lieu_evenement, date_evenement FROM EVENEMENT WHERE date_evenement >= ? AND deleted = false ORDER BY date_evenement ASC;",
                        "s",
                        [$sql_date]
                    );
                    $passed_events = $db->select(
                        "SELECT id_evenement, nom_evenement, lieu_evenement, date_evenement FROM EVENEMENT WHERE date_evenement < ? AND deleted = false ORDER BY date_evenement DESC LIMIT ?;",
                        "si",
                        [$sql_date, $show]
                    );
                    $events_to_display = array_merge($upcoming_events, $passed_events);

                    $closest_event_id = "";

                    foreach ($events_to_display as $event):
                        $eventid = $event["id_evenement"];
                        $event_date = substr($event['date_evenement'], 0, 10);
                        $event_date_info = getdate(strtotime($event_date));
                        $event_date = new DateTime($event_date);
                        $other_classes = "";
                        $isPassed = false;

                        if ($event_date < $current_date) {
                            $date_pin_class = "passed";
                            $date_pin_label = "Passé";
                            $other_classes = 'passed';
                            $isPassed = true;
                        } elseif ($event_date == $current_date) {
                            $date_pin_class = "today";
                            $date_pin_label = "Aujourd'hui";
                            $closest_event_id = "closest-event"; // Marquer l'événement du jour comme le plus proche
                        } else {
                            $date_pin_class = "upcoming";
                            $date_pin_label = "A venir";
                            if (empty($closest_event_id)) {
                                $closest_event_id = "closest-event"; // Marquer le premier événement futur comme le plus proche
                            }
                        }
                ?>
                    <div class="event-box <?php echo "$other_classes";?>" id="<?php echo $closest_event_id ?>">
                        <div class="timeline-event">
                            <h4> <?php echo ucwords($joursFr[$event_date_info['wday']]." ".$event_date_info["mday"]." ".$moisFr[$event_date_info['mon']]);?></h4>
                            <div class="vertical-line"></div>
                            <p> <?php echo "$date_pin_label";?></p>
                            <div class="timeline-marker <?php echo " $date_pin_class" ?>">
                                <div class="time-line"></div>
                            </div>
                        </div>
                        <div class="event" event-id="<?php echo $eventid;?>">
                            <div>
                                <h2><?php echo $event['nom_evenement'];?></h2>
                                <?php echo ucwords($event["lieu_evenement"]);?>
                            </div>
                            <h4
                                <?php
                                $isPlaceDisponible = $db->select(
                                    "SELECT (EVENEMENT.places_evenement < 0 OR (EVENEMENT.places_evenement - (SELECT COUNT(*) FROM INSCRIPTION WHERE INSCRIPTION.id_evenement = EVENEMENT.id_evenement)) > 0) AS isPlaceDisponible FROM EVENEMENT WHERE EVENEMENT.id_evenement = ? ;",
                                    "i",
                                    [$eventid])[0]['isPlaceDisponible'];
                                
                                if($isPlaceDisponible){
                                    $event_subscription_color_class = "event-not-subscribed hover_effect";
                                    $event_subscription_label = "S'inscrire";
                                }else{
                                    $event_subscription_color_class = "event-full";
                                    $event_subscription_label = "Complet";
                                }

                                if($isLoggedIn){
                                    $isSubscribed = !empty($db->select(
                                    "SELECT MEMBRE.id_membre FROM MEMBRE JOIN INSCRIPTION on MEMBRE.id_membre = INSCRIPTION.id_membre WHERE MEMBRE.id_membre = ? AND INSCRIPTION.id_evenement = ? ;",
                                    "ii",
                                    [$_SESSION['userid'], $event["id_evenement"]]
                                    ));
                                    
                                    if($isSubscribed){
                                        $event_subscription_color_class = "event-subscribed";
                                        $event_subscription_label = "Inscrit";
                                    }
                                }
                                
                                if($isPassed){
                                    $event_subscription_color_class = "event-full";
                                    $event_subscription_label = "Passé";
                                }
                                echo "class=\"$event_subscription_color_class\"";
                                ?>>
                                <?php echo $event_subscription_label;?>
                            </h4>
                        </div>
                    </div>
                    <?php $closest_event_id = "";?>
                <?php endforeach; ?>
        </div>
    <a class="show-more" href="/events.php?show=<?php echo $show + 10?>">Voir plus loin dans le passé</a>
</section>
    <?php require_once 'footer.php';?>
    <script src="/scripts/event_details_redirect.js"></script>
    <script src="/scripts/scroll_to_closest_event.js"></script>
    <script>
    (function(){
        const filterPasse  = document.getElementById('filter-passe');
        const filterAvenir = document.getElementById('filter-avenir');
        const filterLieu   = document.getElementById('filter-lieu');

        function applyFilters(){
            const showPasse  = filterPasse.checked;
            const showAvenir = filterAvenir.checked;
            const lieu = filterLieu.value.toLowerCase();

            document.querySelectorAll('.event-box').forEach(box => {
                const isPassed  = box.classList.contains('passed');
                const lieuText  = box.querySelector('.event div')?.textContent?.toLowerCase() ?? '';

                const statusOk = (isPassed && showPasse) || (!isPassed && showAvenir);
                const lieuOk   = lieu === '' || lieuText.includes(lieu);

                box.style.display = (statusOk && lieuOk) ? '' : 'none';
            });
        }

        filterPasse.addEventListener('change', applyFilters);
        filterAvenir.addEventListener('change', applyFilters);
        filterLieu.addEventListener('input', applyFilters);
    })();
    </script>
</body>
</html>
