<?php

function compia_carregar_recursos(): void
{
    wp_enqueue_style(
        'compia-main',
        get_stylesheet_directory_uri() . '/assets/css/main.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'compia-main',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        [],
        '1.0.0',
        true
    );
}

add_action('wp_enqueue_scripts', 'compia_carregar_recursos', 20);