# BPMN : le modèle des règles métier, dessiné, exécuté en partie, testé

Opened: 2026-10-08
Updated: 2026-10-09
Author: agent:main

Une seule todo pour quatre packages : `php-bpmn`, `symfony-bpmn`,
`symfony-bpmn-ds`, `symfony-bpmn-demo`.

## Le besoin (owner)

Poser noir sur blanc, devant un client, les règles d'une plateforme multi-acteurs :
qui peut faire quoi, ce qu'une action déclenche (une notification, un blocage
jusqu'à une date), les dépendances et les workflows entre groupes. Le voir, et
si possible s'en servir pour les droits (voters) et pour les tests d'intégration.
C'est du BPMN : couloirs = acteurs, tâches = actions, passerelles = choix,
timers = « avant la date X », messages = notifications.

## Décisions

- **Standard** : BPMN 2.0 (OMG, ISO/IEC 19510), format d'échange XML. On annonce
  précisément le sous-ensemble supporté (le standard définit des niveaux de
  conformité : descriptif, analytique), plutôt que de prétendre à BPMN entier.
- **Pas de moteur BPMN complet** (Camunda, Flowable : des années de travail, rien
  de mûr en PHP). Un sous-ensemble se traduit vers ce qui existe ; le reste est
  dessiné, non exécuté, et l'outil le dit.
- **Pas de bpmn-js** : un outil maison, construit sur le design system et qui
  l'enrichit (affichage puis édition).
- **Partir de cas réels avant le format** : modéliser à la main un projet client
  et le scénario facture de projet de network (7 utilisateurs, 15 étapes) ; c'est
  ce qui fixe le sous-ensemble et ses traductions.

## Découpage

- **php-bpmn** (sans framework) : lire et écrire le XML BPMN 2.0, un modèle typé
  (processus, couloirs, tâches, passerelles, événements dont timers et messages,
  flux), vérification du sous-ensemble supporté, parcours des chemins d'un
  processus.
- **symfony-bpmn** :
  - traduction vers Symfony Workflow : tâche d'un couloir → transition gardée par
    le grant du couloir (memberships de symfony-user, todo
    `sapiens-group-memberships-and-scoping.md` section 5) ; passerelle exclusive →
    transitions conditionnées ; timer → garde de date (Scheduler) ; message →
    notification (symfony-notification, construit le 2026-10-08) ;
  - une matrice rôle × action × état pour les voters ; les droits fins que BPMN
    n'exprime pas (modifier oui, supprimer non) vivent à côté (matrice, ou tables
    de décision type DMN si le besoin se confirme) ;
  - **les scénarios de test** : un récit à plusieurs acteurs est un parcours d'un
    chemin du processus, un acteur par couloir, exécuté sur le kernel de
    symfony-testing. Reprend la proposition `symfony-scenario` de network (voir
    Sources), qui n'a plus de package à elle.
- **symfony-bpmn-ds** : l'affichage du diagramme (couloirs, tâches, flux), le
  parcours d'un chemin (« user story à tiroirs »), puis l'édition ; sur les
  composants et les JSON schemas du design system (todo
  `symfony-design-system/.wex/journal/todo/component-schemas.md`).
- **symfony-bpmn-demo** : les cas d'exemple, réels compris. Une démo à elle, sans utilisateurs propres : elle s'appuie sur les comptes et les équipes de symfony-user-demo (décision du 2026-10-08, comme notification-demo).


## Cas réels retenus (2026-10-09, avec le propriétaire)

- **Gestion locative** (MOJOE we-invest, `test-1/workflows/` : 8 workflows current/target en YAML, `graphs/` en Mermaid avec les décisions) : entrée du locataire (bail signé, acompte, garantie, état des lieux), demande d'intervention avec devis > 200 € soumis au propriétaire, rappels J-1 / H-1 d'une visite. Anonymisé : une agence de location générique.
- **Lettre de mission** (MOJOE engagement-letters, `.wex/local/sources/roadmap/2026-04-16-export/`) : composition, validation, signatures dans l'ordre manager → associé → client, archivage, renouvellement à 1 ou 3 ans selon le risque, relance. Anonymisé : un cabinet de conseil générique, sans nom de client ni d'outil.
- **Relecture d'article** (inventé, sur les équipes de symfony-user-demo) : un contributeur soumet, un administrateur valide ou renvoie, l'observateur est prévenu, relance après deux jours.

Le propriétaire laisse la main pour le backend (php-bpmn, symfony-bpmn) et revient pour la partie visible (-ds, -demo).

## Conception retenue (2026-10-09)

