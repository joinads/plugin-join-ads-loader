<?php

function joinads_loader_add_admin_menu() {
    // Adicionar o menu principal "Join Ads"
    add_menu_page(
        'Join Ads',
        'Join Ads',
        'manage_options',
        'join_ads_loader_main',
        '__return_false', // Usar uma função que não exibe nada
        'dashicons-plugins-checked'
    );

    // Adicionar a subpágina "Loader"
    add_submenu_page(
        'join_ads_loader_main',
        'Loader Manager',
        'Loader Manager',
        'manage_options',
        'join_ads_loader',
        'joinads_loader_options_page'
    );

    // Adicionar a subpágina "ads.txt Manager"
    add_submenu_page(
        'join_ads_loader_main',
        'ads.txt Manager',
        'ads.txt Manager',
        'manage_options',
        'join_ads_txt_manager',
        'joinads_loader_ads_txt_manager_page'
    );

    // Adicionar a subpágina "Config Manager"
      add_submenu_page(
        'join_ads_loader_main',
        'Configurações',
        'Configurações',
        'manage_options',
        'join_ads_config',
        'joinads_loader_ads_config_page'
    );

    // Remove o primeiro subitem redundante (igual ao item principal)
    remove_submenu_page('join_ads_loader_main', 'join_ads_loader_main');
}
add_action('admin_menu', 'joinads_loader_add_admin_menu');




function joinads_loader_register_loader_settings() {
    register_setting('joinAdsLoader', 'joinads_loader_settings');

    add_settings_section(
        'joinads_loader_joinAdsLoader_section',
        __('', 'wordpress'),
        'joinads_loader_settings_section_callback',
        'joinAdsLoader'
    );

    add_settings_field(
        'joinads_loader_color',
        __('Loader Cor:', 'wordpress'),
        'joinads_loader_color_render',
        'joinAdsLoader',
        'joinads_loader_joinAdsLoader_section'
    );

    add_settings_field(
        'joinads_loader_timeout',
        __('Loader:<br /><small style="font-size: 10px;">(segundos para aguardar o anúncio)</small>', 'wordpress'),
        'joinads_loader_timeout_render',
        'joinAdsLoader',
        'joinads_loader_joinAdsLoader_section'
    );

    add_settings_field(
        'joinads_loader_timeout_home',
        __('Home Loader:<br /><small style="font-size: 10px;">(segundos para aguardar o anúncio)</small>', 'wordpress'),
        'joinads_loader_timeout_home_render',
        'joinAdsLoader',
        'joinads_loader_joinAdsLoader_section'
    );

    add_settings_field(
        'joinads_loader_ad_block',
        __('AdUnit Name:<br /><small style="font-size: 10px;">(Nome do bloco ex: Content1)</small>', 'wordpress'),
        'joinads_loader_ad_block_render',
        'joinAdsLoader',
        'joinads_loader_joinAdsLoader_section'
    );
}
add_action('admin_init', 'joinads_loader_register_loader_settings');



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

function joinads_loader_timeout_home_render() {
    $options = get_option('joinads_loader_settings');
    ?>
    <input type='number' name='joinads_loader_settings[joinads_loader_timeout_home]' value='<?php echo $options['joinads_loader_timeout_home']; ?>'>
    <?php
}

function joinads_loader_ad_block_render() {
    $options = get_option('joinads_loader_settings');
    ?>
    <input type='text' name='joinads_loader_settings[joinads_loader_ad_block]' value='<?php echo $options['joinads_loader_ad_block']; ?>'>
    <?php
}

function joinads_loader_settings_section_callback() {
    echo __('Configure de acordo com as suas preferências', 'wordpress');
}

