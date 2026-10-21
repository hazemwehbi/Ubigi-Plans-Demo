# Couverture des exigences

Le cahier des charges décrit une extension pédagogique, sans service télécom réel ni commerce en ligne.

| Exigence | Réalisation |
| --- | --- |
| ZIP autonome | Dossier unique avec PHP, CSS et JavaScript inclus |
| Catalogue, pays et régions | Deux shortcodes, taxonomie native et styles adaptatifs isolés |
| Recherche et filtres | Recherche par contenu ou destination ; filtres numériques côté serveur et pagination validée |
| Détails | Type de contenu public, modèles WordPress et caractéristiques du forfait |
| Chargement, erreur, absence de résultat | Statut accessible, annulation des requêtes obsolètes et nouvelle tentative |
| Administration et statistiques | Éditeurs de forfaits et destinations, tableau de bord |
| Paramètres, devise et cache | Pagination, devise par défaut et durée configurables ; aucune conversion monétaire |
| Données | Titre, description, statut et dates natifs ; métadonnées protégées |
| API REST | Catalogue, détail, destinations et santé ; aucune écriture anonyme |
| Sécurité | Capacités, nonces, schémas REST, validation stricte, échappement et SQL préparé |
| Cache | Invalidation lors des modifications de contenu, métadonnées, taxonomie ou paramètres |
| IA | Cinq tâches, langue configurable, appel serveur et brouillon à valider ; aucune publication automatique |
| n8n facultatif | Webhook HTTPS signé et différé ; résultat journalisé |
| Journal et diagnostics | Journal limité sans secrets ni contenus ; diagnostics réservés à l’administration |
| Installation et réactivation | Initialisation sans doublons, conservation des pages existantes, aucun changement du thème, des menus ou de l’accueil |
| Désinstallation | Conservation par défaut ; retrait des secrets et tâches ; suppression des contenus uniquement sur option explicite |
| Langues | Documentation française et catalogue français PHP et JavaScript, avec fichiers POT/PO/MO/JSON |
| Livraison | Sources, GPL, dépendances verrouillées, tests, CI, construction et sauvegarde |

Les intégrations nécessitent des services externes facultatifs. Les essais n’utilisent aucun appel IA payant. Le fichier n8n est un exemple de vérification de signature : le workflow destinataire doit gérer le corps brut, les secrets et la validation humaine.

L’activation réseau, WooCommerce, le paiement, le provisionnement eSIM et le renseignement automatique d’une extension SEO sont hors périmètre. Les contenus éditoriaux existants ne sont pas réécrits lors d’un changement de langue.