**php-bpmn**
- Modèle : `Definitions` (processus, collaborations, messages, signaux, erreurs, diagramme), `Process` (nœuds, flux, couloirs), tâches typées (user, manual, service, send, receive, script, business rule), sous-processus, call activity, événements (début, fin, intermédiaires, frontière) et leurs définitions (message, timer, signal, error, conditional, terminate…), passerelles (exclusive, parallel, inclusive, event-based), flux conditionnels et par défaut, couloirs imbriqués.
- Lecture et écriture du XML BPMN 2.0, le diagramme (BPMNDI) conservé d'un aller-retour ; des propriétés à nous dans `extensionElements` (`wex:property`) : rôle ou accès d'un couloir, catégorie de notification d'un message.
- Un builder PHP, un validateur (structure, puis sous-ensemble exécutable), l'énumération des chemins d'un processus (scénarios).

**symfony-bpmn**
- Un registre des `.bpmn` de l'application ; chaque processus traduit en `Symfony\Component\Workflow` (type workflow, réseau de Petri) : places = états d'attente (activités, événements intermédiaires, passerelle event-based, fins), transitions = achèvement d'une activité vers les attentes suivantes à travers les passerelles ; conditions en ExpressionLanguage ; la jointure parallèle par places d'arrivée.
- Gardes : couloir → rôle, ou accès de membership (symfony-user) sur le groupe du sujet.
- Runner : démarrer, achever une tâche, appliquer les transitions automatiques (tâches de service via des handlers, envois, jointures), programmer les timers (Messenger avec délai), émettre les messages (symfony-notification s'il est installé), tracer (symfony-activity s'il est installé).
- Matrice des droits (couloir × tâche), scénarios tirés des chemins, commandes (lister, valider, chemins, matrice, dump Mermaid du workflow).

## État (2026-10-09, agent:main)

Backend fait, testé, documenté (knowledge usage des deux packages) :

- **php-bpmn** (26 tests) : modèle, lecture / écriture XML avec le diagramme, builder, validateur (structure + sous-ensemble exécutable), chemins, ISO 8601 et timers en expression (`${variables['…']}`). Cinq exemples anonymisés dans `examples/` (entrée du locataire, demande d'intervention, réservation de visite, lettre de mission, relecture d'article), écrits depuis `tests/Fixtures/ExampleProcesses.php`.
- **symfony-bpmn** (17 tests) : registre des `.bpmn`, compilation en Symfony Workflow (réseau de Petri : exclusif, parallèle avec jointure, event-based, frontières timer / message, throw events), gardes (conditions ExpressionLanguage, couloir → rôle ou grant sur le groupe du sujet), runner (start, complete, receive, fireTimer, tâches automatiques via handlers, timers Messenger une fois par événement, choix au déclenchement), événements de transition et de message, matrice des droits, scénarios joués par couloir, commandes `bpmn:list|validate|paths|matrix|dump`. Avec symfony-activity : historique `bpmn` ; avec symfony-notification : messages notifiés via `BpmnRecipientResolverInterface`.

Corrigé en chemin : notification sans client HTTP ne démarrait pas (webhook désormais optionnel).

Non fait, volontairement :
- pas d'installation dans MOJOE (rien de visible encore) ;
- sous-processus et call activities compilés comme une seule étape (pas d'exécution de leur flux interne) ;
- boundary non interrompants, inclusive / complex, erreurs, compensation : dessinés, non exécutés (le validateur le dit) ;
- les huit workflows de gestion locative ne sont pas tous convertis : trois en exemples (WF-002, 004, 007), les autres suivront le même chemin si utile.

Reste pour la partie visible (avec le propriétaire) : **symfony-bpmn-ds** (affichage du diagramme — mise en page automatique quand le fichier n'a pas de BPMNDI —, parcours d'un chemin, puis édition) et **symfony-bpmn-demo** (les exemples, sur les comptes et équipes de user-demo ; la relecture d'article branchée sur les grants des équipes).

## Ordre

1. Modéliser les deux cas réels en BPMN, à la main.
2. php-bpmn : modèle, lecture/écriture, sous-ensemble.
3. symfony-bpmn-ds : affichage (lecture seule), pour discuter des cas.
4. symfony-bpmn : Workflow + voters, puis les scénarios de test.
5. Édition dans -ds.

## Sources (lecture seule : network tourne sur des données de prod)

- Proposition scenario : `NETWORK/archeo/proposed-packages/done/symfony-scenario/todo/extract-from-network.md`
  et la page `NETWORK/local/network/.wex/knowledge/readme/archeology/testing.md.j2`
  (design cible, runner, matrice d'accès, exemple YAML + PHP complet, pièges).
- Scénario de référence : `NETWORK/archeo/trees/develop-131-fos-user/tests/Integration/Scenario/ProjectInvoice/`.
- Prérequis tests : todo `symfony-testing/.wex/journal/todo/extract-from-network.md` (étapes 1–4).
- Issues network : 108 (scénarios en user stories), 65 (règles de droits), 271
  (reconnexion après changement de rôle).
