<?php
include 'class-joinads-api.php';
if (!defined('ABSPATH')) {
	exit;
}

class JoinAds_Admin
{



	public function __construct()
	{
		// Remove o hook do menu daqui, pois está sendo chamado duas vezes
		add_action('admin_enqueue_styles', 'wgm_register_styles');
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
		add_action('admin_init', array($this, 'register_settings'));

	}
	public function wgm_register_styles()
	{
		wp_enqueue_style('wgm_tailwindcss', JOINADS_LOADER_URL . 'assets/css/style.css', [], '1.0', 'all');
	}

	public function enqueue_admin_assets($hook_suffix)
	{
		wp_enqueue_style('meu-plugin-estilo', JOINADS_LOADER_URL . 'assets/css/style.css');
		wp_enqueue_script('joinads-loader-admin', JOINADS_LOADER_URL . 'assets/js/admin.js', array('jquery'), JOINADS_LOADER_VERSION, true);

		// Adicionar estilos específicos para o toggle switch
		$custom_css = "
			.switch {
				position: relative;
				display: inline-block;
				width: 60px;
				height: 34px;
			}

			.switch input {
				opacity: 0;
				width: 0;
				height: 0;
			}

			.slider {
				position: absolute;
				cursor: pointer;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background-color: #ccc;
				transition: .4s;
			}

			.slider:before {
				position: absolute;
				content: '';
				height: 26px;
				width: 26px;
				left: 4px;
				bottom: 4px;
				background-color: white;
				transition: .4s;
			}

			input:checked + .slider {
				background-color: #0073aa;
			}

			input:focus + .slider {
				box-shadow: 0 0 1px #0073aa;
			}

			input:checked + .slider:before {
				transform: translateX(26px);
			}

			.slider.round {
				border-radius: 34px;
			}

			.slider.round:before {
				border-radius: 50%;
			}

			.description {
				margin-left: 10px;
				vertical-align: super;
			}
		";
		wp_add_inline_style('meu-plugin-estilo', $custom_css);
	}

	public function register_settings()
	{
		// do Loader
		register_setting('joinAdsLoader', 'joinads_loader_settings');

		//Configurações gerais
		register_setting('joinads_loader_config_settings_group', 'joinads_loader_config_settings');

		$this->add_settings_sections();
		$this->add_settings_fields();

		register_setting('joinads_readmore_settings_group', 'joinads_readmore_enabled');
		register_setting('joinads_readmore_settings_group', 'joinads_readmore_position');

		add_settings_section(
			'joinads_readmore_main_section',
			'Configurações do Leia Mais',
			null,
			'joinads-readmore'
		);

		add_settings_field(
			'joinads_readmore_enabled',
			'Ativar Leia Mais',
			array($this, 'render_enabled_field'),
			'joinads-readmore',
			'joinads_readmore_main_section'
		);

		add_settings_field(
			'joinads_readmore_position',
			'Posição do Leia Mais',
			array($this, 'render_position_field'),
			'joinads-readmore',
			'joinads_readmore_main_section'
		);
	}

	private function add_settings_sections()
	{
		add_settings_section(
			'joinads_loader_joinAdsLoader_section',
			'',
			array($this, 'settings_section_callback'),
			'joinAdsLoader'
		);

		add_settings_section(
			'joinads_loader_main_section',
			'Configurações da API',
			array($this, 'config_section_callback'),
			'join_ads_config'
		);
	}

	private function add_settings_fields()
	{
		//Campos do Loader
		$loader_fields = array(
			'joinads_loader_enabled' => array(
				'label' => 'Ativar Loader:',
				'callback' => 'loader_enabled_field_render'
			),
			'joinads_loader_color' => array(
				'label' => 'Loader Cor:',
				'callback' => 'color_field_render'
			),
			'joinads_loader_timeout' => array(
				'label' => 'Loader:<br /><small style="font-size: 10px;">(segundos para aguardar o anúncio)</small>',
				'callback' => 'timeout_field_render'
			),
			'joinads_loader_timeout_home' => array(
				'label' => 'Home Loader:<br /><small style="font-size: 10px;">(segundos para aguardar o anúncio)</small>',
				'callback' => 'timeout_home_field_render'
			),
			'joinads_loader_ad_block' => array(
				'label' => 'AdUnit Name:<br /><small style="font-size: 10px;">(Nome do bloco ex: Content1)</small>',
				'callback' => 'ad_block_field_render'
			)
		);

		foreach ($loader_fields as $id => $field) {
			add_settings_field(
				$id,
				$field['label'],
				array($this, $field['callback']),
				'joinAdsLoader',
				'joinads_loader_joinAdsLoader_section'
			);
		}

		//Campos de configuração
		$config_fields = array(
			'id_domain' => 'Domínio: <br /><small style="font-size: 10px;">(Nome do domínio ex: joinads.com.br)</small>',
			'token_api' => 'Token da API:',
			'short' => 'Forçar ShortLink:'
		);


		foreach ($config_fields as $id => $label) {
			add_settings_field(
				'joinads_loader_' . $id,
				$label,
				array($this, 'config_field_render'),
				'join_ads_config',
				'joinads_loader_main_section',
				array('field' => $id)
			);
		}
	}

