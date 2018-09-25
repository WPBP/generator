<?php

define( 'WPBP_VERSION', '2.3' );
require_once(dirname( __FILE__ ) . '/miscellaneous.php');
require_once(dirname( __FILE__ ) . '/composer.php');
require_once(dirname( __FILE__ ) . '/tools.php');
require_once(dirname( __FILE__ ) . '/rename.php');
require_once(dirname( __FILE__ ) . '/remove.php');
require_once(dirname( __FILE__ ) . '/wpbp.php');

// Load libraries
use Clio\Clio;

// Initiate Libraries
$cmd = new Commando\Command();
$clio = new Clio();
// Set info on shell for the script
$cmd->setHelp( 'WPBP Generator enable you to get a customized version (based on your needs) of WordPress Plugin Boilerplate Powered.' );
$cmd->option( 'dark' )->describedAs( 'Use a dark theme for console output.' )->boolean();
$cmd->option( 'dev' )->describedAs( 'Download from the master branch (the development version).' )->boolean();
$cmd->option( 'verbose' )->describedAs( 'Verbose output. Because this can be helpful for debugging!' )->boolean();
$cmd->option( 'json' )->describedAs( 'Generate a wpbp.json file in the current folder. Suggested to use the WordPress plugin folder.' )->boolean();
$cmd->option( 'no-download' )->describedAs( 'Do you want to execute composer and npm manually? This is your flag!' )->boolean();

set_color_scheme();
$clio->styleLine( "(>'-')> WPBP Code Generator by Mte90", $info );
if ( $cmd[ 'dark' ] ) {
	echo "!! Dark color scheme in use !!" . PHP_EOL;
} else {
	echo "!! Light color scheme in use !!" . PHP_EOL;
}

echo PHP_EOL;
// Generate the wpbp.json file
create_wpbp_json();
// Load the config with defaults
$config = parse_config();
// Create a constant with the slug of the new plugin
define( 'WPBP_PLUGIN_SLUG', str_replace( ' ', '-', strtolower( $config[ 'plugin_name' ] ) ) );
// Check if a folder with that name already exist
if ( file_exists( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG ) ) {
	$clio->styleLine( 'Folder ' . WPBP_PLUGIN_SLUG . ' already exist!', $error );
	die( 0 );
}
// Unpack the boilerplate
extract_wpbp();
// Magic in progress
execute_generator( $config );
// Done!
echo PHP_EOL;
$clio->styleLine( 'Done, I am superfast!', $info );
$clio->styleLine( 'Don\'t forget to look on https://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered/wiki', $info );
$clio->styleLine( 'Love WordPress-Plugin-Boilerplate-Powered? Please consider supporting our collective:👉 https://opencollective.com/WordPress-Plugin-Boilerplate-Powered/donate', $info );
