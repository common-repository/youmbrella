<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// PLUGIN LOAD DEFAULTS
function splayoumbrella_load_plugin() {

  global $rcsplay;
  global $rcsplay_error_msg;

  if(!get_option('rcsplay_key')) {
    add_option('rcsplay_key', '');
  };
  if(!get_option('rcsplay_company')) {
    add_option('rcsplay_company', '');
  };
  if(!get_option('rcsplay_name')) {
    add_option('rcsplay_name', '');
  };
  if(!get_option('rcsplay_email')) {
    add_option('rcsplay_email', '');
  };
  if(!get_option('rcsplay_last_ping')) {
    add_option('rcsplay_last_ping', '');
  };
  if(!get_option('rcsplay_status')) {
    add_option('rcsplay_status', '0');
  };
  if(!get_option('rcsplay_start')) {
    add_option('rcsplay_start', '');
  };

  $rcsplay['key'] = get_option('rcsplay_key');
  $rcsplay['company'] = get_option('rcsplay_company');
  $rcsplay['name'] = get_option('rcsplay_name');
  $rcsplay['email'] = get_option('rcsplay_email');
  $rcsplay['ping'] = get_option('rcsplay_last_ping');
  $rcsplay['status'] = get_option('rcsplay_status');
  $rcsplay['start'] = get_option('rcsplay_start');

  $rcsplay['api'] = 'https://youmbrella.com/api';
  $rcsplay['headers'] = array(
    'cache-control' => 'no-cache',
    'accept' => 'application/json',
    'authorization' => 'Bearer '.$rcsplay['key']
  );

  $rcsplay = (object)$rcsplay;


  // Initialize admin section of plugin
  if( is_admin() ) {
    wp_enqueue_style('__rcsplay_admin__style_css', SPLAY_PLUGIN_URL . 'admin/css/styles.css');
  }else{
    wp_enqueue_style('__rcsplay_admin__style_css', SPLAY_PLUGIN_URL . 'public/css/styles.css');
  }

  return true;
}
// MENU ADMIN
function rcsplay_options_page() {
    add_menu_page(
        'Youmbrella',
        'Youmbrella ',
        'manage_options',
        SPLAY_PLUGIN_DIR . 'admin/view.php',
        null,
       'dashicons-video-alt3',
        80
    );
}
// Checar status servidor splay
function rcsplay_check_status($key = null) {
  global $rcsplay;

  if($key){
    if(strlen($key) < 10) {
      return false;
    };
    $token = $key;
  }else{
    if(!$rcsplay->key || strlen($rcsplay->key) < 10) {
      return false;
    };
    $token = $rcsplay->key;
  }


  $args = array(
    'headers' => array(
      'cache-control' => 'no-cache',
      'accept' => 'application/json',
      'authorization' => 'Bearer '.$token
    ),
    'redirection' => 0
  );

  $response = wp_remote_post( $rcsplay->api.'/ping', $args );
  $http_code = wp_remote_retrieve_response_code( $response );
  if($http_code === 200){
    splayoumbrella_load_plugin();
    return true;
  }else{
    return false;
  };
}
// Checar status servidor splay
function rcsplay_new_token($new_api_token) {
  // check user capabilities
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }
  // Sanitize
  $new_api_token = sanitize_text_field($new_api_token);
  // Validation
  if(strlen($new_api_token) < 10) {
    return;
  }

  global $rcsplay;
  global $rcsplay_error_msg;

  $args = array(
    'headers' => array(
      'cache-control' => 'no-cache',
      'accept' => 'application/json',
      'authorization' => 'Bearer '.$new_api_token
    ),
    'redirection' => 0
  );
  $response = wp_remote_post( $rcsplay->api.'/ping', $args );
  $http_code = wp_remote_retrieve_response_code( $response );
  if($http_code === 200){
    $body = wp_remote_retrieve_body( $response );
    $user = json_decode($body);

      update_option('rcsplay_key', $new_api_token);
      update_option('rcsplay_company', $user->company);
      update_option('rcsplay_name', $user->name);
      update_option('rcsplay_email', $user->email);
      update_option('rcsplay_status', '1');
      update_option('rcsplay_start', current_time( 'mysql' ));

      $rcsplay->key = get_option('rcsplay_key');
      $rcsplay->company = get_option('rcsplay_company');
      $rcsplay->name = get_option('rcsplay_name');
      $rcsplay->email = get_option('rcsplay_email');
      $rcsplay->status = 1;
      $rcsplay->start = get_option('rcsplay_start');

    return true;
  }else{
    return $rcsplay_error_msg = 'Não foi possível conectar utilizando a chave informada. Acesse youmbrella.com para mais informações.';
  };
};

