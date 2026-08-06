=== Pivot ===
Contributors: cgt-it
Tags: tourism, pivot, wallonie, cgt, offers
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Affiche et permet la recherche des offres touristiques de la base PIVOT du
Commissariat général au Tourisme, sous forme de listings et de pages de détail.

== Description ==

Le plugin interroge le webservice PIVOT et génère les pages de listing et de
détail à la volée. Aucune offre n'est stockée dans la base WordPress: seule la
configuration du plugin l'est (pages, filtres, types d'offres).

* les types d'offres sont regroupés en catégories (hebergement, activite, ...)
* les templates sont surchargeables depuis le thème
* les templates par défaut sont basés sur Bootstrap 4
* aucune dépendance à un autre plugin (WPML est utilisé s'il est présent)

La documentation complète est dans le
[wiki](https://github.com/CGT-IT/pivot/wiki).

== Installation ==

1. Déposer le dossier `pivot` dans `wp-content/plugins/`.
2. Activer le plugin.
3. Renseigner l'URI de base et la clé WS_KEY dans Pivot > Pivot.

== Changelog ==

= 2.5.0 =

Sécurité:

* Correction d'une injection SQL et d'un export de données accessibles sans
  authentification via `?export=dump` (`inc/external/dump.php`, supprimé).
* Correction d'une injection SQL en front via le paramètre `&type=` des pages de
  détail (`pivot_get_offer_type()`).
* Suppression de `inc/external/gpxdownloader.php`, qui permettait la lecture de
  fichiers arbitraires et des requêtes sortantes arbitraires.
* Ajout des vérifications de capability et de nonce sur les écrans
  d'administration (pages, filtres, types d'offres).
* Échappement des sorties d'administration, liste blanche sur les tris SQL.
* Vérification TLS réactivée sur tous les appels sortants.

Performance:

* Mise en cache du thésaurus: une fiche de détail déclenchait jusqu'à plusieurs
  centaines d'appels HTTP séquentiels vers Pivot.
* Client HTTP unique basé sur l'API WordPress, timeouts ramenés à 15 s, repli sur
  le cache en cas d'indisponibilité de Pivot.
* Leaflet, bootstrap-table et d3 sont enregistrés puis chargés à la demande au
  lieu d'être écrits en dur dans le corps de page.
* Les sessions PHP sont supprimées: le cache de page redevient possible sur
  l'ensemble du site.

Fonctionnel:

* La recherche sur les pages de listing se fait côté client, sans rechargement,
  avec l'état des filtres dans l'URL (recherches partageables et indexables).
  Sans JavaScript, le formulaire reste un formulaire GET classique.
* Nouvel endpoint REST `pivot/v1/pages/<id>/offers`.
* Le mode de tri `shuffle` est enfin transmis à Pivot sur les pages de listing.

Compatibilité:

* Les templates clonés dans un thème qui lisent `$_SESSION['pivot'][...]` doivent
  être mis à jour, voir `templates/README.md`.
* `MY_PLUGIN_PATH`, `MY_PLUGIN_URL`, `debug()` et `endsWith()` restent
  disponibles en alias.
