# Contribuer à CharleBin

Merci de votre intérêt pour CharleBin. Ce guide explique le workflow de contribution, les règles de style et les validations à effectuer avant d’ouvrir une Pull Request.

## Pré-requis
- PHP 8.0+ installé localement.
- Dépendances PHP installées via `php bin\composer install`.
- (Optionnel) Node.js si vous exécutez les tests JS.

## Mise en place (dev)
```powershell
cd C:\Users\worlo\PrivateBin
php bin\composer install
copy cfg\conf.sample.php cfg\conf.php
php -S localhost:8080
```

Adaptez `cfg\conf.php` (nom, basepath, modèle de stockage). Par défaut, le backend est Filesystem sous `data`.

## Règles de style & linters
- PHP:
  - Respect du standard PSR‑4 (namespaces `PrivateBin\`, autoload).
  - Style recommandé: PSR‑12 (indentation 4 espaces, accolades et docblocks cohérents).
  - Vérifier la syntaxe PHP avant commit:

```powershell
php -l c:\Users\worlo\PrivateBin\lib\Controller.php
```

- JavaScript:
  - Conserver la structure actuelle (modules autonomes, aucune dépendance non nécessaire).
  - (Optionnel) Ajouter ESLint dans une PR si vous introduisez du JS complexe; inclure la configuration et scripts npm.

- Frontend:
  - Ne pas casser les en‑têtes de sécurité (CSP). Si vous ajoutez des scripts tiers, justifiez et adaptez `main.cspheader`.

## Tests
- Les tests PHP doivent passer:

```powershell
cd C:\Users\worlo\PrivateBin
.\vendor\bin\phpunit tst
```

- Si vous modifiez `js/`, fournissez des tests (mocha) si pertinent:

```powershell
cd C:\Users\worlo\PrivateBin\js
npm install
npm test
```

- Couverture:
  - Les rapports sont écrits sous `log/` (voir [phpunit.xml](file:///c:/Users/worlo/PrivateBin/phpunit.xml)). Créez `log/` si nécessaire.

## Workflow Git / Pull Requests
- Forkez le dépôt et créez une branche dédiée:
  - `feature/xxx` pour nouvelles fonctionnalités
  - `fix/xxx` pour corrections
  - `chore/xxx` pour tâches/outillage
- Commits clairs (idéalement style “Conventional Commits”).
- Avant d’ouvrir la PR:
  - Vérifiez la syntaxe PHP et le style.
  - Exécutez `.\vendor\bin\phpunit tst` et assurez-vous que tous les tests passent.
  - Validez que l’UI fonctionne localement (`php -S localhost:8080`).
  - Mettez à jour la documentation (README/sections pertinentes) si votre changement affecte l’usage ou la configuration.
- Ouvrez la PR avec:
  - Description du changement et de la motivation.
  - Impacts sur la config (ex. `cfg\conf.php`), la sécurité (CSP, YOURLS), ou les backends de stockage.
  - Instructions de migration si vous modifiez le modèle de données (lib/Data ou lib/Model).

## Principes d’architecture à respecter
- Zéro‑connaissance: aucun déchiffrement côté serveur; toute logique doit préserver ce principe.
- Isoler la persistance (lib/Data, lib/Persistence) et suivre les abstractions existantes.
- Ne pas introduire de dépendances PHP/JS non nécessaires; réutiliser les utilitaires déjà présents.
- Sécurité par défaut: conserver/reforcer les en‑têtes et protections.

## Revue & fusion
- Les PRs sont revues pour:
  - Respect du style et des abstractions.
  - Tests suffisants.
  - Absence de régressions de sécurité (CSP, CORS, HTTP warnings).
- Après approbation, squash & merge ou merge commit suivant la politique du dépôt.

## Licence
En contribuant, vous acceptez la licence zlib/libpng du projet.
