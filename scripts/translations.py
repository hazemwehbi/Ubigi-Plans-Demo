"""Génère le catalogue français distribué et le modèle de traduction."""
from pathlib import Path
import json
import re
import struct

root = Path(__file__).resolve().parents[1] / 'ubigi-plans-demo'
out = root / 'languages'
out.mkdir(exist_ok=True)
fr = {
 'Plans': 'Forfaits', 'Plan': 'Forfait', 'Countries & regions': 'Pays et régions',
 'Dashboard': 'Tableau de bord', 'Settings & API': 'Paramètres et API',
 'AI Assistant': 'Assistant IA', 'Logs': 'Journal', 'System Status': 'État du système',
 'API Documentation': 'Documentation API', 'UBIGI · PLANS DEMO': 'UBIGI · FORFAITS DE DÉMONSTRATION',
 'Your next destination. Connected.': 'Votre prochaine destination. Connectée.',
 'Explore international data plans for your next adventure.': 'Découvrez les forfaits de données internationaux pour votre prochain voyage.',
 'Demo catalogue · No real eSIM, payments or connectivity': 'Catalogue de démonstration · Aucun paiement ni connexion eSIM réelle',
 'Search destination': 'Rechercher une destination', 'Where are you travelling?': 'Où partez-vous ?',
 'Country / region': 'Pays ou région', 'All destinations': 'Toutes les destinations',
 'Maximum price': 'Prix maximal', 'Minimum GB': 'Volume minimal en Go',
 'Minimum days': 'Durée minimale en jours', 'Find a plan': 'Trouver un forfait', 'Reset': 'Réinitialiser',
 'Previous': 'Précédent', 'Next': 'Suivant', 'Plan pages': 'Pages du catalogue',
 'Explore destinations': 'Découvrir les destinations', '%d demo plans': '%d forfaits de démonstration',
 'Demo only · No purchase or real connectivity': 'Démonstration uniquement · Aucun achat ni connexion réelle',
 'days': 'jours', 'Loading plans…': 'Chargement des forfaits…',
 'Unable to load plans.': 'Impossible de charger les forfaits.', '%d days validity': 'Validité de %d jours',
 'View plan': 'Voir le forfait',
 '%d demo plans found. Prices are in each plan’s listed currency.': '%d forfaits de démonstration trouvés. Les prix sont exprimés dans la devise de chaque forfait.',
 'No matching plans. Try changing your filters.': 'Aucun forfait correspondant. Modifiez vos filtres.',
 'Page %1$d of %2$d': 'Page %1$d sur %2$d',
 'Request failed. Check your connection and try again.': 'Échec de la requête. Vérifiez votre connexion et réessayez.',
 'Retry': 'Réessayer', 'Plan specifications': 'Caractéristiques du forfait',
 'Data in GB': 'Volume de données en Go', 'Validity in days': 'Validité en jours',
 'Price': 'Prix', 'Currency ISO code': 'Code ISO de la devise',
 'Type: local, regional or global': 'Type : local, régional ou mondial',
 'Training catalogue. No real eSIM connectivity or payments.': 'Catalogue pédagogique. Aucun paiement ni connexion eSIM réelle.',
 'Generate draft': 'Générer un brouillon', 'Task': 'Tâche', 'Language': 'Langue',
 'Generating draft…': 'Génération du brouillon…',
 'Draft ready for review. Nothing has been published.': 'Brouillon prêt à être relu. Rien n’a été publié.',
 'Request failed.': 'Échec de la requête.', 'Plan not found.': 'Forfait introuvable.',
 'Settings saved.': 'Paramètres enregistrés.', 'Event': 'Événement', 'Result': 'Résultat',
 'Configured. Leave blank to retain.': 'Configuré. Laissez le champ vide pour conserver la valeur.',
 'Not configured.': 'Non configuré.', 'Remove stored secret': 'Supprimer le secret enregistré',
 'Enable JavaScript to filter plans. Browse all plans below.': 'Activez JavaScript pour filtrer les forfaits. Consultez tous les forfaits ci-dessous.',
 'Review the draft, then copy approved text to the plan editor. Nothing is published automatically.': 'Relisez le brouillon, puis copiez le texte approuvé dans l’éditeur du forfait. Aucune publication automatique.',
 'AI is disabled or its key is missing.': 'L’IA est désactivée ou sa clé est absente.',
 'AI request failed. Check your key, model and provider account.': 'Échec de la demande IA. Vérifiez la clé, le modèle et le compte fournisseur.',
 'Activate this plugin separately on each site. Network activation is not supported.': 'Activez cette extension séparément sur chaque site. L’activation réseau n’est pas prise en charge.',
 'Assign one or more countries or regions using the destination box. All packages are demo data.': 'Attribuez un ou plusieurs pays ou régions dans le panneau des destinations. Tous les forfaits sont fictifs.',
 'Cache seconds (0–3600)': 'Durée du cache en secondes (0–3600)',
 'Default ISO currency for new plans (no conversion)': 'Devise ISO par défaut des nouveaux forfaits (sans conversion)',
 'Delete ALL plugin plans, destinations and created pages on uninstall': 'Supprimer TOUS les forfaits, destinations et pages créées lors de la désinstallation',
 'Enable AI (sends selected plan content to OpenAI)': 'Activer l’IA (transmet le contenu du forfait sélectionné à OpenAI)',
 'Enable n8n (sends plan content, including drafts, to configured URL)': 'Activer n8n (transmet les forfaits, brouillons compris, à l’URL configurée)',
 'Invalid model identifier.': 'Identifiant de modèle invalide.',
 'Minimum price exceeds maximum.': 'Le prix minimal dépasse le prix maximal.',
 'No draft returned.': 'Aucun brouillon reçu.',
 'Only the last 100 events are retained. Request bodies, URLs, credentials and provider errors are not logged.': 'Seuls les 100 derniers événements sont conservés. Les corps de requête, URL, identifiants et erreurs du fournisseur ne sont pas journalisés.',
 'OpenAI API key': 'Clé API OpenAI', 'OpenAI model': 'Modèle OpenAI',
 'POST /ubigi/v1/ai requires manage_options and cookie authentication with X-WP-Nonce, or a WordPress application password over HTTPS.': 'POST /ubigi/v1/ai nécessite manage_options et une authentification par cookie avec X-WP-Nonce, ou un mot de passe d’application WordPress sur HTTPS.',
 'Plans per page (1–50)': 'Forfaits par page (1–50)',
 'Please wait one minute before generating another draft.': 'Veuillez patienter une minute avant de générer un autre brouillon.',
 'Public GET endpoints; administrative changes use the WordPress editor with capability and nonce checks.': 'Points d’entrée GET publics ; les modifications passent par l’éditeur WordPress avec contrôles de capacité et de nonce.',
 'Published plans: %1$d · Drafts: %2$d · Destinations: %3$d': 'Forfaits publiés : %1$d · Brouillons : %2$d · Destinations : %3$d',
 'Requires WordPress 6.5 and PHP 8.1 or newer.': 'Nécessite WordPress 6.5 et PHP 8.1 ou des versions ultérieures.',
 'Secrets are never sent to the frontend. Prefer UPD_AI_KEY and UPD_WEBHOOK_SECRET constants in wp-config.php; stored options are protected by WordPress access control, not encrypted at rest.': 'Les secrets ne sont jamais transmis au navigateur. Privilégiez les constantes UPD_AI_KEY et UPD_WEBHOOK_SECRET dans wp-config.php ; les options enregistrées sont protégées par les droits WordPress, sans chiffrement en base.',
 'Use Plans to edit packages and Countries & regions to manage destinations. Installation never changes your theme, menu or home page.': 'Utilisez Forfaits pour modifier les offres et Pays et régions pour gérer les destinations. L’installation ne modifie ni le thème, ni les menus, ni l’accueil.',
 'Use a three-letter currency code.': 'Utilisez un code de devise à trois lettres.',
 'Webhook disabled: provide a URL and signing secret.': 'Webhook désactivé : renseignez une URL et un secret de signature.',
 'Webhook must be a public HTTPS URL.': 'Le webhook doit être une URL HTTPS publique.',
 'Webhook signing secret': 'Secret de signature du webhook', 'n8n webhook HTTPS URL': 'URL HTTPS du webhook n8n',
}
messages = set(fr)
for path in root.rglob('*'):
 if path.suffix in ('.php', '.js'):
  text = path.read_text(encoding='utf-8')
  messages.update(re.findall(r"(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*'([^'\n]*)'\s*,\s*'ubigi-plans-demo'", text))
