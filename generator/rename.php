<?php

/**
 * All the functions about renaming files
 */

/**
 * Get the wpbp files, rename it and return a list of files ready to be parsed
 *
 * @global array $config
 * @global object $clio
 * @global object $error
 * @global object $info
 * @param  string $path Where scan.
 * @return array
 */
function get_files( $path = null ) {
    global $clio, $info;
    if ( $path === null ) {
        $path = getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG;
    }

    $files = $list = array();
    $clio->styleLine( 'Rename in progress', $info );
    $dir_iterator = new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS );
    $iterator     = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );

    download_phpcs_standard();

    // Move in array with only paths
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

        $files[] = rename_by_specific_names( $file );
    }

    return $files;
}

/**
 * Rename the files based on a pattern name
 */
function rename_by_specific_names( $file ) {
    global $config, $clio, $error;
    $files = '';
    if ( ( strpos( $file, '.php' ) || strpos( $file, '.txt' ) || strpos( $file, 'Gruntfile.js' ) || strpos( $file, '.pot' ) || strpos( $file, '.yml' ) || strpos( $file, 'gitignore' ) ) ) {
        $pathparts = pathinfo( $file );
        $newname   = replace_content_names( $config, $pathparts[ 'filename' ], '.' . $pathparts[ 'extension' ] );
        $newname   = $pathparts[ 'dirname' ] . DIRECTORY_SEPARATOR . $newname . '.' . $pathparts[ 'extension' ];
        if ( $newname !== $file ) {
            try {
                rename( $file, $newname );
            } catch ( Exception $e ) {
                $clio->styleLine( $e, $error );
            }

            $files = $newname;
            print_v( 'Renamed ' . $file . ' to ' . $newname );
        } else {
            $files = $file;
        }
    }
    
    return $files;
}

/**
 * Replace some keywords with based ones from the plugin name
 *
 * @param array  $config  Generator parameters.
 * @param string $content The text.
 * @return string
 */
function replace_content_names( $config, $content, $ext = '' ) {
    if ( ! empty( $content ) && $content !== 'index' ) {
        print_v( 'Replace placeholders for ' . $content . $ext );
        $ucword  = '';
        $lower   = '';
        $content = str_replace( '// WPBPGen', '', $content );
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
        $content = str_replace( "\n\n", "\n", $content );
    }
    return $content;
}
