# Déploiement sur Hostinger — Univers Diaspora

Guide pas-à-pas pour mettre le site en ligne sur un hébergement Hostinger
(Premium, Business, Cloud Startup ou Cloud Pro). PHP 8.1+ et MySQL 5.7+/MariaDB 10.4+.

---

## 1. Prérequis côté Hostinger (hPanel)

1. **Domaine** rattaché au compte (`universdiaspora.com`).
2. **SSL Let's Encrypt** : `hPanel > Sécurité > SSL > Installer` (gratuit, ~1 min).
3. **Version PHP** : `hPanel > Avancé > Configuration PHP > Version PHP` → **8.1 ou 8.2**.
4. **Extensions PHP** activées (cocher si nécessaire) :
   `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, `intl`, `json`, `session`, `xml`.
5. **Bases de données MySQL** :
   `hPanel > Bases de données > MySQL` → créer une base.
   Notez les identifiants (préfixés `u123456789_…`) :
   - **Hôte** : `localhost`
   - **Nom** : `u123456789_universdiaspora`
   - **Utilisateur** : `u123456789_ud`
   - **Mot de passe** : (généré par hPanel)
6. **Boîte e-mail professionnelle** (recommandé pour le SMTP) :
   `hPanel > E-mails > Comptes e-mail` → créer `no-reply@universdiaspora.com` et `contact@universdiaspora.com`.
7. **Cron Jobs** (facultatif, pour la rotation de logs) :
   `hPanel > Avancé > Tâches Cron` (à configurer plus tard si besoin).

---

## 2. Préparer la configuration locale

Avant le transfert :

```bash
cp config/config.hostinger.example.php config/config.local.php
```

Éditer `config/config.local.php` :

- `app.base_url` : `https://universdiaspora.com` (sans slash final).
- `app.env` : `production`.
- `db.name`, `db.user`, `db.pass` : valeurs hPanel.
- `admin.username` / `admin.password` : un compte fort (≥ 16 car).
- `mail.smtp` : informations SMTP de votre boîte Hostinger
  (`smtp.hostinger.com:465 SSL`).
- `legal.publisher` : SIRET, RCS, adresse, dirigeant (obligatoire en France).
- `ai_assistant.enabled` : `false` au premier déploiement, puis `true` une fois la clé OpenAI ajoutée.

> Ce fichier ne doit JAMAIS être commité (`.gitignore` le gère déjà).

---

## 3. Transférer les fichiers (deux options)

### Option A — File Manager (le plus simple)

