

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

> **Depuis la 2.5.0 — le plugin n'utilise plus `$_SESSION`.**
> Un template cloné qui lit `$_SESSION['pivot'][...]` doit être mis à jour:
>
> | Avant | Maintenant |
> |---|---|
> | `$_SESSION['pivot'][$id]['page_title']` | `$pivot_page->title` |
> | `$_SESSION['pivot'][$id]['path']` | `$pivot_page->path` |
> | `$_SESSION['pivot'][$id]['nb_offres']` | `pivot_get_nb_offers($pivot_page->id)` |
>
> Les filtres actifs voyagent désormais dans la querystring (`pf[<id du filtre>]`),
> ce qui rend les recherches partageables et les pages cachables.

Si vous n'avez pas besoin de personnaliser le listing, **ne clonez rien**:
`pivot-list-template.php` sert de template générique pour toutes les catégories
qui n'ont pas de fichier dédié.

Following lines should be included in ***.list-template.php**

```php
// To know on which page you are
<?php $pivot_page = pivot_get_page_path(_get_path()); ?>
// Should be mandatory, will override "404" title with real title (coming from 'manage page')
<title><?php print esc_html($pivot_page->title .' - '. get_bloginfo('name'));?></title>

// Include default header
<?php get_header(); ?>
// Include sidebar or other ...
<?php get_sidebar(); ?>

// If you want to include filters directly in the template (must be include in the beginning)
<?php pivot_add_filters(); ?>

// Get offers
<?php $offres = pivot_lodging_page($pivot_page->id); ?>
<?php $nb_offres = pivot_get_nb_offers($pivot_page->id); ?>

// Print every thumbnail (handles the per-offer transient cache for you)
<?php print pivot_render_offer_thumbnails($offres, $pivot_page); ?>

// Add pagination. Passing the page id keeps the active filters in the paged URLs.
<?php echo _add_pagination($nb_offres, $pivot_page->nbcol, null, $pivot_page->id); ?>
```

Pour que la recherche asynchrone fonctionne, le template doit exposer trois
points d'accroche (voir `pivot-list-template.php`):

* `id="offers-area"` sur le conteneur des offres,
* `data-pivot-count` sur l'élément affichant le nombre d'offres,
* `data-pivot-pagination` sur le conteneur de la pagination.

Sans eux — ou sans JavaScript — le formulaire de filtres reste un formulaire GET
classique et le rendu se fait côté serveur, comme avant.

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
