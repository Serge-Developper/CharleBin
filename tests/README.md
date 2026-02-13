# Tests PHPUnit - PrivateBin

Ce dossier contient les tests unitaires et d'intégration pour le projet PrivateBin.

## Structure

```
tests/
├── Unit/              # Tests unitaires (classes isolées)
│   └── FilterTest.php
└── Integration/       # Tests d'intégration (interactions entre classes)
    └── FilesystemDataTest.php
```

## Tests réalisés

### Partie 1 : Filter (Logique métier) - 4 tests unitaires

**Fichier :** `tests/Unit/FilterTest.php`

1. **testFormatHumanReadableTimeWithValidInputs** : Teste la méthode avec 12 entrées valides différentes (secondes, minutes, heures, jours, mois, années)
2. **testFormatHumanReadableTimeWithAbbreviations** : Teste les abréviations (min, sec)
3. **testFormatHumanReadableTimeThrowsExceptionForInvalidInput** : Vérifie qu'une exception est levée pour entrée invalide
4. **testFormatHumanReadableTimeWithVariousSpacing** : Teste différents espacements dans l'entrée

### Partie 2 : Data/Filesystem (équivalent Repository) - 4 tests d'intégration

**Fichier :** `tests/Integration/FilesystemDataTest.php`

5. **testCreateAndReadPaste** : Teste la création et lecture d'un paste
6. **testDeleteExistingPaste** : Teste la suppression d'un paste existant
7. **testPasteExists** : Vérifie la méthode `exists()` avant et après création
8. **testReadNonExistentPasteReturnsFalse** : Vérifie le comportement avec un paste inexistant

## Exécution des tests

### Tous les tests

```bash
./vendor/bin/phpunit
```

### Tests unitaires uniquement

```bash
./vendor/bin/phpunit --testsuite Unit
```

### Tests d'intégration uniquement

```bash
./vendor/bin/phpunit --testsuite Integration
```

### Avec couverture de code (si xdebug installé)

```bash
./vendor/bin/phpunit --coverage-html coverage/
```

### Test spécifique

```bash
./vendor/bin/phpunit tests/Unit/FilterTest.php
./vendor/bin/phpunit --filter testCreateAndReadPaste
```

## Résultats attendus

```
PHPUnit 9.6.11 by Sebastian Bergmann and contributors.

........                                                            8 / 8 (100%)

Time: 00:00.123, Memory: 10.00 MB

OK (8 tests, 20 assertions)
```

## Configuration

Le fichier `phpunit.xml` à la racine du projet configure :
- Les suites de tests (Unit, Integration)
- Le bootstrap (autoload Composer)
- Les couleurs et verbosité
- La couverture de code (dossier `lib/`)

## Notes

- Les tests d'intégration utilisent un répertoire temporaire qui est nettoyé après chaque test
- Les tests unitaires de `Filter` nécessitent l'initialisation de `I18n`
- Aucune base de données réelle n'est nécessaire (utilisation du filesystem)
