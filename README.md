# PIVOT
Plugin Wordpress qui fait une connexion à la DB PIVOT du CGT et qui permet un affichage facile de listing et du détails des différentes offres touristiques disponibles


## à savoir:

* à part les données de configuration du plugin, on ne conserve aucune offre venant de PIVOT dans la DB Wordpress.
* les pages (de listing et de détails) sont générées "à la volée".
* j'ai rassemblé les types d'offres dans des catégories (herbegement / activite / ...). Ces offres sont censées avoir un maximum de champs en commun.
* deux templates sont à créer ou à customiser par catégorie (le listing et le détail)
* les templates par défaut sont basés sur Bootstrap 4

## Les templates:

Il ne faut pas modifier l'original. Si vous souhaitez modifier le template par défaut, 
\> Cloner l'existant à la racine de votre thème

Il en faut 3 par catégorie structurés de la façon suivante:
1. pivot-**nomdelacategorie**-list-template.php
    ```
    va servir à afficher sous forme de liste les résultats d'une QUERY.
    ```
2. pivot-**nomdelacategorie**-details-template.php
    ```
    va servir à afficher le page de détails d'une offre spécifique.
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
- [x] Affichage possible en plusieurs langues
    - [x] traductions venant de Pivot
    - [x] EN (= langue de base)
    - [x] FR
    - [ ] NL
    - [ ] DE 
- [x] Template (affichage) par défaut pour liste et détails
- [ ] Affichage des offres liées
- [ ] Affichage des offres 'liées' dans les x km à la ronde
- [ ] Recherche sur base de la localisation si smartphone


