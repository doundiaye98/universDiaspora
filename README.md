## Univers Diaspora (PHP / MySQL / Bootstrap)

Reproduction locale (WAMP) inspirée de `universdiaspora.com` avec ton logo et les visuels de la page d’accueil.

### Structure des dossiers

- `index.php`: point d’entrée (routeur simple)
- `pages/`: pages (home + services)
- `data/`: données statiques (services)
- `public/assets/`: CSS + images
- `config/`: configuration (DB + base_url)
- `sql/`: schéma MySQL
- `storage/`: réservé (logs, exports…)

### Installation (WAMP)

1. Mets le projet dans `C:\wamp\www\universDiaspora`
2. Crée la base et la table en important `sql/schema.sql` (phpMyAdmin).
3. Vérifie `config/config.php` :
   - `db.name` (par défaut `univers_diaspora`)
   - `db.user` / `db.pass` (souvent `root` / vide sur WAMP)
   - `app.base_url` : `http://localhost/universDiaspora`
4. Ouvre `http://localhost/universDiaspora/`

### Notes

- Le formulaire “Contact” enregistre les messages en base dans `contact_messages`.
- Les images de la page d’accueil sont stockées en local dans `public/assets/img/`.

