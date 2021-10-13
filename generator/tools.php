<?php

/**
 * All the functions about tools management
 */ 

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
        $clio->display( "😎 .git folder generated\n" )->style( $info )->clear();
        $gitignore = getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/.gitignore';
        file_put_contents( $gitignore, str_replace( '/plugin-name/', '', file_get_contents( $gitignore ) ) );
        $clio->display( "😎 .gitignore file generated\n" )->style( $info )->clear();
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
            $clio->display( "😀 Grunt install in progress\n" )->style( $info )->clear();
            $output = '';
            exec( 'cd "' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '"; npm install 2>&1', $output );
            $clio->display( "😎 Grunt install done\n" )->style( $info )->clear();
        }
        
        return;
    }
        
    unlink( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/Gruntfile.js' );
    unlink( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/package.json' );
    remove_file_folder( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/assets/sass' );
    $clio->display( "😀 Grunt removed\n" )->style( $info )->clear();
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
            $clio->display( "😀 Grunt removed from GrumPHP\n" )->style( $info )->clear();
        }
        
        if ( !is_empty_or_false( $config[ 'phpstan' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'phpstan' ] );
            $clio->display( "😀 PHPStan removed from GrumPHP\n" )->style( $info )->clear();
        }
        
        if ( !is_empty_or_false( $config[ 'unit-test' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'codeception' ] );
            $clio->display( "😀 Codeception removed from GrumPHP\n" )->style( $info )->clear();
        }
        
        if ( !is_empty_or_false( $config[ 'phpcs' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'phpcs' ] );
            $clio->display( "😀 PHPCS removed from GrumPHP\n" )->style( $info )->clear();
        }
        
        if ( !is_empty_or_false( $config[ 'phpmd' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'phpmd' ] );
            $clio->display( "😀 PHPMD removed from GrumPHP\n" )->style( $info )->clear();
        }
        
        yaml_emit_file( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/grumphp.yml', $grumphp );
    }
}
