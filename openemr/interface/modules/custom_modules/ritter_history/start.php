<?php

function add_ritter_globals( & $args )
{
    $globals = new \Globals\Globals( $args );
    $setting = new \Globals\Setting( 'Show History Widget', 'bool', '1', 'Show history widget on demographics page.' );
    $globals->appendToSection( 'Features', 'show_history_widget', $setting );
    $args = $globals->getData();
}

add_action( 'globals_init', 'add_ritter_globals' );

function render_history_widget()
{
    include __DIR__."/views/history_widget.php";
}

add_action( DEMOGRAPHICS_AFTER_APPOINTMENT_WIDGET, 'render_history_widget' );