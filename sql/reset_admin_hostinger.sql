-- Remet le compte admin Hostinger (phpMyAdmin → onglet SQL).
-- Identifiants : ud_admin / UdAdmin2026!Site

DELETE FROM admin_login_attempts;

-- Si un compte ud_admin existe déjà : maj du mot de passe
UPDATE admin_users
SET password_hash = '$2y$10$3EHa7FsJEgtRBAhCsNExL.thkOeT.LzzJjMuIHVTI0kznEDqAGVna',
    role = 'super_admin',
    is_active = 1
WHERE username = 'ud_admin';

-- Sinon : renommer le premier compte existant
UPDATE admin_users
SET username = 'ud_admin',
    password_hash = '$2y$10$3EHa7FsJEgtRBAhCsNExL.thkOeT.LzzJjMuIHVTI0kznEDqAGVna',
    role = 'super_admin',
    is_active = 1
WHERE id = (
  SELECT id FROM (
    SELECT MIN(id) AS id FROM admin_users
  ) AS t
)
AND NOT EXISTS (
  SELECT 1 FROM (
    SELECT id FROM admin_users WHERE username = 'ud_admin' LIMIT 1
  ) AS u
);

-- Si la table est vide : créer le compte
INSERT INTO admin_users (username, password_hash, role, is_active)
SELECT 'ud_admin',
       '$2y$10$3EHa7FsJEgtRBAhCsNExL.thkOeT.LzzJjMuIHVTI0kznEDqAGVna',
       'super_admin',
       1
WHERE NOT EXISTS (SELECT 1 FROM admin_users LIMIT 1);
