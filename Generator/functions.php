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
  @return boolean
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
                    rmrdir( getcwd() . '/plugin_temp/' );
                    if ( !$cmd[ 'dev' ] ) {
                        unlink( getcwd() . '/plugin.zip' );
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
 * Download the phpcs file
 */
function download_phpcs_standard() {
    global $config, $clio, $info;
    if ( !empty( $config[ 'phpcs-standard' ] ) ) {
        $codeat = file_get_contents( $config[ 'phpcs-standard' ] );
        file_put_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/phpcs.xml', $codeat );
        $clio->styleLine( '😎 PHPCS Standard downloaded', $info );
    }
}

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

/**
 * Clean the composer files and execute the install of the packages
 *
 * @global array $config
 * @global object $clio
 * @global object $info
 */
function clean_composer_file() {
    global $config, $cmd, $clio, $info;
    $composer = json_decode( file_get_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/composer.json' ), true );
    $composer = remove_composer_packages( $composer );

    if ( $config[ 'grumphp' ] === 'false' ) {
        unset( $composer[ 'require-dev' ][ 'phpro/grumphp' ] );
        $clio->styleLine( '😎 Remove GrumPHP done', $info );
    }

    if ( $config[ 'unit-test' ] === 'false' ) {
        unset( $composer[ 'require-dev' ][ 'lucatume/wp-browser' ] );
        $clio->styleLine( '😎 Remove Codeception done', $info );
    }

    if ( count( $composer[ 'require-dev' ] ) === 0 ) {
        unset( $composer[ 'require-dev' ] );
    }

    if ( count( $composer[ 'require' ] ) === 3 ) {
        unset( $composer[ 'extra' ] );
    }

    file_put_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/composer.json', json_encode( $composer, JSON_PRETTY_PRINT ) );
}

/**
 * Remove composer packages that require additional stuff
 *
 * @param string $package The package to parse_config.
 * @param string $composer The composer.json content.
 * @return array
 */
function remove_specific_composer_respositories( $package, $composer ) {
    if ( strpos( $package, 'wp-contextual-help' ) !== false ) {
        $composer = remove_composer_autoload( $composer, 'wp-contextual-help' );
        $composer = remove_composer_repositories( $composer, 'wp-contextual-help' );
    }

    if ( strpos( $package, 'wp-admin-notice' ) !== false ) {
        $composer = remove_composer_repositories( $composer, 'wordpress-admin-notice' );
    }

    return $composer;
}

/**
 * Remove composer packages
 *
 * @param string $composer The composer.json content.
 * @return array
 */
function remove_composer_packages( $composer ) {
    global $config;
    foreach ( $config as $key => $value ) {
        if ( strpos( $key, 'libraries_' ) !== false ) {
            if ( $value === 'false' ) {
                $package = str_replace( 'libraries_', '', $key );
                $package = str_replace( '__', '/', $package );
                if ( isset( $composer[ 'require' ][ $package ] ) ) {
                    unset( $composer[ 'require' ][ $package ] );
                }

                $composer = remove_specific_composer_respositories( $package, $composer );

                print_v( 'Package ' . $package . ' removed!' );
            }
        }
    }

    return $composer;
}

/**
 * Remove composer packages that require additional stuff
 *
 * @return void
 */
function execute_composer() {
    global $cmd, $clio, $info;
    clean_composer_file();
    if ( !$cmd[ 'no-download' ] ) {
        $clio->styleLine( '😀 Composer install in progress (can require few minutes)', $info );
        $output       = '';
        $composer_cmd = 'composer update';
        if ( !$cmd[ 'verbose' ] ) {
            $composer_cmd .= ' 2>&1';
        }

        exec( 'cd ' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '; ' . $composer_cmd, $output );
        $clio->styleLine( '😎 Composer install done', $info );
    }
}

/**
 * Remove the path from autoload
 *
 * @param array  $composer The composer.json content.
 * @param string $searchpath The path where search.
 * @return array
 */
function remove_composer_autoload( $composer, $searchpath ) {
    if ( isset( $composer[ 'autoload' ] ) ) {
        foreach ( $composer[ 'autoload' ][ 'files' ] as $key => $path ) {
            if ( strpos( $path, $searchpath ) ) {
                unset( $composer[ 'autoload' ][ 'files' ][ $key ] );
            }
        }

        if ( empty( $composer[ 'autoload' ][ 'files' ] ) ) {
            unset( $composer[ 'autoload' ] );
        }
    }

    return $composer;
}

/**
 * Remove the url from repositories
 *
 * @param array  $composer The composer.json content.
 * @param string $searchpath The path where search.
 * @return array
 */
function remove_composer_repositories( $composer, $searchpath ) {
    if ( isset( $composer[ 'repositories' ] ) ) {
        foreach ( $composer[ 'repositories' ] as $key => $path ) {
            $url = '';
            if ( isset( $path[ 'url' ] ) ) {
                $url = $path[ 'url' ];
            } elseif ( isset( $path[ 'package' ][ 'source' ][ 'url' ] ) ) {
                $url = $path[ 'package' ][ 'source' ][ 'url' ];
            }

            if ( strpos( $url, $searchpath ) ) {
                unset( $composer[ 'repositories' ][ $key ] );
            }
        }
    }

    return $composer;
}

/**
 * Create the .git folder and update the boilerplate .gitignore file
 *
 * @global array $config
 * @global object $clio
 * @global object $info
 */
function git_init() {
    global $config, $clio, $info;

    if ( $config[ 'git-repo' ] === 'true' ) {
        exec( 'cd ' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '; git init' );
        $clio->styleLine( '😎 .git folder generated', $info );
        $gitignore = getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/.gitignore';
        file_put_contents( $gitignore, str_replace( '/plugin-name/', '', file_get_contents( $gitignore ) ) );
        $clio->styleLine( '😎 .gitignore file generated', $info );
    } else {
        unlink( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/.gitignore' );
    }
}

/**
 * Clean the grunt file and install his packages
 *
 * @global array $config
 * @global object $clio
 * @global object $info
 */
function grunt() {
    global $config, $cmd, $clio, $info;

    if ( $config[ 'grunt' ] === 'true' ) {
        if ( $config[ 'coffeescript' ] === 'false' ) {
            if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . DIRECTORY_SEPARATOR . 'public/assets/coffee' ) ) {
                rmrdir( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . DIRECTORY_SEPARATOR . 'public/assets/coffee' );
            }

            if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . DIRECTORY_SEPARATOR . 'admin/assets/coffee' ) ) {
                rmrdir( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . DIRECTORY_SEPARATOR . 'admin/assets/coffee' );
            }

            $package    = file( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/package.json' );
            $newpackage = array();
            foreach ( $package as $line => $content ) {
                if ( strpos( $content, 'coffee' ) ) {
                    $newpackage[ $line - 1 ] = str_replace( ',', '', $package[ $line - 1 ] );
                } else {
                    $newpackage[] = $package[ $line ];
                }
            }

            file_put_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/package.json', $newpackage );
            $grunt    = file( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/Gruntfile.js' );
            $newgrunt = array();
            foreach ( $grunt as $line => $content ) {
                if ( !( ( $line >= 45 && $line <= 86 ) || $line === 92 || $line === 93 || $line === 97 || $line === 105 || $line === 109 ) ) {
                    $newgrunt[] = $grunt[ $line ];
                }
            }

            file_put_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/Gruntfile.js', $newgrunt );
            $clio->styleLine( '😀 Coffeescript removed', $info );
        }

        if ( !$cmd[ 'no-download' ] ) {
            $clio->styleLine( '😀 Grunt install in progress', $info );
            $output = '';
            exec( 'cd ' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '; npm install 2>&1', $output );
            $clio->styleLine( '😎 Grunt install done', $info );
        }
    } else {
        unlink( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/Gruntfile.js' );
        unlink( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/package.json' );
        $clio->styleLine( '😀 Grunt removed', $info );
    }
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

