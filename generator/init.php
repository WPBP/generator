<?php

define( 'WPBP_VERSION', '3.2.5' );
require_once(dirname( __FILE__ ) . '/miscellaneous.php');
require_once(dirname( __FILE__ ) . '/composer.php');
require_once(dirname( __FILE__ ) . '/tools.php');
require_once(dirname( __FILE__ ) . '/rename.php');
require_once(dirname( __FILE__ ) . '/remove.php');
require_once(dirname( __FILE__ ) . '/wpbp.php');

// Load libraries
use Clio\Clio;
use Clio\Styling\Style;

// Initiate Libraries
$cmd = new Commando\Command();
$clio = new Clio();
$info   = new Style();
$error  = new Style();
$notice = new Style();

// Set info on shell for the script
$cmd->setHelp( 'WPBP Generator enable you to get a customized version (based on your needs) of WordPress Plugin Boilerplate Powered.' );
$cmd->option( 'dark' )->describedAs( 'Use a dark theme for console output.' )->boolean();
$cmd->option( 'dev' )->describedAs( 'Download from the master branch (the development version).' )->boolean();
$cmd->option( 'verbose' )->describedAs( 'Verbose output. Because this can be helpful for debugging!' )->boolean();
$cmd->option( 'json' )->describedAs( 'Generate a wpbp.json file in the current folder. Suggested to use the WordPress plugin folder.' )->boolean();
$cmd->option( 'no-download' )->describedAs( 'Do you want to execute composer and npm manually? This is your flag!' )->boolean();

$output = '';
exec( 'clear', $output );
set_color_scheme();
$clio->display( "(>'-')> WPBP Code Generator" )->style( $info )->newLine();
if ( $cmd[ 'dark' ] ) {
    $clio->clear()->display( "!! Dark color scheme in use !!" )->style( $info )->newLine()->newLine();
} else {
    $clio->clear()->display( "!! Light color scheme in use !!" )->style( $info )->newLine()->newLine();
}

// Generate the wpbp.json file
create_wpbp_json();
// Load the config with defaults
$config = parse_config();
// Create a constant with the slug of the new plugin
define( 'WPBP_PLUGIN_SLUG', str_replace( ' ', '-', strtolower( $config[ 'plugin_name' ] ) ) );
// Check if a folder with that name already exist
if ( file_exists( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG ) ) {
	$clio->style( $error )->display( 'Folder ' . WPBP_PLUGIN_SLUG . ' already exist!' )->newLine();
	die( 0 );
}
// Unpack the boilerplate
extract_wpbp();
// Magic in progress
execute_generator( $config );
// Done!
$clio->clear()->style( $info )->display( "Last cleanings!" )->newLine();
remove_empty_folders();
// Another round to remove the folder that wasn't cleaned at first round
remove_empty_folders();
execute_composer();
$clio->clear()->style( $info )->display( "Done, I am superfast!" )->newLine()->newLine();
$clio->display( "👉 Don't forget to look on https://wpbp.github.io/wiki.html" )->newLine();
$clio->display( "Love WordPress-Plugin-Boilerplate-Powered? Please consider supporting our collective:" )->newLine();
$clio->display( "👉 https://opencollective.com/WordPress-Plugin-Boilerplate-Powered/donate" )->newLine()->clear();