	public function color_field_render()
	{
		$options = get_option('joinads_loader_settings');
		?>
		<input type='text' name='joinads_loader_settings[joinads_loader_color]'
			value='<?php echo esc_attr($options['joinads_loader_color'] ?? ''); ?>' class='my-color-field'>
		<?php
	}

	public function timeout_field_render()
	{
		$options = get_option('joinads_loader_settings');
		?>
		<input type='number' name='joinads_loader_settings[joinads_loader_timeout]'
			value='<?php echo esc_attr($options['joinads_loader_timeout'] ?? ''); ?>'>
		<?php
	}

	public function timeout_home_field_render()
	{
		$options = get_option('joinads_loader_settings');
		?>
		<input type='number' name='joinads_loader_settings[joinads_loader_timeout_home]'
			value='<?php echo esc_attr($options['joinads_loader_timeout_home'] ?? ''); ?>'>
		<?php
	}

	public function ad_block_field_render()
	{
		$options = get_option('joinads_loader_settings');
		?>
		<input type='text' name='joinads_loader_settings[joinads_loader_ad_block]'
			value='<?php echo esc_attr($options['joinads_loader_ad_block'] ?? ''); ?>'>
		<?php
	}

	public function config_field_render($args)
	{
		$options = get_option('joinads_loader_config_settings');
		$field = $args['field'];

		if ($field === 'short') {
			$checked = isset($options[$field]) ? checked(1, $options[$field], false) : '';
			echo "<input type='checkbox' name='joinads_loader_config_settings[$field]' value='1' $checked>";
		} else {
			echo "<input type='text' name='joinads_loader_config_settings[$field]' 
                  value='" . esc_attr($options[$field] ?? '') . "'>";
		}
	}

	public function settings_section_callback()
	{
		echo 'Configure de acordo com as suas preferências';
	}

	public function config_section_callback()
	{
		echo '<p>Preencha as informações abaixo para configurar a API da Join Ads Network.</p>';
	}

	public function add_admin_menu()
	{
		// Adiciona o menu principal
		add_menu_page(
			'Join Ads',
			'Join Ads',
			'manage_options',
			'join_ads_loader_main',
			array($this, 'display_dashboard_page'),
			'dashicons-plugins-checked'
		);

		// Adiciona os submenus
		add_submenu_page(
			'join_ads_loader_main',
			'Loader Manager',
			'Loader Manager',
			'manage_options',
			'join_ads_loader',
			array($this, 'display_loader_page')
		);

		add_submenu_page(
			'join_ads_loader_main',
			'Configurações',
			'Configurações',
			'manage_options',
			'join_ads_config',
			array($this, 'display_config_page')
		);
	}

	public function transformObject($original)
	{
		// Mapeamento de traduções das chaves
		$translations = [
			"impressions" => "Impressões",
			"revenue_client" => "Receita",
			"ecpm_client" => "ECPM",
			"clicks" => "Cliques",
			"ctr" => "CTR",
			"unfilled_impressions" => "Não preenchidas",
			"active_view" => "Viewability",
			"pageview" => "Visualização Única",
			"pmr" => "PMR",
			"ipv" => "IPV",
			"average-roi" => "Roi geral",
			"total_result" => "Resultado Total",
			"total_spend" => "Total Gasto",
			"total_revenue" => "Receita de Campanhas"
		];

		// Definir os helpers para cada chave do objeto original
		$helpers = [
			"impressions" => "Total de impressões entregues pelo Ad Exchange.",
			"revenue_client" => "Receita gerada a partir da sua conta do Ad Manager.",
			"ecpm_client" => "Custo efetivo por mil impressões.",
			"clicks" => "Total de cliques entregues pelo Ad Exchange.",
			"ctr" => "A porcentagem de impressões servidas pelo Ad Exchange que resultaram em cliques dos usuários.",
			"unfilled_impressions" => "Número total de solicitações de anúncios para o servidor do Google Ad Manager, AdSense e Ad Exchange que não retornaram um anúncio.",
			"active_view" => "Porcentagem de impressões visíveis em relação ao total de impressões mensuráveis.",
			"pageview" => "O número de visitantes únicos, ou 'alcance', expostos à sua rede.",
			"pmr" => "Taxa de correspondência de página.",
			"ipv" => "Impressões por visitante.",
			"average-roi" => "Retorno sobre investimento médio",
			"total_result" => "Resultado Total",
			"total_spend" => "Total Gasto",
			"total_revenue" => "Receita de Campanhas"
		];

		// Inicializar o novo objeto
		$newObject = [];

		// Percorrer as chaves do objeto original
		foreach ($original as $key => $value) {

			if ($key === 'requests_served') {
				continue;
			}
			// Traduzir a chave para o português
			$translatedKey = $translations[$key] ?? ucfirst(str_replace('_', ' ', $key)); // Usa a tradução ou mantém o nome em camel case

			// Formatar valores conforme necessário
			if ($key === 'ctr' || $key === 'active_view') {
				// Para 'ctr' e 'pmr', formatar como porcentagem
				$valueFormatted = number_format($value * 100, 2) . '%';
			} elseif ($key === 'ecpm_client' || $key === 'revenue_client') {
				// Para 'ecpm_client' e 'revenue_client', formatar como valor monetário
				$valueFormatted = 'US$ ' . number_format($value, 2, ',', '.');
			} elseif ($key === 'pmr') {
				$valueFormatted = number_format($value, 2) . '%';
			} else {
				// Para os demais valores, manter como está
				if (is_float($value)) {

					$valueFormatted = number_format($value, 2, ',', '.');
				} else {
					$valueFormatted = number_format($value, 0, ',', '.');

				}
			}

			// Adicionar o item ao novo objeto com a chave traduzida
			$newObject[$translatedKey] = [
				"value" => $valueFormatted,
				"helper" => $helpers[$key] ?? "Informação não disponível."
			];
		}

		return $newObject;
	}



