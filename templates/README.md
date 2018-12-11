# Les templates:

Il ne faut pas modifier l'original. Si vous souhaitez modifier le template par défaut, 
\> Cloner l'existant à la racine de votre thème

Il en faut 3 par catégorie structurés de la façon suivante:
1. pivot-**nomdelacategorie**-list-template.php
    ```
    Va servir à afficher sous forme de liste les résultats d'une QUERY.
    ```
2. pivot-**nomdelacategorie**-details-template.php
    ```
    Va servir à afficher le page de détails d'une offre spécifique.
    ```
3. pivot-**nomdelacategorie**-details-part-template.php
    ```
    Il représente la vignette d'une offre.
    Ce template est inclus dans le template n°1 et sera également appelé dans les shortcodes
    ```


Following lines should be included in ** *.list-template.php**
    ```
<!--This is mandatory, you need this to know on which page you are-->
<?php $pivot_page = pivot_get_page_path(_get_path()); ?>
<!--Should be mandatory, will override "404" title with real title (coming from 'manage page')-->
<title><?php print $_SESSION['pivot'][$pivot_page->id]['page_title'] .' - '. get_bloginfo('name');?></title>

<!--Include default header-->
<?php get_header(); ?>
<!--Include sidebar or other ...-->
<?php get_sidebar(); ?>

<!--If you want to include filters directly in the template (must be include in the beginning)-->
<?php pivot_add_filters(); ?>

<!--Get offers-->
<?php $offres = pivot_lodging_page($pivot_page->id); ?>
    ```

$offres is an object with all offers. You'll have to loop on it (var_dump to see what it contains). It depends of each type of offers.
