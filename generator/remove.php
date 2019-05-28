<?php 

/**
 * The functions used to remove files
 */

/**
 * Remove the file or directory
 *
 * @param string $file Path to remove.
 * @return boolean
 */
function remove_file_folder( $file ) {
    if ( file_exists( $file ) ) {
        print_v( 'Removed ' . $file );
        if ( is_dir( $file ) ) {
            rmrdir( $file );
            return true;
        } 
        
        unlink( $file );
    }

    return true;
}


/**
 * Remove folders empty
 */
function remove_empty_folders() {
    $path = getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG; 
    $dir_iterator = new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS );
    $iterator     = iterator_to_array( new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST ) );

    foreach ( $iterator as $file => $object ) {
        // not execute on composer/npm folder
        if ( is_dir( $file ) && strpos( $file, 'vendor' ) === false && strpos( $file, 'node_modules' ) === false && strpos( $file, '/.' ) === false && strpos( $file, '/tests' ) === false ) {
            $there_is_only_index_file = count_files_in_a_folder( $file );
            if ( $there_is_only_index_file === 1 ) {
                remove_file_folder( $file );
            }
                
            continue;
        }
    }
}

/**
 * Count files inside the folder
 *
 * @param string $file Path to remove.
 * @return boolean
 */
function count_files_in_a_folder($path) {
    // (Ensure that the path contains an ending slash)
    $file_count = 0;
    $dir_handle = opendir( $path );
 
    if ( !$dir_handle ) return -1;
 
    while ($file = readdir( $dir_handle )) {
        if ($file == '.' || $file == '..') continue;
 
        if ( is_dir( $path . $file ) ){      
            $file_count += count_files_in_a_folder($path . $file . DIRECTORY_SEPARATOR);
            continue;
        }
        
        $file_count++; // increase file count
    }
 
    closedir( $dir_handle );
    return $file_count;
}

/**
 * Remove files based on the feature required
 *
 * @global array $config
 * @param  string $file
 * @return boolean
 */
function remove_files_by_settings( $file ) {
    global $config;
    $return = false;

    switch ( $file ) {
        // Admin folder
        case strpos( $file, 'actdeact.php' ) && empty( $config[ 'act-deact_actdeact' ] ):
        case strpos( $file, 'admin-enqueue.php' ) && empty( $config[ 'admin-assets_admin-js' ] ) 
                && empty( $config[ 'admin-assets_admin-css' ] ):
        case strpos( $file, 'impexp.php' ) && empty( $config[ 'backend_impexp-settings' ] ):
        case strpos( $file, 'uninstall.php' ) && empty( $config[ 'act-deact_uninstall' ] ):
        case strpos( $file, 'notices.php' ) && empty( $config[ 'libraries_nathanielks__wp-admin-notice' ] ) 
                && empty( $config[ 'libraries_julien731__wp-review-me' ] ) 
                && empty( $config[ 'libraries_julien731__wp-dismissible-notices-handler' ] ):
        case strpos( $file, 'settings-page.php' ) && empty( $config[ 'admin-assets_admin-page' ] ):
        case strpos( $file, '/admin/views' ) && empty( $config[ 'admin-assets_admin-page' ] ):
        // Ajax folder
        case strpos( $file, 'class-ajax.php' ) && empty( $config[ 'ajax_public' ] ):
        case strpos( $file, 'admin-ajax.php' ) && empty( $config[ 'ajax_admin' ] ):
        // Assets folder
        case strpos( $file, 'public.coffee' ) && empty( $config[ 'public-assets_js' ] ):
        case strpos( $file, 'public.js' ) && empty( $config[ 'public-assets_js' ] ):
        case strpos( $file, 'public.css' ) && empty( $config[ 'public-assets_css' ] ):
        case strpos( $file, 'public.scss' ) && empty( $config[ 'public-assets_css' ] ):
        case strpos( $file, 'admin.css' ) && empty( $config[ 'admin-assets_admin-css' ] ):
        case strpos( $file, 'admin.scss' ) && empty( $config[ 'admin-assets_admin-css' ] ):
        case strpos( $file, 'admin.coffee' ) && empty( $config[ 'admin-assets_admin-js' ] ):
        case strpos( $file, 'admin.js' ) && empty( $config[ 'admin-assets_admin-js' ] ):
        case strpos( $file, 'settings.js' ) && empty( $config[ 'admin-assets_settings-js' ] ):
        case strpos( $file, 'settings.coffee' ) && empty( $config[ 'admin-assets_settings-js' ] ):
        case strpos( $file, 'settings.css' ) && empty( $config[ 'admin-assets_settings-css' ] ):
        case strpos( $file, 'settings.scss' ) && empty( $config[ 'admin-assets_settings-css' ] ):
        // Cli folder
        case strpos( $file, 'cli.php' ) && empty( $config[ 'wpcli' ] ):
        // Integrations
        case strpos( $file, 'cmb.php' ) && empty( $config[ 'libraries_cmb2__cmb2' ] ):
        case strpos( $file, 'contextualhelp.php' ) && empty( $config[ 'libraries_mte90__wp-contextual-help' ] ):
        case strpos( $file, '/help-docs' ) && empty( $config[ 'libraries_mte90__wp-contextual-help' ] ):
        case strpos( $file, 'cron.php' ) && empty( $config[ 'libraries_wpbp__cronplus' ] ):
        case strpos( $file, 'fakepage.php' ) && empty( $config[ 'libraries_wpbp__fakepage' ] ):
        case strpos( $file, 'pointers.php' ) && empty( $config[ 'libraries_wpbp__pointerplus' ] ):
        case strpos( $file, 'template.php' ) && empty( $config[ 'libraries_wpbp__template' ] ):
        case strpos( $file, 'widgets.php' ) && empty( $config[ 'libraries_wpbp__widgets-helper' ] ):
        case strpos( $file, '/widgets' ) && empty( $config[ 'libraries_wpbp__widgets-helper' ] ):
        // Internals
        case strpos( $file, 'posttypes.php' ) && empty( $config[ 'libraries_johnbillion__extended-cpts' ] ):
        case strpos( $file, 'shortcode.php' ) && empty( $config[ 'frontend_shortcode' ] ):
        case strpos( $file, 'transient.php' ) && empty( $config[ 'system_transient' ] ):
        case strpos( $file, 'debug.php' ) && empty( $config[ 'libraries_wpbp__debug' ] ):
        // Public
        case strpos( $file, 'class-enqueue.php' ) && empty( $config[ 'public-assets_js' ] ) 
                && empty( $config[ 'public-assets_css' ] )
                && empty( $config[ 'frontend_wp-localize-script' ] ):
        // REST folder
        case strpos( $file, 'rest.php' ) && empty( $config[ 'system_rest' ] ):
        // Template folder
        case strpos( $file, '/templates' ) && empty( $config[ 'frontend_template-system' ] ):
        // Tests folder
        case strpos( $file, '/tests' ) && empty( $config[ 'unit-test' ] ):
        case strpos( $file, 'codeception.dist.yml' ) && empty( $config[ 'unit-test' ] ):
        case strpos( $file, '.env' ) && empty( $config[ 'unit-test' ] ):
        case strpos( $file, 'wp-config-test.php' ) && empty( $config[ 'unit-test' ] ):
        // Others
        case strpos( $file, 'languages' ) && empty( $config[ 'language-files' ] ):
        case strpos( $file, 'grumphp.yml' ) && empty( $config[ 'grumphp' ] ):
        case strpos( $file, 'phpstan.neon' ) && empty( $config[ 'phpstan' ] ):
            $return = remove_file_folder( $file );
            break;
    }

    return $return;
}
