<?php

/**
 * All the functions about renaming files
 */

/**
 * Rename the files based on a pattern name
 */
function rename_by_specific_names( $file ) {
    global $config, $clio, $error;
    if ( ( strpos( $file, '.php' ) || strpos( $file, '.txt' ) || strpos( $file, 'Gruntfile.js' ) || strpos( $file, '.pot' ) || strpos( $file, '.yml' ) || strpos( $file, 'gitignore' ) ) ) {
        $pathparts = pathinfo( $file );
        $newname   = replace_content_names( $config, $pathparts[ 'filename' ] );
        $newname   = $pathparts[ 'dirname' ] . DIRECTORY_SEPARATOR . $newname . '.' . $pathparts[ 'extension' ];
        print_v( 'Ready to rename ' . $file );
        if ( $newname !== $file ) {
            try {
                rename( $file, $newname );
            } catch ( Exception $e ) {
                $clio->styleLine( $e, $error );
            }

            $files[] = $newname;
            print_v( 'Renamed ' . $file . ' to ' . $newname );
        } else {
            $files[] = $file;
        }
    }
}

/**
 * Replace some keywords with based ones from the plugin name
 *
 * @param array  $config  Generator parameters.
 * @param string $content The text.
 * @return string
 */
function replace_content_names( $config, $content ) {
    $ucword  = '';
    $lower   = '';
    $content = str_replace( "//WPBPGen\n", '', $content );
    $content = str_replace( '//WPBPGen', '', $content );
    $content = str_replace( "//\n", '', $content );
    $content = str_replace( 'Plugin_Name', str_replace( ' ', '_', str_replace( '-', '_', $config[ 'plugin_name' ] ) ), $content );
    $content = str_replace( 'plugin-name', WPBP_PLUGIN_SLUG, $content );
    $content = str_replace( 'plugin_name', str_replace( ' ', '_', str_replace( '-', '_', WPBP_PLUGIN_SLUG ) ), $content );
    $content = str_replace( 'WordPress-Plugin-Boilerplate-Powered', WPBP_PLUGIN_SLUG, $content );
    preg_match_all( '/[A-Z]/', ucwords( strtolower( preg_replace( '/[0-9]+/', '', $config[ 'plugin_name' ] ) ) ), $ucword );
    $ucword  = implode( '', $ucword[ 0 ] );
    $content = str_replace( 'PN_', $ucword . '_', $content );
    $lower   = strtolower( $ucword );
    $content = str_replace( 'Pn_', ucwords( $lower ) . '_', $content );
    $content = str_replace( 'pn_', $lower . '_', $content );
    $content = str_replace( '                                      ', '', $content );
    $content = str_replace( "\n\n", "\n", $content );
    return $content;
}
