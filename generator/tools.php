<?php

/**
 * All the functions about tools management
 */ 

/**
 * Download the phpcs file
 */
function download_phpcs_standard() {
    global $config, $clio, $info;
    if ( !is_empty_or_false( $config[ 'phpcs-standard' ] ) ) {
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
        exec( 'cd "' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '"; git init' );
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
        if ( !$cmd[ 'no-download' ] ) {
            $clio->styleLine( '😀 Grunt install in progress', $info );
            $output = '';
            exec( 'cd "' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '"; npm install 2>&1', $output );
            $clio->styleLine( '😎 Grunt install done', $info );
        }
        
        return;
    }
        
    unlink( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/Gruntfile.js' );
    unlink( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/package.json' );
    remove_file_folder( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/assets/sass' );
    $clio->styleLine( '😀 Grunt removed', $info );
}


/**
 * Clean the grumphp file
 *
 * @global array $config
 * @global object $clio
 * @global object $info
 */
function grumphp() {
    global $config, $clio, $info;
    if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/grumphp.yml' ) ) {
        $grumphp = yaml_parse_file ( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/grumphp.yml' );
        if ( !is_empty_or_false( $config[ 'grunt' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'grunt' ] );
            $clio->styleLine( '😀 Grunt removed from GrumPHP', $info );
        }
        
        if ( !is_empty_or_false( $config[ 'phpstan' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'phpstan' ] );
            $clio->styleLine( '😀 PHPStan removed from GrumPHP', $info );
        }
        
        if ( !is_empty_or_false( $config[ 'unit-test' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'codeception' ] );
            $clio->styleLine( '😀 Codeception removed from GrumPHP', $info );
        }
        
        if ( !is_empty_or_false( $config[ 'phpcs-standard' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'phpcs' ] );
            $clio->styleLine( '😀 PHPCS removed from GrumPHP', $info );    
        }
        
        yaml_emit_file( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/grumphp.yml', $grumphp );
    }
}
