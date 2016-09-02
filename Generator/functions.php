<?php

use LightnCandy\LightnCandy;
use LightnCandy\Runtime;

/**
 * Print the label if the shell is executed as verbose
 * 
 * @global object $cmd
 * @global object $clio
 * @global object $yellow
 * @param string $label
 */
function print_v( $label ) {
  global $cmd, $clio, $yellow;

  if ( $cmd[ 'verbose' ] ) {
    $clio->styleLine( $label, $yellow );
  }
}

/**
 * Generate a new wpbp.json in the folder
 * 
 * @global object $cmd
 * @global object $clio
 * @global object $red
 * @global object $white
 */
function create_wpbp_json() {
  global $cmd, $clio, $red, $white;

  if ( $cmd[ 'json' ] ) {
    if ( !copy( dirname( __FILE__ ) . '/wpbp.json', getcwd() . '/wpbp.json' ) ) {
	$clio->styleLine( 'Failed to copy wpbp.json...', $red );
    } else {
	$clio->styleLine( '😀 wpbp.json generated', $white );
	exit();
    }
  } else {
    if ( !file_exists( getcwd() . '/wpbp.json' ) ) {
	$clio->styleLine( '😡 wpbp.json file missing...', $red );
	$clio->styleLine( '😉 Generate with: wpbp-generator --json', $red );
	$clio->styleLine( 'Forget a Q&A system and fill that json with your custom configuration!', $red );
	$clio->styleLine( '  Do your changes and execute again! Use the --dev parameter to get the development version!', $red );
	exit();
    }
  }
}

/**
 * Download the boilerplate based from theversion asked
 * 
 * @global object $cmd
 * @global object $clio
 * @global object $white
 */
function download_wpbp() {
  global $cmd, $clio, $white, $red;
  $version = WPBP_VERSION;

  if ( $cmd[ 'dev' ] ) {
    $version = 'master';
  }
  $clio->styleLine( '😎 Downloading ' . $version . ' package', $white );

  $download = @file_get_contents( 'http://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered/archive/' . $version . '.zip' );
  if ( $download === false ) {
    $clio->styleLine( '😡 The ' . $version . ' version is not avalaible', $red );
    die();
  }
  file_put_contents( 'plugin.zip', $download );

  extract_wpbp();
}

/**
 * Extract the boilerplate
 * 
 * @global object $cmd
 * @global object $clio
 * @global object $white
 * @global object $red
 */
function extract_wpbp() {
  global $cmd, $clio, $white, $red;
  if ( file_exists( getcwd() . '/plugin.zip' ) ) {
    if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG ) ) {
	$clio->styleLine( 'Folder ' . WPBP_PLUGUIN_SLUG . ' already exist!', $red );
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
    //If the package not exist download it
    download_wpbp();
  }
}

/**
 * Execute Lightncandy on the boilerplate files
 * 
 * @global object $cmd
 * @global object $clio
 * @global object $white
 * @param array $config
 */
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
  $clio->styleLine( 'Generation done, i am superfast! You: (ʘ_ʘ)', $white );
  execute_composer();
  git_init();
  grunt();
}

/**
 * Load user wpbp.json and add the terms missing as false
 * 
 * @global object $clio
 * @global object $red
 * @return array
 */
function parse_config() {
  global $clio, $red;
  $config = json_decode( file_get_contents( getcwd() . '/wpbp.json' ), true );
  //Detect a misleading json file
  if ( json_last_error() !== JSON_ERROR_NONE ) {
    $clio->styleLine( '😡 Your JSON is broken!', $red );
    exit;
  }
  $config = array_to_var( $config );
  $config_default = array_to_var( json_decode( file_get_contents( dirname( __FILE__ ) . '/wpbp.json' ), true ) );
  foreach ( $config_default as $key => $value ) {
    if ( !isset( $config[ $key ] ) ) {
	$config[ $key ] = 'false';
    }
  }
  return $config;
}

/**
 * LightnCandy require an array bidimensional "key" = true, so we need to convert a multidimensional in bidimensional
 * 
 * @param array $array
 * @return array
 */
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
		$newarray[ $key . '_' . strtolower( $subkey ) ] = $subvalue;
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

/**
 * Get the wpbp files, rename it and return a list of files ready to be parsed
 * 
 * @global array $config
 * @global object $clio
 * @global object $red
 * @global object $white
 * @param string $path
 * @return array
 */