missing = messages - fr.keys()
if missing:
 raise ValueError('Traductions françaises manquantes : ' + repr(sorted(missing)))
header = 'Project-Id-Version: Ubigi Plans Demo 1.0.0\nContent-Type: text/plain; charset=UTF-8\nLanguage: fr_FR\nPlural-Forms: nplurals=2; plural=(n > 1);\n'
quote = lambda value: json.dumps(value, ensure_ascii=False)
for filename, catalog in [('ubigi-plans-demo.pot', {}), ('ubigi-plans-demo-fr_FR.po', fr)]:
 lines = ['msgid ""', 'msgstr ' + quote(header if catalog else header.replace('Language: fr_FR', 'Language: ')), '']
 for msg in sorted(messages):
  lines += ['msgid ' + quote(msg), 'msgstr ' + quote(catalog.get(msg, '')), '']
 (out / filename).write_text('\n'.join(lines), encoding='utf-8')

catalog = {'': header, **fr}
keys = sorted(catalog)
original = [k.encode() for k in keys]
translated = [catalog[k].encode() for k in keys]
offset = 28 + len(keys) * 16
original_table = []; original_data = b''
for value in original:
 original_table.append((len(value), offset + len(original_data)))
 original_data += value + b'\0'
translated_table = []; translated_data = b''
for value in translated:
 translated_table.append((len(value), offset + len(original_data) + len(translated_data)))
 translated_data += value + b'\0'
binary = struct.pack('<7I', 0x950412de, 0, len(keys), 28, 28 + len(keys)*8, 0, 0)
for length, start in original_table + translated_table:
 binary += struct.pack('<2I', length, start)
(out / 'ubigi-plans-demo-fr_FR.mo').write_bytes(binary + original_data + translated_data)
jed = {'generator': 'Ubigi Plans Demo', 'domain': 'messages', 'locale_data': {'messages': {'': {'domain': 'messages', 'lang': 'fr_FR', 'plural-forms': 'nplurals=2; plural=(n > 1);'}, **{k:[v] for k,v in fr.items()}}}}
for handle in ('upd', 'upd-admin'):
 (out / f'ubigi-plans-demo-fr_FR-{handle}.json').write_text(json.dumps(jed, ensure_ascii=False), encoding='utf-8')
print(f'Catalogue français généré : {len(messages)} messages traduits.')
