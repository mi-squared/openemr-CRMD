<?php
/**
 * Define paths to openemr and vendor dir for use in plugins
 */
if ( !defined( 'OPENEMR_DIRECTORY' ) ) {
    define( 'OPENEMR_DIRECTORY', realpath( __DIR__.'/openemr' ) );
}

set_include_path( get_include_path() . PATH_SEPARATOR . OPENEMR_DIRECTORY );

if ( !defined( 'VENDOR_DIRECTORY' ) ) {
    define( 'VENDOR_DIRECTORY', realpath( __DIR__.'/vendor' ) );
}

set_include_path( get_include_path() . PATH_SEPARATOR . VENDOR_DIRECTORY );
