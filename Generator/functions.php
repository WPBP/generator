<?php

use LightnCandy\LightnCandy;
use LightnCandy\Runtime;

function print_v( $label ) {
  global $cmd, $clio, $yellow;

  if ( $cmd[ 'verbose' ] ) {
    $clio->styleLine( $label, $yellow );
  }
}

function create_wpbp_json() {
  global $cmd, $clio, $red, $white;

  if ( $cmd[ 'json' ] ) {
    if ( !copy( dirname( __FILE__ ) . '/wpbp.json', getcwd() . '/wpbp.json' ) ) {
	$clio->styleLine( "Failed to copy wpbp.json...", $red );
    } else {
	$clio->styleLine( '😀 wpbp.json generated', $white );
	exit();
    }
  } else {
    if ( !file_exists( getcwd() . '/wpbp.json' ) ) {
	$clio->styleLine( "😡 wpbp.json file missing...", $red );
	$clio->styleLine( "😉 Generate with: wpbp-generator --json", $red );
	$clio->styleLine( "  Do your changes and execute again! Use the --dev parameter to get the development version!", $red );
	exit();
    }
  }
}

function download_wpbp() {
  global $cmd, $clio, $white;
  $version = WPBP_VERSION;

  if ( $cmd[ 'dev' ] ) {
    $version = 'master';
  }
  $clio->styleLine( '😎 Downloading ' . $version . ' package', $white );
  $download = file_get_contents( 'http://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered/archive/' . $version . '.zip' );
  file_put_contents( 'plugin.zip', $download );
  extract_wpbp();
}

function extract_wpbp() {
  global $cmd, $clio, $white, $red, $config;
  if ( file_exists( getcwd() . '/plugin.zip' ) ) {
    if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG ) ) {
	$clio->styleLine( "Folder " . WPBP_PLUGUIN_SLUG . ' already exist!', $red );
	exit();
    }
    $clio->styleLine( 'Extract Boilerplate', $white );
    $zip = new ZipArchive;
    $res = $zip->open( getcwd() . '/plugin.zip' );
    if ( $res === TRUE ) {
	$zip->extractTo( getcwd() . '/plugin_temp/' );
	$zip->close();
	$version = WPBP_VERSION;

	if ( $cmd[ 'dev' ] ) {
	  $version = 'master';
	}
	try {
	  rename( getcwd() . '/plugin_temp/WordPress-Plugin-Boilerplate-Powered-' . $version . '/plugin-name/', getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG );
	  rmrdir( getcwd() . '/plugin_temp/' );
	  if ( !$cmd[ 'dev' ] ) {
	    unlink( getcwd() . '/plugin.zip' );
	  }
	} catch ( Exception $e ) {
	  $clio->styleLine( $e, $red );
	}
	$clio->styleLine( 'Boilerplate Extracted ', $white );
    }
  } else {
    download_wpbp();
  }
}

function execute_generator( $config ) {
  global $cmd, $clio, $white;
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
    if ( $newfile !== $file_content ) {
	print_v( 'Parsed ' . $file );
    }
    file_put_contents( $file, $newfile );
  }

  echo "\n";
  $clio->styleLine( "Generation done, i am superfast! You: (ʘ_ʘ)", $white );
  execute_composer();
  git_init();
  grunt();
}

function parse_config() {
  global $clio, $red;
  $config = json_decode( file_get_contents( getcwd() . '/wpbp.json' ), true );
  if ( json_last_error() !== JSON_ERROR_NONE ) {
    $clio->styleLine( "😡 Your JSON is wrong!", $red );
    exit;
  }
  $config = array_to_var( $config );
  $config_default = array_to_var( json_decode( file_get_contents( dirname( __FILE__ ) . '/wpbp.json' ), true ) );
  foreach ( $config_default as $key => $value ) {
    if ( !isset( $config[ $key ] ) ) {
	$config[ $key ] = 'no';
    }
  }
  return $config;
}

//LightnCandy require an array bidimensional "key" = true, so we need to convert a multidimensional in bidimensional
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
		  $newarray[ $subkey . '_' . strtolower( str_replace( '/', '__', $subsubvalue ) ) ] = '';
		}
	    }
	  } else {
	    if ( !is_numeric( $subkey ) ) {
		if ( $subvalue === 'true' ) {
		  $newarray[ $key . '_' . strtolower( $subkey ) ] = 'true';
		} elseif ( $subvalue === 'false' ) {
		  $newarray[ $key . '_' . strtolower( $subkey ) ] = 'false';
		} else {
		  $newarray[ $key . '_' . strtolower( $subkey ) ] = $subvalue;
		}
	    } else {
		$newarray[ $key . '_' . strtolower( str_replace( '/', '__', $subvalue ) ) ] = '';
	    }
	  }
	}
    } else {
	//Is a single key
	if ( $subarray === 'true' ) {
	  $newarray[ $key ] = 'true';
	} elseif ( $subarray === 'false' ) {
	  $newarray[ $key ] = 'false';
	} else {
	  $newarray[ $key ] = $subvalue;
	}
    }
  }
  return $newarray;
}

