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
        case strpos( $file, 'actdeact.php' ) && $config[ 'act-deact_actdeact' ] === 'false':
        case strpos( $file, 'admin-enqueue.php' ) && $config[ 'admin-assets_admin-js' ] === 'false' 
                && $config[ 'admin-assets_admin-css' ] === 'false':
        case strpos( $file, 'impexp.php' ) && $config[ 'backend_impexp-settings' ] === 'false':
        case strpos( $file, 'uninstall.php' ) && $config[ 'act-deact_uninstall' ] === 'false':
        case strpos( $file, 'notices.php' ) && $config[ 'libraries_nathanielks__wp-admin-notice' ] === 'false' 
                && $config[ 'libraries_julien731__wp-review-me' ] === 'false' 
                && $config[ 'libraries_julien731__wp-dismissible-notices-handler' ] === 'false':
        case strpos( $file, 'settings-page.php' ) && $config[ 'admin-assets_admin-page' ] === 'false':
        case strpos( $file, '/admin/views' ) && $config[ 'admin-assets_admin-page' ] === 'false':
        // Ajax folder
        case strpos( $file, 'class-ajax.php' ) && $config[ 'ajax_public' ] === 'false':
        case strpos( $file, 'admin-ajax.php' ) && $config[ 'ajax_admin' ] === 'false':
        // Assets folder
        case strpos( $file, 'public.coffee' ) && $config[ 'public-assets_js' ] === 'false':
        case strpos( $file, 'public.js' ) && $config[ 'public-assets_js' ] === 'false':
        case strpos( $file, 'public.css' ) && $config[ 'public-assets_css' ] === 'false':
        case strpos( $file, 'public.scss' ) && $config[ 'public-assets_css' ] === 'false':
        case strpos( $file, 'admin.css' ) && $config[ 'admin-assets_admin-css' ] === 'false':
        case strpos( $file, 'admin.scss' ) && $config[ 'admin-assets_admin-css' ] === 'false':
        case strpos( $file, 'admin.coffee' ) && $config[ 'admin-assets_admin-js' ] === 'false':
        case strpos( $file, 'admin.js' ) && $config[ 'admin-assets_admin-js' ] === 'false':
        case strpos( $file, 'settings.js' ) && $config[ 'admin-assets_settings-js' ] === 'false':
        case strpos( $file, 'settings.coffee' ) && $config[ 'admin-assets_settings-js' ] === 'false':
        case strpos( $file, 'settings.css' ) && $config[ 'admin-assets_settings-css' ] === 'false':
        case strpos( $file, 'settings.scss' ) && $config[ 'admin-assets_settings-css' ] === 'false':
        // Cli folder
        case strpos( $file, 'cli.php' ) && $config[ 'wpcli' ] === 'false':
        // Integrations
        case strpos( $file, 'cmb.php' ) && $config[ 'libraries_cmb2__cmb2' ] === 'false':
        case strpos( $file, 'contextualhelp.php' ) && $config[ 'libraries_mte90__wp-contextual-help' ] === 'false':
        case strpos( $file, '/help-docs' ) && $config[ 'libraries_mte90__wp-contextual-help' ] === 'false':
        case strpos( $file, 'cron.php' ) && $config[ 'libraries_wpbp__cronplus' ] === 'false':
        case strpos( $file, 'fakepage.php' ) && $config[ 'libraries_wpbp__fakepage' ] === 'false':
        case strpos( $file, 'pointers.php' ) && $config[ 'libraries_wpbp__pointerplus' ] === 'false':
        case strpos( $file, 'template.php' ) && $config[ 'libraries_wpbp__template' ] === 'false':
        case strpos( $file, 'widgets.php' ) && $config[ 'libraries_wpbp__widgets-helper' ] === 'false':
        case strpos( $file, '/widgets' ) && $config[ 'libraries_wpbp__widgets-helper' ] === 'false':
        // Internals
        case strpos( $file, 'posttypes.php' ) && $config[ 'libraries_johnbillion__extended-cpts' ] === 'false':
        case strpos( $file, 'shortcode.php' ) && $config[ 'frontend_shortcode' ] === 'false':
        case strpos( $file, 'transient.php' ) && $config[ 'system_transient' ] === 'false':
        case strpos( $file, 'debug.php' ) && $config[ 'libraries_wpbp__debug' ] === 'false':
        // Public
        case strpos( $file, 'class-enqueue.php' ) && $config[ 'public-assets_js' ] === 'false' 
                && $config[ 'public-assets_css' ] === 'false'
                && $config[ 'frontend_wp-localize-script' ]:
        // REST folder
        case strpos( $file, 'rest.php' ) && $config[ 'system_rest' ] === 'false':
        // Template folder
        case strpos( $file, '/templates' ) && $config[ 'frontend_template-system' ] === 'false':
        // Tests folder
        case strpos( $file, '/tests' ) && $config[ 'unit-test' ] === 'false':
        case strpos( $file, 'codeception.dist.yml' ) && $config[ 'unit-test' ] === 'false':
        case strpos( $file, '.env' ) && $config[ 'unit-test' ] === 'false':
        case strpos( $file, 'wp-config-test.php' ) && $config[ 'unit-test' ] === 'false':
        // Others
        case strpos( $file, 'languages' ) && $config[ 'language-files' ] === 'false':
        case strpos( $file, 'grumphp.yml' ) && $config[ 'grumphp' ] === 'false':
            $return = remove_file_folder( $file );
            break;
    }

    return $return;
}
