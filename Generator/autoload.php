<?php

define("WPBP_VERSION", "2.0.0");
// Use composer autoloader
require_once(dirname( __FILE__ ) . '/../vendor/autoload.php');
require_once(dirname( __FILE__ ) . '/functions.php');

//Load libraries
use Clio\Clio;
use Clio\Style\Style;

//Initiate Libraries
$cmd = new Commando\Command();
$white = new Style();
$white->setTextColor( "white" )->setBold( true )->setUnderscore();
$red = new Style();
$red->setTextColor( "red" )->setBold( true );
$yellow = new Style();
$yellow->setTextColor( "yellow" )->setBold( true );
$clio = new Clio();
//Set info for the script
$cmd->setHelp( 'WPBP Generator enable you to get a customized version based from your needs a WordPress Plugin Boilerplate Powered' );
$cmd->option( 'dev' )->describedAs( 'Download from the master branch' )->boolean();
$cmd->option( 'verbose' )->describedAs( 'Get a verbose output' )->boolean();
$cmd->option( 'json' )->describedAs( 'Generate a wpbp.json file in the folder' )->boolean();

//Generate the wpbp.json file
create_wpbp_json();

$config = parse_config();

download_wpbp();

execute_generator( $config );