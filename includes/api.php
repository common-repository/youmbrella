<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function splay_ajax_api_handler() {
  if ( ! current_user_can( 'edit_posts' ) ) {
    echo rcsplay_json_response('Usuário sem permissão', 500);
    exit;
  }
  global $rcsplay;
  if(!isset($_POST['video_id'])){
    echo rcsplay_json_response('Parâmetro obrigatório ausente', 500);
    exit;
  };
  $vid = sanitize_text_field($_POST['video_id']);
  // Validation
  if(strlen($vid) < 3) {
    return false;
  }
  if(!$rcsplay) {
    return false;
  };
  if(!$rcsplay->api) {
    return false;
  };
  if(!$rcsplay->headers) {
    return false;
  };
  // $rcsplay->headers = [];
  // YOUTUBE REQUEST
  $endpoint = "https://www.youtube.com/oembed?url=http://www.youtube.com/watch?v={$vid}&format=json";
  $json = wp_remote_get($endpoint);
  $json_response = wp_remote_retrieve_response_code($json);
  if($json_response <> 200){
    echo rcsplay_json_response('Vídeo não econtrado. Confira o código ou URL inserido', 422);
    exit;
  }else{
    $body_post = json_decode(wp_remote_retrieve_body($json), true);
    $body_post['video_id'] = $vid;
    $args = array(
      'headers' => $rcsplay->headers,
      'redirection' => 0,
      'body' => $body_post
    );

    $response = wp_remote_post( $rcsplay->api.'/video', $args );
    $http_code = wp_remote_retrieve_response_code( $response );
    $body_resp = json_decode(wp_remote_retrieve_body($response));

    if($http_code === 200){
      if(!isset($body_resp)){
        echo rcsplay_json_response('Não foi possível salvar o vídeo. Visite youmbrella.com para mais informações', 500);
        exit;
      };
      if(!$body_resp->embedkey){
        echo rcsplay_json_response('Não foi possível salvar o vídeo. Visite youmbrella.com para mais informações', 500);
        exit;
      };
      echo rcsplay_json_response('OK', 200, $body_resp);
    }elseif($http_code === 302){
      echo rcsplay_json_response('Não foi possível autenticar. Visite youmbrella.com para mais informações', 500);
      exit;
    }else{
      $remote_error = ($body_resp->message) ? $body_resp->message : 'Não foi possível salvar o vídeo.';
      echo rcsplay_json_response($remote_error, 422);
      exit;
    };
  };
  // Don't forget to stop execution afterward.
  wp_die();
}
add_action( 'wp_ajax_splayoumbrella', 'splay_ajax_api_handler' );
?>
