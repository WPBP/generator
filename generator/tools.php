<?php

/**
 * All the functions about tools management
 */ 

/**
 * Download the phpcs file
 */
function download_phpcs_standard() {
    global $config, $clio, $info;
    if ( !empty( $config[ 'phpcs-standard' ] ) && $config[ 'phpcs-standard' ] === false ) {
        $codeat = file_get_contents( $config[ 'phpcs-standard' ] );
        file_put_contents( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/phpcs.xml', $codeat );
        $clio->styleLine( '😎 PHPCS Standard downloaded', $info );
    }
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
        return;
    } 
    
    remove_file_folder( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/.gitignore' );
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
                remove_file_folder( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . DIRECTORY_SEPARATOR . 'public/assets/coffee' );
            }

            if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . DIRECTORY_SEPARATOR . 'admin/assets/coffee' ) ) {
                remove_file_folder( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . DIRECTORY_SEPARATOR . 'admin/assets/coffee' );
            }

            $package    = file( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/package.json' );
            $newpackage = array();
            foreach ( $package as $line => $content ) {
                if ( strpos( $content, 'coffee' ) ) {
                    $newpackage[ $line - 1 ] = str_replace( ',', '', $package[ $line - 1 ] );
                    continue;
                }
                
                $newpackage[] = $package[ $line ];
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
