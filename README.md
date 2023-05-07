# Archers de Caen

Les archers de Caen est une association loi 1901, fondé en 1964 réunissant de
nombreux caennais (et ses alentours) souhaitant pratiquer le tir à l'arc pour le
loisir ou la compétition.

## Présentation / Use case du projet

Ceci est la 3e version du site des archers de Caen, il a pour but de fournir :
- Un site vitrine à l'association
- Une gestion d'actualités
- Permettre une gestion des résultats sportifs (concours, passage de fleche, etc.) des licenciés
- Une gestion des licences des archers
- Une boutique, permettant d'acheter des goodies (t-shirt, pantalon, casquette, etc.)

## Open Source

La décision a été prise de mettre en open source le projet, le club étant
une association loi 1901 promouvant le partage et l'entraide, il parait pertinent
que le code de son site soit Open Source, permettant a chacun de venir apporter
sa contribution au site.
Attention ! La réutilisation du site, du style, des logos, des photos, etc. n'est pas
autorisé !

## Stack technique

- Symfony avec une structure réadaptée, cf. `docs/structure.md`pour plus de détails.
- Twig, pour le front
- EasyAdmin, pour le back-office
- MariaDB, pour la base de donnée
- Fontawesome, pour les icons
- Manypixels, pour les illustrations, en type "two color"
- Helloasso, pour la boutique et la gestion des paiements

## Get started

### Pré-requis

- PHP >= 8.1
- Node >= 16.17
- NPM >= 9.6
- Mysql / Mariadb

### Lancer le projet

- Copier `.env` vers `.env.local` et éditer avec les bonnes valeurs


- `composer install`
- `npm install`


- `php -S localhost:80 -t public`
- `npm run dev-server`

## Contributeur

@ Damien Hebert |
[site](https://damienhebert.fr) |
[twitter](https://twitter.com/Doskyft) |
[github](https://github.com/Doskyft) |
[gitlab](https://gitlab.com/Doskyft1)
