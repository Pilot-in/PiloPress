<?php

require_once( _PIP_PATH . 'assets/libs/scssphp/scss.inc.php' );

use ScssPhp\ScssPhp\Compiler;

if ( !class_exists( 'PIP_Scss_Php' ) ) {
    class PIP_Scss_Php {

        private $dirs;
        private $compile_method;
        private $compiler;
        private $compile_errors;
        private $sourcemaps;
        private $variables;
        private $cache = _PIP_PATH . 'cache/';
        private $scss_dirs;
        private $css_dirs;

        /**
         * PIP_Scss_Php constructor.
         *
         * @see Compiling: https://scssphp.github.io/scssphp/docs/
         *
         * @param array $scss_args
         */
        public function __construct( $scss_args = array() ) {
            // Parse args
            $scss_args = wp_parse_args( $scss_args, array(
                'dirs'       => array(
                    array(
                        'scss_dir' => _PIP_PATH . 'assets/src/scss/',
                        'css_dir'  => _PIP_PATH . 'assets/dist/css/',
                    ),
                ),
                'compiling'  => 'ScssPhp\ScssPhp\Formatter\Crunched',
                'errors'     => 'show',
                'sourcemaps' => 'SOURCE_MAP_FILE',
                'variables'  => array(),
            ) );

            // Set class variables
            $this->dirs           = $scss_args['dirs'];
            $this->compile_method = $scss_args['compiling'];
            $this->sourcemaps     = $scss_args['sourcemaps'];
            $this->compile_errors = array();

            // Instantiate compiler
            $this->compiler = new Compiler();
            $this->compiler->setFormatter( $this->compile_method );

            // Custom SCSS variables
            $variables = apply_filters( 'pip/scss/variables', $scss_args['variables'] );
            if ( !empty( $variables ) ) {
                foreach ( $variables as $key => $value ) {
                    if ( strlen( trim( $value ) ) == 0 ) {
                        unset( $variables[ $key ] );
                    }
                }
            }
            $this->variables = $variables;
            $this->compiler->setVariables( $variables );

            foreach ( $this->dirs as $dir ) {
                $this->scss_dirs[] = $dir['scss_dir'];
                $this->css_dirs[]  = $dir['css_dir'];
            }
            // SCSS paths
            $this->compiler->setImportPaths( $this->scss_dirs );

            // Set Source map
            $this->compiler->setSourceMap( constant( 'ScssPhp\ScssPhp\Compiler::' . $this->sourcemaps ) );
        }

        /**
         * Compiler - Takes scss $in and writes compiled css to $out file
         * Catches errors and puts them the object's compiled_errors property
         *
         * @param $in
         * @param $out
         * @param $instance
         */
        private function compiler( $in, $out, $instance ) {
            // Browse all directories
            foreach ( $instance->dirs as $dir ) {
                if ( strpos( $in, $dir['scss_dir'] ) === 0 ) {
                    // If "cache" directory is writable
                    if ( is_writable( $this->cache ) ) {
                        try {
                            // Map file
                            $source_map_file = basename( $out ) . '.map';

                            // This value is prepended to the individual entry in the "source" field
                            $source_root = '/';

                            // URL of the map file
                            $source_map_url = $source_map_file;

                            // Base path for filename normalization
                            $source_map_base_path = rtrim( ABSPATH, '/' );

                            // Absolute path to a file to write the map to
                            $source_map_write_to = parse_url( $dir['css_dir'] . $source_map_file, PHP_URL_PATH );

                            // Set source map options
                            $this->compiler->setSourceMapOptions( array(
                                'sourceMapWriteTo'  => $source_map_write_to,
                                'sourceMapURL'      => get_template_directory_uri() . '/' . $source_map_url,
                                'sourceMapBasepath' => $source_map_base_path,
                                'sourceRoot'        => $source_root,
                            ) );

                            // Compile file
                            $css = $this->compiler->compile( file_get_contents( $in ), $in );

                            // Put CSS content in cache file
                            file_put_contents( $this->cache . basename( $out ), $css );

                        } catch ( Exception $e ) {
                            // Log error
                            $errors = array(
                                'file'    => basename( $in ),
                                'message' => $e->getMessage(),
                            );
                            array_push( $instance->compile_errors, $errors );
                        }
                    } else {
                        // Log error
                        $errors = array(
                            'file'    => __( 'CSS Directories: ', 'pilopress' ) . $dir['css_dir'],
                            'message' => __( 'File Permission Error, permission denied. Please make the cache directory writable.', 'pilopress' ),
                        );
                        array_push( $instance->compile_errors, $errors );
                    }
                }
            }

            // SASS compilation errors
            if ( !empty( $instance->compile_errors ) ) {
                acf_log( 'DEBUG: SASS Compile Errors', $instance->compile_errors );
            }
        }

        /**
         * Loops through scss directory and compilers files that end with .scss and are not prepend with '_'.
         */
        public function compile() {
            $input_files = array();

            // Loop through directory
            foreach ( $this->dirs as $dir ) {
                if ( acf_maybe_get( $dir, 'scss_file' ) ) {

                    // If file is specified
                    array_push( $input_files, $dir['scss_file'] );
                } else {

                    // Get .scss files that do not start with '_'
                    foreach ( new DirectoryIterator( $dir['scss_dir'] ) as $file ) {
                        if ( substr( $file, 0, 1 ) != '_' && pathinfo( $file->getFilename(), PATHINFO_EXTENSION ) == 'scss' ) {
                            array_push( $input_files, $file->getFilename() );
                        }
                    }
                }
            }

            // Browse all directories
            foreach ( $input_files as $scss_file ) {
                // For each input file, find matching css file and compile
                foreach ( $this->dirs as $dir ) {
                    // Get SCSS file path
                    $input = $dir['scss_dir'] . $scss_file;

                    // Get CSS file name
                    if ( acf_maybe_get( $dir, 'css_file' ) ) {
                        $output_name = acf_maybe_get( $dir, 'css_file' );
                    } else {
                        $output_name = preg_replace( "/\.[^$]*/", '.css', $scss_file );
                    }

                    // Get CSS file path
                    $output = $dir['css_dir'] . $output_name;

                    // Launch compiler
                    if ( file_exists( $input ) ) {
                        $this->compiler( $input, $output, $this );
                    }
                }
            }

            // If no compile errors
            if ( count( $this->compile_errors ) < 1 ) {
                // Browse all directories
                foreach ( $this->dirs as $dir ) {
                    // If pilopress directory in theme is writable
                    if ( is_writable( $dir['css_dir'] ) ) {
                        foreach ( new DirectoryIterator( $this->cache ) as $cache_file ) {

                            // If there's a CSS file in cache directory
                            if ( pathinfo( $cache_file->getFilename(), PATHINFO_EXTENSION ) == 'css' ) {
                                // Put content in CSS file in pilopress directory in theme
                                file_put_contents( $dir['css_dir'] . $cache_file, file_get_contents( $this->cache . $cache_file ) );

                                // Delete cache file on successful write
                                unlink( $this->cache . $cache_file->getFilename() );
                            }
                        }
                    } else {
                        // Log errors
                        $errors = array(
                            'file'    => __( 'CSS Directory: ', 'pilopress' ) . $dir['css_dir'],
                            'message' => __( 'File Permissions Error, permission denied. Please make your CSS directory writable.', 'pilopress' ),
                        );
                        array_push( $this->compile_errors, $errors );
                    }
                }
            }

            // SASS compilation errors
            if ( !empty( $this->compile_errors ) ) {
                acf_log( 'DEBUG: SASS Compile Errors', $this->compile_errors );
            }
        }
    }

    // Instantiate class
    new PIP_Scss_Php();
}