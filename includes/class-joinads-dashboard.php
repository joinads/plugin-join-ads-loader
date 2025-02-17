<?php
if (!defined('ABSPATH')) {
    exit;
}

class JoinAds_Dashboard
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Join Ads',
            'Join Ads',
            'manage_options',
            'join_ads_loader_main',
            array($this, 'display_dashboard_page'),
            'dashicons-plugins-checked'
        );

        // Submenu Dashboard
        add_submenu_page(
            'join_ads_loader_main',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'join_ads_loader_main',
            array($this, 'display_dashboard_page')
        );
    }

    public function display_dashboard_page()
    {
        $options = get_option('joinads_loader_config_settings');
        $api = new JoinAds_API();
        $dashboard_data = $api->get_dashboard_data();
        ?>


        <div class="css-y9g7i8">
            <div class="css-gazh6o">
                <!-- DASHBOARD -->
                <div class="MuiGrid-item css-4tz9zv">
                    <div class="css-1lois7l">
                        <h3 class="css-21yr5o">Dashboard Join Ads</h3>
                    </div>
                    <!-- FILTRO -->
                    <form method="get">
                        <div class="css-1ipn5om">
                            <div class="css-tzsjye">
                                <label class="css-16prq53" data-shrink="true" id="date-select-label">Data</label>
                                <input type="hidden" name="page" value="join_ads_loader_main">
                                <select name="period" onchange="this.form.submit()">
                                    <option value="7" <?php selected($_GET['period'] ?? '7', '7'); ?>>Últimos 7 dias
                                    </option>
                                    <option value="15" <?php selected($_GET['period'] ?? '7', '15'); ?>>Últimos 15
                                        dias</option>
                                    <option value="30" <?php selected($_GET['period'] ?? '7', '30'); ?>>Últimos 30
                                        dias</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- CARDS -->
                <div class="css-16udod0">
                    <div class="css-isbt42">
                        <?php
                        if (!is_wp_error($dashboard_data) && isset($dashboard_data['FloatingNumbers'])) {
                            $cardValues = $this->transformObject($dashboard_data['FloatingNumbers']);

                            foreach ($cardValues as $key => $value) {
                        ?>
                                <div class="css-r98zzg">
                                    <div class="css-u6v7fs">
                                        <div class="css-3ofp2w">
                                            <div class="css-4jin6z">
                                                <div class="css-69i1ev">
                                                    <h6 class="css-iylkxw">
                                                        <div class="css-1bntj9o">
                                                            <?php echo $key ?>
                                                        </div>
                                                    </h6>
                                                </div>
                                                <h4 class="css-q1yb4z">
                                                    <div class="css-1bntj9o"><?php echo $value['value']; ?></div>
                                                </h4>
                                                <div class="css-1mucns2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="css-1jwcqz0">
                    <!-- MAIORES PAISES -->
                    <div class="css-1f9xl3h">
                        <div class="css-1ih4t8m">
                            <div class="css-rfnosa">
                                <h5 class="css-uw5qdi">Maiores 5 países</h5>
                                <hr class="css-w82akl">
                                <div class="css-0">
                                    <ul class="css-8h6itf">
                                        <?php
                                        if (!is_wp_error($dashboard_data) && isset($dashboard_data['CountryRevenues'])) {
                                            // $cardValues = $this->transformObject($dashboard_data['FloatingNumbers']);
                                            $count = 1;
                                            $greenValue = 242;
                                            foreach ($dashboard_data['CountryRevenues'] as $key => $value) {
                                                $greenValue = max(0, $greenValue - 30);
                                        ?>

                                                <li class="css-ktwerk">
                                                    <div class="css-1w30cbz" style="background-color: rgb(60, <?php echo $greenValue; ?>, 107);">
                                                        <span class="css-14vsv3w"><?php echo $count ?></span>
                                                    </div>
                                                    <div class="css-1xar93x">
                                                        <a class="css-1edxfyp"><?php echo $value['country'] ?></a>

                                                        <div class="css-1ofqig9">
                                                            <div class="css-gg4vpm">
                                                                <p class="css-fi5e9e">Revenue:</p>
                                                                <p class="css-1lnf5xi">$<?php echo number_format($value['revenue'], 2, ',', '.') ?></p>
                                                            </div>
                                                            <div class="css-gg4vpm">
                                                                <p class="css-fi5e9e">eCPM:</p>
                                                                <p class="css-1lnf5xi">$<?php echo number_format($value['ecpm'], 2, ',', '.') ?></p>
                                                            </div>
                                                            <div class="css-gg4vpm">
                                                                <p class="css-fi5e9e">CTR:</p>
                                                                <p class="css-1lnf5xi"><?php echo number_format($value['ctr'], 2, ',', '.') ?>%</p>
                                                            </div>
                                                        </div>

                                                    </div>

                                                </li>
                                                <hr class="css-w82akl">

                                        <?php
                                                $count++;
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TOP URLS -->
                    <div class="css-1m7shs5">
                        <div class="css-1ih4t8m">
                            <div class="css-1ber4kh">
                                <h5 class=" css-uw5qdi">Top 5 URLs</h5>
                                <hr class="css-w82akl">
                                <div class="css-10klw3m">
                                    <ul class="css-uowjlg ">

                                        <?php
                                        if (!is_wp_error($dashboard_data) && isset($dashboard_data['TopFiveUrls'])) {
                                            // $cardValues = $this->transformObject($dashboard_data['FloatingNumbers']);
                                            $count = 1;
                                            $greenValue = 242;
                                            foreach ($dashboard_data['TopFiveUrls'] as $key => $value) {
                                                $greenValue = max(0, $greenValue - 30);
                                        ?>
                                                <li class="css-ktwerk">
                                                    <div class="css-1w30cbz" style="background-color: rgb(60, <?php echo $greenValue; ?>, 107);">
                                                        <span class="css-14vsv3w"><?php echo $count ?></span>
                                                    </div>
                                                    <div class="css-1xar93x">

                                                        <a href="<?php echo $value['url'] ?>" target="_blank" rel="noopener noreferrer" class="topLink">
                                                            <?php echo $value['url'] ?>
                                                        </a>


                                                        <div class="css-1ofqig9">
                                                            <div class="css-gg4vpm">
                                                                <p class="css-fi5e9e">Revenue:</p>
                                                                <p class="css-1lnf5xi">$<?php echo number_format($value['revenue'], 2, ',', '.') ?></p>
                                                            </div>
                                                            <div class="css-gg4vpm">
                                                                <p class="css-fi5e9e">eCPM:</p>
                                                                <p class="css-1lnf5xi">$<?php echo number_format($value['ecpm'], 2, ',', '.') ?></p>
                                                            </div>
                                                            <div class="css-gg4vpm">
                                                                <p class="css-fi5e9e">CTR:</p>
                                                                <p class="css-1lnf5xi"><?php echo number_format($value['ctr'], 2, ',', '.') ?>%</p>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </li>
                                                <hr class="css-w82akl">

                                        <?php
                                                $count++;
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php
    }
}
