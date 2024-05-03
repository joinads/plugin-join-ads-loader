<?php
/*
Plugin Name: Join Ads Loader
Plugin URI: https://joinads.me
Description: Adds a loading screen to your site until the page is fully loaded.
Version: 1.0
Author: Caio Norder
Author URI: https://joinads.me
Plugin URI: https://github.com/joinads/plugin-join-ads-loader
GitHub Plugin URI: https://github.com/joinads/plugin-join-ads-loader
GitHub Branch: main
*/

// Enqueue the loader styles
function joinads_loader_styles() {
    $data = <<<EOD
                <style>
                    #st-loader__wrapper{
                        position:fixed;
                        width:100vw;
                        height:100vh;
                        z-index:999999;
                        top:0;
                        background-color:rgb(255, 255, 255, 0.5);
                        backdrop-filter: blur(10px);
                        -webkit-backdrop-filter: blur(10px);
                        opacity:0;
                        animation:st-fadeIn 0.2s forwards
                    }
                    @keyframes st-fadeIn{
                        0%{
                            opacity:0
                        }
                        100%{
                            opacity:1
                        }
                    }
                    @keyframes st-fadeOut{
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
                    #st-loader__spinner{
                        position:relative;
                        width:60px;
                        height:60px;
                        margin:auto;
                        top:50%;
                        border:8px solid #f3f3f3;
                        border-radius:50%;
                        border-top:8px solid #3bf26b;
                        -webkit-animation:spin 1s linear infinite;
                        animation:st-spin 1s linear infinite
                    }
                    @-webkit-keyframes st-spin{
                        0%{
                            -webkit-transform:rotate(0deg)
                        }
                        100%{
                            -webkit-transform:rotate(360deg)
                        }
                    }
                    @keyframes st-spin{
                        0%{
                            transform:rotate(0deg)
                        }
                        100%{
                            transform:rotate(360deg)
                        }
                    }
                </style>
            EOD;
    echo $data;
}

add_action('wp_head', 'joinads_loader_styles');

// Add the loader HTML to the beginning of the body
function joinads_loader_html() {
    $data = <<<EOD
                <div id="st-loader__wrapper">
                    <div id="st-loader__spinner"></div>
                  </div>
                <script>
                    window.scrollTo({top: 0, behavior: 'smooth'});

                    let loader = document.querySelector('#st-loader__wrapper');
                    let waitSlotRender = ['Content1'];

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
                    }, 8000);

                    function fadeOut(div) {
                        if (document.getElementById(div.id)) {
                            div.addEventListener('animationend', () => {
                                document.body.style.position = '';
                                div.remove();
                            });
                            div.style = 'animation: st-fadeOut 0.5s forwards;';
                        }
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
                                if (!event.isEmpty &&
                                    (
                                        Array.isArray(waitSlotRender) && waitSlotRender.includes(event.slot.getSlotElementId())
                                    )) {
                                    waitFor(() => true).then(() => {
                                        fadeOut(loader);
                                    });
                                }
                            });
                        });
                    } 
                </script>
                EOD;
    echo $data;
}

add_action('wp_body_open', 'joinads_loader_html');
?>
