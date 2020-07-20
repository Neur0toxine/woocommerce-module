<?php
/**
 * Version: 4.0.0
 * WC requires at least: 3.0
 * WC tested up to: 3.9.3
 * Plugin Name: WooCommerce retailCRM
 * Plugin URI: https://wordpress.org/plugins/woo-retailcrm/
 * Description: Integration plugin for WooCommerce & retailCRM
 * Author: RetailDriver LLC
 * Author URI: http://retailcrm.pro/
 * Text Domain: retailcrm
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if (!class_exists( 'WC_Integration_Retailcrm')) :

    /**
     * Class WC_Integration_Retailcrm
     */
    class WC_Integration_Retailcrm {
        const WOOCOMMERCE_SLUG = 'woocommerce';
        const WOOCOMMERCE_PLUGIN_PATH = 'woocommerce/woocommerce.php';

        private static $instance;

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Construct the plugin.
         */
        public function __construct() {
            $this->load_plugin_textdomain();

            if (class_exists( 'WC_Integration' )) {
                self::load_module();
                add_filter('woocommerce_integrations', array( $this, 'add_integration'));
            } else {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            }
        }

        public function woocommerce_missing_notice() {
            if (static::isWooCommerceInstalled()) {
                if (!is_plugin_active(static::WOOCOMMERCE_PLUGIN_PATH)) {
                    echo '
                    <div class="error">
                        <p>
                            Activate WooCommerce in order to enable retailCRM integration!
                            <a href="' . wp_nonce_url(admin_url('plugins.php')) . '" aria-label="Activate WooCommerce">
                                Click here to open plugins manager
                            </a>
                        </p>
                    </div>
                    ';
                }
            } else {
                echo '
                <div class="error">
                    <p>
                        <a href="'
                    . static::generatePluginInstallationUrl(static::WOOCOMMERCE_SLUG)
                    . '" aria-label="Install WooCommerce">Install WooCommerce</a> in order to enable retailCRM integration!
                    </p>
                </div>
                ';
            }
        }

        public function load_plugin_textdomain() {
            load_plugin_textdomain('retailcrm', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        /**
         * Add a new integration to WooCommerce.
         *
         * @param $integrations
         *
         * @return array
         */
        public function add_integration( $integrations ) {
            $integrations[] = 'WC_Retailcrm_Base';
            return $integrations;
        }

        /**
         * Loads module classes.
         */
        public static function load_module()
        {
            require_once(self::checkCustomFile('include/interfaces/class-wc-retailcrm-builder-interface.php'));
            require_once(self::checkCustomFile('include/models/class-wc-retailcrm-customer-switcher-state.php'));
            require_once(self::checkCustomFile('include/models/class-wc-retailcrm-customer-switcher-result.php'));
            require_once(self::checkCustomFile('include/components/class-wc-retailcrm-logger.php'));
            require_once(self::checkCustomFile('include/components/class-wc-retailcrm-history-assembler.php'));
            require_once(self::checkCustomFile('include/components/class-wc-retailcrm-paginated-request.php'));
            require_once(self::checkCustomFile('include/components/class-wc-retailcrm-customer-switcher.php'));
            require_once(self::checkCustomFile('include/abstracts/class-wc-retailcrm-abstract-builder.php'));
            require_once(self::checkCustomFile('include/abstracts/class-wc-retailcrm-abstracts-settings.php'));
            require_once(self::checkCustomFile('include/abstracts/class-wc-retailcrm-abstracts-data.php'));
            require_once(self::checkCustomFile('include/abstracts/class-wc-retailcrm-abstracts-address.php'));
            require_once(self::checkCustomFile('include/customer/woocommerce/class-wc-retailcrm-wc-customer-builder.php'));
            require_once(self::checkCustomFile('include/order/class-wc-retailcrm-order.php'));
            require_once(self::checkCustomFile('include/order/class-wc-retailcrm-order-payment.php'));
            require_once(self::checkCustomFile('include/order/class-wc-retailcrm-order-item.php'));
            require_once(self::checkCustomFile('include/order/class-wc-retailcrm-order-address.php'));
            require_once(self::checkCustomFile('include/customer/class-wc-retailcrm-customer-address.php'));
            require_once(self::checkCustomFile('include/customer/class-wc-retailcrm-customer-corporate-address.php'));
            require_once(self::checkCustomFile('include/class-wc-retailcrm-icml.php'));
            require_once(self::checkCustomFile('include/class-wc-retailcrm-orders.php'));
            require_once(self::checkCustomFile('include/class-wc-retailcrm-customers.php'));
            require_once(self::checkCustomFile('include/class-wc-retailcrm-inventories.php'));
            require_once(self::checkCustomFile('include/class-wc-retailcrm-history.php'));
            require_once(self::checkCustomFile('include/class-wc-retailcrm-ga.php'));
            require_once(self::checkCustomFile('include/class-wc-retailcrm-daemon-collector.php'));
            require_once(self::checkCustomFile('include/class-wc-retailcrm-base.php'));
            require_once(self::checkCustomFile('include/functions.php'));
        }

        /**
         * Check custom file
         *
         * @param string $file
         *
         * @return string
         */
        public static function checkCustomFile($file)
        {
            $oldCustomizationFiles = array(
                'include/class-wc-retailcrm-icml.php',
                'include/class-wc-retailcrm-orders.php',
                'include/class-wc-retailcrm-customers.php',
                'include/class-wc-retailcrm-inventories.php',
                'include/class-wc-retailcrm-history.php',
                'include/class-wc-retailcrm-ga.php',
                'include/class-wc-retailcrm-daemon-collector.php'
            );

            if (in_array($file, $oldCustomizationFiles)) {
                $pathWithoutInclude = WP_CONTENT_DIR . '/retailcrm-custom/' . str_replace('include/', '', $file);

                if (file_exists($pathWithoutInclude)) {
                    return $pathWithoutInclude;
                }
            }

            if (file_exists( WP_CONTENT_DIR . '/retailcrm-custom/' . $file)) {
                return WP_CONTENT_DIR . '/retailcrm-custom/' . $file;
            }

            return WP_PLUGIN_DIR . '/woo-retailcrm/' . $file;
        }

        /**
         * Returns true if WooCommerce was found in plugin cache
         *
         * @return bool
         */
        private function isWooCommerceInstalled()
        {
            $plugins = wp_cache_get( 'plugins', 'plugins' );

            if (!$plugins) {
                $plugins = get_plugins();
            } elseif (isset($plugins[''])) {
                $plugins = $plugins[''];
            }

            if (!isset($plugins[static::WOOCOMMERCE_PLUGIN_PATH])) {
                return false;
            }

            return true;
        }

        /**
         * Generate plugin installation url
         *
         * @param $pluginSlug
         *
         * @return string
         */
        private function generatePluginInstallationUrl($pluginSlug)
        {
            $action = 'install-plugin';

            return wp_nonce_url(
                add_query_arg(
                    array(
                        'action' => $action,
                        'plugin' => $pluginSlug
                    ),
                    admin_url( 'update.php' )
                ),
                $action.'_'.$pluginSlug
            );
        }
    }

    if (!class_exists('WC_Retailcrm_Plugin')) {
        require_once (dirname(__FILE__) . '/include/class-wc-retailcrm-plugin.php');
    }

    $plugin = WC_Retailcrm_Plugin::getInstance(__FILE__);
    $plugin->register_activation_hook();
    $plugin->register_deactivation_hook();

    add_action('plugins_loaded', array('WC_Integration_Retailcrm', 'get_instance'), 0);
endif;
