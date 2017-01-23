<?php

define( 'WPBP_VERSION', '2.0.4' );
require_once(dirname( __FILE__ ) . '/functions.php');

// Load libraries
use Clio\Clio;
use Clio\Style\Style;

// Initiate Libraries
$cmd = new Commando\Command();
$white = new Style();
$white->setTextColor( 'white' )->setBold( true )->setUnderscore();
$red = new Style();
$red->setTextColor( 'red' )->setBold( true );
$yellow = new Style();
$yellow->setTextColor( 'yellow' )->setBold( true );
$clio = new Clio();
// Set info on shell for the script
$cmd->setHelp( 'WPBP Generator enable you to get a customized version based from your needs a WordPress Plugin Boilerplate Powered' );
$cmd->option( 'dev' )->describedAs( 'Download from the master branch' )->boolean();
$cmd->option( 'verbose' )->describedAs( 'Get a verbose output' )->boolean();
$cmd->option( 'json' )->describedAs( 'Generate a wpbp.json file in the folder' )->boolean();
$cmd->option( 'no-download' )->describedAs( 'Do you want to execute composer and npm manually? This is your flag' )->boolean();
$clio->styleLine( "(>'-')> WPBP Code Generator by Mte90", $white );
echo "\n";
// Generate the wpbp.json file
create_wpbp_json();
// Load the config with defaults
$config = parse_config();
// Create a constant with the slug of the new plugin
define( 'WPBP_PLUGIN_SLUG', str_replace( ' ', '-', strtolower( $config[ 'plugin_name' ] ) ) );
// Check if a folder with that name already exist
if ( file_exists( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG ) ) {
  $clio->styleLine( 'Folder ' . WPBP_PLUGIN_SLUG . ' already exist!', $red );
  die( 0 );
}
// Unpack the boilerplate
extract_wpbp();
// Magic in progress
execute_generator( $config );
// Done!
echo "\n";
$clio->styleLine( 'Done, I am superfast! You:(ʘ_ʘ)', $white );
$clio->styleLine( 'Don\'t forget to look on https://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered/wiki', $white );
