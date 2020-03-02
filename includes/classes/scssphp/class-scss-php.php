<?php

require_once( PIP_PATH . 'assets/libs/scssphp/scss.inc.php' );

use ScssPhp\ScssPhp\Compiler;

if ( !class_exists( 'PIP_Scss_Php' ) ) {
    class PIP_Scss_Php {

        private $dirs;
        private $compile_method;
        private $compiler;
        private $compile_errors;
        private $sourcemaps;
        private $variables;
        private $scss_dirs;
        private $cache = PIP_PATH . 'cache/';

        /**
         * PIP_Scss_Php constructor.
         *
         * @see https://scssphp.github.io/scssphp/docs/
         *
         * @param array $scss_args
         */
        public function __construct( $scss_args = array() ) {
            // Parse args
            $scss_args = wp_parse_args( $scss_args, array(
                'dirs'       => array(
                    array(
                        'scss_dir' => PIP_PATH . 'assets/src/scss/',
                        'css_dir'  => PIP_PATH . 'assets/dist/css/',
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
            $variables = apply_filters( 'pip/scss/variables', (array) $scss_args['variables'] );
            if ( !empty( $variables ) ) {
                foreach ( $variables as $key => $value ) {
                    if ( strlen( trim( $value ) ) == 0 ) {
                        unset( $variables[ $key ] );
                    }
                }
            }
            $this->variables = $variables;
            $this->compiler->setVariables( $variables );

            // Set SCSS paths
            foreach ( $this->dirs as $dir ) {
                $this->scss_dirs[] = acf_maybe_get( $dir, 'scss_dir' );
            }
            $this->compiler->setImportPaths( $this->scss_dirs );

            // Set Source map
            $this->compiler->setSourceMap( constant( 'ScssPhp\ScssPhp\Compiler::' . $this->sourcemaps ) );
        }

        /**
         * Put compiled and minified SCSS in wanted directory
         */
        public function compile() {
            $input_files = array();

            // Loop through directories
            foreach ( $this->dirs as $dir ) {

                // If SCSS code, compile directly
                if ( acf_maybe_get( $dir, 'scss_code' ) ) {

                    // Get CSS file name
                    $css_to_name = 'style.css';
                    if ( acf_maybe_get( $dir, 'css_file' ) ) {
                        $css_to_name = $dir['css_file'];
                    }

                    // Get CSS file path
                    $css_to = $dir['css_dir'] . $css_to_name;

                    // Launch compiler
                    $this->compiler( $dir['scss_code'], $css_to, $this, $dir['css_dir'] );

                } elseif ( acf_maybe_get( $dir, 'scss_file' ) ) {

                    // If file is specified
                    array_push( $input_files, $dir['scss_file'] );

                } else {

                    // Get all .scss files that do not start with '_'
                    foreach ( new DirectoryIterator( $dir['scss_dir'] ) as $file ) {
                        if ( substr( $file, 0, 1 ) != '_' && pathinfo( $file->getFilename(), PATHINFO_EXTENSION ) == 'scss' ) {
                            array_push( $input_files, $file->getFilename() );
                        }
                    }
                }
            }

            // Browse all directories
            if ( $input_files ) {
                foreach ( $input_files as $scss_file ) {

                    // For each input file, find matching css file and compile
                    foreach ( $this->dirs as $dir ) {

                        // Get SCSS file path
                        $scss_from = $dir['scss_dir'] . $scss_file;

                        // Get CSS file name
                        if ( acf_maybe_get( $dir, 'css_file' ) ) {
                            $css_to_name = acf_maybe_get( $dir, 'css_file' );
                        } else {
                            $css_to_name = preg_replace( "/\.[^$]*/", '.css', $scss_file );
                        }

                        // Get CSS file path
                        $css_to = $dir['css_dir'] . $css_to_name;

                        // Launch compiler
                        if ( file_exists( $scss_from ) ) {
                            $this->compiler( $scss_from, $css_to, $this );
                        }
                    }
                }
            }

            // If no compile errors
            if ( count( $this->compile_errors ) < 1 ) {

                // Browse all directories
                foreach ( $this->dirs as $dir ) {

                    // If pilopress directory in theme is writable
                    if ( is_writable( $dir['css_dir'] ) ) {

                        // Browse all cache files
                        foreach ( new DirectoryIterator( $this->cache ) as $cache_file ) {

                            // If not current file, skip
                            if ( $cache_file->getFilename() !== $dir['css_file'] ) {
                                continue;
                            }

                            // If there's a CSS file in cache directory
                            if ( pathinfo( $cache_file->getFilename(), PATHINFO_EXTENSION ) == 'css' ) {

                                // Put content in CSS file in pilopress directory in theme
                                file_put_contents( $dir['css_dir'] . $cache_file->getFilename(), file_get_contents( $this->cache . $cache_file->getFilename() ) );

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

            // SCSS compilation errors
            if ( !empty( $this->compile_errors ) ) {
                acf_log( 'DEBUG: SCSS Compile Errors', $this->compile_errors );
            }
        }

        /**
         * Compiles and minifies $scss_from into $css_to file, in cache directory
         *
         * @param $scss_from
         * @param $css_to
         * @param $instance
         * @param bool $css_dir
         */
        private function compiler( $scss_from, $css_to, $instance, $css_dir = false ) {
            // Browse all directories
            foreach ( $instance->dirs as $dir ) {

                // Is current scss dir ?
                if ( file_exists( $scss_from ) && strpos( $scss_from, $dir['scss_dir'] ) !== 0 ) {
                    continue;
                }

                // Is current css dir ?
                if ( file_exists( $css_dir ) && strpos( $css_dir, $dir['css_dir'] ) !== 0 ) {
                    continue;
                }

                // Is current css file ?
                if ( strpos( $css_to, $dir['css_file'] ) !== ( strlen( $css_to ) - strlen( $dir['css_file'] ) ) ) {
                    continue;
                }

                // If cache directory is not writable
                if ( !is_writable( $this->cache ) ) {

                    // Log error
                    $errors = array(
                        'file'    => __( 'CSS Directories: ', 'pilopress' ) . $dir['css_dir'],
                        'message' => __( 'File Permission Error, permission denied. Please make the cache directory writable.', 'pilopress' ),
                    );
                    array_push( $instance->compile_errors, $errors );
                }

                // Get SCSS content
                if ( acf_maybe_get( $dir, 'scss_code' ) ) {
                    $from_content = $dir['scss_code'];
                } else {
                    $from_content = file_get_contents( $scss_from );
                }

                try {
                    // Map file
                    $source_map_file = basename( $css_to ) . '.map';

                    // Prepended to "source" field entries for relocating source files
                    $source_root = '/';

                    // URL of the map file
                    $source_map_url = get_template_directory_uri() . '/' . $source_map_file;

                    // Base path for filename normalization
                    $source_map_base_path = rtrim( ABSPATH, '/' );

                    // Absolute path to write .map file
                    $source_map_write_to = parse_url( $dir['css_dir'] . $source_map_file, PHP_URL_PATH );

                    // Set source map options
                    $this->compiler->setSourceMapOptions( array(
                        'sourceMapWriteTo'  => $source_map_write_to,
                        'sourceMapURL'      => $source_map_url,
                        'sourceMapBasepath' => $source_map_base_path,
                        'sourceRoot'        => $source_root,
                        'sourceMapFilename' => $source_map_file,
                    ) );

                    // Compile file
                    $css = $this->compiler->compile( $from_content, $dir['scss_dir'] );

                    // Put CSS content in cache file
                    file_put_contents( $this->cache . basename( $css_to ), $css );

                } catch ( Exception $e ) {

                    // Log error
                    $errors = array(
                        'file'    => !is_string( $scss_from ) ? basename( $scss_from ) : 'Error with SCSS code',
                        'message' => $e->getMessage(),
                    );
                    array_push( $instance->compile_errors, $errors );
                }
            }

            // SCSS compilation errors
            if ( !empty( $instance->compile_errors ) ) {
                acf_log( 'DEBUG: SCSS Compile Errors', $instance->compile_errors );
            }
        }
    }

    // Instantiate class
    new PIP_Scss_Php();
}