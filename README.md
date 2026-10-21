# Ubigi Plans Demo

Extension WordPress autonome de présentation et de gestion de forfaits de données de voyage **fictifs**. Ce projet pédagogique est indépendant d’Ubigi et de Transatel. Il ne vend aucun forfait et ne fournit aucune connexion eSIM réelle.

## Installation

1. Prérequis : WordPress 6.5 ou ultérieur, PHP 8.1 ou ultérieur et une base compatible avec WordPress. Activez séparément sur chaque site ; l’activation réseau n’est pas prise en charge.
2. Ouvrez **Extensions → Ajouter une extension → Téléverser une extension**.
3. Sélectionnez **`dist/ubigi-plans-demo.zip`**, puis installez et activez. L’archive des sources complète n’est pas une archive d’installation WordPress.
4. Ouvrez **Ubigi Demo**, puis les liens vers les pages des forfaits et des destinations.
5. Pour un autre emplacement, insérez `[ubigi_plans]` ou `[ubigi_countries]` dans une page. Ajoutez les pages au menu de votre thème si nécessaire.

La première activation crée 12 forfaits, 6 destinations et 2 pages. Elle ne modifie ni la page d’accueil, ni le thème, ni les menus. Une réactivation conserve les données sans dupliquer les exemples. Si les chemins `ubigi-plans` ou `ubigi-countries` existent déjà, les pages sont préservées : insérez le shortcode dans ces pages ou créez une nouvelle page. Les pages utilisent le thème actif ; les styles du catalogue sont limités à son conteneur.

## Fonctionnalités

- Gestion du titre, de la description, du statut, des révisions, du prix, de la devise, du volume, de la durée et du type de forfait.
- Taxonomie hiérarchique des pays et régions, avec des slugs tels que `jp`, `eu` et `world`.
- Recherche dans les titres, descriptions et destinations ; filtres par destination, prix, volume et durée ; pagination.
- Pages de détail natives, liens vers les destinations et présentation adaptative.
- API REST publique en lecture seule excluant les brouillons et les contenus protégés par mot de passe.
- Paramètres d’affichage et de cache, tableau de bord, journal des 100 derniers événements et état du système.
- Assistant IA proposant cinq tâches éditoriales ; validation et copie manuelles des brouillons.
- Webhook n8n facultatif, signé avec HMAC-SHA256 et transmis par WP-Cron.

La devise par défaut s’applique aux nouveaux forfaits uniquement. Chaque forfait conserve sa devise. Aucun taux de change n’est calculé : le filtre compare les montants numériques affichés dans leurs devises respectives.

Choisissez **Français** dans **Réglages → Général → Langue du site** et, pour l’administration, dans la langue du profil si elle diffère. Les identifiants techniques et les chaînes sources de traduction restent stables. Les titres et descriptions des forfaits et les noms des destinations sont des contenus modifiables : ils ne sont pas réécrits automatiquement lors d’un changement de langue.

Pour régénérer les traductions : `python scripts/translations.py`. Pour une autre langue, traduisez le modèle POT ; les fichiers JavaScript peuvent également être produits avec `wp i18n make-json`.

## Intégration IA

Dans **Paramètres et API**, renseignez une clé OpenAI et un modèle accessible à votre compte prenant en charge l’API Responses, puis activez l’IA. Le modèle par défaut `gpt-4.1-mini` est modifiable. Un compte fournisseur et un service actif sont nécessaires ; les appels peuvent être facturés.

Dans **Assistant IA**, sélectionnez un forfait, une tâche et la langue souhaitée. Les tâches sont la description, la suggestion d’un titre et d’une méta-description, le résumé, la révision et la reformulation. Le résultat est un texte à relire : il ne modifie ni ne publie le forfait et ne renseigne aucun champ SEO automatiquement.

Définissez de préférence les secrets dans `wp-config.php`, hors du dépôt :

```php
define( 'UPD_AI_KEY', 'votre-cle-fournisseur' );
define( 'UPD_WEBHOOK_SECRET', 'un-secret-partage-long-et-aleatoire' );
```

Les constantes sont prioritaires sur les options enregistrées. Un champ vide conserve le secret ; l’option de suppression efface uniquement la valeur stockée en base. Ces valeurs ne sont pas chiffrées : ne publiez jamais la base ni `wp-config.php` sur GitHub. Le navigateur ne reçoit ni la clé ni l’erreur brute du fournisseur. La génération est limitée à une demande par administrateur et par minute. Le clic de génération transmet les données du forfait sélectionné à OpenAI avec `store:false`.

## Intégration n8n

Renseignez une URL publique de webhook de production en HTTPS et un secret partagé, puis activez l’envoi. Une modification programme un événement après 10 secondes. Les modifications rapprochées sont regroupées en un événement contenant l’état le plus récent. Les brouillons sont également transmis lorsque l’intégration est activée. Ne placez aucun secret dans les descriptions.

```json
{"event":"plan.updated","event_id":"uuid","plan":{"id":123,"name":"Japon 10 Go","gb":10,"days":30,"price":18,"currency":"EUR","type":"local"}}
```

