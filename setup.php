<?php

function joinads_loader_add_admin_menu() {
    add_options_page(
        'Join Ads Loader Settings',
        'Join Ads Loader',
        'manage_options',
        'join_ads_loader',
        'joinads_loader_options_page'
    );
}
add_action('admin_menu', 'joinads_loader_add_admin_menu');


function joinads_loader_settings_init() {
    register_setting('joinAdsLoader', 'joinads_loader_settings');

    add_settings_section(
        'joinads_loader_joinAdsLoader_section',
        __('Configurações do Plugin Join Ads Loader', 'wordpress'),
        'joinads_loader_settings_section_callback',
        'joinAdsLoader'
    );

    add_settings_field(
        'joinads_loader_color',
        __('Cor do Loader', 'wordpress'),
        'joinads_loader_color_render',
        'joinAdsLoader',
        'joinads_loader_joinAdsLoader_section'
    );

    add_settings_field(
        'joinads_loader_timeout',
        __('Tempo do Loader (em segundos)', 'wordpress'),
        'joinads_loader_timeout_render',
        'joinAdsLoader',
        'joinads_loader_joinAdsLoader_section'
    );

    add_settings_field(
        'joinads_loader_ad_block',
        __('Nome do Bloco de Anuncio', 'wordpress'),
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
    echo '<div class="wrap">';
    echo '<h2>Join Ads Loader Settings</h2>';
    echo '<form action="options.php" method="post">';

    if (function_exists('settings_fields')) {
        settings_fields('joinAdsLoader');
    } else {
        echo 'Erro: função settings_fields não existe.';
    }

    if (function_exists('do_settings_sections')) {
        do_settings_sections('joinAdsLoader');
    } else {
        echo 'Erro: função do_settings_sections não existe.';
    }

    if (function_exists('submit_button')) {
        submit_button();
    } else {
        echo 'Erro: função submit_button não existe.';
    }

    echo '</form>';
    echo '</div>';
}


function joinads_loader_enqueue_color_picker($hook_suffix) {
    if ('settings_page_join_ads_loader' !== $hook_suffix) {
        return;
    }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('joinads-loader-script-handle', plugins_url('script.js', __FILE__ ), array('wp-color-picker'), false, true);
}
add_action('admin_enqueue_scripts', 'joinads_loader_enqueue_color_picker');
?>