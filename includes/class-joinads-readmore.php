<?php
if (!defined('ABSPATH')) {
    exit;
}

class JoinAds_ReadMore
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_filter('the_content', array($this, 'insert_button'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function insert_button($content)
    {
        if (!is_singular('post') || !get_option('joinads_readmore_enabled', 0)) {
            return $content;
        }

        $position = (int) get_option('joinads_readmore_position', 3);
        $position = max(1, $position);
        
        $content_parts = preg_split('/(<\/?p[^>]*>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (count($content_parts) >= $position * 2) {
            // Dividir o conteúdo em visível e oculto
            $visible_content = array_slice($content_parts, 0, $position * 2);
            $hidden_content = array_slice($content_parts, $position * 2);

            // Criar o botão
            $button = '<div class="joinads-readmore-block">
                <button class="joinads-readmore-button">
                    Leia mais
                </button>
            </div>';

            // Montar o conteúdo final
            $content = implode('', $visible_content);
            $content .= $button;
            $content .= '<div class="joinads-hidden-content" style="display:none;">';
            $content .= implode('', $hidden_content);
            $content .= '</div>';
        }

        return $content;
    }

    public function enqueue_scripts()
    {
        if (is_singular('post')) {
            wp_enqueue_style(
                'joinads-readmore-style',
                JOINADS_LOADER_URL . 'assets/css/readmore.css',
                array(),
                JOINADS_LOADER_VERSION
            );

            wp_enqueue_script(
                'joinads-readmore-script',
                JOINADS_LOADER_URL . 'assets/js/readmore.js',
                array('jquery'),
                JOINADS_LOADER_VERSION,
                true
            );
        }
    }
}

// Inicializar o ReadMore
add_action('plugins_loaded', array('JoinAds_ReadMore', 'get_instance'));