function rcsplay_json_response($message = null, $code = 200, $resp = null){
    // clear the old headers
    header_remove();
    // set the actual code
    http_response_code($code);
    // set the header to make sure cache is forced
    header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
    // treat this as json
    header('Content-Type: application/json');
    $status = array(
        200 => '200 OK',
        400 => '400 Bad Request',
        422 => 'Unprocessable Entity',
        500 => '500 Internal Server Error'
        );
    // ok, validation error, or failure
    header('Status: '.$status[$code]);
    // return the encoded json
    return json_encode(array(
        'status' => $code, // success or not?
        'message' => $message,
        'resp' => $resp,
        ));
}
//GUTENBERG
function gutenberg_rcsplay_block() {
    wp_register_script(
        'gutenberg-rcsplay',
        plugins_url( 'gutenberg/block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor' )
    );

    register_block_type( 'gutenberg-rcsplay/rcsplay-block', array(
        'editor_script' => 'gutenberg-rcsplay',
        'attributes' => array(
          'backend' => 'gluglui'
        )
        // plugins_url( 'gutenberg/block.js', __FILE__ ),
    ) );
}
//CLASSIC EDITOR
function splayoumbrella_shortcode_func($attributes, $content = '') {
  extract( shortcode_atts( array(
		'align' => 'center'
	), $attributes ) );

  return <<<HTML
  <div id="rcsplay-block-div-iframe" class="wp-block-gutenberg-rcsplay-rcsplay-block">
    <iframe src="https://youmbrella.com/embed/{$content}" frameborder="0" id="rcsplay_view_iframe">SPLAY &#8211; Vídeo protegido</iframe>
  </div>
HTML;
};

//TinyMce
// Add TinyMCE button and plugin filters
function splayoumbrella_classic_button() {
	if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
		add_filter( 'mce_buttons', 'splayoumbrella_register_tinymce_button' );
		add_filter( 'mce_external_plugins', 'splayoumbrella_classic_button_script' );
	}
}
if(get_option('rcsplay_key')){
  add_action( 'admin_init', 'splayoumbrella_classic_button' );
};

// Add TinyMCE buttons onto the button array
function splayoumbrella_register_tinymce_button( $buttons ) {
	array_push( $buttons, 'myoumbrella_button' );
	return $buttons;
}

// Add TinyMCE button script to the plugins array
function splayoumbrella_classic_button_script( $plugin_array ) {
	$plugin_array['myoumbrella_button_script'] = SPLAY_PLUGIN_URL . 'includes/classic/button.js';  // Change this to reflect the path/filename to your js file
	return $plugin_array;
}

// Style the button with a dashicon icon instead of an image
function splayoumbrella_classic_button_dashicon() {
	?>
	<style type="text/css">
	.mce-i-myoumbrella_button:before {
		content: '\f236';
    color: #e55c3e;
		display: inline-block;
		-webkit-font-smoothing: antialiased;
		font: normal 20px/1 'dashicons';
		vertical-align: top;
	}
	</style>
	<?php
}
if(get_option('rcsplay_key')){
  add_action( 'admin_head', 'splayoumbrella_classic_button_dashicon' );
}
