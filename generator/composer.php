<?php
/**
 * All the functions that involve the composer.json file
 */
 
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

        exec( 'cd "' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '"; ' . $composer_cmd, $output );
        $clio->styleLine( '😎 Composer install done', $info );
    }
}

/**
 * Clean the composer files and execute the install of the packages
 *
 * @global array $config
 * @global object $clio
 * @global object $info
 */
function clean_composer_file() {
    global $config, $clio, $info;
    $composer = json_decode( file_get_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/composer.json' ), true );
    $composer = remove_composer_packages( $composer );

    if ( is_empty_or_false( $config[ 'grumphp' ] ) ) {
        unset( $composer[ 'require-dev' ][ 'phpro/grumphp' ] );
        $clio->styleLine( '😎 Remove GrumPHP done', $info );
    }
    
    if ( is_empty_or_false( $config[ 'phpstan' ] ) ) {
        unset( $composer[ 'require-dev' ][ 'szepeviktor/phpstan-wordpress' ] );
        unset( $composer[ 'require-dev' ][ 'szepeviktor/phpstan-phpdoc' ] );
        $clio->styleLine( '😎 Remove PHPStan WordPress support done', $info );
    }

    if ( is_empty_or_false( $config[ 'unit-test' ] ) ) {
        unset( $composer[ 'require-dev' ][ 'lucatume/wp-browser' ] );
        unset( $composer[ 'require-dev' ][ 'lucatume/function-mocker' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/module-asserts' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/module-phpbrowser' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/module-webdriver' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/module-db' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/module-filesystem' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/module-cli' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/module-rest' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/util-universalframework' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/codeception' ] );
        unset( $composer[ 'require-dev' ][ 'codeception/codeception-progress-reporter' ] );
        unset( $composer[ 'require-dev' ][ 'phpunit/phpunit' ] );
        $clio->styleLine( '😎 Remove Codeception done', $info );
    }

    if ( count( $composer[ 'require-dev' ] ) === 0 ) {
        unset( $composer[ 'require-dev' ] );
    }
    
    if ( isset( $composer[ 'repositories' ] ) && count( $composer[ 'repositories' ] ) === 0 ) {
        unset( $composer[ 'repositories' ] );
    }

    if ( empty( $composer[ 'extra' ][ 'installer-paths' ][ 'vendor/{$name}/' ] ) ) {
        unset( $composer[ 'extra' ] );
    }
    
    $composer = remove_folder_for_autoload( $composer );
    
    $clio->styleLine( '😎 Cleaning Composer file', $info );
    
    $composer = json_encode( $composer, JSON_PRETTY_PRINT );
    $composer = replace_author_strings( $composer );
    
    file_put_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/composer.json', $composer );
}

/**
 * Remove composer packages that require additional stuff
 *
 * @param string $package The package to parse_config.
 * @param string $composer The composer.json content.
 * @return array
 */
function remove_specific_composer_respositories( $package, $composer ) {    
    if ( strpos( $package, 'wp-custom-bulk-actions' ) !== false ) {
        $composer = remove_composer_autoload( $composer, 'wp-custom-bulk-actions' );
        $composer = remove_composer_repositories( $composer, 'wp-custom-bulk-actions' );
        unset( $composer[ 'extra' ][ 'installer-paths' ][ 'vendor/{$name}/' ][ 3 ] );
    }
    
    if ( strpos( $package, 'cmb2-grid' ) !== false ) {
        unset( $composer[ 'extra' ][ 'installer-paths' ][ 'vendor/{$name}/' ][ 1 ] );
    } else if ( strpos( $package, 'cmb2' ) !== false ) {
        unset( $composer[ 'extra' ][ 'installer-paths' ][ 'vendor/{$name}/' ][ 0 ] );
    }
    
    if ( strpos( $package, 'wp-cache-remember' ) !== false ) {
        unset( $composer[ 'extra' ][ 'installer-paths' ][ 'vendor/{$name}/' ][ 2 ] );
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
    global $config, $clio, $info;
    $clio->styleLine( '😎 Remove Composer packages', $info );
    foreach ( $config as $key => $value ) {
        if ( strpos( $key, 'libraries_' ) !== false ) {
            if ( empty( $value ) ) {
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
            unset( $composer[ 'autoload' ][ 'files' ] );
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
 * Remove the autoload folders that are not avalaible
 *
 * @param array  $composer The composer.json content.
 * @return array
 */
function remove_folder_for_autoload( $composer ) {
    if ( isset( $composer[ 'autoload' ] ) ) {
        foreach ( $composer[ 'autoload' ][ 'psr-4' ] as $key => $path ) {
            $there_is_only_index_file = count_files_in_a_folder( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/' . $path );
            if ( $there_is_only_index_file === 1 ) {
                unset( $composer[ 'autoload' ][ 'psr-4' ][ $key ] );
            }
        }
        $composer[ 'autoload' ][ 'psr-4' ] = $composer[ 'autoload' ][ 'psr-4' ]; 
    }
    
    return $composer;
}

/**
 * Replace some keywords with based ones from the plugin name
 *
 * @param string  $composer The composer.json content.
 * @return string
 */
function replace_author_strings( $composer ) {
    global $config;
    
    $composer = str_replace( "wpbp\/wordpress-plugin-boilerplate-powered", strtolower( str_replace( ' ', '-', $config[ 'author_name' ] ) . '/' . str_replace( ' ', '-', $config[ 'plugin_name' ] ) ), $composer );
    $composer = str_replace( 'author_name', $config[ 'author_name' ], $composer );
    $composer = str_replace( 'http:\/\/author.url', $config[ 'author_url' ], $composer );
    $composer = str_replace( 'author@email.it', $config[ 'author_email' ], $composer );

    return $composer;
}
