<?php

/* BUTTONS */

Kirki::add_section( 'buttons_style', array(
    'title'             => __( 'Primary button', 'ventcamp' ),
    'panel'             => 'buttons',
    'priority'          => 10,
    'capability'        => 'edit_theme_options',
) );

Kirki::add_field( 'ventcamp_theme_config', array(
    'type'              => 'typography',
    'settings'          => 'button_font',
    'label'             => __( 'Button font', 'ventcamp' ),
    'section'           => 'buttons_style',
    'priority'          => 1,
    'default'           => array(
        'font-family'       => '"PT Sans Caption", Roboto',
        'font-weight'       => '700',
        'line-height'       => '1',
        'letter-spacing'    => '0.59em'
    ),
    'choices'           => array(
        'font-style'        => true,
        'font-family'       => true,
        'font-weight'       => true,
        'line-height'       => true,
        'letter-spacing'    => true
    ),
    'transport'         => 'postMessage',
    'less'              => 'button_font'
) );

Kirki::add_field( 'ventcamp_theme_config', array(
    'settings'          => 'button_text_color',
    'label'             => __( 'Text color', 'ventcamp' ),
    'section'           => 'buttons_style',
    'type'              => 'color',
    'default'           => '#ffffff',
    'priority'          => 2,
    'transport'         => 'postMessage',
    'less'              => 'button_text_color'
) );

Kirki::add_field( 'ventcamp_theme_config', array(
    'settings'          => 'button_background_color',
    'label'             => __( 'Background color', 'ventcamp' ),
    'section'           => 'buttons_style',
    'type'              => 'color-alpha',
    'default'           => 'rgba(254, 73, 24, 1)',
    'priority'          => 3,
    'transport'         => 'postMessage',
    'less'              => 'button_background_color'
) );

Kirki::add_field( 'ventcamp_theme_config', array(
    'type'              => 'slider',
    'settings'          => 'button_border_width',
    'label'             => __( 'Border width', 'ventcamp' ),
    'section'           => 'buttons_style',
    'default'           => 0,
    'priority'          => 4,
    'choices'           => array(
        'min'               => 0,
        'max'               => 10,
        'step'              => 1
    ),
    'transport'         => 'postMessage',
    'less'              => 'button_border_width'
) );

Kirki::add_field( 'ventcamp_theme_config', array(
    'settings'          => 'button_border_color',
    'label'             => __( 'Border color', 'ventcamp' ),
    'section'           => 'buttons_style',
    'type'              => 'color-alpha',
    'default'           => 'rgba(255, 255, 255, 0)',
    'priority'          => 5,
    'transport'         => 'postMessage',
    'less'              => 'button_border_color'
) );

Kirki::add_field( 'ventcamp_theme_config', array(
    'type'              => 'slider',
    'settings'          => 'button_border_radius',
    'label'             => __( 'Border radius', 'ventcamp' ),
    'section'           => 'buttons_style',
    'default'           => 4,
    'priority'          => 6,
    'choices'           => array(
        'min'               => 0,
        'max'               => 50,
        'step'              => 1
    ),
    'transport'         => 'postMessage',
    'less'              => 'button_border_radius'
) );

// Kirki::add_field( 'ventcamp_theme_config', array(
//     'settings' => 'buttons_style_add_icon',
//     'label'    => __( 'Add button icon', 'ventcamp' ),
//     'section'  => 'buttons_style',
//     'type'     => 'checkbox',
//     'priority' => 9,
//     'default'  => '1'
// ) );

// Kirki::add_field( 'ventcamp_theme_config', array(
//     'settings' => 'buttons_style_icon',
//     'label'    => __( 'Icon', 'ventcamp' ),
//     'section'  => 'buttons_style',
//     'type'     => 'select-icon',
//     'default'  => 'left',
//     'priority' => 10,
//     'choices'  => ventcamp_get_icons_list(),
//     'active_callback' => 'ventcamp_button_icon_callback'
// ) );

// Kirki::add_field( 'ventcamp_theme_config', array(
//     'settings' => 'buttons_style_icon_color',
//     'label'    => __( 'Icon color', 'ventcamp' ),
//     'section'  => 'buttons_style',
//     'type'     => 'color',
//     'default'  => '#ffffff',
//     'priority' => 11,
//     'active_callback' => 'ventcamp_button_icon_callback'
// ) );

// Kirki::add_field( 'ventcamp_theme_config', array(
//     'settings' => 'buttons_style_icon_placement',
//     'label'    => __( 'Icon placement', 'ventcamp' ),
//     'section'  => 'buttons_style',
//     'type'     => 'select',
//     'default'  => 'left',
//     'priority' => 12,
//     'choices'     => array(
//             'left' => __( 'Left', 'ventcamp' ),
//             'right' => __( 'Right', 'ventcamp' )
//         ),
//     'active_callback' => 'ventcamp_button_icon_callback'
// ) );