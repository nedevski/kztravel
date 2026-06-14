<?php
defined( 'ABSPATH' ) || exit;

define( 'KZTRAVEL_VERSION', '1.0.0' );
define( 'KZTRAVEL_DIR', get_template_directory() );
define( 'KZTRAVEL_URI', get_template_directory_uri() );

require_once KZTRAVEL_DIR . '/inc/setup.php';
require_once KZTRAVEL_DIR . '/inc/post-types.php';
require_once KZTRAVEL_DIR . '/inc/admin-trip-editor.php';
require_once KZTRAVEL_DIR . '/inc/acf-fields.php';
require_once KZTRAVEL_DIR . '/inc/options.php';
require_once KZTRAVEL_DIR . '/inc/strings.php';
require_once KZTRAVEL_DIR . '/inc/formatters.php';
require_once KZTRAVEL_DIR . '/inc/trip-utils.php';
require_once KZTRAVEL_DIR . '/inc/trip-meta-boxes.php';
require_once KZTRAVEL_DIR . '/inc/taxonomy.php';
require_once KZTRAVEL_DIR . '/inc/filters.php';
