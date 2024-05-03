<?php

function minificar($codigo_css) {
    // Remover espaços em branco desnecessários
    $codigo_css = preg_replace('/\s+/', ' ', $codigo_css);

    // Remover comentários
    $codigo_css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $codigo_css);

    // Remover espaços antes e depois de : e ;
    $codigo_css = preg_replace('/\s*([:;])\s*/', '$1', $codigo_css);

    // Remover quebras de linha
    $codigo_css = str_replace(array("\r\n", "\r", "\n"), '', $codigo_css);

    return $codigo_css;
}
