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
    protected $dashboard;

    public function __construct()
    {
        // Carrega dependências primeiro
        $this->load_dependencies();
        
        // Define hooks depois que as classes estão carregadas
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies()
    {
        $this->admin = new JoinAds_Admin();
        $this->public = new JoinAds_Public();
        $this->updater = new JoinAds_Updater();
        $this->readmore = JoinAds_ReadMore::get_instance();
        $this->dashboard = new JoinAds_Dashboard();
    }

    private function define_admin_hooks()
    {
        if ($this->admin) {
            // Registra o menu com prioridade 9 para garantir que seja executado antes
            add_action('admin_menu', array($this->admin, 'add_admin_menu'), 9);
            add_action('admin_init', array($this->admin, 'register_settings'));
            add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_admin_assets'));
        }

        if ($this->dashboard) {
            // Registra o menu do dashboard com prioridade 10
            add_action('admin_menu', array($this->dashboard, 'add_admin_menu'), 10);
        }
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
        // Debug
        add_action('admin_notices', function() {
            if (current_user_can('manage_options')) {
                echo '<div class="notice notice-info"><p>JoinAds Loader iniciado</p></div>';
            }
        });

        $this->define_admin_hooks();
        $this->define_public_hooks();
        
        do_action('joinads_loader_started');
    }
}