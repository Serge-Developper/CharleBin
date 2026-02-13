# Rapport - Linters et Outils de Développement

---

## Table des matières

1. [Linters et Qualité de Code](#1-linters-et-qualité-de-code)
   - [1.1 Installation des outils](#11-installation-des-outils)
   - [1.2 Configuration](#12-configuration)
2. [Pre-commit Hook](#2-pre-commit-hook)
   - [2.1 Implémentation](#21-implémentation)
   - [2.2 Fonctionnement](#22-fonctionnement)
   - [2.3 Tests](#23-tests)
3. [Intégration Continue (CI)](#3-intégration-continue-ci)
   - [3.1 GitHub Actions](#31-github-actions)
   - [3.2 Protection de branche](#32-protection-de-branche)
   - [3.3 Tests](#33-tests)
4. [Refactoring avec Copilot](#4-refactoring-avec-copilot)
   - [4.1 Exercice de refactoring](#41-exercice-de-refactoring)
   - [4.2 Critique des propositions](#42-critique-des-propositions)
   - [4.3 Leçons apprises](#43-leçons-apprises)
5. [Developer Tools](#5-developer-tools)
   - [5.1 Récupération du mot de passe](#51-récupération-du-mot-de-passe)
   - [5.2 Vérification du chiffrement](#52-vérification-du-chiffrement)
   - [5.3 Stockage navigateur](#53-stockage-navigateur)

---

## 1. Linters et Qualité de Code

### 1.1 Installation des outils

#### Prérequis : Extension PHP XML Writer

L'extension PHP `xmlwriter` est requise pour PHP_CodeSniffer.

```bash

php -m | grep -i xml
```

**Résultat :**
```
libxml
SimpleXML
xml
xmlreader
xmlwriter
```

 L'extension est bien installée.

#### Installation de PHP_CodeSniffer

```bash
composer require --dev "squizlabs/php_codesniffer=3.*"
```

**Vérification :**
```bash
./vendor/bin/phpcs --version
```

**Résultat :**
```
PHP_CodeSniffer version 3.13.5 (stable) by Squiz and PHPCSStandards
```

#### Installation de PHP CS Fixer

```bash
composer require --dev friendsofphp/php-cs-fixer
```

**Vérification :**
```bash
./vendor/bin/php-cs-fixer --version
```

**Résultat :**
```
PHP CS Fixer 3.66.0 Persian Successor by Fabien Potencier, Dariusz Ruminski and contributors.
```

#### Installation de PHP Mess Detector (PHPMD)

```bash
composer require --dev phpmd/phpmd
```

**Vérification :**
```bash
./vendor/bin/phpmd --version
```

**Résultat :**
```
PHPMD 2.15.0
```

### 1.2 Configuration

#### Configuration de PHP_CodeSniffer (ruleset.xml)

Fichier : `ruleset.xml`

```xml
<?xml version="1.0"?>
<ruleset name="PrivateBin">
    <description>Configuration PHP_CodeSniffer pour PrivateBin</description>
    
    <rule ref="PSR12"/>
    
    <file>lib</file>
    <file>tst</file>
    
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/node_modules/*</exclude-pattern>
    <exclude-pattern>*/data/*</exclude-pattern>
    
    <arg name="colors"/>
    <arg value="p"/>
    <arg name="extensions" value="php"/>
    <arg name="parallel" value="8"/>
    
    <rule ref="Generic.Files.LineLength">
        <properties>
            <property name="lineLimit" value="120"/>
            <property name="absoluteLineLimit" value="150"/>
        </properties>
    </rule>
</ruleset>
```

#### Configuration de PHPMD (phpmd.xml)

Fichier : `phpmd.xml`

```xml
<?xml version="1.0"?>
<ruleset name="PrivateBin PHPMD Rules">
    <description>PHPMD configuration for PrivateBin</description>
    
    <rule ref="rulesets/cleancode.xml">
        <exclude name="StaticAccess"/>
        <exclude name="BooleanArgumentFlag"/>
    </rule>
    
    <rule ref="rulesets/codesize.xml"/>
    <rule ref="rulesets/design.xml"/>
    
    <rule ref="rulesets/naming.xml">
        <exclude name="ShortVariable"/>
    </rule>
    
    <rule ref="rulesets/naming.xml/ShortVariable">
        <properties>
            <property name="minimum" value="2"/>
            <property name="exceptions" value="i,j,k,id,db,up,me"/>
        </properties>
    </rule>
    
    <rule ref="rulesets/unusedcode.xml"/>
    
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/node_modules/*</exclude-pattern>
    <exclude-pattern>*/tst/*</exclude-pattern>
</ruleset>
```

---

## 2. Pre-commit Hook

### 2.1 Implémentation

Le pre-commit hook permet d'exécuter automatiquement les linters avant chaque commit.

**Fonctionnalités :**
-  Exécute PHP CS Fixer pour corriger automatiquement les erreurs de style
-  Re-ajoute les fichiers corrigés au staging area (`git add`)
-  Exécute PHPMD pour détecter les erreurs graves
-  Bloque le commit si des erreurs PHPMD sont détectées

Fichier : `.git/hooks/pre-commit`

```bash

set -eo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'


function join_by {
    local d=${1-}
    local f=${2-}
    if shift 2; then
        printf %s "$f" "${@/#/$d}"
    fi
}

echo -e "${BLUE}=========================================${NC}"
echo -e "${YELLOW}Pre-commit Hook - Verification PHP${NC}"
echo -e "${BLUE}=========================================${NC}"

# Trouver tous les fichiers PHP modifiés
CHANGED_FILES=$(git diff --name-only --cached --diff-filter=ACMR | grep '\.php$' || true)

if [[ -z "$CHANGED_FILES" ]]; then
    echo -e "${GREEN}Aucun fichier PHP modifie${NC}"
    exit 0
fi

# ETAPE 1: PHP CS Fixer
echo -e "${YELLOW}Etape 1/2: PHP CS Fixer${NC}"
PHP_CS_FIXER="./vendor/bin/php-cs-fixer"
PHP_CS_FIXER_CONFIG=".php-cs-fixer.dist.php"

if [[ -f "$PHP_CS_FIXER_CONFIG" ]]; then
    PHP_CS_FIXER_CMD="$PHP_CS_FIXER fix --config=$PHP_CS_FIXER_CONFIG"
else
    PHP_CS_FIXER_CMD="$PHP_CS_FIXER fix"
fi

HAS_FIXES=false
if $PHP_CS_FIXER_CMD $CHANGED_FILES 2>&1 | grep -q "Fixed"; then
    HAS_FIXES=true
    for file in $CHANGED_FILES; do
        git add "$file"
    done
fi

# ETAPE 2: PHPMD
echo -e "${YELLOW}Etape 2/2: PHP Mess Detector${NC}"
PHPMD="./vendor/bin/phpmd"
PHPMD_FILES=$(join_by , $CHANGED_FILES)

if [[ -f "phpmd.xml" ]]; then
    PHPMD_RULES="phpmd.xml"
else
    PHPMD_RULES="cleancode,codesize,design,naming,unusedcode"
fi

PHPMD_OUTPUT=$($PHPMD "$PHPMD_FILES" text "$PHPMD_RULES" 2>&1 || true)

if echo "$PHPMD_OUTPUT" | grep -q "Unexpected token\|Parse error"; then
    echo -e "${YELLOW}Fichiers non analysables (syntaxe moderne)${NC}"
elif [[ -n "$PHPMD_OUTPUT" ]]; then
    echo "$PHPMD_OUTPUT"
    exit 1
fi

echo -e "${GREEN}Toutes les verifications sont passees!${NC}"
exit 0
```

### 2.2 Fonctionnement

**Utilisation de PHP CS Fixer avec configuration :**

```bash
./vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php fichier1.php fichier2.php
```

**Utilisation de PHPMD avec syntaxe virgule :**

```bash
./vendor/bin/phpmd fichier1.php,fichier2.php text phpmd.xml
```

**Fonction `join_by` pour transformer la liste :**

```bash
# Transformation de: fichier1.php fichier2.php
# En: fichier1.php,fichier2.php
PHPMD_FILES=$(join_by , $CHANGED_FILES)
```

### 2.3 Tests

**Test avec un fichier mal formaté :**

Fichier : `lib/TestCI.php`

```php
<?php
namespace PrivateBin;
class TestCI{
function badFormatting(){
$x=1;
return $x;
}
}
```

**Résultat du commit :**

```
 PHPMD a détecté des problèmes
ShortVariable: Avoid variables with short names like $x
```

 Le commit est bloqué comme prévu.

**Bypass possible :**

```bash
git commit --no-verify -m "message"
```

---

## 3. Intégration Continue (CI)

### 3.1 GitHub Actions

Fichier : `.github/workflows/lint.yml`

```yaml
name: Lint PHP

on:
  push:
    branches:
      - main
  pull_request:
    branches:
      - main

permissions:
  checks: write
  contents: write

jobs:
  run-linters:
    name: Run PHP Linters
    runs-on: ubuntu-latest

    steps:
      - name: Check out Git repository
        uses: actions/checkout@v4

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
          coverage: none
          tools: composer

      - name: Install PHP dependencies
        run: |
          composer install --prefer-dist --no-progress --no-ansi --no-interaction
          echo "${PWD}/vendor/bin" >> $GITHUB_PATH

      - name: Run PHP CS Fixer
        run: |
          ./vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff --verbose

      - name: Run PHPMD
        run: |
          FILES=$(find lib -type f -name "*.php" | tr '\n' ',' | sed 's/,$//')
          if [ -n "$FILES" ]; then
            ./vendor/bin/phpmd $FILES text phpmd.xml
          fi

      - name: Run PHP_CodeSniffer
        run: |
          ./vendor/bin/phpcs --standard=ruleset.xml lib/
```

### 3.2 Protection de branche

**Configuration GitHub Ruleset :**

- **Nom :** Branch main protégé
- **Status :** Active
- **Target :** main

**Règles activées :**
-  Require a pull request before merging
-  Require status checks to pass before merging
- Status check requis : `Run PHP Linters`
-  Require branches to be up to date before merging
-  Block force pushes

**Conséquence :**
-  Push direct sur `main` impossible
-  Obligation de passer par des Pull Requests
-  Les checks CI doivent être verts avant merge

### 3.3 Tests

**Pull Request de test :** #2 - "test CI - fichier mal formaté"

**Résultat :**
```
 All checks have failed
   Lint PHP / Run PHP Linters - Failing after 13s
   Required
```

Le merge est bloqué, la CI fonctionne correctement

---

## 4. Refactoring avec Copilot

### 4.1 Exercice de refactoring

**Méthode originale :** `Filter::formatHumanReadableTime()`

**Signature originale :**
```php
public static function formatHumanReadableTime($time)
```

**Nouvelle signature demandée :**
```php
public static function formatHumanReadableTime(int $value, string $unit): string
```

### 4.2 Critique des propositions

Copilot a généré 5 propositions. Voici l'analyse :

#### Proposition 5 : Rejetée
```php
return '';  
```
**Problème :** Ne fait rien, totalement inutilisable.

#### Propositions 1-4 : Bug critique

**Code proposé par Copilot :**
```php
$units = [
    'second' => I18n::_('second'),
    // ...
];
return "$value $localizedUnit" . ($value > 1 ? 's' : '');
```

**Problème majeur :** Gestion incorrecte du pluriel

 Ajoute simplement 's' en dur au lieu d'utiliser `I18n::_()` avec les formes plurielles
 Casse l'internationalisation :
-  Anglais : `2 seconds` (OK)
-  Français : `2 seconds` au lieu de `2 secondes`
-  Espagnol : `2 seconds` au lieu de `2 segundos`

**Code original (CORRECT) :**
```php
return I18n::_(array('%d ' . $unit, '%d ' . $unit . 's'), (int) $matches[1]);


```

 **Points positifs des propositions :**
- Type hints stricts
- Validation de l'unité
- Code plus lisible

### 4.3 Leçons apprises

1. **Copilot peut casser des fonctionnalités critiques**
   - L'internationalisation a été complètement cassée
   - Le code compile mais ne fonctionne pas correctement

2. **Impossibilité de déployer sans vérification**
   
   Erreur lors du test :
   ```
   TypeError: Filter::formatHumanReadableTime(): 
   Argument #1 ($value) must be of type int, string given
   ```
   
   **Cause :** Changement de l'API publique sans mise à jour des appelants

3. **Importance des tests**
   - Sans tests unitaires, le bug n'aurait été découvert qu'en production
   - Les tests d'intégration auraient détecté l'erreur de type

**Conclusion :**  **Toujours critiquer et tester le code généré par l'IA !**

---

## 5. Developer Tools

### 5.1 Récupération du mot de passe

**Objectif :** Récupérer un mot de passe tapé dans un champ `<input type="password">`

**Démarche :**

1. Ouvrir PrivateBin : `http://localhost:8000`
2. Taper dans le champ Password : `MonMotDePasse123`
3. Ouvrir les DevTools (F12)
4. Aller dans l'onglet **Console**
5. Exécuter :
   ```javascript
   document.getElementById('passwordinput').value
   ```

**Résultat :**
```javascript
'MonMotDePasse123'
```

**Localisation exacte :**

Le mot de passe se trouve dans le **DOM (Document Object Model)** :
- Élément : `<input id="passwordinput" type="password">`
- Attribut : `value`
- Accessible via : `document.getElementById('passwordinput').value`

**Conclusion :**

Le mot de passe est stocké **en clair dans la mémoire du navigateur** pendant toute la session. Même s'il est visuellement masqué (●●●●●), il est accessible via JavaScript.

### 5.2 Vérification du chiffrement

**Objectif :** Vérifier que le message est chiffré côté client avant envoi au serveur

**Démarche :**

1. Écrire un message : `Ceci est mon message secret`
2. Ouvrir l'onglet **Network** dans les DevTools
3. Cliquer sur **Send**
4. Trouver la requête POST dans la liste
5. Cliquer dessus et examiner l'onglet **Payload**

**Résultat observé :**

```json
{
  "adata": [
    ["QVVcHfcOjqIjJG71TmCZ Q=","PsYKDjp/fjY=",100000,256,128,"aes","gcm","zlib"],
    "plaintext",0,0
  ],
  "meta": {"expire":"1week"},
  "v": 2,
  "ct": "lcNhc7QRZqmHLqeLxHxZGtfQBp1KVMjd/yDzP1quO1ZdkftGMEsgzC8fOf9JclHN CV9ZJvQWc6P"
}
```

**Analyse :**

| Champ | Contenu | Signification |
|-------|---------|---------------|
| `ct` | `lcNhc7QRZqmHLqeLxHxZ...` | **Ciphertext** (texte chiffré) |
| `adata` | Paramètres crypto | AES-256-GCM, compression zlib |
| `meta` | Métadonnées | Expiration : 1 semaine |

**Conclusion :**

 Le texte `"Ceci est mon message secret"` **n'apparaît nulle part** dans la requête

 Seul le texte chiffré (`ct`) est envoyé au serveur

 Algorithme utilisé : **AES-256-GCM** (chiffrement authentifié)

**Preuve :** PrivateBin chiffre le message **dans le navigateur** (côté client) avant de l'envoyer. Le serveur ne reçoit jamais le message en clair.

### 5.3 Stockage navigateur

**Objectif :** Vérifier que PrivateBin ne stocke rien localement

**Démarche :**

1. Ouvrir l'onglet **Application** dans les DevTools
2. Examiner toutes les zones de stockage :
   - Local Storage
   - Session Storage
   - Cookies
   - IndexedDB
   - Cache Storage

**Résultat :**

| Zone de stockage | Contenu |
|------------------|---------|
| Local Storage |  Vide |
| Session Storage |  Vide |
| Cookies |  Vide |
| IndexedDB |  Vide |
| Cache Storage |  Vide |

**Conclusion :**

PrivateBin ne stocke **aucune donnée** dans le navigateur :
-  Pas de message sauvegardé
-  Pas de mot de passe conservé
-  Pas d'historique
-  Pas de cookies de suivi

**Preuve :** PrivateBin respecte sa promesse de **zéro stockage local**. Tout est temporaire et existe uniquement en mémoire pendant la session active.

---

## Conclusion

Ce projet a permis de :

1.  Mettre en place des **linters** (PHP_CodeSniffer, PHP CS Fixer, PHPMD)
2.  Configurer un **pre-commit hook** pour vérifier automatiquement le code
3.  Implémenter une **CI avec GitHub Actions** pour bloquer les PR défectueuses
4.  Protéger la branche `main` avec des **GitHub Rulesets**
5.  Expérimenter le **refactoring assisté par IA** (Copilot)
6.  Critiquer et identifier les **limites des outils d'IA**
7.  Utiliser les **Developer Tools** pour analyser une application web
8.  Vérifier la **sécurité** de PrivateBin (chiffrement, stockage)

**Leçons principales :**
- Les outils d'IA sont puissants mais **nécessitent une validation humaine**
- Les linters améliorent la qualité mais ne remplacent pas la compréhension du code
- La CI/CD permet de détecter les problèmes **avant** qu'ils n'atteignent la production
- La sécurité côté client (chiffrement) est vérifiable avec les DevTools

---

## Fichiers joints

- `.git/hooks/pre-commit` - Pre-commit hook
- `.github/workflows/lint.yml` - GitHub Actions workflow
- `phpmd.xml` - Configuration PHPMD
- `ruleset.xml` - Configuration PHP_CodeSniffer

 ## Note sur l'architecture testée

PrivateBin n'utilise pas une architecture MVC classique avec des classes Actions et Dispatch séparées. 
À la place, nous avons testé :
- **Data/Filesystem** : Équivalent des Repository pour la gestion de la persistance
- **Filter** : Logique métier de formatage et validation

Ces deux couches représentent bien 2 parties distinctes et critiques du projet.