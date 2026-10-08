# BPMN : le modèle des règles métier, dessiné, exécuté en partie, testé

Opened: 2026-10-08
Updated: 2026-10-08
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
