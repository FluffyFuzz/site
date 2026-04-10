<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <title>Accueil</title>

    <link rel="stylesheet" href="/styles/index_style.css">
    <link rel="stylesheet" href="/styles/general_style.css">
    <link rel="stylesheet" href="/styles/header_style.css">
    <link rel="stylesheet" href="/styles/footer_style.css">
    <link rel="stylesheet" href="/styles/bubble.css">

</head>

<body id="index" class="body_margin">

    <?php
     require_once 'header.php';
     require_once 'database.php';
     $db = new DB();
     $isLoggedIn = isset($_SESSION["userid"]);
    ?>
    <div id="page-container">
        <!--H1 A METTRE -->
        <section>
            <h2 class="titre_vertical"> ADIIL</h2>
            <div id="index_carrousel">
                <img src="/assets/photo_accueil_BDE.webp" alt="Carrousel ADIIL">
            </div>
        </section>

        <section>
            <div class="paragraphes">
                <p>
                    <b class="underline">L'ADIIL</b>, ou l'<b>Association</b> du <b>Département</b> <b>Informatique</b>
                    de l'<b>IUT</b> de <b>Laval</b>,
                    est une organisation étudiante dédiée à créer un environnement propice à l'épanouissement dans le
                    campus.
                    Participer a des évèvements, et plus globalement a la vie du département.
                </p>
                <p>
                    L'ADIIL, véritable moteur de la vie étudiante à l'IUT de Laval,
                    offre un cadre propice à l'épanouissement académique et social des étudiants en informatique.
                    En participant à ses événements variés, les étudiants enrichissent leur expérience universitaire,
                    tout en renforçant les liens au sein de la communauté.
                </p>
            </div>
            <h2 class="titre_vertical">L'ASSO</h2>
        </section>

        <section>
            <h2 class="titre_vertical">SCORES</h2>

            <div id="podium">
                <?php
                $podium = $db->select(
                    "SELECT prenom_membre, xp_membre, pp_membre FROM MEMBRE ORDER BY xp_membre DESC LIMIT 3;"
                );

                foreach ([2,1,3] as $member_number) :
                    $pod = $podium[$member_number - 1];

                    ?>
                <div class="podium_unit">
                    <h3>#0<?php echo $member_number?></h3>
                    <h4><?php echo $pod['prenom_membre'];?></h4>
                    <div>
                        <?php if ($pod['pp_membre'] == null) :?>
                            <img src="/admin/ressources/default_images/user.jpg" alt="Profile Picture"
                            class="profile_picture">
                        <?php else :?>
                            <img src="/api/files/<?php echo $pod['pp_membre'];?>" alt="Profile Picture"
                                class="profile_picture">
                        <?php endif?>
                        <?php echo $pod['xp_membre'];?> xp
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <div class="events-display">
                <?php
                    $date = getdate();
                    $sql_date = $date["year"] . "-" . $date["mon"] . "-" . $date["mday"];
                    $events_to_display = $db->select(
                        "SELECT id_evenement, nom_evenement, lieu_evenement, date_evenement FROM EVENEMENT WHERE date_evenement >= ? ORDER BY date_evenement ASC LIMIT 2;",
                        "s",
                        [$sql_date]
                    );

                    foreach ($events_to_display as $event) :
                        $eventid = $event["id_evenement"];?>

                <div class="event" event-id="<?php echo $eventid;?>">
                    <div>
                        <h2><?php echo $event['nom_evenement'];?></h2>
                            <?php
                                $moisFr = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];

                                $event_date = substr($event['date_evenement'], 0, 10);
                                $event_date_info = getdate(strtotime($event_date));
                                echo ucwords($event_date_info["mday"] . " " . $moisFr[$event_date_info['mon']] . ", " . $event["lieu_evenement"]);
                            ?>
                    </div>

                    <h4 <?php
                            $isPlaceDisponible = $db->select(
                                "SELECT (EVENEMENT.places_evenement < 0 OR (EVENEMENT.places_evenement - (SELECT COUNT(*) FROM INSCRIPTION WHERE INSCRIPTION.id_evenement = EVENEMENT.id_evenement)) > 0) AS isPlaceDisponible FROM EVENEMENT WHERE EVENEMENT.id_evenement = ? ;",
                                "i",
                                [$eventid]
                            )[0]['isPlaceDisponible'];

                        if ($isPlaceDisponible) {
                                //editable
                                $event_subscription_color_class = "event-not-subscribed hover_effect";
                                $event_subscription_label = "S'inscrire";
                        } else {
                        //editable
                            $event_subscription_color_class = "event-full";
                            $event_subscription_label = "Complet";
                        }

                        if ($isLoggedIn) {
                            $isSubscribed = !empty($db->select(
                                "SELECT MEMBRE.id_membre FROM MEMBRE JOIN INSCRIPTION on MEMBRE.id_membre = INSCRIPTION.id_membre WHERE MEMBRE.id_membre = ? AND INSCRIPTION.id_evenement = ? ;",
                                "ii",
                                [$_SESSION['userid'], $event["id_evenement"]]
                            ));

                            if ($isSubscribed) {
                                //editable
                                $event_subscription_color_class = "event-subscribed";
                                $event_subscription_label = "Inscrit";
                            }
                        }

                            echo "class=\"$event_subscription_color_class\"";
                        ?>>
                        <?php echo $event_subscription_label;?>

                    </h4>
                </div>
                    <?php endforeach; ?>
                <h3><a href="/events.php">Voir tous les événements</a></h3>
            </div>
            <h2 class="titre_vertical">EVENT</h2>

        </section>
    </div>
    <?php require_once 'footer.php';?>
    <script src="/scripts/event_details_redirect.js"></script>
    <script src="/scripts/bubble.js"></script>
</body>

</html>