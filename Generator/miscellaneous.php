<?php

use LightnCandy\LightnCandy;
use LightnCandy\Runtime;
use Clio\Style\Style;

$info   = new Style();
$error  = new Style();
$notice = new Style();

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
 * @return array
 */
function array_to_var( $array ) {
    $newarray = array();
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
            // Is a single key
            $newarray[ $key ] = $subarray;
            if ( $subarray === 'true' ) {
                $newarray[ $key ] = 'true';
            } elseif ( $subarray === 'false' ) {
                $newarray[ $key ] = 'false';
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

/**
 * Remove the file or directory
 *
 * @param string $file Path to remove.
 * @return boolean
 */
function remove_file_folder( $file ) {
    if ( file_exists( $file ) ) {
        if ( is_dir( $file ) ) {
            rmrdir( $file );
        } else {
            unlink( $file );
        }
    }

    return true;
}
