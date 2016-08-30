<?php

use LightnCandy\LightnCandy;

function print_v( $label ) {
  global $cmd, $clio;

  if ( $cmd[ 'verbose' ] ) {
    $clio->textColor( "white" )->setBold()->line( $label )->nl();
  }
}

function create_wpbp_json() {
  global $cmd, $clio, $red, $white;

  if ( $cmd[ 'json' ] ) {
    if ( !copy( dirname( __FILE__ ) . '/wpbp.json', getcwd() . '/wpbp.json' ) ) {
	$clio->styleLine( "Failed to copy wpbp.json...", $red );
    } else {
	$clio->styleLine( 'wpbp.json generated', $white );
    }
  } else {
    if ( !file_exists( getcwd() . '/wpbp.json' ) ) {
	$clio->styleLine( "wpbp.json file missing...", $red );
	$clio->styleLine( "Generate with: wpbp-generator --json", $red );
	exit();
    }
  }
}

function parse_wpbp_json() {
//
//  $data = array( 'name' => $config[ 'name' ], 'value' => 10000, 'testo' => true );
//  $template = file_get_contents( 'test.php' );
//  $php = LightnCandy::compile( $template );
//  $render = LightnCandy::prepare( $php );
//
//  print_v( 'Siamo in verbose' );
//
//  $dev = $cmd[ 'dev' ] ? ucwords( $cmd[ 0 ] ) : $cmd[ 0 ];
//
//  if ( $dev ) {
//    $clio->textColor( "white" )->setBold()->line( "Modalita dev" )->nl();
//  }
//
//// Save the compiled PHP code into a php file
//  file_put_contents( 'render.php', str_replace( "//WPBPGen\n", '', $render( $data ) ) );
//  $clio->styleLine( "Fatto", $h1 );
//  $clio->textColor( "blue" )->setUnderscore()->out( "Some regular text underneath the title" )->nl( 2 );
}

//LightnCandy require an array bidimensional "key" = true, so we need toconvert a multidimensional in bidimensional
function array_to_var( $array ) {
  $newarray = array();
  foreach ( $array as $key => $subarray ) {
    if ( is_array( $subarray ) ) {
	foreach ( $subarray as $subkey => $subvalue ) {
	  if ( is_array( $subvalue ) ) {
	    foreach ( $subvalue as $subsubkey => $subsubvalue ) {
		if ( !is_nan( $subsubkey ) ) {
		  $newarray[ $subkey . '_' . strtolower( $subsubvalue ) ] = true;
		}
	    }
	  } else {
	    if ( !is_numeric( $subkey ) ) {
		$newarray[ $key . '_' . strtolower($subkey) ] = $subvalue;
	    } else {
		$newarray[ $key . '_' . strtolower($subvalue) ] = true;
	    }
	  }
	}
    } else {
	$newarray[ $key ] = ( bool ) $subarray;
    }
  }

  return $newarray;
}
