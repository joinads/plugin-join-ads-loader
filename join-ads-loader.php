<?php
/*
Plugin Name: Join Ads
Plugin URI: https://joinads.me
Description: Feito para uma melhor experiência com a Join Ads.
Version: 2.3.3
Author: Caio Norder
Author URI: https://joinads.me
Text Domain: join-ads-loader
Domain Path: /languages
*/

// Prevenir acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Definições globais
define('JOINADS_LOADER_VERSION', '2.3');
define('JOINADS_LOADER_FILE', __FILE__);
define('JOINADS_LOADER_PATH', plugin_dir_path(__FILE__));
define('JOINADS_LOADER_URL', plugin_dir_url(__FILE__));

// Carregamento dos arquivos
require_once JOINADS_LOADER_PATH . 'includes/class-joinads-loader.php';
require_once JOINADS_LOADER_PATH . 'includes/class-joinads-admin.php';
require_once JOINADS_LOADER_PATH . 'includes/class-joinads-public.php';
require_once JOINADS_LOADER_PATH . 'includes/class-joinads-updater.php';
require_once JOINADS_LOADER_PATH . 'includes/class-joinads-api.php';
require_once JOINADS_LOADER_PATH . 'includes/class-joinads-readmore.php';

// Inicialização do plugin
if (!function_exists('joinads_loader_init')) {
    function joinads_loader_init() {
        $plugin = new JoinAds_Loader();
        $plugin->run();
    }
}
add_action('plugins_loaded', 'joinads_loader_init');