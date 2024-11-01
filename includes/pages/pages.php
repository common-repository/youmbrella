<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class SplayoumbrellaPageTemplater {

  /**
  * A reference to an instance of this class.
  */
  private static $instance;

  /**
  * The array of templates that this plugin tracks.
  */
  protected $templates;

  /**
  * Returns an instance of this class.
  */
  public static function get_instance() {

    if ( null == self::$instance ) {
      self::$instance = new SplayoumbrellaPageTemplater();
    }

    return self::$instance;

  }

  /**
  * Initializes the plugin by setting filters and administration functions.
  */
  private function __construct() {

    $this->templates = array();


    // Add a filter to the attributes metabox to inject template into the cache.
    if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

      // 4.6 and older
      add_filter(
        'page_attributes_dropdown_pages_args',
        array( $this, 'register_splayoumbrella_templates' )
      );

    } else {

      // Add a filter to the wp 4.7 version attributes metabox
      add_filter(
        'theme_page_templates', array( $this, 'add_new_splayoumbrella_template' )
      );

    }

    // Add a filter to the save post to inject out template into the page cache
    add_filter(
      'wp_insert_post_data',
      array( $this, 'register_splayoumbrella_templates' )
    );


    // Add a filter to the template include to determine if the page has our
    // template assigned and return it's path
    add_filter(
      'template_include',
      array( $this, 'view_splayoumbrella_template')
    );


    // Add your templates to this array.
    $this->templates = array(
      'splay-playlist-page-template.php' => 'Youmbrella Playlist',
      'splay-landing-page-template.php' => 'Youmbrella Landing Page',
    );

  }

  /**
  * Adds our template to the page dropdown for v4.7+
  *
  */
  public function add_new_splayoumbrella_template( $posts_templates ) {
    $posts_templates = array_merge( $posts_templates, $this->templates );
    return $posts_templates;
  }

  /**
  * Adds our template to the pages cache in order to trick WordPress
  * into thinking the template file exists where it doens't really exist.
  */
  public function register_splayoumbrella_templates( $atts ) {

    // Create the key used for the themes cache
    $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

    // Retrieve the cache list.
    // If it doesn't exist, or it's empty prepare an array
    $templates = wp_get_theme()->get_page_templates();
    if ( empty( $templates ) ) {
      $templates = array();
    }

    // New cache, therefore remove the old one
    wp_cache_delete( $cache_key , 'themes');

    // Now add our template to the list of templates by merging our templates
    // with the existing templates array from the cache.
    $templates = array_merge( $templates, $this->templates );

    // Add the modified cache to allow WordPress to pick it up for listing
    // available templates
    wp_cache_add( $cache_key, $templates, 'themes', 1800 );

    return $atts;

  }

  /**
  * Checks if the template is assigned to the page
  */
  public function view_splayoumbrella_template( $template ) {

    // Get global post
    global $post;

    // Return template if post is empty
    if ( ! $post ) {
      return $template;
    }

    // Return default template if we don't have a custom one defined
    if ( ! isset( $this->templates[get_post_meta(
      $post->ID, '_wp_page_template', true
      )] ) ) {
        return $template;
      }

      $file = plugin_dir_path( __FILE__ ). get_post_meta(
        $post->ID, '_wp_page_template', true
      );

      // Just to be safe, we check if the file exist first
      if ( file_exists( $file ) ) {
        return $file;
      } else {
        echo $file;
      }

      // Return template
      return $template;

    }

  }
  add_action( 'plugins_loaded', array( 'SplayoumbrellaPageTemplater', 'get_instance' ) );
  add_action('add_meta_boxes', 'add_splayoumbrella_pages_meta');
  function add_splayoumbrella_pages_meta(){
    global $post;

    if(!empty($post)){
      $pageTemplate = get_post_meta($post->ID, '_wp_page_template', true);

      if($pageTemplate == 'splay-playlist-page-template.php'){
        add_meta_box(
          'splay_playlist_meta', // $id
          'Youmbrella Playlist Select', // $title
          'splayoumbrella_id_meta_playlist_form', // $callback
          'page', // $page
          'normal', // $context
          'high'
        ); // $priority
      }

      if($pageTemplate == 'splay-landing-page-template.php'){
        add_meta_box(
          'splay_landing_meta', // $id
          'Youmbrella Landing Page Select', // $title
          'splayoumbrella_id_meta_landing_form', // $callback
          'page', // $page
          'normal', // $context
          'high'
        ); // $priority
      }
    }
  }

  function splayoumbrella_id_meta_playlist_form($post){
    global $rcsplay;
    if(!$rcsplay->key || strlen($rcsplay->key) < 10) {
      ?>
        <div class="">
          <div class="status negative col col-6">
            <p>
              Você ainda não conectou a sua conta Youmbrella. Pegue <a href="https://youmbrella.com" target="_blank">aqui</a> sua chave de acesso grátis
            </p>
          </div>
        </div>
      <?php
      return false;
    };
    $args = array(
      'headers' => $rcsplay->headers,
      'redirection' => 0
    );
    $response = wp_remote_get( $rcsplay->api.'/playlists', $args );
    $http_code = wp_remote_retrieve_response_code( $response );
    if($http_code === 200){
      $playlists = json_decode(wp_remote_retrieve_body( $response ));
      if(count($playlists) > 0){
        $selected = get_post_meta($post->ID, '_splay_playlist', true);
        ?>
        <label for="splay_playlist_select">Selecione uma playlist: </label>
        <select name='splay_playlist_selected' id='splay_playlist_selected' class="postbox">
          <option value="">Selecione uma Playlist</option>
          <?php foreach ($playlists as $k => $p): ?>
            <option value="<?php echo esc_attr($k); ?>" <?php selected($selected, $k); ?>><?php echo esc_html($p); ?></option>
          <?php endforeach; ?>
        </select>
        <?php
      }else{
        echo 'Nenhuma lista enviada';
      };
    }else{
      echo 'Erro ao conectar com Youmbrella.com';
    };
  }

  function splayoumbrella_id_meta_landing_form($post){
    global $rcsplay;
    if(!$rcsplay->key || strlen($rcsplay->key) < 10) {
      ?>
        <div class="">
          <div class="status negative col col-6">
            <p>
              Você ainda não conectou a sua conta Youmbrella. Pegue <a href="https://youmbrella.com" target="_blank">aqui</a> sua chave de acesso grátis
            </p>
          </div>
        </div>
      <?php
      return false;
    };
    $args = array(
      'headers' => $rcsplay->headers,
      'redirection' => 0
    );
    $response = wp_remote_get( $rcsplay->api.'/landings', $args );
    $http_code = wp_remote_retrieve_response_code( $response );
    if($http_code === 200){
      $landings = json_decode(wp_remote_retrieve_body( $response ));
      if(count($landings) > 0){
        $selected = get_post_meta($post->ID, '_splay_landing', true);
        ?>
        <label for="splay_landing_select">Selecione uma landing page: </label>
        <select name='splay_landing_selected' id='splay_landing_selected' class="postbox">
          <option value="">Selecione uma Landing Page</option>
          <?php foreach ($landings as $k => $l): ?>
            <option value="<?php echo esc_attr($k); ?>" <?php selected($selected, $k); ?>><?php echo esc_html($l); ?></option>
          <?php endforeach; ?>
        </select>
        <?php
      }else{
        echo 'Nenhuma landing page disponível';
      };
    }else{
      echo 'Erro ao conectar com Youmbrella.com';
    };
  }

  function splayoumbrella_save_postdata($post_id){
    if ( ! current_user_can( 'edit_pages' ) ) {
      return;
    };
    if (array_key_exists('splay_playlist_selected', $_POST)) {
      update_post_meta(
        $post_id,
        '_splay_playlist',
        $_POST['splay_playlist_selected']
      );
    }
    if (array_key_exists('splay_landing_selected', $_POST)) {
      update_post_meta(
        $post_id,
        '_splay_landing',
        $_POST['splay_landing_selected']
      );
    }
  }
  add_action('save_post', 'splayoumbrella_save_postdata');
