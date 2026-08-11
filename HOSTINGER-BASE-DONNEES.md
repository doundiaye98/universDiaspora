# Base de données Hostinger — Univers Diaspora

Compte Hostinger : préfixe **`u528552725_`**

## 1. Créer la base dans hPanel

**Sites web → Bases de données → Créer**

| Champ hPanel | Saisir (suffixe) | Nom complet |
|--------------|------------------|-------------|
| Nom de la base | `udiaspora` | `u528552725_udiaspora` |
| Utilisateur MySQL | `ud` (suffixe court) | `u528552725_ud` |
| Mot de passe | (votre choix, notez-le) | — |

> Ne saisir que le **suffixe** dans hPanel (`ud`), pas le préfixe compte. Sinon Hostinger crée `u528552725_u528552725_ud` (double préfixe) — c’est le cas actuel du site : utiliser alors le nom **complet** affiché dans hPanel.
>
> Ne pas utiliser une adresse e-mail comme nom d'utilisateur MySQL.

## 2. Fichier de configuration

Sur le serveur : `config/config.local.php` (déjà préparé en local, à uploader).

```php
'db' => [
    'host' => 'localhost',
    'port' => 3306,
    'name' => 'u528552725_udiaspora',
    // Copier le nom EXACT affiché dans hPanel (ex. u528552725_u528552725_ud si double préfixe)
    'user' => 'u528552725_u528552725_ud',
    'pass' => 'LE_MOT_DE_PASSE_CHOISI_DANS_HPANEL',
    'charset' => 'utf8mb4',
],
```

## 3. Créer les tables (2 méthodes)

### Méthode A — Automatique (recommandée)

1. Uploadez le site + `config/config.local.php`.
2. Ouvrez : `https://mediumspringgreen-hare-515139.hostingersite.com/`
3. Le PHP crée toutes les tables et remplit les services / équipe / témoignages.

### Méthode B — Import phpMyAdmin

1. hPanel → **phpMyAdmin** → cliquez sur `u528552725_udiaspora` à gauche.
2. Onglet **Importer** → fichier `sql/schema.hostinger.sql` → **Exécuter**.
3. Ouvrez le site une fois pour le compte admin et les données initiales.

## 4. Vérification

Dans phpMyAdmin, vous devez voir **11 tables** :

- `contact_messages`, `appointments`, `admin_users`, `admin_login_attempts`
- `services`, `service_bullets`, `announcements`, `job_applications`
- `team_members`, `ai_conversations`, `testimonials`

Admin : `https://universdiaspora.com/?page=admin-login` — identifiants dans `config/config.local.php` → `admin`.

Si la connexion échoue (ancien compte en base) : uploadez `reset_admin.php`, ouvrez-le une fois dans le navigateur, puis **supprimez-le** immédiatement du serveur.

## 5. En cas d'erreur

| Erreur | Cause probable |
|--------|----------------|
| Access denied | Mauvais user/pass dans `config.local.php` |
| Unknown database | Base pas créée dans hPanel ou mauvais nom |
| #1044 CREATE DATABASE | Normal sur Hostinger — utiliser méthode A ou B, pas `schema.sql` local |

Logs : `storage/logs/php-errors.log` sur le serveur.
