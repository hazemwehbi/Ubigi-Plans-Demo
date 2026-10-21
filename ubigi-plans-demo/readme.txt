=== Ubigi Plans Demo ===
Contributors: demo-project
Tags: catalogue, travel, demo, rest-api
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Catalogue pédagogique de forfaits de voyage. Aucun paiement ni connexion eSIM réelle. Aucune affiliation avec Ubigi ou Transatel.

== Description ==
Une seule extension fournit les forfaits fictifs, destinations, filtres, détails, API REST, brouillons IA facultatifs et webhooks n8n signés. Les fonctions principales fonctionnent sans compte externe ni outil de construction. L’installation comprend 12 forfaits et 6 destinations.

La documentation et les traductions distribuées sont en français. Sélectionnez Français comme langue WordPress pour utiliser les traductions. Les contenus éditoriaux restent modifiables.

L’API Responses d’OpenAI, https://api.openai.com/v1/responses, est appelée uniquement après activation explicite et à la demande d’un administrateur. Les données du forfait sélectionné sont transmises au fournisseur. Politiques : https://openai.com/policies/privacy-policy/ et https://openai.com/policies/terms-of-use/. Après activation de n8n, les données, brouillons compris, sont envoyées à l’URL HTTPS configurée. L’extension ne transmet aucune télémétrie.

== Installation ==
Dans Extensions > Ajouter une extension > Téléverser une extension, sélectionnez ubigi-plans-demo.zip, puis installez et activez. Ouvrez Ubigi Demo pour visiter les pages créées. Les shortcodes [ubigi_plans] et [ubigi_countries] permettent un autre emplacement. Activez séparément sur chaque site ; l’activation réseau est exclue.

== Questions fréquentes ==
= Les forfaits sont-ils réels ? =
Non, tous les forfaits sont des exemples.
= Faut-il des clés API ? =
Non pour les fonctions principales. L’IA facultative nécessite une clé ; n8n nécessite votre propre service, une URL et un secret de signature.
= La désinstallation efface-t-elle les contenus ? =
Non, sauf activation explicite de la suppression des données dans les paramètres. Les secrets enregistrés et tâches planifiées sont retirés dans tous les cas.

== Historique ==
= 1.0.0 =
Version de démonstration avec documentation et catalogue français.
