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
        exec( 'cd "' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '"; git init &> /dev/null' );
        $clio->clear()->style( $info )->display( "😎 .git folder generated" )->newLine();
        $gitignore = getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/.gitignore';
        file_put_contents( $gitignore, str_replace( '/plugin-name/', '', file_get_contents( $gitignore ) ) );
        $clio->clear()->style( $info )->display( "😎 .gitignore file generated" )->newLine();
        return;
    } 
    
    remove_file_folder( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/.gitignore' );
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
        if ( is_empty_or_false( $config[ 'phpstan' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'phpstan' ] );
            $clio->clear()->style( $info )->display( "😀 PHPStan removed from GrumPHP" )->newLine();
        }
        
        if ( is_empty_or_false( $config[ 'unit-test' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'codeception' ] );
            $clio->clear()->style( $info )->display( "😀 Codeception removed from GrumPHP" )->newLine();
        }
        
        if ( is_empty_or_false( $config[ 'phpcs' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'phpcs' ] );
            $clio->clear()->style( $info )->display( "😀 PHPCS removed from GrumPHP" )->newLine();
        }
        
        if ( is_empty_or_false( $config[ 'phpmd' ] ) ) {
            unset( $grumphp[ 'parameters' ][ 'tasks' ][ 'phpmd' ] );
            $clio->clear()->style( $info )->display( "😀 PHPMD removed from GrumPHP" )->newLine();
        }
        
        yaml_emit_file( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '/grumphp.yml', $grumphp );
    }
}
