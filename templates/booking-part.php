<?php $offre = $args; ?>
<?php $orc = _get_urn_value($offre, 'urn:fld:orc');?>
<?php if(esc_url($orc)):?>
  <?php $link = $orc; ?>
<?php else: ?>
  <?php $other = _get_urn_value($offre, 'urn:fld:sitresa');?>
  <?php if(esc_url($other)):?>
    <?php $link = $other; ?>
  <?php else: ?>
    <?php $default = _get_urn_value($offre, 'urn:fld:urlresa:default'); ?>
    <?php if(esc_url($default)):?>
      <?php $link = $other; ?>
    <?php endif; ?>
  <?php endif; ?>
<?php endif; ?>

<?php if(isset($link) && esc_url($link)): ?>
  <a title="<?php __('Link to', 'pivot').' '.__('booking system');?>" class="button btn-block btn-lg text-center" target="_blank" href="<?php print $link;?>"><i class="fa fa-credit-card pr-2"></i><?php print __('Book', 'pivot');?></a>
<?php endif; ?>
