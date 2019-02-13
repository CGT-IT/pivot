
<?php $offre = _get_offer_details(); ?>
<?php _add_meta_data($offre, 'details'); ?>
<?php get_header(); ?>

<article class="pivot-offer row m-3">
  <div class="col-xs-12 col-md-8">
    <div class="row">
      
      <?php print pivot_template('pivot-image-slider', $offre); ?>
        
      <div class="col-12">
        <h2 class="pivot-title"><?php print _get_urn_value($offre, 'urn:fld:nomofr'); ?></h2>

        <!--<div class="tab-content card">-->
        <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 lis-f-14"></i><?php esc_html_e('Description', 'pivot')?></h5>
        <section class="card lis-brd-light mb-4">
          <div class="card-body p-4">
            <p class="pivot-desc item mb-0"><?php print _get_urn_value($offre, 'urn:fld:descmarket') ;?></p>
          </div>
        </section>

        <?php print _add_section($offre,'urn:cat:accueil', __('Extra infos'), 'fa-info'); ?>
        <?php print _add_section_linked_offers($offre); ?>
      </div>
    </div>
  </div>

  <aside class="col-xs-12 col-md-3">

    <?php print _add_section_contact($offre); ?>
    
    <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-calendar-check-o"></i><?php esc_html_e('Dates', 'pivot')?></h5>
    <section class="pivot-share card lis-brd-light mb-4">
      <div class="card-body p-4">
        <?php foreach($offre->spec as $specification): ?>
          <?php if($specification->attributes()->urn->__toString() == 'urn:obj:date'): ?>
            <?php foreach($specification->spec as $dateObj): ?>

              <?php if($dateObj->attributes()->urn->__toString() == 'urn:fld:date:datedeb'): ?>
                <?php $dateStart = date("Y-m-d", strtotime(str_replace('/', '-', $dateObj->value->__toString()))); ?>
                <?php if(date('Y', strtotime($dateStart)) == 2018): ?>
                  <div class="time time-start">
                      <div datetime="<?php echo date("Y-M-D h:m", strtotime($dateStart)); ?>">
                      Start: <span class="day"><?php echo date('d', strtotime($dateStart));?></span>
                      <span class="month"><?php echo date('M', strtotime($dateStart));?></span>
                      <span class="year"><?php echo date('Y', strtotime($dateStart));?></span>
                    </div>
                  </div>
                <?php endif; ?>
              <?php endif; ?>

              <?php if($dateObj->attributes()->urn->__toString() == 'urn:fld:date:datefin'): ?>
                <?php $dateEnd = date("Y-m-d", strtotime(str_replace('/', '-', $dateObj->value->__toString()))); ?>
                  <?php if($dateEnd != $dateStart): ?>
                    <div class="time time-end">
                      <div datetime="<?php echo date("Y-M-D h:m", strtotime($dateEnd)); ?>">
                        End: <span class="day"><?php echo date('d', strtotime($dateEnd));?></span>
                        <span class="month"><?php echo date('M', strtotime($dateEnd));?></span>
                        <span class="year"><?php echo date('Y', strtotime($dateEnd));?></span>
                      </div>
                    </div>
                <?php endif; ?>
              <?php endif; ?>

            <?php endforeach; ?>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </section>

    <?php print _add_section($offre,'urn:cat:classlab', __('Theme(s)'), 'fa-list-ol'); ?>
    <?php print _add_section_share($offre); ?>
        
    <h5 class="lis-font-weight-500"><i class="fa fa-align-right pr-2 fa-calendar"></i><?php esc_html_e('Add to calendar', 'pivot')?></h5>
    <section class="pivot-share card lis-brd-light mb-4">
      <!-- Link to add event to external calendar -->
      <div class="add-to-calendar card-body p-4">
        <span class="fa fa-align-right pr-2 fa-google"><a href="https://calendar.google.com/calendar/r/eventedit?text=<?php echo _get_urn_value($offre, 'urn:fld:nomofr'); ?>&dates=<?php echo date("Ymd", strtotime($dateStart)); ?>/<?php echo date("Ymd", strtotime($dateEnd)); ?>">Google</a></span>
        <!--span class="fa fa-align-right pr-2 fa-google"><a href="https://calendar.google.com/calendar/r/eventedit?text=<?php // echo _get_urn_value($offre, 'urn:fld:nomofr'); ?>&dates=<?php // echo date("Ymd", strtotime($dateStart)); ?>/<?php // echo date("Ymd", strtotime($dateEnd)); ?>&details=Pour+plus+de+détails:<?php // print $url_offer_details; ?>&location=<?php // echo $address; ?>">Google</a></span>-->
        <span class="fa fa-align-right pr-2 fa-envelope"><a href="https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent&startdt=<?php echo $dateStart; ?>&enddt=<?php echo $dateEnd; ?>&subject=<?php echo _get_urn_value($offre, 'urn:fld:nomofr'); ?>&body=Pour+plus+de+détails:+">Outlook</a></span>
        <span class="fa fa-align-right pr-2 fa-yahoo"><a href="https://calendar.yahoo.com/?v=60&view=d&type=20&title=<?php echo _get_urn_value($offre, 'urn:fld:nomofr'); ?>&st=<?php echo date("Ymd", strtotime($dateStart)); ?>&et=<?php echo date("Ymd", strtotime($dateEnd)); ?>&desc=Pour+plus+de+détails:<?php print $url_offer_details; ?>&in_loc=<?php echo $address; ?>&uid=">Yahoo</a></span>
      </div>
    </section>

  </aside>

</article>


<?php get_footer();