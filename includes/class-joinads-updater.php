<?php
if (!defined('ABSPATH')) {
    exit;
}

class JoinAds_Updater {
    public function __construct() {
        add_action('admin_init', array($this, 'check_update'));
        add_filter('site_transient_update_plugins', array($this, 'inject_update'));
        add_action('upgrader_process_complete', array($this, 'post_update'), 10, 2);
    }

    public function check_update() {
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

    public function inject_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $update_info = get_option('join_ads_loader_update_info');
        if ($update_info) {
            $obj = new stdClass();
            $obj->slug = 'plugin-join-ads-loader';
            $obj->new_version = $update_info['new_version'];
            $obj->url = $update_info['url'];
            $obj->package = $update_info['package'];
            $obj->plugin = plugin_basename(JOINADS_LOADER_FILE);
            $transient->response[$obj->plugin] = $obj;
        }

        return $transient;
    }

    public function post_update($upgrader_object, $options) {
        if ($options['action'] == 'update' && $options['type'] == 'plugin') {
            if (isset($options['plugins']) && in_array(plugin_basename(JOINADS_LOADER_FILE), $options['plugins'])) {
                delete_option('join_ads_loader_update_info');
            }
        }
    }
} 