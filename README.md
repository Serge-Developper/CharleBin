# CharleBin

CharleBin est une instance/fork de PrivateBin, un pastebin zéro‑connaissance. Tout le contenu est chiffré côté client (navigateur) en AES‑256‑GCM avant l’envoi, ce qui signifie que le serveur n’a aucune connaissance des données. CharleBin vise la simplicité, la sécurité et l’auto‑hébergement.

## Fonctionnalités
- Chiffrement côté client AES‑256‑GCM (Zéro‑connaissance).
- Expiration configurable des pastes (5 min → 1 an, jamais).
- Discussions/commentaires, burn-after-reading, mot de passe.
- Formatage: texte brut, syntax highlighting, Markdown.
- Téléversement de fichier (optionnel).
- Icônes (identicon/jdenticon/vizhash), QR code, intégration YOURLS pour short URLs.
- Backends de stockage: système de fichiers (par défaut), base de données, Google Cloud Storage, S3.
- Limitation de trafic et purge des pastes expirés.
- Internationalisation (i18n) et manifeste PWA.
- En-têtes de sécurité (CSP, X-Frame-Options, Referrer-Policy, etc.).

## Pile technique
- PHP >= 8.0 et autoload PSR‑4 ([composer.json](file:///c:/Users/worlo/PrivateBin/composer.json)).
- Point d’entrée HTTP ([index.php](file:///c:/Users/worlo/PrivateBin/index.php)).
- Contrôleur et routage applicatif ([Controller.php](file:///c:/Users/worlo/PrivateBin/lib/Controller.php)).
- Configuration centralisée ([Configuration.php](file:///c:/Users/worlo/PrivateBin/lib/Configuration.php)).
- Templates PHP ([tpl](file:///c:/Users/worlo/PrivateBin/tpl)) et ressources frontend ([js](file:///c:/Users/worlo/PrivateBin/js), [css](file:///c:/Users/worlo/PrivateBin/css), [i18n](file:///c:/Users/worlo/PrivateBin/i18n)).
- Tests unitaires PHP ([phpunit.xml](file:///c:/Users/worlo/PrivateBin/phpunit.xml), [tst](file:///c:/Users/worlo/PrivateBin/tst)).

## Arborescence
- bin — utilitaires (composer, migration, administration).
- cfg — configuration (ex. conf.sample.php).
- css — styles (Bootstrap, thèmes prettify).
- i18n — traductions JSON (fr, en, de, etc.).
- img — icônes et logos.
- js — scripts frontend et assets (prettify, showdown, zlib.wasm).
- lib — logique serveur PHP (models, persistence, data, controller, view).
- tpl — templates de pages.
- tst — tests unitaires PHP.

## Prérequis
- PHP 8.0+ avec extensions courantes (openssl, json).
- Accès CLI à PHP pour composer et le serveur intégré.
- (Optionnel) Node.js pour exécuter les tests JS (mocha) du dossier js/test.

## Installation
- Installer les dépendances PHP.

```powershell
cd C:\Users\worlo\PrivateBin
php bin\composer install
```

- Configurer l’application:
  - Copier le fichier d’exemple et adapter vos paramètres (nom, basepath, backend de stockage, YOURLS…).

```powershell
copy cfg\conf.sample.php cfg\conf.php
```

Points clés de configuration:
- Nom affiché: main.name (par défaut “CharleBin” via [Configuration.php](file:///c:/Users/worlo/PrivateBin/lib/Configuration.php#L37-L61)).
- Chemin de base public: main.basepath (URL complète terminée par /).
- Backend de stockage: [model] et [model_options] (Filesystem par défaut).
- Sécurité: main.cspheader, httpwarning, compression.

## Démarrage (dev)
- Lancer le serveur web intégré de PHP:

```powershell
cd C:\Users\worlo\PrivateBin
php -S localhost:8080
```

- Ouvrir http://localhost:8080

Si votre code n’est pas dans le document root, ajustez la constante PATH dans [index.php](file:///c:/Users/worlo/PrivateBin/index.php#L13-L18).

## API (routage)
Le contrôleur détermine l’opération via la méthode HTTP et les paramètres ([Controller.php](file:///c:/Users/worlo/PrivateBin/lib/Controller.php#L123-L152), [Request.php](file:///c:/Users/worlo/PrivateBin/lib/Request.php#L103-L161)).

- Créer un paste: POST / avec Content-Type: application/json (FormatV2: v, ct, adata, meta…). Le chiffrement doit être fait côté client.
- Lire un paste: GET /?pasteid=<id> avec Accept: application/json.
- Supprimer un paste: requête contenant pasteid et deletetoken (DELETE ou GET), retour JSON.
- JSON-LD: GET /?jsonld=<type> pour modèles JSON-LD (paste/comment/meta).
- YOURLS proxy: GET /shortenviayourls?link=<url> si configuré.

En mode API JSON, les en‑têtes de CORS sont définis automatiquement (Access-Control-Allow-*).

## Tests
- Tests PHP:

```powershell
cd C:\Users\worlo\PrivateBin
.\vendor\bin\phpunit tst
```

Couverture et rapports: voir [phpunit.xml](file:///c:/Users/worlo/PrivateBin/phpunit.xml#L3-L21) (log/coverage-clover.xml, log/php-coverage-report).

- (Optionnel) Tests JS:

```powershell
cd C:\Users\worlo\PrivateBin\js
npm install
npm test
```

## Sécurité
- L’application applique des en-têtes (CSP, X-Frame-Options, etc.) et désactive le cache en sortie HTML.
- Utiliser HTTPS; conserver httpwarning = true.
- Ne jamais exposer la clé d’API YOURLS côté client; utilisez le proxy serveur.

## Licence et crédits
- Licence: zlib/libpng (voir composer.json).
- Projet original: PrivateBin — https://privatebin.info/

## Cypress 

 - J'ai crée un nouveau test via Cypress Studio puis j'ai crée un paste qui enregistre en temps réel une chaine de caractères et un mdp puis si on relance un test, Cypress va relancer tout le test qu'on a crée précédemment et indiquera si un problème est survenu et a qu'elle ligne précise dans le fichier js respectif du test
 - Voici l'URL du test avec comme mot de passe test : http://localhost:8000/?6a45f850077ed46e#86aVe5dKPqFj25t86fLw5Mu9ZyauTPtKb31RSyMNi2nx