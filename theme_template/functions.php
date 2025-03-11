<?php
//featured image
add_theme_support( 'post-thumbnails' );
// Customize logo
add_theme_support('custom-logo', array());

// Remove <p> and <br/> from Contact Form 7
add_filter('wpcf7_autop_or_not', '__return_false');
