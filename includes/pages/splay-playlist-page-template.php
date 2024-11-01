<?php
/*
Template Name: Youmbrella Playlist
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $post;
$playlist = get_post_meta($post->ID, '_splay_playlist', true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php single_post_title(); ?></title>
  <style type="text/css">
  body, html
  {
    margin: 0; padding: 0; height: 100%; overflow: hidden;
  }

  #content
  {
    position:absolute; left: 0; right: 0; bottom: 0; top: 0px;
  }
  </style>
</head>
<body>
  <div id="content">
    <iframe width="100%" height="100%" frameborder="0" src="https://youmbrella.com/p/<?php echo $playlist; ?>"></iframe>
  </div>
</body>
</html>
