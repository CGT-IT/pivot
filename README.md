# PIVOT
Plugin Wordpress qui fait une connexion à la DB PIVOT du CGT et qui permet un affichage sous forme de listing 
et de détails des différentes offres touristiques disponibles.
Il est également possible d'ajouter des filtres 

## Documentation

Disponible dans le [wiki](https://github.com/CGT-IT/pivot/wiki)
Infos pour l'installation et test disponible [ici](https://github.com/CGT-IT/pivot/wiki/Installer-&-configurer#informations-de-test)

## à savoir:

* à part les données de configuration du plugin, on ne conserve aucune offre venant de PIVOT dans la DB Wordpress.
* les pages (de listing et de détails) sont générées "à la volée".
* les types d'offres sont rassemblées dans des catégories (herbegement / activite / ...). Ces offres sont censées avoir un maximum de champs en commun.
* le listing a un template générique (`pivot-list-template.php`): seules les catégories
  qui ont besoin d'un affichage différent nécessitent leur propre template.
* les templates par défaut sont basés sur Bootstrap 4
* ce plugin n'a pas de dépendance à d'autre(s) plugin(s). WPML est utilisé s'il est
  installé, pour la traduction des titres de pages et de filtres.
* depuis la 2.5.0 le plugin n'utilise plus de session PHP: les filtres actifs sont dans
  l'URL, ce qui rend les recherches partageables et les pages cachables.

## Les templates:

Il ne faut pas modifier l'original. Si vous souhaitez modifier le template par défaut, 
\> Cloner l'existant à la racine de votre thème

Il en faut 3 par catégorie structurés de la façon suivante:
1. pivot-**nomdelacategorie**-list-template.php
    ```
    Va servir à afficher sous forme de liste les résultats d'une QUERY.
    ```
2. pivot-**nomdelacategorie**-details-template.php
    ```
    Va servir à afficher la page de détails d'une offre spécifique.
    ```
3. pivot-**nomdelacategorie**-details-part-template.php
    ```
    Il représente la vignette d'une offre.
    Ce template est inclus dans le template n°1 et sera également appelé dans les shortcodes
    ```

## To do list:

- [x] Pages de configuration du plugin
- [x] Affichage liste des offres;
- [x] Pagination de la liste;
- [x] Affichage des détails d’une offre;
- [x] Critères de recherche sur les offres;
- [x] Placement des offres sur une carte;
- [x] Ajout de shortcode
- [x] Affichage possible en plusieurs langues
    - [x] traductions venant de Pivot
    - [x] EN (= langue de base)
    - [x] FR
    - [x] NL
    - [x] DE 
- [x] Template (affichage) par défaut pour liste et détails
- [x] Aide à la création des shortcodes
- [x] Affichage des offres liées
- [x] Recherche sans rechargement de page, état des filtres dans l'URL
- [ ] Affichage des offres 'liées' dans les x km à la ronde
- [ ] Recherche sur base de la localisation si smartphone

## Développement

```bash
composer install   # PHPCS + WPCS + PHPCompatibility
composer lint      # php -l sur tous les fichiers
composer cs        # analyse (sécurité, SQL préparé, échappement, compat PHP)
```

Le `phpcs.xml` est volontairement partiel: il cible les règles qui attrapent des
bugs et des failles, pas le formatage. À durcir progressivement.


