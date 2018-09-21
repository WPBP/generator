<?php

/**
 * Generate a new wpbp.json in the folder
 *
 * @global object $cmd
 * @global object $clio
 * @global object $error
 * @global object $info
 */
function create_wpbp_json() {
    global $cmd, $clio, $error, $info;

    if ( $cmd[ 'json' ] ) {
        if ( !copy( dirname( __FILE__ ) . '/wpbp.json', getcwd() . '/wpbp.json' ) ) {
            $clio->styleLine( 'Failed to copy wpbp.json...', $error );
        } else {
            $clio->styleLine( '😀 wpbp.json generated', $info );
            exit();
        }
    } else {
        if ( !file_exists( getcwd() . '/wpbp.json' ) ) {
            $clio->styleLine( '😡 wpbp.json file missing...', $error );
            $clio->styleLine( '😉 Generate it with: wpbp-generator --json', $error );
            $clio->styleLine( 'Forget a hipster Q&A procedure and fill that JSON with your custom configuration!', $error );
            $clio->styleLine( '  Let\'s do your changes and execute the script again! Use the --dev parameter to use the development version of the boilerplate!', $error );
            $clio->styleLine( '', $info );
            $clio->styleLine( 'Help: wpbp-generator --help 😉', $info );
            exit();
        }
    }
}

/**
 * Download the boilerplate based from theversion asked
 *
 * @global object $cmd
 * @global object $clio
 * @global object $info
 */
function download_wpbp() {
    global $cmd, $clio, $info, $error;
    $version = WPBP_VERSION;

    if ( $cmd[ 'dev' ] ) {
        $version = 'master';
    }

    $clio->styleLine( '😎 Downloading ' . $version . ' package', $info );

    $download = @file_get_contents( 'http://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered/archive/' . $version . '.zip' );
    if ( $download === false ) {
        $clio->styleLine( '😡 The ' . $version . ' version is not yet avalaible! Use the --dev parameter!', $error );
        die();
    }

    file_put_contents( 'plugin.zip', $download );

    extract_wpbp();
}

/*
 * If temporary folder exist rename it or copy to the folder to process
 *
 * @return boolean
 */
function plugin_temp_exist() {
    global $cmd, $clio, $info;
    if ( file_exists( getcwd() . '/plugin_temp' ) ) {
        $clio->styleLine( 'Boilerplate extracted found', $info );
        if ( $cmd[ 'dev' ] ) {
            copy_dir( getcwd() . '/plugin_temp', getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG );
        } else {
            rename( getcwd() . '/plugin_temp', getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG );
        }

        return true;
    }

    return;
}

/**
 * Extract the boilerplate
 *
 * @global object $cmd
 * @global object $clio
 * @global object $info
 * @global object $error
 */
function extract_wpbp() {
    global $cmd, $clio, $info, $error;
    if ( ! plugin_temp_exist() ) {
        if ( file_exists( getcwd() . '/plugin.zip' ) ) {
            if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG ) ) {
                $clio->styleLine( 'Folder ' . WPBP_PLUGIN_SLUG . ' already exist!', $error );
                exit();
            }

            $clio->styleLine( 'Extract Boilerplate', $info );
            $zip = new ZipArchive;
            $res = $zip->open( getcwd() . '/plugin.zip' );
            if ( $res === true ) {
                $zip->extractTo( getcwd() . '/plugin_temp/' );
                $zip->close();
                $version = WPBP_VERSION;

                if ( $cmd[ 'dev' ] ) {
                    $version = 'master';
                }

                try {
                    rename( getcwd() . '/plugin_temp/WordPress-Plugin-Boilerplate-Powered-' . $version . '/plugin-name/', getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG );
                    rename( getcwd() . '/plugin_temp/WordPress-Plugin-Boilerplate-Powered-' . $version . '/.gitignore', getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/.gitignore' );
                    remove_file_folder( getcwd() . '/plugin_temp/' );
                    if ( !$cmd[ 'dev' ] ) {
                        remove_file_folder( getcwd() . '/plugin.zip' );
                    }
                } catch ( Exception $e ) {
                    $clio->styleLine( $e, $error );
                }

                $clio->styleLine( 'Boilerplate Extracted', $info );
            }

            return;
        }

        // If the package not exist download it
        download_wpbp();
    }
}

/**
 * Execute Lightncandy on the boilerplate files
 *
 * @global object $cmd
 * @global object $clio
 * @global object $info
 * @param  array $config The config of the request.
 */
function execute_generator( $config ) {
    global $cmd, $clio, $info;
    $files = get_files();
    foreach ( $files as $file ) {
        $file_content = file_get_contents( $file );
        if ( $cmd[ 'dev' ] ) {
            print_v( 'Parsing ' . $file );
            $lc         = LightnCandy::compile(
                $file_content,
                array(
                    'flags' => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_RENDER_DEBUG,
                )
            );
            $lc_prepare = LightnCandy::prepare( $lc );
            $newfile    = $lc_prepare( $config, array( 'debug' => Runtime::DEBUG_ERROR_EXCEPTION | Runtime::DEBUG_ERROR_LOG ) );
        } else {
            $lc         = LightnCandy::compile( $file_content );
            $lc_prepare = LightnCandy::prepare( $lc );
            $newfile    = $lc_prepare( $config );
        }

        if ( strpos( $file, '.gitignore' ) ) {
            $newfile = str_replace( 'plugin-name/', '', $newfile );
        }

        $newfile = replace_content_names( $config, $newfile );
        if ( $newfile !== $file_content ) {
            print_v( 'Parsed ' . $file );
            file_put_contents( $file, $newfile );
        }
    }

    echo PHP_EOL;
    $clio->styleLine( 'Generation done, I am superfast! You: (ʘ_ʘ)', $info );
    execute_composer();
    git_init();
    grunt();
}

/**
 * Load user wpbp.json and add the terms missing as false
 *
 * @global object $clio
 * @global object $error
 * @return array
 */
function parse_config() {
    global $clio, $error;
    $config = json_decode( file_get_contents( getcwd() . '/wpbp.json' ), true );
    // Detect a misleading json file
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $clio->styleLine( '😡 Your JSON is broken!', $error );
        exit;
    }

    $config         = array_to_var( $config );
    $config_default = array_to_var( json_decode( file_get_contents( dirname( __FILE__ ) . '/wpbp.json' ), true ) );
    foreach ( $config_default as $key => $value ) {
        if ( !isset( $config[ $key ] ) ) {
            $config[ $key ] = 'false';
        }
    }

    return $config;
}

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

        rename_by_specific_names( $file );
    }

    return $files;
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

