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
        if ( is_dir( $file ) ) {
            rmrdir( $file );
        } else {
            unlink( $file );
        }
    }

    return true;
}

/**
 * Remove file in case of settings
 *
 * @global array $config
 * @param  string $file
 * @return boolean
 */
function remove_file( $file ) {
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
        case strpos( $file, 'cmb.php' ) && $config[ 'libraries_webdevstudios__cmb2' ] === 'false':
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
