
<?php /* Template Name: pivot-lodging-list-template */ ?>

<!--Include header-->
<?php get_header(); ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>  
<script src="//cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
<script src="<?php echo plugins_url('/../js/map.js', __FILE__) ?>"></script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<!--Include sidebar-->
<?php get_sidebar(); ?>
<?php global $base_url; ?>

<!--Get offers-->
<?php $offres = pivot_lodging_page(); ?>

<div class="container pivot-list">
    <input type="text" class="search" />
    <ul class="list list-group">
      <?php foreach($offres as $offre): ?>
        <?php // print '<pre>'; print_r($offre); print '</pre>'; ?>
      
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <p class="name">
            <?php $url_offer_details = get_bloginfo('wpurl').'/'.$_SESSION['pivot']['path'].'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
            <a title="Link to <?php print $offre->nom->__toString(); ?>" href="<?php print $url_offer_details; ?>"><?php print $offre->nom->__toString(); ?></a>
            <span class="badge badge-info badge">
              <?php if(isset($offre->adresse1->commune)): ?>
                <span class="locality"><?php print $offre->adresse1->commune->value->__toString(); ?></span>
              <?php endif; ?>  
            </span>
          </p>
        </li>
            
      <?php endforeach; ?>  
    </ul>
    <ul class="pagination"></ul>
</div>

<div class="row">
  <div class="col-4">
    <div class="list-group" id="list-tab" role="tablist">
      <?php foreach($offres as $offre): ?>
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-toggle="list" href="#<?php echo $offre->attributes()->codeCgt->__toString(); ?>" role="tab">
          <?php print $offre->nom->__toString(); ?>
          <?php if(isset($offre->adresse1->commune)): ?>
            <span class="badge badge-info badge">
              <?php print $offre->adresse1->commune->value->__toString(); ?>
            </span>        
          <?php endif; ?>  
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="col-8">
    <div class="tab-content" id="nav-tabContent">
      <?php foreach($offres as $offre): ?>
        <div class="tab-pane fade show" id="<?php echo $offre->attributes()->codeCgt->__toString(); ?>" role="tabpanel" aria-labelledby="list-home-list">
          <p><?php print _get_urn_value($offre, 'urn:fld:mobi1'); ?> <?php print _get_urn_documentation('urn:fld:mobi1'); ?></p>
          <p><?php print _get_urn_value($offre, 'urn:fld:phone1'); ?></p>
          <div class="adr">
            <dd class="street-address"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span> <?php print $offre->adresse1->rue->__toString(); ?>, <?php print $offre->adresse1->numero->__toString(); ?></dd>
            <dd>
              <span class="postal-code"><?php print $offre->adresse1->cp->__toString(); ?></span>
              <span class="locality"><?php print $offre->adresse1->commune->value->__toString(); ?></span>
            </dd>
            <dd class="country-name"><?php print $offre->adresse1->pays->__toString(); ?></dd>
            <dd class="pivot-latitude d-none"><?php print $offre->adresse1->latitude->__toString(); ?></dd>
            <dd class="pivot-longitude d-none"><?php print $offre->adresse1->longitude->__toString(); ?></dd>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="row"><div class="col-8">
<div id="accordion">
  <?php foreach($offres as $offre): ?>
    <div class="card card-list">
      <div class="card-header d-flex justify-content-between align-items-center" id="h<?php echo $offre->attributes()->codeCgt->__toString(); ?>">
        <button class="btn btn-link collapsed d-flex justify-content-between align-items-center" data-toggle="collapse" data-target="#c<?php echo $offre->attributes()->codeCgt->__toString(); ?>" aria-expanded="false" aria-controls="c<?php echo $offre->attributes()->codeCgt->__toString(); ?>">
          <?php print $offre->nom->__toString(); ?>  
          <?php if(isset($offre->adresse1->commune)): ?>
            <span class="badge badge-info badge">
              <?php print $offre->adresse1->commune->value->__toString(); ?>
            </span>        
          <?php endif; ?>  
        </button>
      </div>

      <div id="c<?php echo $offre->attributes()->codeCgt->__toString(); ?>" class="collapse" aria-labelledby="h<?php echo $offre->attributes()->codeCgt->__toString(); ?>" data-parent="#accordion">
        <div class="card-body">
          <p><?php print _get_urn_value($offre, 'urn:fld:mobi1'); ?> <?php print _get_urn_documentation('urn:fld:mobi1'); ?></p>
          <p><?php print _get_urn_value($offre, 'urn:fld:phone1'); ?></p>
          <div class="adr">
            <dd class="street-address"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span> <?php print $offre->adresse1->rue->__toString(); ?>, <?php print $offre->adresse1->numero->__toString(); ?></dd>
            <dd>
              <span class="postal-code"><?php print $offre->adresse1->cp->__toString(); ?></span>
              <span class="locality"><?php print $offre->adresse1->commune->value->__toString(); ?></span>
            </dd>
            <dd class="country-name"><?php print $offre->adresse1->pays->__toString(); ?></dd>
            <dd class="pivot-latitude d-none"><?php print $offre->adresse1->latitude->__toString(); ?></dd>
            <dd class="pivot-longitude d-none"><?php print $offre->adresse1->longitude->__toString(); ?></dd>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>    </div>
  </div>

<?php // echo _add_pagination($_SESSION['pivot']['nb_offres']); ?>

<?php get_footer();