<?php
function joinads_loader_check_update() {
    $plugin_slug = basename(dirname(JOINADS_LOADER_FILE));
    $plugin_data = get_plugin_data(JOINADS_LOADER_FILE);
    $current_version = $plugin_data['Version'];

    $url = "https://raw.githubusercontent.com/joinads/plugin-join-ads-loader/main/version.json?ver=" . time();
    $response = wp_remote_get($url, array('headers' => array('User-Agent' => 'WordPress/' . $plugin_slug)));

    if (!is_wp_error($response) && $response['response']['code'] == 200) {
        $release_info = json_decode($response['body']);
        if (version_compare($current_version, $release_info->version, '<')) {
            update_option('join_ads_loader_update_info', array(
                'new_version' => $release_info->version,
                'url' => 'https://github.com/joinads/plugin-join-ads-loader',
                'package' => 'https://github.com/joinads/plugin-join-ads-loader/archive/main.zip'
            ));
        }
    }
}
add_action('admin_init', 'joinads_loader_check_update');



function joinads_loader_inject_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $update_info = get_option('join_ads_loader_update_info');
    if ($update_info) {
        $obj = new stdClass();
        $obj->slug = 'plugin-join-ads-loader'; // Deve ser o mesmo slug do diretório do plugin
        $obj->new_version = $update_info['new_version'];
        $obj->url = $update_info['url'];
        $obj->package = $update_info['package']; // URL do arquivo ZIP da nova versão
        $obj->plugin = plugin_basename(JOINADS_LOADER_FILE); // Caminho base do plugin
        $transient->response[$obj->plugin] = $obj;
    }

    return $transient;
}
add_filter('site_transient_update_plugins', 'joinads_loader_inject_update');


function joinads_loader_post_update($upgrader_object, $options) {
    if ($options['action'] == 'update' && $options['type'] == 'plugin' ) {
        // Limpa as informações de atualização se o plugin atualizado é este plugin
        if (isset($options['plugins']) && in_array(plugin_basename(JOINADS_LOADER_FILE), $options['plugins'])) {
            delete_option('join_ads_loader_update_info');
        }
    }
}
add_action('upgrader_process_complete', 'joinads_loader_post_update', 10, 2);