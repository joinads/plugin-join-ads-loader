<?php
if (!defined('ABSPATH')) {
    exit;
}

class JoinAds_Loader
{
    protected $admin;
    protected $public;
    protected $updater;
    protected $readmore;

    public function __construct()
    {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        new JoinAds_Admin();
        new JoinAds_Dashboard();
    }

    private function load_dependencies()
    {
        $this->admin = new JoinAds_Admin();
        $this->public = new JoinAds_Public();
        $this->updater = new JoinAds_Updater();
        $this->readmore = JoinAds_ReadMore::get_instance();
    }

    private function define_admin_hooks()
    {
        // add_action('admin_enqueue_scripts', 'meu_plugin_estilos');

        add_action('admin_menu', array($this->admin, 'add_admin_menu'));
        add_action('admin_init', array($this->admin, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_admin_assets'));
    }

    private function define_public_hooks()
    {
        add_action('wp_head', array($this->public, 'add_preconnect_and_dns_prefetch'), -1);
        add_action('wp_head', array($this->public, 'add_custom_shortlink'), 0);
        add_action('wp_head', array($this->public, 'add_loader_styles'));

        if (!function_exists('wp_body_open')) {
            add_action('wp_footer', array($this->public, 'add_loader_html'));
        } else {
            add_action('wp_body_open', array($this->public, 'add_loader_html'));
        }
    }

    


    public function run()
    {
        do_action('joinads_loader_started');
    }
}

// Inicializar o Loader
add_action('plugins_loaded', function() {
    new JoinAds_Loader();
});