function get_files( $path = null ) {
  global $config, $clio, $red, $white;
  if ( $path === null ) {
    $path = getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG;
  }
  $files = $list = array();
  $clio->styleLine( 'Rename in progress', $white );
  $dir_iterator = new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS );
  $iterator = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );
  //Move in array with only paths
  foreach ( $iterator as $file => $object ) {
    $list[] = $file;
  }
  foreach ( $list as $file ) {
    if ( !file_exists( $file ) ) {
	continue;
    }
    if ( remove_file( $file ) ) {
	continue;
    }
    if ( (strpos( $file, '.php' ) || strpos( $file, '.txt' ) || strpos( $file, 'Gruntfile.js' ) ) ) {
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

/**
 * Replace some keywords with based ones from the plugin name
 * 
 * @param array $config
 * @param string $content
 * @return string
 */
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

/**
 * Clean teh composer files and execute the install of the packages
 * 
 * @global array $config
 * @global object $clio
 * @global object $white
 */
function execute_composer() {
  global $config, $cmd, $clio, $white;
  $composer = json_decode( file_get_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '/composer.json' ), true );
  foreach ( $config as $key => $value ) {
    if ( strpos( $key, 'libraries_' ) !== false ) {
	if ( $value === 'false' ) {
	  $package = str_replace( 'libraries_', '', $key );
	  $package = str_replace( '__', '/', $package );
	  if ( isset( $composer[ 'require' ][ $package ] ) ) {
	    unset( $composer[ 'require' ][ $package ] );
	  }
	  foreach ( $composer[ 'extra' ][ 'installer-paths' ] as $folder => $packageset ) {
	    if ( empty( $composer[ 'extra' ][ 'installer-paths' ][ $folder ] ) ) {
		unset( $composer[ 'extra' ][ 'installer-paths' ][ $folder ] );
	    } else {
		foreach ( $packageset as $ispackage => $value ) {
		  if ( $value === $package ) {
		    unset( $composer[ 'extra' ][ 'installer-paths' ][ $folder ][ $ispackage ] );
		  }
		}
	    }
	  }
	  if ( strpos( $package, 'webdevstudios/cmb2' ) !== false ) {
	    $composer = remove_composer_autoload( $composer, 'Cmb2/' );
	    $composer = remove_composer_repositories( $composer, 'wpackagist' );
	  }
	  if ( strpos( $package, 'origgami/cmb2-grid' ) !== false ) {
	    $composer = remove_composer_autoload( $composer, 'Cmb2-grid' );
	    $composer = remove_composer_repositories( $composer, 'cmb2-grid' );
	  }
	  if ( strpos( $package, 'plugin/posts-to-posts' ) !== false ) {
	    $composer = remove_composer_autoload( $composer, 'posts-to' );
	  }
	  if ( strpos( $package, 'wp-admin-notice' ) !== false ) {
	    $composer = remove_composer_repositories( $composer, 'wordpress-admin-notice' );
	  }
	  print_v( 'Package ' . $package . ' removed!' );
	}
    }
  }
  file_put_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '/composer.json', json_encode( $composer, JSON_PRETTY_PRINT ) );
  if ( !$cmd[ 'no-download' ] ) {
    $clio->styleLine( '😀 Composer install in progress', $white );
    $output = '';
    exec( 'cd ' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '; composer update 2>&1', $output );
    $clio->styleLine( '😎 Composer install done', $white );
  }
}

/**
 * Remove the path from autoload
 * 
 * @param array $composer
 * @param string $searchpath
 * @return array
 */
function remove_composer_autoload( $composer, $searchpath ) {
  foreach ( $composer[ 'autoload' ][ 'files' ] as $key => $path ) {
    if ( strpos( $path, $searchpath ) ) {
	unset( $composer[ 'autoload' ][ 'files' ][ $key ] );
    }
  }
  if ( empty( $composer[ 'autoload' ][ 'files' ] ) ) {
    unset( $composer[ 'autoload' ] );
  }
  return $composer;
}

/**
 * Remove the url from repositories
 * 
 * @param array $composer
 * @param string $searchpath
 * @return array
 */
function remove_composer_repositories( $composer, $searchpath ) {
  foreach ( $composer[ 'repositories' ] as $key => $path ) {
    if ( strpos( $path['url'], $searchpath ) ) {
	unset( $composer[ 'repositories' ][ $key ] );
    }
  }
  return $composer;
}

