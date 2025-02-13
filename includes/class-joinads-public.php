<?php
if (!defined('ABSPATH')) {
    exit;
}

class JoinAds_Public {
    public function add_preconnect_and_dns_prefetch()
    {
        $infos = '
        <link rel="dns-prefetch" href="https://pageview.joinads.me">
        <link rel="dns-prefetch" href="https://office.joinads.me">
        <link rel="dns-prefetch" href="https://script.joinads.me">
        
        <link rel="preconnect" href="https://pageview.joinads.me">
        <link rel="preconnect" href="https://office.joinads.me">
        <link rel="preconnect" href="https://script.joinads.me">
        ';

        $options = get_option('joinads_loader_config_settings');
        if (!empty($options['id_domain'])) {
            $infos .= '
            <link rel="preload" href="https://script.joinads.me/myad' . $options['id_domain'] . '.js" crossorigin="anonymous" as="script">
            <script type="module" src="https://script.joinads.me/myad' . $options['id_domain'] . '.js" crossorigin="anonymous" async></script>
            ';
        }

        echo $this->minificar($infos);
    }

    public function should_show_loader() {
        $options = get_option('joinads_loader_settings');
        
        // Verifica se o loader está ativado
        if (empty($options['joinads_loader_enabled'])) {
            return false;
        }
        
        // Verifica se o loader está configurado
        if (empty($options['joinads_loader_ad_block'])) {
            return false;
        }

        // Se estiver na home/front page, verifica se tem tempo configurado
        if (is_home() || is_front_page()) {
            return !empty($options['joinads_loader_timeout_home']);
        }

        // Para outras páginas, verifica se tem tempo configurado
        return !empty($options['joinads_loader_timeout']);
    }

    public function add_custom_shortlink() {
        $options = get_option('joinads_loader_config_settings');
        if (isset($options['short']) && $options['short'] == 1) {
            if (is_singular()) {
                $post_id = get_the_ID();

                if (isset($_GET['idf'])) {
                    $post_id = absint($_GET['idf']);
                }

                $shortlink = home_url('/?p=' . $post_id);
                echo "<link rel='shortlink' href='" . esc_url($shortlink) . "' />";
            }
        }
    }

    public function add_loader_styles() {
        if (!$this->should_show_loader()) {
            return;
        }

        $options = get_option('joinads_loader_settings');
        $loaderColor = empty($options['joinads_loader_color']) ? '#81d742' : $options['joinads_loader_color'];
        
        $css = $this->get_loader_css($loaderColor);
        $data = '<style>' . $this->minificar($css) . '</style>';
        echo $data;
    }

    private function get_loader_css($loaderColor) {
        return <<<EOD
            #joinadsloader__wrapper{
                position:fixed;
                width:100vw;
                height:100vh;
                z-index:999999;
                top:0;
                background-color:rgb(255, 255, 255, 0.5);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                opacity:0;
                animation:joinadsloader-fadeIn 0.2s forwards
            }
            @keyframes joinadsloader-fadeIn{
                0%{opacity:0}
                100%{opacity:1}
            }
            @keyframes joinadsloader-fadeOut{
                0%{
                    opacity:1;
                    z-index:9999
                }
                100%{
                    opacity:0;
                    z-index:0;
                    display:none
                }
            }
            #joinadsloader__spinner{
                position:relative;
                width:60px;
                height:60px;
                margin:auto;
                top:50%;
                border:8px solid #f3f3f3;
                border-radius:50%;
                border-top:8px solid $loaderColor;
                -webkit-animation:spin 1s linear infinite;
                animation:joinadsloader-spin 1s linear infinite
            }
            @-webkit-keyframes joinadsloader-spin{
                0%{-webkit-transform:rotate(0deg)}
                100%{-webkit-transform:rotate(360deg)}
            }
            @keyframes joinadsloader-spin{
                0%{transform:rotate(0deg)}
                100%{transform:rotate(360deg)}
            }
        EOD;
    }

    public function add_loader_html() {
        if (!$this->should_show_loader()) {
            return;
        }

        $options = get_option('joinads_loader_settings');
        $timeout = empty($options['joinads_loader_timeout']) ? 7000 : $options['joinads_loader_timeout'] * 1000;
        $adunit = empty($options['joinads_loader_ad_block']) ? 'Content1' : $options['joinads_loader_ad_block'];

        if(is_home() || is_front_page()){
            $timeout = empty($options['joinads_loader_timeout_home']) ? 3000 : $options['joinads_loader_timeout_home'] * 1000;
        }

        $html = $this->get_loader_html($timeout, $adunit);
        echo $this->minificar($html);
    }

    private function get_loader_html($timeout, $adunit) {
        return <<<EOD
            <div id="joinadsloader__wrapper">
                <div id="joinadsloader__spinner"></div>
            </div>
            <script>
                window.scrollTo({top: 0, behavior: 'smooth'});
                disableScroll();

                let loader = document.querySelector('#joinadsloader__wrapper');
                let waitSlotRender = ['$adunit'];

                if (waitSlotRender.length > 0) {
                    setupAdListener();
                } else {
                    window.addEventListener("load", () => {
                        waitFor(() => true).then(() => {
                            fadeOut(loader);
                        });
                    });
                }

                setTimeout(() => {
                    fadeOut(loader);
                }, $timeout);

                function fadeOut(div) {
                    if (document.getElementById(div.id)) {
                        div.addEventListener('animationend', () => {
                            document.body.style.position = '';
                            div.remove();
                        });
                        div.style = 'animation: joinadsloader-fadeOut 0.5s forwards;';
                    }
                    enableScroll();
                }

                function waitFor(conditionFunction) {
                    const poll = resolve => {
                        if (conditionFunction()) resolve();
                        else setTimeout(() => poll(resolve), 10);
                    };
                    return new Promise(poll);
                }

                function setupAdListener() {
                    window.googletag = window.googletag || {cmd: []};
                    googletag.cmd.push(() => {
                        googletag.pubads().addEventListener('slotOnload', event => {
                            if (!event.isEmpty) {
                                adLoaded = true;
                                fadeOut(loader);
                            }
                            if (Array.isArray(waitSlotRender) && waitSlotRender.includes(event.slot.getSlotElementId())) {
                                waitFor(() => true).then(() => {
                                    fadeOut(loader);
                                });
                            }
                        });
                    });
                } 

                function disableScroll() {
                    document.body.style.overflow = 'hidden';
                    document.body.style.position = 'fixed';
                    document.body.style.width = '100%';
                }

                function enableScroll() {
                    document.body.style.overflow = '';
                    document.body.style.position = '';
                    document.body.style.width = '';
                }            
            </script>
        EOD;
    }

    private function minificar($codigo) {
        // Remover espaços em branco desnecessários
        $codigo = preg_replace('/\s+/', ' ', $codigo);
        // Remover comentários
        $codigo = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $codigo);
        // Remover espaços antes e depois de : e ;
        $codigo = preg_replace('/\s*([:;])\s*/', '$1', $codigo);
        // Remover quebras de linha
        $codigo = str_replace(array("\r\n", "\r", "\n"), '', $codigo);
        return $codigo;
    }
} 