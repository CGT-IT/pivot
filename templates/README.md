

# Les templates:

**Il ne faut pas modifier l'original.** 

Si vous souhaitez modifier le(s) template(s) par défaut: 
 > Cloner l'existant à la racine de votre thème
 
 > Si vous créez de nouvelles catégories, même chose ajoutez ces nouveaux templates à la racine de votre thème.

Il en faut 3 par catégorie structurés de la façon suivante:
1. pivot-**nomdelacategorie**-list-template.php
    > Va servir à afficher sous forme de liste les résultats d'une QUERY.

2. pivot-**nomdelacategorie**-details-template.php
    > Va servir à afficher le page de détails d'une offre spécifique.

3. pivot-**nomdelacategorie**-details-part-template.php
    > Il représente la vignette d'une offre.
    > Ce template est inclus dans le template n°1 et sera également appelé dans les shortcodes

Following lines should be included in ***.list-template.php**

```php
// To know on which page you are
<?php $pivot_page = pivot_get_page_path(_get_path()); ?>
// Should be mandatory, will override "404" title with real title (coming from 'manage page')
<title><?php print $_SESSION['pivot'][$pivot_page->id]['page_title'] .' - '. get_bloginfo('name');?></title>

// Include default header
<?php get_header(); ?>
// Include sidebar or other ...
<?php get_sidebar(); ?>

// If you want to include filters directly in the template (must be include in the beginning)
<?php pivot_add_filters(); ?>

// Get offers
<?php $offres = pivot_lodging_page($pivot_page->id); ?>
// Loop on offers
<?php foreach($offres as $offre): ?>
  // Construct file name for the template "details part"
  <?php $name = 'pivot-'.$pivot_page->type.'-details-part-template'; ?>
  // Add Path and map to $offre object
  <?php $offre->path = $_SESSION['pivot'][$pivot_page->id]['path']; ?>
  <?php $offre->map = $_SESSION['pivot'][$pivot_page->id]['map']; ?>
  // Print "vignette" of the offer detail
  <?php print pivot_template($name, $offre); ?>
<?php endforeach; ?>

// Add pagination
<?php echo _add_pagination($_SESSION['pivot'][$pivot_page->id]['nb_offres']); ?>
```

$offre is an object with all details. You'll have to var_dump it to see what it contains. It depends of each type of offers.

Following lines should be included in ***.details-part-template.php**

```php
// To get $offre object
<?php $offre = $args; ?>
```
Following lines should be included in ***.details-template.php**

```php
// To get $offre object
<?php $offre = _get_offer_details(); ?>
// Add metadata to HTML page (for FB, twitter, google)
<?php _add_meta_data($offre, 'details'); ?>
// Add default header to the page
<?php get_header(); ?>
```
**Just browse default template to see how it's build.**

There are some usefull functions like to help to build the template:
* _search_specific_urn_img($offre, $urn, $height, $color, $original = FALSE);
* _get_urn_value($offre, $urn);
* _get_ranking_picto($offre);
* _get_urn_documentation($urn);
* _get_urn_documentation_full_spec($urn);
* All functions in inc\pivot-template-helper.php
