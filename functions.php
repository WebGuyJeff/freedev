<?php
namespace BigupWeb\Freedev;

/**
 * Freedev Theme Entry.
 *
 * @package freedev
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright 2023 Jefferson Real
 */

// Development debugging.
$enable_debug = true;

// Set global constants.
define( 'FREEDEV_DEBUG', $enable_debug );
define( 'FREEDEV_PATH', trailingslashit( __DIR__ ) );
define( 'FREEDEV_URL', trailingslashit( get_site_url( null, strstr( __DIR__, '/wp-content/' ) ) ) );

// Register namespaced autoloader.
$namespace = 'BigupWeb\\Freedev\\';
$root      = FREEDEV_PATH . 'classes/';
require_once $root . 'autoload.php';

// Setup the plugin.
$Theme_Setup = new Theme_Setup();
$Theme_Setup->all();