	private function check_loader_status()
	{
		$options = get_option('joinads_loader_settings');
		return !empty($options['joinads_loader_ad_block']);
	}

	private function get_loader_timeout()
	{
		$options = get_option('joinads_loader_settings');
		return $options['joinads_loader_timeout'] ?? 7;
	}

	public function display_loader_page()
	{
		?>
		<div class="wrap" style="background:#FFF;padding:20px;margin-top:20px;">
			<h2>Join Ads Loader Settings</h2>
			<form action="options.php" method="post">
				<?php
				settings_fields('joinAdsLoader');
				do_settings_sections('joinAdsLoader');
				submit_button('Salvar Configurações');
				?>
			</form>
		</div>
		<?php
	}

	public function display_ads_txt_page()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$ads_txt_file = ABSPATH . 'ads.txt';

		if (isset($_POST['submit_ads_txt'])) {
			check_admin_referer('save_ads_txt', 'joinads_loader_ads_txt_nonce');
			$ads_txt_content = sanitize_textarea_field($_POST['ads_txt_content']);
			file_put_contents($ads_txt_file, $ads_txt_content);
			echo '<div class="notice notice-success is-dismissible"><p>ads.txt atualizado com sucesso.</p></div>';
		}

		$ads_txt_content = '';
		if (file_exists($ads_txt_file)) {
			$ads_txt_content = file_get_contents($ads_txt_file);
		}

		?>
		<div class="wrap" style="background:#FFF;padding:20px;margin-top:20px;">
			<h2>Gerenciador do ads.txt</h2>
			<p>Adicione abaixo seu ads.txt caso não esteja na lista.</p>
			<form method="post" action="">
				<?php wp_nonce_field('save_ads_txt', 'joinads_loader_ads_txt_nonce'); ?>
				<textarea name="ads_txt_content" rows="20" cols="100"
					style="width:100%"><?php echo esc_textarea($ads_txt_content); ?></textarea>
				<?php submit_button('Salvar ads.txt', 'primary', 'submit_ads_txt'); ?>
			</form>
		</div>
		<?php
	}

	public function display_config_page()
	{
		?>
		<div class="wrap" style="background:#FFF;padding:20px;margin-top:20px;">
			<h2>Configurações Join Ads Network</h2>
			<form action="options.php" method="post">
				<?php
				settings_fields('joinads_loader_config_settings_group');
				do_settings_sections('join_ads_config');
				submit_button('Salvar Configurações');
				?>
			</form>
		</div>
		<?php
	}

	public function display_readmore_page() {
		if (!current_user_can('manage_options')) {
			return;
		}
		?>
		<div class="wrap" style="background:#FFF;padding:20px;margin-top:20px;">
			<h1>Configurações do Leia Mais</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('joinads_readmore_settings_group');
				do_settings_sections('joinads-readmore');
				submit_button('Salvar Configurações');
				?>
			</form>
		</div>
		<?php
	}

	// Adicionar o novo método para renderizar o toggle switch
	public function loader_enabled_field_render() {
		$options = get_option('joinads_loader_settings');
		$enabled = isset($options['joinads_loader_enabled']) ? $options['joinads_loader_enabled'] : 0;
		?>
		<label class="switch">
			<input type="checkbox" name="joinads_loader_settings[joinads_loader_enabled]" 
				   value="1" <?php checked(1, $enabled); ?>>
			<span class="slider round"></span>
		</label>
		<span class="description">Ativar/Desativar o loader</span>
		<?php
	}

	public function render_enabled_field()
	{
		$enabled = get_option('joinads_readmore_enabled', 0);
	?>
		<label>
			<input type="checkbox" name="joinads_readmore_enabled" value="1" <?php checked(1, $enabled); ?>>
			Ativar o recurso Leia Mais
		</label>
	<?php
	}

	public function render_position_field()
	{
		$position = get_option('joinads_readmore_position', 3);
	?>
		<input
			type="number"
			name="joinads_readmore_position"
			value="<?php echo esc_attr($position); ?>"
			min="1"
			max="20"
			step="1">
		<p class="description">
			Digite após qual parágrafo o botão "Leia mais" deve aparecer (entre 1 e 20)
		</p>
	<?php
	}

}