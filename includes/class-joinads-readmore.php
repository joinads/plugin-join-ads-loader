<?php
if (!defined('ABSPATH')) {
    exit;
}

class JoinAds_ReadMore
{
    private static $instance = null;
    private $enabled = false;
    private $position = 3;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Carrega as configurações
        $this->enabled = get_option('joinads_readmore_enabled', 0);
        $this->position = (int) get_option('joinads_readmore_position', 3);
        
        // Registra os hooks apenas se o recurso estiver habilitado
        if ($this->enabled) {
            $this->init_hooks();
        }
    }

    private function init_hooks()
    {
        // Registra os hooks com prioridade adequada
        add_filter('the_content', array($this, 'insert_button'), 20);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 10);
    }

    public function insert_button($content)
    {
        // Verifica se deve processar o conteúdo
        if (!is_singular('post') || !$this->enabled || is_admin()) {
            return $content;
        }

        // Garante que a posição é válida
        $position = max(1, min(20, $this->position));
        
        // Divide o conteúdo mantendo as tags HTML intactas
        $content_parts = preg_split('/(<\/?p[^>]*>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (count($content_parts) >= $position * 2) {
            try {
                // Divide o conteúdo em visível e oculto
                $visible_content = array_slice($content_parts, 0, $position * 2);
                $hidden_content = array_slice($content_parts, $position * 2);

                // Cria o botão com ID único
                $button = sprintf(
                    '<div class="joinads-readmore-block">
                        <button class="joinads-readmore-button" id="joinads-readmore-%d">
                            %s
                        </button>
                    </div>',
                    get_the_ID(),
                    esc_html__('Leia mais', 'join-ads-loader')
                );

                // Monta o conteúdo final com ID único para a div oculta
                return sprintf(
                    '%s%s<div class="joinads-hidden-content" id="joinads-content-%d" style="display:none;">%s</div>',
                    implode('', $visible_content),
                    $button,
                    get_the_ID(),
                    implode('', $hidden_content)
                );
            } catch (Exception $e) {
                // Em caso de erro, retorna o conteúdo original
                return $content;
            }
        }

        return $content;
    }

    public function enqueue_scripts()
    {
        if (!is_singular('post') || !$this->enabled) {
            return;
        }

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

// Remova a inicialização aqui, pois ela já é feita no Loader principal
// add_action('plugins_loaded', array('JoinAds_ReadMore', 'get_instance'));
