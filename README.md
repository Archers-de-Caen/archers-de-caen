# Archers de Caen

Les archers de Caen est une association loi 1901, fondé en 1964 réunissant de nombreux caennais (et ses alentours)
souhaitant pratiquer le tir à l'arc pour le loisir ou la compétition.

## Présentation / Use case du projet

Ceci est la 3e version du site des archers de Caen, il a pour but de fournir :
- Un site vitrine à l'association
- Une gestion d'actualités
- Permettre une gestion des résultats sportifs (concours, passage de fleche, etc.) des licenciés
- Une gestion des licences des archers
- Une boutique, permettant d'acheter des goodies (t-shirt, pantalon, casquette, etc.) 

## Open Source

La décision a été prise de mettre en open source le projet, le club étant une association loi 1901 promouvant le partage et
l'entraide, il parait pertinent que le code de son site soit Open Source, permettant a chacun de venir apporter 
sa contribution au site.

## Stack technique

Je vais parler à titre personnel, le site aurait pu être fait sur un Wordpress, ce qui aurait surement permit de gagner 
du temps de développement, mais ce n'est pas une techno qui m'attire, de plus ayant encore beaucoup de chose à apprendre,
ce site va me permettre de tenter des choses différentes, j'ai donc fait le choix de Symfony mon framework de coeur depuis
plusieurs années, de plus si vous avez l'habitude de la structure de symfony vous pouvez vous rendre compte que le projet
ne la suit pas, cf. `docs/structure.md` pour plus de détails.

## Get started

### Pré-requis

- PHP >= 8.1
- Yarn
- Mysql / Mariadb

### Lancer le projet

- copy .env to .env.sample and edit him
- `php -S localhost:80 -t public`

## Contributeur

@ Damien Hebert / [twitter](https://twitter.com/Doskyft) / [github](https://github.com/Doskyft) / [gitlab](https://gitlab.com/Doskyft1)