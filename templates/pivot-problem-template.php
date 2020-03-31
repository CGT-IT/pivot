<?php $error = $args; ?>

<?php get_header(); ?>

<div class="container text-center p-4">
  <h1><?php _e('Pivot Error', 'pivot'); ?></h1>
  <h3><?php _e('The Walloon tourist database encounters a problem.', 'pivot'); ?></h3>
  <p><?php _e('The content of this page cannot be displayed for the moment', 'pivot'); ?></p>
  <p class="lead"><?php _e('Please try again later, Thank you !', 'pivot'); ?></p>
  <a class="btn btn-info btn-lg" role="button" href="<?php print get_home_url();?>"><?php _e('Go to Homepage', 'pivot'); ?></a>
  <div class="text-left align-left mt-5">
    <h4 class="text-left mr-4 d-inline">Error log</h4>
    <form action="" method="post" class="form-inline text-left d-inline">
      <input type="submit" value="<?php _e('Send report to admin', 'pivot');?>" />
      <input type="hidden" name="button_error_pressed" value="1" />
    </form>
  </div>
  <p><?php print _show_warning($error, 'danger');?></p>

  <?php

  if(isset($_POST['button_error_pressed']))
  {
    global $wp;
    $url = home_url($wp->request);
    $site_name = get_bloginfo('name');
      $to      = get_bloginfo('admin_email');
      $subject = 'Pivot Error on '.$site_name;
      $message = $url.'<br/><br/>'.$error;
      $headers = 'From: '. $to . "\r\n" .
          'Reply-To: '.$to. "\r\n" .
          'X-Mailer: PHP/' . phpversion();

      $sent = mail($to, $subject, $message, $headers);

      if($sent == true){
        print '<br>'._show_warning('Email sent, thank you !.', 'success');
      }else{
        print '<br>'._show_warning('Error sending Email.'); 
      }
  }
  ?>
</div>

<?php get_footer();