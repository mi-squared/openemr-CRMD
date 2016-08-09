<?php

function render_insurance_default_javascript()
{
    include __DIR__."/scripts/insurance_default.php";
}
add_action( PATIENT_REPORT_BEFORE_HTML_END, 'render_insurance_default_javascript' );

function add_routing_slip_navigation()
{
    include __DIR__."/scripts/ritter_left_nav.php";
}
add_action( LEFT_NAV_BEFORE_HTML_END, 'add_routing_slip_navigation' );



