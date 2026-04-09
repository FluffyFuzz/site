# Rapport de diagnostic fonctionnel

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDA004
Titre            : Modifier les stocks d'un article de la boutique
Type             : Bug
Criticité        : 2 (Grave)
Fichier(s)       : api/item.php, api/filter.php
Problème         : La directive ini_set('display_errors', 1) présente dans api/item.php (marquée TODO) injectait toute erreur PHP sous forme de HTML dans le body de la réponse ; combinée à output_buffering = On (docker-compose.yml), ce HTML était concaténé avant le JSON, le rendant non parseable côté client. De plus, filter::bool() dans filter.php utilisait un contrôle $filtered === null qui ne pouvait jamais être vrai, car filter_var() sans FILTER_NULL_ON_FAILURE retourne false (et non null) pour toute valeur invalide.
Correction       : Suppression de ini_set('display_errors', 1) dans api/item.php pour que les erreurs PHP ne contaminent plus le corps JSON de la réponse. Ajout du flag FILTER_NULL_ON_FAILURE dans filter_var() de filter::bool() afin que le contrôle de nullité soit effectivement atteint lors d'une valeur booléenne invalide.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDA005
Titre            : Modifier le nom d'un article de la boutique
Type             : Bug
Criticité        : 2 (Grave)
Fichier(s)       : api/item.php
Problème         : Même cause racine que FDA004 — la directive ini_set('display_errors', 1) dans api/item.php provoquait l'injection de HTML d'erreur PHP dans la réponse JSON. Modifier le nom et modifier les stocks passent tous deux par la fonction update_item() du même fichier.
Correction       : Aucune modification supplémentaire requise. Le bug est résolu par la correction de FDA004 (suppression de ini_set('display_errors', 1) dans api/item.php).
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDA010
Titre            : Créer une actualité
Type             : Bug
Criticité        : 2 (Grave)
Fichier(s)       : api/news.php, script.sql
Problème         : Le trigger SQL permissions_create_event (AFTER INSERT ON ACTUALITE) référençait la colonne `Gestion des actualites` dans la vue LISTE_PERMISSIONS, colonne inexistante — le nom réel est p_actualite. Cette erreur SQL dans le trigger annulait systématiquement chaque INSERT dans la table ACTUALITE. La directive ini_set('display_errors', 1) présente dans api/news.php masquait l'erreur réelle en renvoyant du HTML à la place du JSON.
Correction       : Correction du nom de colonne dans le trigger (p_actualite au lieu de `Gestion des actualites`) dans script.sql et en base via DROP/CREATE TRIGGER. Suppression de ini_set('display_errors', 1) dans api/news.php.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDM012
Titre            : Ajouter une photo à la galerie
Type             : Fonctionnalité manquante (bloquée)
Criticité        : 2 (Grave)
Fichier(s)       : event_details.php, add_media.php
Problème         : La fonctionnalité était inaccessible car la page event_details.php retournait une page blanche (même cause que FDV008 — colonne image_evenement absente de la base). Le fichier add_media.php existe et est fonctionnel.
Correction       : Aucune modification de code requise. Résolu par le rebuild qui corrige FDV008 et restaure l'accès à event_details.php depuis laquelle l'ajout de photo est déclenché.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDM013
Titre            : Ajouter une vidéo à la galerie
Type             : Fonctionnalité manquante
Criticité        : 2 (Grave)
Fichier(s)       : event_details.php, files_save.php
Problème         : Deux obstacles cumulés. D'abord, la page event_details.php était inaccessible (cf. FDV008). Ensuite, même après rétablissement de la page, le formulaire d'ajout ne ciblait que les images (accept="image/jpeg, image/png, image/webp") et saveImage() dans files_save.php n'autorisait que les types MIME image — les vidéos étaient donc refusées à l'upload. La galerie affichait par ailleurs systématiquement une balise <img> quel que soit le fichier, sans rendu adapté aux vidéos.
Correction       : Ajout des types MIME vidéo (video/mp4, video/webm, video/ogg, video/quicktime) dans le tableau allowedTypes de saveImage() dans files_save.php. Mise à jour de l'attribut accept du file input dans event_details.php pour inclure les formats vidéo dont video/quicktime (.mov). Ajout dans la galerie personnelle d'une détection d'extension (mp4, webm, ogg, mov) pour afficher une balise <video controls> à la place de <img> pour les fichiers vidéo.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDM011
Titre            : Se désinscrire d'un événement
Type             : Fonctionnalité manquante
Criticité        : 2 (Grave)
Fichier(s)       : event_unsubscribe.php (nouveau), event_details.php
Problème         : La page event_details.php affichait uniquement un bouton "Inscrit" sans possibilité de se désinscrire. Aucune route de désinscription n'existait.
Correction       : Création de event_unsubscribe.php qui vérifie que l'inscription existe, supprime la ligne dans INSCRIPTION, et retire l'XP associé (avec GREATEST(0, xp - delta) pour éviter un XP négatif). Remplacement du bouton "Inscrit" dans event_details.php par un formulaire POST vers event_unsubscribe.php.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDV008
Titre            : Consulter la galerie d'un événement
Type             : Bug
Criticité        : 2 (Grave)
Fichier(s)       : script.sql
Problème         : La page event_details.php levait une exception MySQLi "Unknown column 'image_evenement' in SELECT" car la base de données en production tournait sur un ancien schéma ne comprenant pas encore cette colonne.
Correction       : Aucune modification de code requise. La colonne image_evenement est présente dans script.sql depuis FDV007. Le bug disparaît après un redémarrage complet (docker-compose down/up --build) qui réapplique le script SQL complet.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDA034
Titre            : Créer le compte d'un utilisateur
Type             : Bug
Criticité        : 2 (Grave)
Fichier(s)       : api/users.php, api/models/Member.php
Problème         : Deux causes cumulées. D'abord, ini_set('display_errors', 1) dans api/users.php injectait du HTML d'erreur PHP dans la réponse JSON (même pattern que FDA004/FDA015). Ensuite, Member::create() effectuait un INSERT sans fournir password_membre (colonne NOT NULL) ni en gérant correctement pp_membre (colonne NOT NULL, passée avec null PHP), ce qui provoquait l'échec de l'insertion en base.
Correction       : Suppression de ini_set('display_errors', 1) dans api/users.php. Correction de Member::create() : ajout de password_membre dans l'INSERT avec un mot de passe aléatoire hashé (password_hash + random_bytes), et conversion de $pp (File|null) en chemin de fichier string avec fallback '' pour respecter la contrainte NOT NULL de pp_membre.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDA032
Titre            : Consulter le chat des administrateurs
Type             : Fonctionnalité manquante
Criticité        : 3 (Très grave)
Fichier(s)       : api/chat.php (nouveau), admin/panels/chat.html, admin/scripts/chat.js (nouveau), admin/styles/chat.css (nouveau), script.sql
Problème         : La page chat du panel admin affichait uniquement une photo de chat aléatoire (https://cataas.com/cat). Aucune table de messages ni d'interface de conversation n'existait.
Correction       : Création des tables CONVERSATION (type ENUM 'admin'/'ticket' pour la scalabilité future) et MESSAGE dans script.sql, avec une conversation admin pré-insérée (id=1). Création de api/chat.php (GET avec paramètre after pour le polling incrémental, champ is_mine calculé côté serveur ; POST pour envoyer un message), protégé par la permission p_log. Remplacement de chat.html par un vrai chat (liste de messages scrollable + barre d'envoi), avec admin/scripts/chat.js gérant le chargement initial, le polling toutes les 5 secondes, l'envoi par bouton ou Entrée, et admin/styles/chat.css pour le rendu en bulles (messages propres à droite en bleu, autres à gauche).
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDA033
Titre            : Envoyer un message dans le chat des administrateurs
Type             : Fonctionnalité manquante
Criticité        : 3 (Très grave)
Fichier(s)       : api/chat.php (nouveau), admin/panels/chat.html, admin/scripts/chat.js (nouveau)
Problème         : Même cause racine que FDA032 — aucune interface de chat ni route POST d'envoi de message n'existait.
Correction       : Aucune modification supplémentaire requise. La correction de FDA032 implémente simultanément la consultation et l'envoi de messages.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDA022
Titre            : Supprimer une photo ou une vidéo de la galerie
Type             : Fonctionnalité manquante
Criticité        : 2 (Grave)
Fichier(s)       : api/media.php (nouveau), admin/panels/evenements.html, admin/scripts/events.js
Problème         : L'onglet "Galerie" n'existait pas dans le panel admin, rendant impossible la consultation et la suppression des médias associés à un événement. Aucune route API pour lire ou supprimer les entrées de la table MEDIA n'était implémentée.
Correction       : Création de api/media.php exposant GET (liste des médias par id_evenement) et DELETE (suppression par id_media), protégé par la permission p_evenement. Ajout d'un bouton "Galerie" dans la barre d'actions de la page événement (admin/panels/evenements.html) qui déploie sous les propriétés une grille de vignettes des médias de l'événement sélectionné, chacune munie d'un bouton de suppression. La logique de chargement et de suppression est ajoutée dans admin/scripts/events.js.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDA016
Titre            : Modifier le titre d'un événement
Type             : Bug
Criticité        : 2 (Grave)
Fichier(s)       : api/event.php, api/models/Event.php
Problème         : Même cause racine que FDA015 — la directive ini_set('display_errors', 1) dans api/event.php permettait aux avertissements PHP de contaminer la réponse JSON. Spécifiquement, Event::update() passait bool $reductions à bind_param avec le type 'i' sans cast explicite, générant en PHP 8.2 un avertissement de conversion implicite bool→int dont le HTML était concaténé avant le JSON via output_buffering, rendant la réponse non parseable.
Correction       : Aucune modification supplémentaire requise pour ini_set (déjà retiré en FDA015). Ajout du cast explicite (int)$reductions dans Event::update() en parallèle du même correctif appliqué à Event::create() dans FDA015.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDA015
Titre            : Créer un événement
Type             : Bug
Criticité        : 2 (Grave)
Fichier(s)       : api/event.php, api/models/Event.php
Problème         : La directive ini_set('display_errors', 1) présente dans api/event.php provoquait, comme pour FDA004 et FDA010, l'injection de HTML d'erreur PHP dans la réponse JSON. En PHP 8.2, le passage d'un bool (false) à bind_param avec le type 'i' génère un avertissement de conversion implicite ; combiné à output_buffering = On, ce HTML était concaténé avant le JSON, rendant la réponse non parseable et déclenchant le message "Erreur lors de la création de l'évenement" côté client.
Correction       : Suppression de ini_set('display_errors', 1) dans api/event.php. Ajout d'un cast explicite (int) sur le paramètre $reductions dans Event::create() pour supprimer l'avertissement de conversion implicite bool→int lors du bind_param.
======================

=== RÉSUMÉ RAPPORT ===
Identifiant      : FDM014
Titre            : Consulter son agenda
Type             : Fonctionnalité manquante
Criticité        : 3 (Très grave)
Fichier(s)       : agenda.php, api/agenda.php (nouveau), styles/planner_style.css
Problème         : La page agenda pointait vers un iframe sur https://edt.gemino.dev, un service tiers inaccessible, rendant la fonctionnalité totalement non opérationnelle. Aucun mécanisme n'existait pour récupérer l'emploi du temps universitaire ni pour afficher les événements du site auxquels l'étudiant est inscrit.
Correction       : Création de api/agenda.php qui télécharge le fichier ICS du département (Dpt INFO, ressource ADE unique) avec mise en cache d'une heure, et applique un filtre hiérarchique : pour chaque étudiant, seuls les cours correspondant à son groupe TP (ex. Grp 21A), son groupe TD (TD21) et sa promotion (BUT INFO2) sont conservés, les autres groupes étant exclus. Les événements BDE auxquels l'étudiant est inscrit (tables INSCRIPTION et EVENEMENT) sont fusionnés au résultat. Remplacement de l'iframe par un calendrier hebdomadaire HTML/JS (8h–20h, navigation semaine, jour courant mis en évidence, cours en bleu, événements BDE en vert, événements journée entière affichés en badge superposé sans perturber la grille).
======================
