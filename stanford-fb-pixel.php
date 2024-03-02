<?php
/*
Plugin Name: Stanford FB Pixel
Description: Enqueue Stanford's global FaceBook Pixel
Version:     1.1
Author:      JB Christy
Author URI:  https://www.stanford.edu/site/
License:     Educational Community License 2.0
License URI: http://directory.fsf.org/wiki/License:ECL2.0
*/

namespace Stanford\FBPixel;

function enqueue_snippet() {
  wp_enqueue_script( 'sufb-pixel', plugins_url( '', __FILE__ ) . '/pixel.js', [], '1.0.1', FALSE );
  $options = get_option( 'sufbp_settings' );
  if ( isset( $options['sufbp_pixel_id'] ) && !empty( $options['sufbp_pixel_id'] ) ) {
    wp_localize_script( 'sufb-pixel', 'sufbpOptions', [ $options ] ) ;
  }
}
add_action( 'wp_enqueue_scripts', 'Stanford\FBPixel\enqueue_snippet');

function emit_img() {
?>
<noscript>
  <!-- stanford.edu Global Pixel - May 2021 -->
  <img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id=1199096620574484&ev=PageView&noscript=1"
  />
</noscript>
<?php
}
//add_action( 'wp_body_open', 'Stanford\FBPixel\emit_img' );

//// Settings

add_action( 'admin_menu', 'Stanford\FBPixel\add_admin_menu' );
add_action( 'admin_init', 'Stanford\FBPixel\settings_init' );

function add_admin_menu(  ) {
  add_options_page(
      'Stanford FB Pixel',
      'Stanford FB Pixel',
      'manage_options',
      'stanford_fb_pixel',
      'Stanford\FBPixel\options_page'
  );
}

function settings_init(  ) {
  register_setting( 'pluginPage', 'sufbp_settings' );
  add_settings_section(
      'sufbp_pluginPage_section',
      __( 'Site FaceBook Pixel ID', 'stanford' ),
      'Stanford\FBPixel\settings_section_callback',
      'pluginPage'
  );

  add_settings_field(
      'sufbp_pixel_id',
      __( 'FaceBook Pixel ID', 'stanford' ),
      'Stanford\FBPixel\pixel_id_render',
      'pluginPage',
      'sufbp_pluginPage_section'
  );
}

function pixel_id_render(  ) {
  $options = get_option( 'sufbp_settings' );
  ?>
  <input type='text' name='sufbp_settings[sufbp_pixel_id]' placeholder="<?php _e( 'e.g., 1199096620574484', 'stanford' ); ?>"
         value='<?php echo $options['sufbp_pixel_id']; ?>'
  />
  <?php
}

function settings_section_callback(  ) {
  echo __( 'If you have a FaceBook Pixel specific to this site, enter it below. It will be triggered in addition to Stanford\'s global pixel. ', 'stanford' );
  echo __( 'If you do not have a FaceBook Pixel specific to this site, leave this field blank.', 'stanford' );
}

function options_page(  ) {
  ?>
  <form action='options.php' method='post'>

    <h2>Stanford FB Pixel</h2>

    <?php
    settings_fields( 'pluginPage' );
    do_settings_sections( 'pluginPage' );
    submit_button();
    ?>

  </form>
  <?php
}

function get_settings_link( $anchor_text = "Settings" ) {
  $settings_url = esc_url( add_query_arg(
     'page', 'stanford_fb_pixel',
     admin_url( 'options-general.php' )
  ));
  return '<a href="' . $settings_url . '">' . $anchor_text . '</a>';
}

function add_settings_link( $links ) {
  $options_link = get_settings_link( __( 'Settings', 'stanford' ) );
  array_push( $links, $options_link );
  return $links;
}
add_filter( 'plugin_action_links_stanford-fb-pixel/stanford-fb-pixel.php', '\Stanford\FBPixel\add_settings_link' );

//// Activation

function activation_notice() {
  // take note of plugin activation
  set_transient( 'sufbp-activated', TRUE, 5 );
}
register_activation_hook( __FILE__, '\Stanford\FBPixel\activation_notice' );

function show_activation_warning() {
  // transient is only set if the plugin was activated within the last 5 seconds
  if ( get_transient( 'sufbp-activated' ) ) {
    $options_link = get_settings_link( __( 'the settings page', 'stanford' ) );
?>
    <div class="notice notice-error is-dismissible">
      <h3><?php _e( 'Stanford FB Pixel' ) ?></h3>
      <p>
        <?php _e( "Thank you for activating the Stanford FB Pixel.") ?>
        <?php _e( "If you have a site-specific FaceBook, please visit {$options_link} and enter the pixel id.") ?>
      </p>
    </div>
<?php
  }
}
add_action( 'admin_notices', '\Stanford\FBPixel\show_activation_warning' );