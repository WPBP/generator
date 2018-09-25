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
        case strpos( $file, '_ActDeact.php' ) && $config[ 'act-deact_actdeact' ] === 'false':
        case strpos( $file, '_ImpExp.php' ) && $config[ 'backend_impexp-settings' ] === 'false':
        case strpos( $file, '_Uninstall.php' ) && $config[ 'act-deact_uninstall' ] === 'false':
        case strpos( $file, '_FakePage.php' ) && $config[ 'libraries_wpbp__fakepage' ] === 'false':
        case strpos( $file, '_Pointers.php' ) && $config[ 'libraries_wpbp__pointerplus' ] === 'false':
        case strpos( $file, '_template.php' ) && $config[ 'libraries_wpbp__template' ] === 'false':
        case strpos( $file, '_CMB.php' ) && $config[ 'libraries_webdevstudios__cmb2' ] === 'false':
        case strpos( $file, '_ContextualHelp.php' ) && $config[ 'libraries_kevinlangleyjr__wp-contextual-help' ] === 'false':
        case strpos( $file, '_PostTypes.php' ) && $config[ 'libraries_johnbillion__extended-cpts' ] === 'false':
        case strpos( $file, '/help-docs' ) && $config[ 'libraries_kevinlangleyjr__wp-contextual-help' ] === 'false':
        case strpos( $file, '/templates' ) && $config[ 'frontend_template-system' ] === 'false':
        case strpos( $file, '/widgets' ) && $config[ 'libraries_wpbp__widgets-helper' ] === 'false':
        case strpos( $file, '/public/assets/js' ) && $config[ 'public-assets_js' ] === 'false':
        case strpos( $file, '/public/assets/css' ) && $config[ 'public-assets_css' ] === 'false':
        case strpos( $file, '/public/assets/sass' ) && $config[ 'public-assets_css' ] === 'false':
        case strpos( $file, '/public/assets' ) && $config[ 'public-assets_css' ] === 'false' && $config[ 'public-assets_js' ] === 'false':
        case strpos( $file, '/public/ajax' ) && $config[ 'ajax_public' ] === 'false':
        case strpos( $file, '/admin/ajax' ) && $config[ 'ajax_admin' ] === 'false':
        case strpos( $file, '/admin/views' ) && $config[ 'admin-assets_admin-page' ] === 'false':
        case strpos( $file, '/admin/assets' ) && $config[ 'admin-assets_admin-page' ] === 'false':
        case strpos( $file, '/admin/assets/css/admin' ) && $config[ 'admin-assets_admin-css' ] === 'false':
        case strpos( $file, '/admin/assets/sass/admin' ) && $config[ 'admin-assets_admin-css' ] === 'false':
        case strpos( $file, '/admin/assets/js/admin' ) && $config[ 'admin-assets_admin-js' ] === 'false':
        case strpos( $file, '/admin/assets/js/settings' ) && $config[ 'admin-assets_settings-js' ] === 'false':
        case strpos( $file, '/admin/assets/coffee/admin' ) && $config[ 'admin-assets_admin-js' ] === 'false':
        case strpos( $file, '/admin/assets/coffee/settings' ) && $config[ 'admin-assets_settings-js' ] === 'false':
        case strpos( $file, '/admin/assets/css/settings' ) && $config[ 'admin-assets_settings-css' ] === 'false':
        case strpos( $file, '/admin/assets/sass/settings' ) && $config[ 'admin-assets_settings-css' ] === 'false':
        case strpos( $file, '/tests' ) && $config[ 'unit-test' ] === 'false':
        case strpos( $file, 'codeception.yml' ) && $config[ 'unit-test' ] === 'false':
        case strpos( $file, 'wp-config-test.php' ) && $config[ 'unit-test' ] === 'false':
        case strpos( $file, 'languages' ) && $config[ 'language-files' ] === 'false':
        case strpos( $file, '_WPCli.php' ) && $config[ 'wpcli' ] === 'false':
        case strpos( $file, 'grumphp.yml' ) && $config[ 'grumphp' ] === 'false':
            $return = remove_file_folder( $file );
            break;
    }

    return $return;
}
