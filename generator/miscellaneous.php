<?php

/**
 * The functions used internally
 */

function set_color_scheme() {
    global $cmd;
    set_color_scheme_light();

    if ( $cmd[ 'dark' ] ) {
        set_color_scheme_dark();
    }
}

function set_color_scheme_dark() {
    global $info, $notice, $error;

    $info->setTextColor( 'white' )->setBold( true )->setUnderscore();
    $error->setTextColor( 'red' )->setBold( true );
    $notice->setTextColor( 'yellow' )->setBold( true );
}

function set_color_scheme_light() {
    global $info, $notice, $error;

    $info->setTextColor( 'black' )->setBold( true )->setUnderscore();
    $error->setTextColor( 'red' )->setBold( true );
    $notice->setTextColor( 'yellow' )->setBold( true );
}

/**
 * Print the label if the shell is executed as verbose
 *
 * @global object $cmd
 * @global object $clio
 * @global object $notice
 * @param  string $label Text.
 */
function print_v( $label ) {
    global $cmd, $clio, $notice;

    if ( $cmd[ 'verbose' ] ) {
        $clio->styleLine( $label, $notice );
    }
}

/**
 * LightnCandy require an array bidimensional "key" = true, so we need to convert a multidimensional in bidimensional
 *
 * @param  array $array The config to parse.
 * @param  bool  $default If it is the default file to load.
 * @return array
 */
function array_to_var( $array, $default = false ) {
    $newarray = array();
    
    $set = 'true';
    if( $default ) {
        $set = '';
    }
    // Get the json
    foreach ( $array as $key => $subarray ) {
        // Check if an array
        if ( is_array( $subarray ) ) {
            foreach ( $subarray as $subkey => $subvalue ) {
                // Again it's an array with another inside
                if ( is_array( $subvalue ) ) {
                    foreach ( $subvalue as $subsubkey => $subsubvalue ) {
                        if ( !is_nan( $subsubkey ) ) {
                            // If empty lightcandy takes as true
                            $newarray[ $subkey . '_' . strtolower( str_replace( '/', '__', $subsubvalue ) ) ] = $set;
                        }
                    }
                } else {
                    if ( !is_numeric( $subkey ) ) {
                        $newarray[ $key . '_' . strtolower( $subkey ) ] = $subvalue;
                        
                        if( $subvalue === 'true' && $default ) {
                            $newarray[ $key . '_' . strtolower( $subkey ) ] = $set;
                        }
                    } else {
                        $newarray[ $key . '_' . strtolower( str_replace( '/', '__', $subvalue ) ) ] = $set;
                    }
                }
            }
        } else {
            // Is a single key
            $newarray[ $key ] = $subarray;
            if ( $subarray === 'true' ) {
                $newarray[ $key ] = 'true';
            } elseif ( $subarray === 'false' ) {
                $newarray[ $key ] = '';
            }
            
            if( $default ) {
                $newarray[ $key ] = '';
            }
        }
    }

    return $newarray;
}

/**
 * Copy the directory
 *
 * @param string $source Path of origin.
 * @param string $dest   Path of destination.
 *
 * @return boolean
 */
function copy_dir( $source, $dest ) {
    // Simple copy for a file
    if ( is_file( $source ) ) {
        return copy( $source, $dest );
    }

    // Make destination directory
    if ( !is_dir( $dest ) ) {
        mkdir( $dest );
    }

    // Loop through the folder
    $dir = dir( $source );
    while ( false !== $entry = $dir->read() ) {
        // Skip pointers
        if ( $entry === '.' || $entry === '..' ) {
            continue;
        }

        // Deep copy directories
        copy_dir( "$source/$entry", "$dest/$entry" );
    }

    // Clean up
    $dir->close();
    return true;
}

/*
 * If temporary folder exist rename it or copy to the folder to process
 *
 * @return boolean
 */
function plugin_temp_exist() {
    global $cmd, $clio, $info;
    if ( file_exists( getcwd() . '/plugin_temp' ) ) {
        $clio->styleLine( 'Boilerplate already extracted found', $info );
        if ( $cmd[ 'dev' ] ) {
            copy_dir( getcwd() . '/plugin_temp', getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG );
        } else {
            rename( getcwd() . '/plugin_temp', getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG );
        }

        return true;
    }

    return;
}