/**
 * Create the .git folder
 * 
 * @global array $config
 * @global object $clio
 * @global object $white
 */
function git_init() {
  global $config, $clio, $white;

  if ( $config[ 'git-repo' ] === 'true' ) {
    exec( 'cd ' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '; git init' );
    $clio->styleLine( '😎 .git folder generated', $white );
  }
}

/**
 * Clean the grunt file and install his packages
 * 
 * @global array $config
 * @global object $clio
 * @global object $white
 */
function grunt() {
  global $config, $cmd, $clio, $white;

  if ( $config[ 'coffeescript' ] === 'false' ) {
    if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . DIRECTORY_SEPARATOR . 'public/assets/coffee' ) ) {
	rmrdir( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . DIRECTORY_SEPARATOR . 'public/assets/coffee' );
    }
    if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . DIRECTORY_SEPARATOR . 'admin/assets/coffee' ) ) {
	rmrdir( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . DIRECTORY_SEPARATOR . 'admin/assets/coffee' );
    }

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
  if ( !$cmd[ 'no-download' ] ) {
    $clio->styleLine( '😀 Grunt install in progress', $white );
    $output = '';
    exec( 'cd ' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGUIN_SLUG . '; npm install 2>&1', $output );
    $clio->styleLine( '😎 Grunt install done', $white );
  }
}

/**
 * Remove file in case of settings
 * 
 * @global array $config
 * @param string $file
 * @return boolean
 */
function remove_file( $file ) {
  global $config;
  $return = false;

  switch ( $file ) {
    case strpos( $file, '_ActDeact.php' ) && $config[ 'act-deact_actdeact' ] === 'false':
    case strpos( $file, '_ImpExp.php' ) && $config[ 'backend_impexp-settings' ] === 'false':
    case strpos( $file, '_Uninstall.php' ) && $config[ 'act-deact_uninstall' ] === 'false':
    case strpos( $file, '_P2P.php' ) && $config[ 'libraries_wpackagist-plugin__posts-to-posts' ] === 'false':
    case strpos( $file, '_FakePage.php' ) && $config[ 'libraries_wpbp__fakepage' ] === 'false':
    case strpos( $file, '_Pointers.php' ) && $config[ 'libraries_wpbp__pointerplus' ] === 'false':
    case strpos( $file, '_CMB.php' ) && $config[ 'libraries_webdevstudios__cmb2' ] === 'false':
    case strpos( $file, '_ContextualHelp.php' ) && $config[ 'libraries_voceconnect__wp-contextual-help' ] === 'false':
    case strpos( $file, '/help-docs' ) && $config[ 'libraries_voceconnect__wp-contextual-help' ] === 'false':
    case strpos( $file, '/templates' ) && $config[ 'frontend_template-system' ] === 'false':
    case strpos( $file, '/widgets/sample.php' ) && $config[ 'libraries_wpbp__widgets-helper' ] === 'false':
    case strpos( $file, '/widgets' ) && $config[ 'libraries_wpbp__widgets-helper' ] === 'false':
    case strpos( $file, '/public/assets/js' ) && $config[ 'public-assets_js' ] === 'false':
    case strpos( $file, '/public/assets/css' ) && $config[ 'public-assets_css' ] === 'false':
    case strpos( $file, '/public/assets/sass' ) && $config[ 'public-assets_css' ] === 'false':
    case strpos( $file, '/public/assets' ) && $config[ 'public-assets_css' ] === 'false' && $config[ 'public-assets_js' ] === 'false':
    case strpos( $file, '/admin/views' ) && $config[ 'admin-page' ] === 'false':
    case strpos( $file, '/admin/assets' ) && $config[ 'admin-page' ] === 'false':
    case strpos( $file, '_Extras.php' ) && ( $config[ 'backend_bubble-notification-pending-cpt' ] === 'false' &&
    $config[ 'backend_dashboard-atglance' ] === 'false' && $config[ 'backend_dashboard-activity' ] === 'false' &&
    $config[ 'system_push-notification' ] === 'false' && $config[ 'system_transient-example' ] === 'false' ):
	if ( file_exists( $file ) ) {
	  if ( is_dir( $file ) ) {
	    rmrdir( $file );
	  } else {
	    unlink( $file );
	  }
	}
	$return = true;
	break;
  }

  return $return;
}