function get_files( $path = null ) {
  global $config, $clio, $red, $white;
  if ( $path === null ) {
    $path = getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG;
  }
  $files = array();
  $clio->styleLine( "Rename in progress", $white );
  $dir_iterator = new RecursiveDirectoryIterator( $path );
  $iterator = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );
  foreach ( $iterator as $file => $object ) {
    if ( (!strpos( $file, '..' ) && !strpos( $file, '/.' )) && (strpos( $file, '.php' ) || strpos( $file, '.txt' ) || strpos( $file, 'Gruntfile.js' )) ) {
	$pathparts = pathinfo( $file );
	$newname = replace_content_names( $config, $pathparts[ 'filename' ] );
	$newname = $pathparts[ 'dirname' ] . DIRECTORY_SEPARATOR . $newname . '.' . $pathparts[ 'extension' ];
	if ( $newname !== $file ) {
	  try {
	    rename( $file, $newname );
	  } catch ( Exception $e ) {
	    $clio->styleLine( $e, $red );
	  }
	  $files[] = $newname;
	  print_v( 'Renamed ' . $file . ' to ' . $newname );
	} else {
	  $files[] = $file;
	}
    }
  }
  return $files;
}

function replace_content_names( $config, $content ) {
  $ucword = '';
  $lower = '';
  $content = str_replace( "//WPBPGen\n", '', $content );
  $content = str_replace( "//WPBPGen", '', $content );
  $content = str_replace( "//\n", '', $content );
  $content = str_replace( "Plugin_Name", str_replace( ' ', '_', str_replace( '-', '_', $config[ 'plugin_name' ] ) ), $content );
  $content = str_replace( "plugin-name", WPBP_PLUGUIN_SLUG, $content );
  preg_match_all( "/[A-Z]/", ucwords( strtolower( $config[ 'plugin_name' ] ) ), $ucword );
  $ucword = implode( '', $ucword[ 0 ] );
  $content = str_replace( "PN_", $ucword . '_', $content );
  $lower = strtolower( $ucword );
  $content = str_replace( "Pn_", ucwords( $lower ) . '_', $content );
  $content = str_replace( "pn_", $lower . '_', $content );
  return $content;
}

function execute_composer() {
  global $config, $clio, $white;
  $composer = json_decode( file_get_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '/composer.json' ), true );
  foreach ( $config as $key => $value ) {
    if ( strpos( $key, 'libraries_' ) !== false ) {
	if ( $value === 'no' ) {
	  $package = str_replace( 'libraries_', '', $key );
	  $package = str_replace( '__', '/', $package );
	  if ( isset( $composer[ 'require' ][ $package ] ) ) {
	    unset( $composer[ 'require' ][ $package ] );
	  }
	  foreach ( $composer[ 'extra' ][ 'installer-paths' ] as $folder => $packageset ) {
	    foreach ( $packageset as $ispackage => $value ) {
		if ( $value === $package ) {
		  unset( $composer[ 'extra' ][ 'installer-paths' ][ $folder ][ $ispackage ] );
		}
	    }
	  }
	  print_v( 'Package ' . $package . ' removed!' );
	}
    }
  }
  file_put_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '/composer.json', json_encode( $composer, JSON_PRETTY_PRINT ) );
  $clio->styleLine( '😀 Composer install in progress', $white );
  $output = '';
  exec( 'cd ' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '; composer update 2>&1', $output );
}

function git_init() {
  global $config, $clio, $white;

  if ( $config[ 'git-repo' ] === 'true' ) {
    exec( 'cd ' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '; git init' );
    $clio->styleLine( '😀 .git folder generated', $white );
  }
}

function grunt() {
  global $config, $clio, $white;

  if ( $config[ 'coffeescript' ] === 'false' ) {
    rmrdir( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . DIRECTORY_SEPARATOR . 'admin/assets/coffee' );
    rmrdir( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . DIRECTORY_SEPARATOR . 'public/assets/coffee' );

    $grunt = file( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '/Gruntfile.js' );
    $newgrunt = array();
    foreach ( $grunt as $line => $content ) {
	if ( !(($line >= 45 && $line <= 84 ) || $line === 91 || $line === 92 || $line === 96 || $line === 105 || $line === 110) ) {
	  $newgrunt[] = $grunt[ $line ];
	}
    }
    file_put_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '/Gruntfile.js', $newgrunt );
    $clio->styleLine( '😀 Coffeescript removed', $white );
  }
  $clio->styleLine( '😀 Grunt install in progress', $white );
  $output = '';
  exec( 'cd ' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '; npm install 2>&1', $output );
}
