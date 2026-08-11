# Corriger la 404 Hostinger (« simple accident »)

Ce message est la **page par défaut d’Hostinger**, pas le site Univers Diaspora.
→ Le serveur ne trouve **pas** `index.php` à la racine du site.

## Étape 1 — Ouvrir le diagnostic

https://mediumspringgreen-hare-515139.hostingersite.com/health.php

- **Si health.php affiche aussi la 404 Hostinger** : les fichiers ne sont pas au bon endroit (étape 2).
- **Si health.php s’affiche** : lisez la liste des fichiers OK / MANQUANT.

## Étape 2 — Structure correcte dans le gestionnaire de fichiers

Ouvrez **public_html/**. Vous devez voir **en direct** :

```
index.php
.htaccess
health.php
app/
config/
public/
pages/
...
```

### Mauvais (très fréquent)

```
public_html/
  universDiaspora/     ← À SUPPRIMER après déplacement
    index.php
    app/
    ...
```

**Correction :** sélectionnez tout le contenu de `universDiaspora/`, **Déplacer** vers `public_html/`, puis supprimez le dossier vide.

## Étape 3 — Supprimer la page par défaut Hostinger

Dans `public_html/`, supprimez si présents :

- `default.php`
- `index.html` (celui d’Hostinger, pas le nôtre s’il existe)

## Étape 4 — Fichiers obligatoires

Uploadez ou vérifiez :

- `public_html/config/config.local.php` (base `u528552725_udiaspora`)
- Tables importées via `sql/schema.hostinger.sql` dans phpMyAdmin

## Étape 5 — Tests dans l’ordre

1. https://mediumspringgreen-hare-515139.hostingersite.com/health.php
2. https://mediumspringgreen-hare-515139.hostingersite.com/index.php
3. https://mediumspringgreen-hare-515139.hostingersite.com/

## Étape 6 — Site dans un sous-dossier (temporaire)

Si vous ne pouvez pas déplacer les fichiers tout de suite, testez :

https://mediumspringgreen-hare-515139.hostingersite.com/universDiaspora/index.php

Si ça marche, il faut **déplacer** le contenu vers `public_html/` (solution propre).

## Après succès

Supprimez `health.php` du serveur.
