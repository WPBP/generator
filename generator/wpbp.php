<?php

/**
 * The functions used to run the generator
 */
 
use LightnCandy\LightnCandy;
use LightnCandy\Runtime;
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
        if ( !copy( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'wpbp.json', getcwd() . DIRECTORY_SEPARATOR . 'wpbp.json' ) ) {
            $clio->clear()->style( $error )->display( "Failed to copy wpbp.json..." );
            return;
        }
        
        $clio->clear()->style( $info )->display( "😀 wpbp.json generated" )->newLine()->clear();
        exit();
        
        return;
    }
    
    if ( !file_exists( getcwd() . DIRECTORY_SEPARATOR . 'wpbp.json' ) ) {
        $clio->clear()->style( $error )->display( "😡 wpbp.json file missing..." )->newLine();
        $clio->style( $error )->display( "😉 Generate it with: wpbp-generator --json" )->newLine();
        $clio->style( $error )->display( "Forget a hipster Q&A procedure and fill that JSON with your custom configuration!" )->newLine();
        $clio->style( $error )->display( "👉 Let's do your changes and execute the script again! Use the --dev parameter to use the development version of the boilerplate!" )->newLine()->newLine();
        $clio->clear()->style( $info )->display( "Help: wpbp-generator --help 😉" )->newLine()->clear();
        exit();
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

    $clio->clear()->style( $info )->display( "😎 Downloading " . $version . " package" )->newLine();

    $download = @file_get_contents( 'http://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered/archive/' . $version . '.zip' );
    if ( $download === false ) {
        $clio->clear()->style( $error )->display( "😡 The " . $version . " version is not yet avalaible! Use the --dev parameter!" )->newLine();
        die();
    }

    file_put_contents( 'plugin.zip', $download );

    extract_wpbp();
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
        if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . 'plugin.zip' ) ) {
            if ( file_exists( getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG ) ) {
                $clio->clear()->style( $error )->display( "Folder " . WPBP_PLUGIN_SLUG . " already exist!" )->newLine();
                exit();
            }

            $clio->clear()->style( $info )->display( "Extract Boilerplate" )->newLine();
            try {
                $zip = new ZipArchive;
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
                exit();
            }
            $res = $zip->open( getcwd() . DIRECTORY_SEPARATOR . 'plugin.zip' );
            if ( $res === true ) {
                $zip->extractTo( getcwd() . DIRECTORY_SEPARATOR . 'plugin_temp' . DIRECTORY_SEPARATOR );
                $zip->close();
                $version = WPBP_VERSION;

                if ( $cmd[ 'dev' ] ) {
                    $version = 'master';
                }

                try {
                    rename( getcwd() . DIRECTORY_SEPARATOR . 'plugin_temp' . DIRECTORY_SEPARATOR . 'WordPress-Plugin-Boilerplate-Powered-' . $version . DIRECTORY_SEPARATOR . 'plugin-name', getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG );
                    rename( getcwd() . DIRECTORY_SEPARATOR . 'plugin_temp' . DIRECTORY_SEPARATOR . 'WordPress-Plugin-Boilerplate-Powered-' . $version . DIRECTORY_SEPARATOR . '.gitignore', getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . DIRECTORY_SEPARATOR . '.gitignore' );
                    remove_file_folder( getcwd() . DIRECTORY_SEPARATOR . 'plugin_temp' );
                    if ( !$cmd[ 'dev' ] ) {
                        remove_file_folder( getcwd() . DIRECTORY_SEPARATOR . 'plugin.zip' );
                    }
                } catch ( Exception $e ) {
                    $clio->clear()->style( $error )->display( $e );
                }

                $clio->clear()->style( $info )->display( "Boilerplate Extracted" )->newLine();
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
    global $clio, $info;
    $files = get_files();
    foreach ( $files as $file ) {
        $file_content = file_get_contents( $file );
        $new_file_content = replace_name_slug( $config, $file_content );
        $new_file_content = parse_conditional_template( $file, $config, $new_file_content );

        if ( strpos( $file, '.gitignore' ) ) {
            $new_file_content = str_replace( 'plugin-name/', '', $new_file_content );
        }

        if ( $new_file_content !== $file_content ) {
            file_put_contents( $file, $new_file_content );
        }
    }

    $clio->clear()->style( $info )->display( "Generation done, I am superfast! You: (ʘ_ʘ)\n" )->newLine();
    git_init();
    strip_packagejson();
    $clio->clear()->style( $info )->display( "😀 NPM install in progress (takes a while... sadly)" )->newLine();
    exec( 'cd "' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '"; npm install 2>&1', $output );
    $clio->clear()->style( $info )->display( "😎 NPM install done" )->newLine();
    exec( 'cd "' . getcwd() . DIRECTORY_SEPARATOR . WPBP_PLUGIN_SLUG . '"; npm run build 2>&1', $output );
    $clio->clear()->style( $info )->display( "😎 NPM build done" )->newLine();
    grumphp();
}

function parse_conditional_template( $file, $config, $file_content ) {
    global $cmd;
    if ( $cmd[ 'dev' ] ) {
        print_v( 'Parsing ' . $file );
        $lc         = LightnCandy::compile(
                $file_content,
                array(
                    'flags' => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_RENDER_DEBUG,
                )
        );
        $lc_prepare = LightnCandy::prepare( $lc );
        $file_content = $lc_prepare( $config, array( 'debug' => Runtime::DEBUG_ERROR_EXCEPTION | Runtime::DEBUG_ERROR_LOG ) );

        return $file_content;
    }
    
    $lc         = LightnCandy::compile( $file_content );
    $lc_prepare = LightnCandy::prepare( $lc );
    $file_content    = $lc_prepare( $config );
    
    return $file_content;
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
    $config = json_decode( file_get_contents( getcwd() . DIRECTORY_SEPARATOR . 'wpbp.json' ), true );
    // Detect a misleading json file
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $clio->clear()->style( $error )->display( "😡 Your JSON is broken!" )->newLine();
        exit;
    }

    $config         = array_to_var( $config );

    $config_default = json_decode( file_get_contents( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'wpbp.json' ), true );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $clio->clear()->style( $error )->display( "😡 WPBP JSON is broken!" )->newLine();
        exit;
    }

    $config_default = array_to_var( $config_default, true );
    if ( empty( $config_default ) ) {
        $clio->style( $error )->display( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'wpbp.json not found!' )->newLine();
        exit;
    }

    foreach ( $config_default as $key => $value ) {
        if ( !isset( $config[ $key ] ) ) {
            $config[ $key ] = '';
        }
    }

    return $config;
}
