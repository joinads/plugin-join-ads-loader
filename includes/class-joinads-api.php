<?php
if (!defined('ABSPATH')) {
    exit;
}

class JoinAds_API {
    private $base_url = 'https://office.joinads.me/api/';
    private $token;
    private $domain;

    public function __construct() {
        $options = get_option('joinads_loader_config_settings');
        $this->token = isset($options['token_api']) ? sanitize_text_field($options['token_api']) : '';
        $this->domain = isset($options['id_domain']) ? sanitize_text_field($options['id_domain']) : '';
    }

    public function get_dashboard_data() {
        // Validar token
        if (empty($this->token)) {
            return new WP_Error('missing_token', 'Token da API não configurado');
        }
        // Validar domínio
        if (empty($this->domain)) {
            return new WP_Error('missing_domain', 'Domínio não configurado');
        }
        // Pegar o período selecionado ou usar padrão (7 dias)
        $period = isset($_GET['period']) ? absint($_GET['period']) : 7; 
        // Definir datas no formato correto
        $end_date = date('Y-m-d\TH:i:s.v\Z');
        $start_date = date('Y-m-d\TH:i:s.v\Z', strtotime("-{$period} days"));
        // Preparar payload
        $payload = array(
            'start' => $start_date,
            'end' => $end_date,
            'domain' => array($this->domain)
        );
        // Fazer requisição
        $response = wp_remote_post($this->base_url . 'reports/main-filter', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'body' => json_encode($payload),
            'timeout' => 30,
            'sslverify' => true
        ));
        

        if (is_wp_error($response)) {
            return new WP_Error('api_error', $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
       
        // Verificar erros específicos da API
        if ($response_code !== 200) {
            $error_message = 'Erro na API';
            if (isset($data['message'])) {
                $error_message = $data['message'];
            }
            if (isset($data['details'])) {
                $error_message .= ': ' . json_encode($data['details']);
            }
            return new WP_Error('api_error', $error_message);
        }

        if (!$data) {
            return new WP_Error('api_error', 'Erro ao processar dados da API');
        }

        return $data;
    }

    public function get_error_message($wp_error) {
        if (!is_wp_error($wp_error)) {
            return '';
        }

        $error_messages = array(
            'missing_token' => 'Token da API não configurado. Por favor, configure nas configurações do plugin.',
            'missing_domain' => 'Domínio não configurado. Por favor, configure nas configurações do plugin.',
            'api_error' => 'Erro na comunicação com a API: ',
        );

        $code = $wp_error->get_error_code();
        $message = $error_messages[$code] ?? '';
        
        if ($code === 'api_error') {
            $message .= $wp_error->get_error_message();
        }

        return $message;
    }
}
