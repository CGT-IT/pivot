<?php /* Template Name: pivot-address-template */ ?>

<?php $offre = $args; ?>

<div class="col-xl-4 col-lg-6 col-md-12 col-sm-12 col-xs-12">
  <article class="pivot-offer">

    <div class="container-img">
      <img class="pivot-img zoom pivot-img-list" src="https://pivotweb.tourismewallonie.be/PivotWeb-3.1/img/<?php print $offre->attributes()->codeCgt->__toString() ;?>;w=400;h=400"/>
      <div class="top-left-corner">
        <span class="item-services">
          <?php foreach($offre->spec as $specification): ?>
            <?php if($specification->attributes()->urn->__toString() == 'urn:obj:date'): ?>
              <?php foreach($specification->spec as $dateObj): ?>
                <?php if($dateObj->attributes()->urn->__toString() == 'urn:fld:date:datedeb'): ?>
                  <?php $dateStart = date("Y-m-d", strtotime(str_replace('/', '-', $dateObj->value->__toString()))); ?>
            <?php if(date('Y', strtotime($dateStart)) == 2018): ?>
                    <div class="time time-start">
                        <div datetime="<?php echo date("Y-M-D h:m", strtotime($dateStart)); ?>">
                        <div class="day"><?php echo date('d', strtotime($dateStart));?></div>
                        <div class="month"><?php echo date('M', strtotime($dateStart));?></div>
                      </div>
                    </div>
                  <?php print $dateStart; ?>
            <?php endif; ?>

                <?php endif; ?>
                <?php if($dateObj->attributes()->urn->__toString() == 'urn:fld:date:datefin'): ?>
                  <?php $dateEnd = date("Y-m-d", strtotime(str_replace('/', '-', $dateObj->value->__toString()))); ?>
                    <?php if($dateEnd != $dateStart): ?>
                      <div class="time time-end">
                        <div datetime="<?php echo date("Y-M-D h:m", strtotime($dateEnd)); ?>">
                          <div class="day"><?php echo date('d', strtotime($dateEnd));?></div>
                          <i class="arrow-right"></i>  
                          <div class="month"><?php echo date('M', strtotime($dateEnd));?></div>
                        </div>
                      </div>
                  <?php endif; ?>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
          <?php endforeach; ?>
        </span>
      </div>
      <?php if(isset($offre->adresse1->commune)): ?>
        <div class="top-right-corner">
          <span class="locality"><?php print $offre->adresse1->commune->value->__toString(); ?></span>
        </div>
      <?php endif; ?>
      <div class="bottom">
        <div class="container-fluid">
          <div class="row">
            <div class="col-10">
              <span class="item-services">
                <?php $url_offer_details = get_bloginfo('wpurl').'/'.$offre->path.'/'.$offre->attributes()->codeCgt->__toString().'&type='.$offre->typeOffre->attributes()->idTypeOffre->__toString(); ?>
                <h4 class="pivot-title"><a title="Link to <?php print $offre->nom->__toString(); ?>" href="<?php print $url_offer_details; ?>"><?php print $offre->nom->__toString(); ?></a></h4>
              </span>
            </div>
            <div class="col-1">
              <div class="social">
                </div><span class="facebook"><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url_offer_details; ?>"><span class="fa fa-facebook"></span></a></span>
             <div class="col-1">   <span class="twitter"><a href="https://twitter.com/intent/tweet?url=<?php echo $url_offer_details; ?>"><span class="fa fa-twitter"></span></a></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="info">
      <?php // $address = _get_address_one_line($offre); ?>
      <!-- Add offer Object to query var as it is available in the address template -->
      <?php // set_query_var('offre', $offre); ?>
      <!-- Include address shared template -->
      <?php // include("pivot-address-template.php"); ?>
      <?php // echo _get_address_html($offre); ?>

      <p class="pivot-code-cgt d-none item"><?php print $offre->attributes()->codeCgt->__toString(); ?></p>
    </div>
    <div class="row justify-content-between d-none">
      <!-- Link to add event to external calendar -->
      <div class="add-to-calendar col-10">
        <span><a href="https://calendar.google.com/calendar/r/eventedit?text=<?php echo $offre->nom->__toString(); ?>&dates=<?php echo date("Ymd", strtotime($dateStart)); ?>/<?php echo date("Ymd", strtotime($dateEnd)); ?>&details=Pour+plus+de+détails:<?php print $url_offer_details; ?>&location=<?php echo $address; ?>">Google</a></span>
        <span><a href="https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent&startdt=<?php echo $dateStart; ?>&enddt=<?php echo $dateEnd; ?>&subject=<?php echo $offre->nom->__toString(); ?>&body=Pour+plus+de+détails:+">Outlook</a></span>
        <span><a href="https://calendar.yahoo.com/?v=60&view=d&type=20&title=<?php echo $offre->nom->__toString(); ?>&st=<?php echo date("Ymd", strtotime($dateStart)); ?>&et=<?php echo date("Ymd", strtotime($dateEnd)); ?>&desc=Pour+plus+de+détails:<?php print $url_offer_details; ?>&in_loc=<?php echo $address; ?>&uid=">Yahoo</a></span>
      </div>
      <!-- Link to share event on social media -->
      <div class="social col-2">
          <span class="facebook"><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url_offer_details; ?>"><span class="fa fa-facebook"></span></a></span>
          <span class="twitter"><a href="https://twitter.com/intent/tweet?url=<?php echo $url_offer_details; ?>"><span class="fa fa-twitter"></span></a></span>
      </div>
    </div>
  </article>
</div>