-- Redirections externes : Voyages + Supermarket
-- À exécuter dans phpMyAdmin Hostinger (base u528552725_udiaspora) si besoin.

UPDATE services
SET external_url = 'https://www.terangavoyages.com/',
    coming_soon = 0
WHERE slug = 'voyages';

UPDATE services
SET external_url = 'https://yombalmarket.com/',
    coming_soon = 0,
    description = 'Boutique en ligne Yombal Market : produits locaux, épicerie et livraison.'
WHERE slug = 'supermarket';
