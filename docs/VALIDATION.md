# Rapport de validation

Validation initiale réalisée localement le 21 octobre 2026. Ce rapport décrit les contrôles observés ; il ne garantit pas toutes les combinaisons d’hébergement et de thème.

| Environnement ou contrôle | Résultat initial |
| --- | --- |
| WordPress 7.1.3, PHP 8.3.28, MySQL 8.4.7 | 31 contrôles d’intégration et 8 contrôles du cycle de vie réussis |
| WordPress 6.5, PHP 8.1.33, MySQL 8.4.7 | 31 contrôles d’intégration et 8 contrôles du cycle de vie réussis |
| WPCS 3.4.1 et PHP_CodeSniffer 3.13.6 | WordPress-Core et règles d’échappement, de nonces et de validation des entrées respectés |
| Syntaxe | Fichiers PHP validés par `php -l`, JavaScript par `node --check` |
| Vérification visuelle initiale | Catalogue et cartes visibles sur une largeur mobile avec le thème fourni |

La suite couvre l’initialisation et la réactivation, les détails publics, les filtres, la recherche, la pagination, les entrées invalides, la santé sans données sensibles, la confidentialité des brouillons et contenus protégés, le cache et les permissions d’édition. Elle vérifie aussi les shortcodes, l’IA désactivée, les brouillons simulés sans publication, la limitation des appels, les signatures, les redirections interdites, les journaux et le masquage des erreurs fournisseur.

Les tests du cycle de vie vérifient la conservation par défaut, le retrait des secrets et tâches, la suppression explicite des données appartenant à l’extension, la conservation des pages étrangères et la réinitialisation des exemples.

Les requêtes OpenAI et n8n sont simulées par les filtres HTTP WordPress. Aucun appel payant ni workflow n8n réel n’a été utilisé. Vérifiez les clés, quotas, modèles, contrôles du workflow et accès réseau lors de l’activation des intégrations. Le webhook effectue une seule tentative et dépend de WP-Cron ; aucun workflow entrant ne modifie les contenus.

La vérification visuelle initiale portait sur le mobile. Les points de rupture CSS pour ordinateur sont inclus ; aucun laboratoire de compatibilité, test de charge ou audit d’intrusion indépendant n’est revendiqué. La CI GitHub est préparée mais n’a pas été exécutée sur GitHub depuis cet environnement.

Le catalogue français comprend 88 messages. Le fichier MO a été chargé par WordPress et la traduction « Plans → Forfaits » vérifiée. Les 31 contrôles d’intégration et les 8 contrôles du cycle de vie ont été exécutés avec succès sur WordPress 7.1.3 et PHP 8.3.28. Le contrôle WPCS est conforme et les archives distribuées ont été vérifiées.

Les archives excluent les installations locales, configurations de test, outils tiers, caches, journaux et identifiants. Des empreintes SHA-256 accompagnent les deux livrables.
