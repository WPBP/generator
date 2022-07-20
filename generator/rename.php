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
    $clio->clear()->style( $info )->display( "Rename/Remove in progress" )->newLine();
    $dir_iterator = new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS );
    $iterator     = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );

    // Move in array with only paths
    foreach ( $iterator as $file => $object ) {
        $list[] = $file;
    }

    foreach ( $list as $file ) {
        if ( !file_exists( $file ) ) {
            continue;
        }

        if ( remove_files_by_settings( $file ) ) {
            continue;
        }
        
        if ( empty( $file ) ) {
            continue;
        }
        
        if ( strpos( $file, 'index.php' ) || strpos( $file, '_generated' ) || strpos( $file, 'Helper' ) || strpos( $file, '_output' ) || strpos( $file, 'vendor' ) ) {
            continue;
        }

        $file = rename_by_specific_extensions( $file );
        
        if ( !empty( $file ) ) {
            $files[] = $file;
        }
    }

    return $files;
}

/**
 * Rename the files based on a extensions
 */
function rename_by_specific_extensions( $file ) {
    global $config, $clio, $error;
    $files = '';
    if ( strpos( $file, '.php' ) || strpos( $file, '.txt' ) || strpos( $file, '.pot' ) || strpos( $file, '.yml' ) || strpos( $file, '.neon' ) || strpos( $file, 'gitignore' ) || strpos( $file, '.env' ) || strpos( $file, '.json' ) || strpos( $file, '.js' ) ) {
        $pathparts = pathinfo( $file );
        $newname   = replace_name_slug( $config, $pathparts[ 'filename' ] );
        $newname   = $pathparts[ 'dirname' ] . DIRECTORY_SEPARATOR . $newname . '.' . $pathparts[ 'extension' ];
        $files = $file;
        if ( $newname !== $file ) {
            try {
                rename( $file, $newname );
            } catch ( Exception $e ) {
                $clio->styleLine( $e, $error );
            }

            $files = $newname;
            print_v( 'Renamed ' . $file . ' to ' . $newname );
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
function replace_name_slug( $config, $content ) {
    if ( ! empty( $content ) && $content !== 'index' ) {        
        $ucword  = '';
        $lower   = '';
        $class = preg_replace('/[0-9\@\.\;\" "]+/', '', str_replace( ' ', '_', str_replace( '-', '_', $config[ 'plugin_name' ] ) ) );
        $content = str_replace( '// WPBPGen', '', $content );
        $content = str_replace( '# WPBPGen', '', $content );
        $content = str_replace( '//WPBPGen', '', $content );
        $content = str_replace( '// {{/', '{{/', $content );
        $content = str_replace( '# {{/', '{{/', $content );
        $content = str_replace( '//{{/', '{{/', $content );
        $content = str_replace( "{{plugin_name}}", $config[ 'plugin_name' ], $content );
        $content = str_replace( "//\n", '', $content );
        $content = str_replace( "// \n", '', $content );
        $content = str_replace( "        \n\n", '', $content );
        $content = str_replace( "        \n        \n", '', $content );
        $content = str_replace( "\t\t\t\n\t\t\t\n", '', $content );
        $content = str_replace( 'Plugin_Name', $class, $content );
        $content = str_replace( 'Plugin_name', $class, $content );
        $content = preg_replace('/Plugin Name$/', $config[ 'plugin_name' ], $content );
        $content = preg_replace('/Plugin name/', $config[ 'plugin_name' ], $content );
        $content = str_replace( 'plugin-name', WPBP_PLUGIN_SLUG, $content );
        $content = str_replace( 'plugin_name', str_replace( ' ', '_', str_replace( '-', '_', WPBP_PLUGIN_SLUG ) ), $content );
        preg_match_all( '/[A-Z]/', ucwords( strtolower( preg_replace( '/[0-9]+/', '', $config[ 'plugin_name' ] ) ) ), $ucword );
        $ucword  = implode( '', $ucword[ 0 ] );
        $content = str_replace( 'PN_', $ucword . '_', $content );
        $lower   = strtolower( $ucword );
        $content = str_replace( 'Pn_', ucwords( $lower ) . '_', $content );
        $content = str_replace( 'pn_', $lower . '_', $content );
    }
    
    return $content;
}