function joinads_loader_options_page() {
    echo '<div class="wrap" style="background:#FFF;display:block;padding:10px;">';
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


function joinads_loader_ads_txt_manager_page() {
    // Verificar se o usuário tem permissão para editar opções
    if (!current_user_can('manage_options')) {
        return;
    }

    // Caminho do arquivo ads.txt
    $ads_txt_file = ABSPATH . 'ads.txt';    

    // Verificar se o formulário foi submetido
    if (isset($_POST['submit_ads_txt'])) {
        check_admin_referer('save_ads_txt', 'joinads_loader_ads_txt_nonce');

        // Salvar o conteúdo do arquivo ads.txt
        $ads_txt_content = sanitize_textarea_field($_POST['ads_txt_content']);
        file_put_contents($ads_txt_file, $ads_txt_content);

        echo '<div class="notice notice-success is-dismissible"><p>ads.txt atualizado com sucesso.</p></div>';
    }

    // Ler o conteúdo do arquivo ads.txt
    $ads_txt_content = '';
    if (file_exists($ads_txt_file)) {
        $ads_txt_content = file_get_contents($ads_txt_file);
    }

    ?>
    <div class="wrap" style="background:#FFF;display:block;padding:10px;">
        <h2>Gerenciador do ads.txt</h2>
        <p>Adicione a baixo seu ads.txt caso não esteja na lista.</p>
        <form method="post" action="">
            <?php wp_nonce_field('save_ads_txt', 'joinads_loader_ads_txt_nonce'); ?>
            <textarea name="ads_txt_content" rows="20" cols="100" style="width:100%"><?php echo esc_textarea($ads_txt_content); ?></textarea>
            <?php submit_button('Salvar ads.txt', 'primary', 'submit_ads_txt'); ?>
        </form>
    </div>
    <?php
}

function joinads_loader_register_config_settings() {
    // Registrar configuração
    register_setting('joinads_loader_config_settings_group', 'joinads_loader_config_settings');

    // Adicionar seção
    add_settings_section(
        'joinads_loader_main_section',
        'Configurações da API',
        'joinads_loader_section_callback',
        'join_ads_config'
    );

    // Adicionar campo ID do Cliente
    add_settings_field(
        'joinads_loader_id_client',
        'Join Ads ID:',
        'joinads_loader_id_client_render',
        'join_ads_config',
        'joinads_loader_main_section'
    );

    // Adicionar campo ID do Domínio
    add_settings_field(
        'joinads_loader_id_domain',
        'Domínio ID:',
        'joinads_loader_id_domain_render',
        'join_ads_config',
        'joinads_loader_main_section'
    );

    // Adicionar campo Token da API
    add_settings_field(
        'joinads_loader_token_api',
        'Token da API:',
        'joinads_loader_token_api_render',
        'join_ads_config',
        'joinads_loader_main_section'
    );
}
add_action('admin_init', 'joinads_loader_register_config_settings');



function joinads_loader_id_client_render() {
    $options = get_option('joinads_loader_config_settings');
    ?>
    <input type='text' name='joinads_loader_config_settings[id_client]' value='<?php echo isset($options['id_client']) ? esc_attr($options['id_client']) : ''; ?>'>
    <?php
}

function joinads_loader_id_domain_render() {
    $options = get_option('joinads_loader_config_settings');
    ?>
    <input type='text' name='joinads_loader_config_settings[id_domain]' value='<?php echo isset($options['id_domain']) ? esc_attr($options['id_domain']) : ''; ?>'>
    <?php
}

function joinads_loader_token_api_render() {
    $options = get_option('joinads_loader_config_settings');
    ?>
    <input type='text' name='joinads_loader_config_settings[token_api]' value='<?php echo isset($options['token_api']) ? esc_attr($options['token_api']) : ''; ?>'>
    <?php
}



function joinads_loader_section_callback() {
    echo '<p>Preencha as informações abaixo para configurar a API da Join Ads Network.</p>';
}

function joinads_loader_ads_config_page() {
    ?>
    <div class="wrap" style="background:#FFF;display:block;padding:10px;">
        <h2>Configurações Join Ads Network</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('joinads_loader_config_settings_group');
            do_settings_sections('join_ads_config');
            submit_button('Salvar Configurações');
            ?>
        </form>
    </div>
    <?php
}


function joinads_loader_enqueue_color_picker($hook_suffix) {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('joinads-loader-script-handle', plugins_url('script.js', __FILE__ ), array('wp-color-picker'), false, true);
}
add_action('admin_enqueue_scripts', 'joinads_loader_enqueue_color_picker');

?>