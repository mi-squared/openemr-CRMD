<?php

$GLOBALS['migrate'] = new \Migration\Migrate();

function kickoff_migrations( $args ) 
{
    $GLOBALS['migrate']->onPluginsStarted( $args );
}
add_action( 'plugins_started', 'kickoff_migrations' );

function migrate_up()
{
    $GLOBALS['migrate']->migrate( \Migration\Migrate::UP );
}
add_action( 'migrate_up', 'migrate_up' );

function migrate_down()
{
    $GLOBALS['migrate']->migrate( \Migration\Migrate::DOWN );
}
add_action( 'migrate_down', 'migrate_down' );
