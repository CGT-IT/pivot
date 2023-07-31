<?php $dates = $args; ?>

<div class="pivot-date-object">
    <?php foreach ($dates as $date): ?>
      <?php if (isset($date['fin']) && $date['fin'] != '' && $date['fin'] != $date['deb']): ?>
        <?php if ((strtotime($date['fin']) >= strtotime('today'))): ?>
          <span class="time time-start">
              <span datetime="<?php print date("Y-M-D h:m", strtotime($date['deb'])); ?>">
                  <span class="day"><?php print date('d', strtotime($date['deb'])); ?></span>
                  <span class="month"><?php print date_i18n(__('F'), strtotime($date['deb'])); ?></span>
              </span>
          </span>
          <span class="time time-end">
              <span datetime="<?php print date("Y-M-D h:m", strtotime($date['fin'])); ?>">
                  <i class="fas fa-angle-double-right"></i>
                  <span class="day"><?php print date('d', strtotime($date['fin'])); ?></span>
                  <span class="month"><?php print date_i18n(__('F'), strtotime($date['fin'])); ?></span>
              </span>
          </span>
          <p class="pivot-details-open">
              <?php if (isset($date['houv1']) && $date['houv1'] != '00:00'): ?>
                <?php print _x('From', 'hours', 'pivot') . ' ' . $date['houv1']; ?>
              <?php endif; ?>
              <?php if (isset($date['hferm1']) && $date['hferm1'] != '00:00'): ?>
                <?php print ' ' . _x('to', 'hours', 'pivot') . ' ' . $date['hferm1']; ?>
              <?php endif; ?>
              <?php if (isset($date['detailouv'])): ?>
                <br><?php print $date['detailouv']; ?>
              <?php endif; ?>
          </p>
        <?php endif; ?>
      <?php else: ?>
        <?php if ((strtotime($date['deb']) >= strtotime('- 1day'))): ?>
          <span class="time time-start">
              <span datetime="<?php print date("Y-M-D h:m", strtotime($date['deb'])); ?>">
                  <span class="day"><?php print date('d', strtotime($date['deb'])); ?></span>
                  <span class="month"><?php print date_i18n(__('F'), strtotime($date['deb'])); ?></span>
              </span>
          </span>
          <p class="pivot-details-open">
              <?php if (isset($date['houv1']) && $date['houv1'] != '00:00'): ?>
                <?php print _x('From', 'hours', 'pivot') . ' ' . $date['houv1']; ?>
              <?php endif; ?>
              <?php if (isset($date['hferm1']) && $date['hferm1'] != '00:00'): ?>
                <?php print ' ' . _x('to', 'hours', 'pivot') . ' ' . $date['hferm1']; ?>
              <?php endif; ?>
              <?php if (isset($date['detailouv'])): ?>
                <br><?php print $date['detailouv']; ?>
              <?php endif; ?>
          </p>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>
</div>