1. `hPanel > Fichiers > Gestionnaire de fichiers` → ouvrir `public_html/`.
2. **Vider** `public_html/` (sauvegarder ce qui s'y trouve avant).
3. Côté local, créer un **ZIP du projet** en EXCLUANT :
   - `.git/`, `.idea/`, `.vscode/`, `terminals/`, `agent-transcripts/`
   - `node_modules/`, `vendor/` (s'ils existent)
   - `storage/logs/*` et `storage/candidatures/*`
   - `config/config.local.php` (à transférer séparément, voir étape 4)
4. **Vérifiez impérativement** que le ZIP contient tout le dossier **`public/assets/`** :
   - `public/assets/css/style.css` (design du site)
   - `public/assets/img/` (logo, icônes, images des services)
   - `public/assets/js/` (scripts)
   Sans ce dossier, le site s'affiche en texte brut sans mise en page.
5. Uploader le ZIP dans `public_html/` puis cliquer sur **Extraire**.
6. Supprimer le ZIP après extraction.

### Option B — Git (plus propre, sur les plans Business+)

1. Activer SSH : `hPanel > Avancé > Accès SSH`.
2. Se connecter en SSH :
   ```bash
   ssh -p 65002 u123456789@<adresse-ssh-hostinger>
   ```
3. Cloner le dépôt :
   ```bash
   cd ~/domains/universdiaspora.com/public_html
   git clone https://github.com/<utilisateur>/universdiaspora.git .
   ```
4. Pour les mises à jour futures : `git pull --ff-only` côté serveur.

---

## 4. Importer le schéma de base + uploader la config

1. Le schéma se crée **automatiquement** au premier chargement du site (via `app/db.php`).
   **Ne pas** importer `sql/schema.sql` dans phpMyAdmin sur Hostinger : il contient
   `CREATE DATABASE univers_diaspora`, ce qui provoque l’erreur **#1044 Accès refusé**
   (sur l’hébergement partagé, seul hPanel peut créer une base ; la vôtre s’appelle
   `u528552725_udiaspora`, pas `univers_diaspora`).
   Si vous ouvrez phpMyAdmin : sélectionnez **`u528552725_udiaspora`** à gauche, puis
   **Importer** le fichier **`sql/schema.hostinger.sql`** (pas `schema.sql`).
   Ensuite ouvrez l’URL du site une fois pour remplir les services / admin automatiquement.
2. Uploader `config/config.local.php` dans `public_html/config/` via le File Manager
   (à éditer directement en ligne si plus pratique).

---

## 5. Première vérification

1. Visiter `https://universdiaspora.com/` — la page d'accueil doit s'afficher.
2. Visiter `https://universdiaspora.com/?page=admin-login` — se connecter avec
   les identifiants définis dans `config.local.php` (l'utilisateur admin est
   créé automatiquement au premier chargement de la base).
3. Visiter `https://universdiaspora.com/sitemap.xml` — vérifier que le sitemap est servi.
4. Visiter `https://universdiaspora.com/url-inexistante` — la page d'erreur 404
   personnalisée doit apparaître.
5. Tester un formulaire (Rendez-vous, Contact) — vérifier l'arrivée d'un mail.

### Si vous voyez la 404 Hostinger (« Cette page n'existe pas » / « simple accident »)

Ce message **ne vient pas** du site PHP : Hostinger ne trouve **aucun fichier** à servir.

1. **Racine web** : dans le gestionnaire de fichiers, ouvrez `public_html/`. Vous devez voir **directement** :
   - `index.php`
   - `.htaccess`
   - dossiers `app/`, `config/`, `public/`, etc.  
   **Incorrect** : `public_html/universDiaspora/index.php` (un sous-dossier en trop).

2. **Supprimez** la page par défaut Hostinger si présente : `default.php`, `index.html` (page d’accueil Hostinger).

3. **Testez dans l’ordre** :
   - `https://mediumspringgreen-hare-515139.hostingersite.com/health.php`
   - `https://mediumspringgreen-hare-515139.hostingersite.com/index.php`  
   Si `health.php` affiche « OK » pour `index.php` mais pas la page d’accueil → problème `.htaccess` ou `config.local.php`.  
   Si **tout** est en 404 → mauvais dossier ou fichiers non uploadés.

4. hPanel → **Sites web** → votre site → racine documentaire = `public_html`.

### Si vous voyez « 403 Interdit » (page Hostinger)

Message type : *« L'accès à cette ressource sur le serveur est refusé ! »*

1. **Structure des fichiers** (cause la plus fréquente)  
   Dans le gestionnaire Hostinger, `public_html/` doit contenir **directement** :
   - `index.php`
   - `.htaccess`
   - dossiers `app/`, `config/`, `public/`, etc.  
   **Incorrect** : `public_html/universDiaspora/index.php` (un niveau de trop).  
   Déplacez tout le contenu **dans** `public_html/`, pas dans un sous-dossier.

2. **Test PHP** : ouvrez `https://votre-site.hostingersite.com/health.php`  
   - Si **OK** s’affiche → PHP marche ; vérifiez `config/config.local.php` puis `index.php`.  
   - Si **403** aussi → permissions ou mauvais dossier (étape 1).

3. **Permissions** (File Manager → clic droit → Permissions) :
   - Dossiers : **755**
   - Fichiers : **644**
   - `index.php` et `.htaccess` doivent être lisibles.

4. **`.htaccess` à jour** : ré-uploadez le `.htaccess` racine du projet (sans `php_value`, compatible Hostinger).

5. **PHP 8.1+** : hPanel → Configuration PHP → version **8.1** ou **8.2**.

6. Ne pas ouvrir dans le navigateur : `/config/`, `/app/`, `/sql/` → 403 normal (protection).

### Si une page blanche apparaît

- `hPanel > Fichiers > storage/logs/php-errors.log` (ou via File Manager) :
  les erreurs y sont journalisées.
- Vérifier la version PHP active (PHP 8.1+).
- Vérifier les permissions : tout le contenu doit appartenir à votre utilisateur Hostinger
  (`hPanel > Fichiers > Permissions`).
- Vérifier que `config/.htaccess` est bien présent (Deny all) — il bloque
  l'accès direct à `config/config.local.php`.

---

## 6. SEO et référencement

1. **Google Search Console** : `https://search.google.com/search-console`
   - Ajouter la propriété (DNS ou TXT).
   - Soumettre `https://universdiaspora.com/sitemap.xml`.
2. **Bing Webmaster Tools** : pareil.
3. **`robots.txt`** est déjà servi sur la racine.
4. Le **JSON-LD** Organization + WebSite + LocalBusiness est embarqué dans `<head>`
   et déclare Univers Diaspora comme `creator` / `publisher`.

---

## 7. Sécurité — points à vérifier après mise en ligne

- [ ] HTTPS obligatoire (`.htaccess` redirige automatiquement).
- [ ] HSTS activé (en-tête `Strict-Transport-Security`).
- [ ] Mot de passe admin fort, différent de tout autre.
- [ ] `display_errors` à `0` en production (déjà géré par `bootstrap.php`).
- [ ] Sauvegarde automatique : `hPanel > Sauvegardes` (souvent inclus).
- [ ] Pas d'erreur 5xx dans `storage/logs/php-errors.log` après 24 h de prod.
- [ ] Le formulaire RDV insère bien les colonnes `service_slug` et `volet_id`
      en base (visible dans l'admin Inbox).
- [ ] `?page=admin-ai-conversations` accessible et alimenté si l'IA est activée.

---

## 8. Mises à jour ultérieures

### Via Git (recommandé)

```bash
ssh u123456789@<adresse>
cd ~/domains/universdiaspora.com/public_html
git pull --ff-only
```

Les colonnes manquantes en base sont ajoutées automatiquement par `app/db.php`
(migrations idempotentes).

### Via File Manager

- Uploader uniquement les fichiers modifiés.
- Ne JAMAIS écraser `config/config.local.php`.
- Ne JAMAIS écraser `storage/`.

---

## 9. Sauvegarde recommandée

Hebdomadairement :

1. `hPanel > Bases de données > phpMyAdmin > Exporter` → SQL.
2. `hPanel > Fichiers > Gestionnaire de fichiers > storage/` → télécharger
   (candidatures CV).
3. Stocker chez vous (cloud personnel ou disque externe).

---

## 10. Désactivation temporaire du site (maintenance)

Pour basculer en page de maintenance, créez `public_html/maintenance.html`
puis ajoutez en début de `.htaccess` :

```apache
RewriteCond %{REMOTE_ADDR} !^VOTRE\.IP\.PUBLIQUE$
RewriteCond %{REQUEST_URI} !^/maintenance\.html$
RewriteCond %{REQUEST_URI} !\.(css|js|png|jpe?g|svg|woff2?)$
RewriteRule ^ /maintenance.html [R=503,L]
ErrorDocument 503 /maintenance.html
```

---

**Conception & développement : Studio Univers Diaspora**
