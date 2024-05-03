<?php

function joinads_loader_add_admin_menu() {
    add_menu_page(
        'Join Ads Loader Settings',   // Título da página
        'Join Ads Loader',            // Título do menu
        'manage_options',             // Capacidade necessária para ver este menu
        'join_ads_loader',            // Slug do menu
        'joinads_loader_options_page', // Função que renderiza a página de opções
        'dashicons-admin-generic',    // Ícone do menu
        6                             // Posição do menu
    );
}
add_action('admin_menu', 'joinads_loader_add_admin_menu');

function joinads_loader_options_page() {
    ?>
    <div class="wrap">
        <h2>Join Ads Loader Settings</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('joinAdsLoader');
            do_settings_sections('joinAdsLoader');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}



function joinads_loader_settings_init() {
    register_setting('joinAdsLoader', 'joinads_loader_settings');

    add_settings_section(
        'joinads_loader_joinAdsLoader_section',
        __('Customize your loader settings', 'wordpress'),
        'joinads_loader_settings_section_callback',
        'joinAdsLoader'
    );

    add_settings_field(
        'joinads_loader_color',
        __('Loader Color', 'wordpress'),
        'joinads_loader_color_render',
        'joinAdsLoader',
        'joinads_loader_joinAdsLoader_section'
    );

    add_settings_field(
        'joinads_loader_timeout',
        __('Loader Timeout (in seconds)', 'wordpress'),
        'joinads_loader_timeout_render',
        'joinAdsLoader',
        'joinads_loader_joinAdsLoader_section'
    );

    add_settings_field(
        'joinads_loader_ad_block',
        __('Ad Block to wait for', 'wordpress'),
        'joinads_loader_ad_block_render',
        'joinAdsLoader',
        'joinads_loader_joinAdsLoader_section'
    );
}
add_action('admin_init', 'joinads_loader_settings_init');


function joinads_loader_color_render() {
    $options = get_option('joinads_loader_settings');
    ?>
    <input type='text' name='joinads_loader_settings[joinads_loader_color]' value='<?php echo $options['joinads_loader_color']; ?>' class='my-color-field'>
    <?php
}

function joinads_loader_timeout_render() {
    $options = get_option('joinads_loader_settings');
    ?>
    <input type='number' name='joinads_loader_settings[joinads_loader_timeout]' value='<?php echo $options['joinads_loader_timeout']; ?>'>
    <?php
}

function joinads_loader_ad_block_render() {
    $options = get_option('joinads_loader_settings');
    ?>
    <input type='text' name='joinads_loader_settings[joinads_loader_ad_block]' value='<?php echo $options['joinads_loader_ad_block']; ?>'>
    <?php
}

function joinads_loader_settings_section_callback() {
    echo __('Set your preferences for the Join Ads Loader.', 'wordpress');
}

function joinads_loader_options_page() {
    ?>
    <div class="wrap">
        <h2>Join Ads Loader Settings</h2>
        <form action='options.php' method='post'>
            <?php
            settings_fields('joinAdsLoader');
            do_settings_sections('joinAdsLoader');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function joinads_loader_enqueue_color_picker($hook_suffix) {
    if('toplevel_page_join_ads_loader' !== $hook_suffix)
        return;

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('joinads-loader-script-handle', plugins_url('my-script.js', __FILE__ ), array('wp-color-picker'), false, true);
}
add_action('admin_enqueue_scripts', 'joinads_loader_enqueue_color_picker');

// In your JavaScript file:
jQuery(document).ready(function($){
    $('.my-color-field').wpColorPicker();
});
