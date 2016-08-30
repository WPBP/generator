<?php

use LightnCandy\LightnCandy;
use LightnCandy\Runtime;

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

function download_wpbp() {
  global $cmd, $clio, $white;
  $version = WPBP_VERSION;

  if ( $cmd[ 'dev' ] ) {
    $version = 'dev';
  }
  $clio->styleLine( 'Downloading ' . $version . ' package', $white );

//  if ( file_exists( getcwd() . '/plugin.zip' ) ) {
//    $zip = new ZipArchive;
//    $res = $zip->open( getcwd() .'plugin.zip' );
//    if ( $res === TRUE ) {
//	$zip->extractTo( '/' );
//	$zip->close();
//	echo 'woot!';
//    } else {
//	echo 'doh!';
//    }
//  }
}

function execute_generator( $config ) {
  global $yellow, $cmd, $clio;
  $files = get_files();
  foreach ( $files as $file ) {
    $file_content = file_get_contents( $file );
    if ( $cmd[ 'dev' ] ) {
	$lc = LightnCandy::compile( $file_content, Array(
			'flags' => LightnCandy::FLAG_RENDER_DEBUG
		  ) );
	$lc_prepare = LightnCandy::prepare( $lc );
	$newfile = $lc_prepare( $config, array( 'debug' => Runtime::DEBUG_ERROR_EXCEPTION ) );
    } else {
	$lc = LightnCandy::compile( $file_content );
	$lc_prepare = LightnCandy::prepare( $lc );
	$newfile = $lc_prepare( $config );
    }
    $newfile = replace_content_names( $config, $newfile );
    if ( $cmd[ 'dev' ] ) {
	if ( $newfile !== $file_content ) {
	  $clio->styleLine( 'Parsed ' . $file, $yellow );
	}
    }
    file_put_contents( $file, $newfile );
  }
}

function parse_config() {
  $config = array_to_var( json_decode( file_get_contents( getcwd() . '/wpbp.json' ), true ) );
  $config_default = array_to_var( json_decode( file_get_contents( dirname( __FILE__ ) .'/wpbp.json' ), true ) );
  foreach ( $config_default as $key => $value ) {
    if ( !isset( $config[ $key ] ) ) {
	$config[ $key ] = 'false';
    }
  }
  return $config;
}

//LightnCandy require an array bidimensional "key" = true, so we need toconvert a multidimensional in bidimensional
function array_to_var( $array ) {
  $newarray = array();
  //Get the json
  foreach ( $array as $key => $subarray ) {
    //Check if an array
    if ( is_array( $subarray ) ) {
	foreach ( $subarray as $subkey => $subvalue ) {
	  //Again it's an array with another inside
	  if ( is_array( $subvalue ) ) {
	    foreach ( $subvalue as $subsubkey => $subsubvalue ) {
		if ( !is_nan( $subsubkey ) ) {
		  //If empty lightcandy takes as true
		  $newarray[ $subkey . '_' . strtolower( str_replace( '/', '_', $subsubvalue ) ) ] = '';
		}
	    }
	  } else {
	    if ( !is_numeric( $subkey ) ) {
		if ( $subvalue === 'true' ) {
		  $newarray[ $key . '_' . strtolower( $subkey ) ] = '';
		} elseif ( $subvalue === 'false' ) {
		  $newarray[ $key . '_' . strtolower( $subkey ) ] = 'false';
		} else {
		  $newarray[ $key . '_' . strtolower( $subkey ) ] = $subvalue;
		}
	    } else {
		$newarray[ $key . '_' . strtolower( str_replace( '/', '_', $subvalue ) ) ] = '';
	    }
	  }
	}
    } else {
	//Is a single key
	if ( $subarray === 'true' ) {
	  $newarray[ $key ] = '';
	} elseif ( $subarray === 'false' ) {
	  $newarray[ $key ] = 'false';
	} else {
	  $newarray[ $key ] = $subvalue;
	}
    }
  }
  return $newarray;
}

function get_files() {
  $files = scandir( getcwd() );
  $list = array();
  foreach ( $files as $file ) {
    //do your work here
    if ( !in_array( $file, array( '.', '..', 'wpbp.json' ) ) ) {
	if ( !is_dir( $file ) ) {
	  if ( strpos( $file, '.php' ) || strpos( $file, '.txt' ) ) {
	    $list[] = $file;
	  }
	}
    }
  }
  return $list;
}

function replace_content_names( $config, $content ) {
  $ucword = '';
  $lower = '';
  $content = str_replace( "//WPBPGen\n", '', $content );
  $content = str_replace( "//WPBPGen", '', $content );
  $content = str_replace( "//\n", '', $content );
  $content = str_replace( "Plugin_Name", str_replace( ' ', '_', str_replace( '-', '_', $config[ 'plugin_name' ] ) ), $content );
  $content = str_replace( "plugin-name", str_replace( ' ', '-', strtolower( $config[ 'plugin_name' ] ) ), $content );
  preg_match_all( "/[A-Z]/", ucwords( strtolower( $config[ 'plugin_name' ] ) ), $ucword );
  $ucword = implode( '', $ucword[ 0 ] );
  $content = str_replace( "PN_", $ucword . '_', $content );
  $lower = strtolower( $ucword );
  $content = str_replace( "Pn_", ucwords( $lower ) . '_', $content );
  $content = str_replace( "pn_", $lower . '_', $content );
  return $content;
}
