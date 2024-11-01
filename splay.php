<?php
/*
  Plugin Name: Youmbrella
  Plugin URI: https://youmbrella.com?ref=pluginWp
  Description: YouTube Protected Embed.
  Version: 1.0.0
  Author: Rafael Castelli
  Author URI: https://rafaelcastelli.com.br
  Text Domain: youmbrella
  Domain Path: /languages
  License: GPL v3

  Youmbrella for WordPress
  Copyright (C) 2012-2019, Rafael Castelli, castelli@dayleads.com

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
   */

//define('WP_DEBUG', true);
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'SPLAY_VERSION', '1.0.0' );
define( 'SPLAY_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
define( 'SPLAY_PLUGIN_URL', plugins_url( '/' , __FILE__ ) );
define( 'SPLAY_PLUGIN_FILE', __FILE__ );

require_once SPLAY_PLUGIN_DIR . 'includes/functions.php';

if(get_option('rcsplay_key')){
  add_action( 'init', 'gutenberg_rcsplay_block' );
};
add_action( 'admin_menu', 'rcsplay_options_page' );
add_action( 'plugins_loaded', 'splayoumbrella_load_plugin', 8 );

add_shortcode( 'youmbrella', 'splayoumbrella_shortcode_func' );

include SPLAY_PLUGIN_DIR . 'includes/pages/pages.php';
include SPLAY_PLUGIN_DIR . 'includes/api.php';
