<?php
function joinads_add_preconnect_and_dns_prefetch() {
    $infos = '
    <link rel="dns-prefetch" href="https://pageview.joinads.me">
    <link rel="dns-prefetch" href="https://office.joinads.me">
    <link rel="dns-prefetch" href="https://script.joinads.me">
    
    <link rel="preconnect" href="https://pageview.joinads.me">
    <link rel="preconnect" href="https://office.joinads.me">
    <link rel="preconnect" href="https://script.joinads.me">
    ';

    $options = get_option('joinads_loader_config_settings');
    if($options['id_domain']){
        $infos .= '
        <link rel="preload" href="https://script.joinads.me/myad'.$options['id_domain'].'.js" crossorigin="anonymous" as="script">
        <script type="module" src="https://script.joinads.me/myad'.$options['id_domain'].'.js" crossorigin="anonymous" async></script>
        ';
    }

    echo minificar($infos);
}
add_action('wp_head', 'joinads_add_preconnect_and_dns_prefetch',-1);




function joinads_loader_styles() {
    $options = get_option('joinads_loader_settings');
    if (empty($options['joinads_loader_color'])) {
        $loaderColor = '#81d742';
    } else {
        $loaderColor = $options['joinads_loader_color'];
    }
    
    $css = <<<EOD
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
                        0%{
                            opacity:0
                        }
                        100%{
                            opacity:1
                        }
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
                        0%{
                            -webkit-transform:rotate(0deg)
                        }
                        100%{
                            -webkit-transform:rotate(360deg)
                        }
                    }
                    @keyframes joinadsloader-spin{
                        0%{
                            transform:rotate(0deg)
                        }
                        100%{
                            transform:rotate(360deg)
                        }
                    }
            EOD;
    $data = '<style>' . minificar($css) . '</style>';
    echo $data;
}

add_action('wp_head', 'joinads_loader_styles');

// Add the loader HTML to the beginning of the body
function joinads_loader_html() {
    $options = get_option('joinads_loader_settings');
    if (empty($options['joinads_loader_timeout'])) {
        $timeout = 7000;
    } else {
        $timeout = $options['joinads_loader_timeout']*1000;
    }
    
    if(empty($options['joinads_loader_ad_block'])) {
        $adunit = 'Content1';
    } else {
        $adunit = $options['joinads_loader_ad_block'];
    }

    if(is_home() || is_front_page()){
        if (empty($options['joinads_loader_timeout_home'])) {
            $timeout = 3000;
        } else {
            $timeout = $options['joinads_loader_timeout_home']*1000;
        }
    }

    $data = <<<EOD
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
    echo minificar($data);
}

add_action('wp_body_open', 'joinads_loader_html');
