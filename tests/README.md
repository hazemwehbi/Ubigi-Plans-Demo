# Tests d’intégration

Utilisez uniquement une installation WordPress de site unique, jetable, avec une base propre et un administrateur. Les tests créent des données, modifient les prix et suppriment des contenus temporaires. Ne les exécutez jamais en production.

1. Installez WordPress 6.5 ou ultérieur, PHP 8.1 ou ultérieur et MySQL ou MariaDB compatible.
2. Copiez `ubigi-plans-demo` dans `wp-content/plugins`. Le test active l’extension.
3. Définissez `UPD_WP_ROOT` avec le chemin absolu de WordPress.
4. Lancez `php tests/integration.php` depuis la racine du dépôt.
5. Lancez `php tests/uninstall.php` pour vérifier la conservation et la suppression explicite. Ce test supprime les données de démonstration, vérifie les pages étrangères puis recrée les exemples.

Un échec déclenche une exception et un code de sortie non nul. Les tests vérifient l’initialisation sans doublons, l’API, les paramètres, les permissions, la confidentialité, le cache et les intégrations simulées. La CI utilise une base éphémère. Les requêtes externes sont interceptées par `pre_http_request` ; aucun appel IA payant ne doit être consommé. Les identifiants techniques des scénarios restent stables pour faciliter le diagnostic.