La charge complète comprend aussi le statut, la description, les destinations, les dates et le lien. L’en-tête `X-Ubigi-Signature` contient la représentation hexadécimale de `HMAC-SHA256(corps_brut, secret_partage)`. Activez l’accès au corps brut du nœud Webhook. Vérifiez les octets originaux avec une comparaison en temps constant avant le traitement ; ne calculez pas la signature sur du JSON réencodé. Exemple : [docs/n8n-verification.js](docs/n8n-verification.js). Renvoyez HTTP 2xx en cas de succès. Utilisez `event_id` pour éviter les doublons lors d’une relance manuelle.

Aucun point d’entrée public ne réécrit les contenus et aucune publication n’est automatique. Organisez la validation dans votre workflow. Chaque événement fait l’objet d’une seule tentative ; le résultat est journalisé. Après correction d’un échec, modifiez le forfait pour déclencher un nouvel envoi. Si WP-Cron est désactivé, programmez `wp cron event run --due-now` sur le serveur. n8n est un service externe facultatif, pas un serveur inclus dans PHP.

## API REST

Les chemins sont relatifs au site. Les permaliens simples sont pris en charge avec `?rest_route=/ubigi/v1/plans`.

| Méthode et chemin | Fonction |
| --- | --- |
| `GET /wp-json/ubigi/v1/plans` | Catalogue publié : `items,total,pages,page` |
| `GET /wp-json/ubigi/v1/plans/123` | Détail ou erreur 404 |
| `GET /wp-json/ubigi/v1/countries` | Destinations et nombre de forfaits |
| `GET /wp-json/ubigi/v1/health` | `status:ok,demo:true`, sans information sensible du serveur |
| `POST /wp-json/ubigi/v1/ai` | Brouillon réservé aux administrateurs |

Exemple : `/wp-json/ubigi/v1/plans?country=jp&min_price=0&max_price=30&min_gb=3&min_days=7&page=1&per_page=9`.

`country` et `search` acceptent au plus 100 octets ; `page` est un entier de 1 à 100000 ; `per_page`, de 1 à 50 ; les limites de prix et `min_gb` vont de 0 à 100000 ; `min_days` est un entier de 1 à 3650. Une entrée invalide produit une erreur 400. Une recherche vide retourne une liste vide avec HTTP 200.

Pour l’IA, 401/403 signalent un défaut d’autorisation, 429 une limitation et 502 un échec fournisseur. Corps POST : `{"id":123,"task":"description","language":"French"}`. Authentification par cookie avec `X-WP-Nonce`, ou mot de passe d’application WordPress sur HTTPS. L’édition passe par l’éditeur WordPress et ses capacités natives ; aucune écriture anonyme n’est autorisée.

Le cache est configurable de 0 à 3600 secondes. Sa génération change à la modification des contenus, métadonnées, destinations ou paramètres ; les anciennes entrées expirent automatiquement. La lecture publique n’est pas limitée par IP dans l’extension : pour un site très sollicité, configurez une limitation côté serveur ou CDN, notamment pour la recherche partielle.

## Maintenance et sauvegarde

Sauvegardez la base et `wp-content` avant la mise à jour, puis remplacez l’extension depuis WordPress. Les exemples ne sont pas recréés lors d’une mise à jour normale. Pour restaurer, utilisez ensemble la base et les fichiers d’un même point de sauvegarde.

La désinstallation conserve par défaut les contenus et paramètres, mais retire les secrets enregistrés et les tâches planifiées. L’option de suppression **efface définitivement tous les forfaits, destinations et pages créées par l’extension**, y compris leurs modifications ultérieures. La désactivation arrête les tâches tout en conservant les données et secrets. Les constantes de `wp-config.php` restent sous la responsabilité de l’administrateur.

## GitHub et construction

Publiez les sources en excluant `.qa`, `dist` et les secrets, conformément à `.gitignore`. Joignez le ZIP d’installation à une version GitHub Release. Aucun outil de construction, Composer ou Node.js n’est nécessaire sur l’hébergement.

Sous Windows, créez le ZIP d’installation avec :

```powershell
powershell -ExecutionPolicy Bypass -File scripts/build.ps1
```

Pour produire les deux archives et leurs empreintes SHA-256 : `python scripts/package.py`. Python est uniquement un outil de développement. Installez les outils de contrôle avec `composer install`, puis lancez `composer lint`. Contrôlez la syntaxe avec `php -l` et `node --check`.

Consultez [les tests](tests/README.md), [le rapport de validation](docs/VALIDATION.md) et [la couverture des exigences](docs/REQUIREMENTS.md). La CI se trouve dans `.github/workflows/ci.yml`.

## Architecture

`UPD_Plugin` gère le cycle de vie, l’affichage, le cache et les envois différés ; `UPD_API`, les contrats de lecture et de génération ; `UPD_Admin`, les écrans d’administration et la validation. Les données utilisent les types de contenus, taxonomies, métadonnées et options natifs. Aucune table dédiée, React ou WooCommerce n’est nécessaire. Les détails utilisent les modèles du thème et les connexions externes sont désactivées par défaut.

Les protections comprennent les capacités natives, nonces, échappements contextuels, requêtes SQL préparées, validations REST et refus des webhooks locaux ou non HTTPS. Les webhooks ne suivent pas les redirections et leurs charges utiles ne sont pas journalisées. Testez votre hébergement, votre thème et vos autres extensions avant la production.

Licence GPL-2.0-or-later. Les marques appartiennent à leurs titulaires respectifs. Le texte juridique original de la licence est conservé pour éviter toute ambiguïté.
