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
            if ( $there_is_only_index_file === 0 ) {
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
    if( file_exists( $path ) && is_dir( $path ) ) {
        $dir_handle = opendir( $path );
    
        if ( !$dir_handle ) return -1;
    
        while ($file = readdir( $dir_handle )) {
            if ( in_array( $file, array( '.', '..', 'index.php' ), true ) ) continue;
    
            if ( is_dir( $path . $file ) ){      
                $file_count += count_files_in_a_folder($path . $file . DIRECTORY_SEPARATOR);
                continue;
            }
            
            $file_count++; // increase file count
        }
    
        closedir( $dir_handle );
    }

    if ( !is_dir( $path ) ) {
        return 1;
    }

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
        case strpos( $file, 'backend/ActDeact.php' ) && is_empty_or_false( $config[ 'act-deact_actdeact' ] ):
        case strpos( $file, 'backend/Enqueue.php' ) && is_empty_or_false( $config[ 'admin-assets_admin-js' ] ) 
                && is_empty_or_false( $config[ 'admin-assets_admin-css' ] ):
        case strpos( $file, 'backend/ImpExp.php' ) && is_empty_or_false( $config[ 'backend_impexp-settings' ] ):
        case strpos( $file, 'backend/Notices.php' ) && is_empty_or_false( $config[ 'libraries_wpdesk__wp-notice' ] ) 
                && is_empty_or_false( $config[ 'libraries_julien731__wp-review-me' ] ) 
                && is_empty_or_false( $config[ 'libraries_yoast__i18n-module' ] ):
        case strpos( $file, 'backend/Settings_Page.php' ) && is_empty_or_false( $config[ 'admin-assets_admin-page' ] ):
        case strpos( $file, 'backend/Pointers.php' ) && is_empty_or_false( $config[ 'libraries_wpbp__pointerplus' ] ):
        case strpos( $file, 'backend/views' ) && is_empty_or_false( $config[ 'admin-assets_admin-page' ] ):
        // Ajax folder
        case strpos( $file, 'ajax' ) && is_empty_or_false( $config[ 'ajax' ] ):
        // Assets folder
        case strpos( $file, 'public.js' ) && is_empty_or_false( $config[ 'public-assets_js' ] ):
        case strpos( $file, 'public.css' ) && is_empty_or_false( $config[ 'public-assets_css' ] ):
        case strpos( $file, 'public.scss' ) && is_empty_or_false( $config[ 'public-assets_css' ] ):
        case strpos( $file, 'admin.css' ) && is_empty_or_false( $config[ 'admin-assets_admin-css' ] ):
        case strpos( $file, 'admin.scss' ) && is_empty_or_false( $config[ 'admin-assets_admin-css' ] ):
        case strpos( $file, 'admin.js' ) && is_empty_or_false( $config[ 'admin-assets_admin-js' ] ):
        case strpos( $file, 'settings.js' ) && is_empty_or_false( $config[ 'admin-assets_settings-js' ] ):
        case strpos( $file, 'settings.css' ) && is_empty_or_false( $config[ 'admin-assets_settings-css' ] ):
        case strpos( $file, 'settings.scss' ) && is_empty_or_false( $config[ 'admin-assets_settings-css' ] ):
        // Cli folder
        case strpos( $file, 'cli/Example.php' ) && is_empty_or_false( $config[ 'wpcli' ] ):
        // Integrations
        case strpos( $file, 'integrations/CMB.php' ) && is_empty_or_false( $config[ 'libraries_cmb2__cmb2' ] ):
        case strpos( $file, 'integrations/Cron.php' ) && is_empty_or_false( $config[ 'libraries_wpbp__cronplus' ] ):
        case strpos( $file, 'integrations/FakePage.php' ) && is_empty_or_false( $config[ 'libraries_wpbp__fakepage' ] ):
        case strpos( $file, 'integrations/Template.php' ) && is_empty_or_false( $config[ 'libraries_wpbp__template' ] ):
        case strpos( $file, '/Widgets' ) && is_empty_or_false( $config[ 'libraries_wpbp__widgets-helper' ] ):
        // Internals
        case strpos( $file, 'internals/PostTypes.php' ) && is_empty_or_false( $config[ 'libraries_johnbillion__extended-cpts' ] ):
        case strpos( $file, 'internals/Shortcode.php' ) && is_empty_or_false( $config[ 'frontend_shortcode' ] ):
        case strpos( $file, 'internals/ShortcodeBlock.php' ) && is_empty_or_false( $config[ 'libraries_ayecode__wp-super-duper' ] ):
        case strpos( $file, 'internals/Transient.php' ) && is_empty_or_false( $config[ 'system_transient' ] ):
        case strpos( $file, 'internals/Block.php' ) && is_empty_or_false( $config[ 'backend_block' ] ):
        case strpos( $file, 'assets/src/block' ) && is_empty_or_false( $config[ 'backend_block' ] ):
        case strpos( $file, 'assets/src/plugin-block.js' ) && is_empty_or_false( $config[ 'backend_block' ] ):
        case strpos( $file, 'assets/src/styles/block.scss' ) && is_empty_or_false( $config[ 'backend_block' ] ):
        case strpos( $file, 'debug.php' ) && is_empty_or_false( $config[ 'libraries_wpbp__debug' ] ):
        // Frontend
        case strpos( $file, 'frontend' ) && is_empty_or_false( $config[ 'public-assets_js' ] ) 
                && is_empty_or_false( $config[ 'public-assets_css' ] )
                && is_empty_or_false( $config[ 'frontend_wp-localize-script' ] ):
        case strpos( $file, 'Body_Class.php' ) && is_empty_or_false( $config[ 'frontend_body-class' ] ):
        // REST folder
        case strpos( $file, 'rest/Example.php' ) && is_empty_or_false( $config[ 'system_rest' ] ):
        // Template folder
        case strpos( $file, '/templates' ) && is_empty_or_false( $config[ 'frontend_template-system' ] ):
        case strpos( $file, '/Template.php' ) && is_empty_or_false( $config[ 'frontend_template-system' ] ):
        // Tests folder
        case strpos( $file, '/tests' ) && is_empty_or_false( $config[ 'unit-test' ] ):
        case strpos( $file, 'codeception.dist.yml' ) && is_empty_or_false( $config[ 'unit-test' ] ):
        case strpos( $file, '.env' ) && is_empty_or_false( $config[ 'unit-test' ] ):
        case strpos( $file, 'wp-config-test.php' ) && is_empty_or_false( $config[ 'unit-test' ] ):
        // Others
        case strpos( $file, 'languages' ) && is_empty_or_false( $config[ 'language-files' ] ):
        case strpos( $file, 'grumphp.yml' ) && is_empty_or_false( $config[ 'grumphp' ] ):
        case strpos( $file, 'uninstall.php' ) && is_empty_or_false( $config[ 'act-deact_uninstall' ] ):
        case strpos( $file, 'phpstan.neon' ) && is_empty_or_false( $config[ 'phpstan' ] ):
            $return = remove_file_folder( $file );
            break;
    }

    return $return;
}
