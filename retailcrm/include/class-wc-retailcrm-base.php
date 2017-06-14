<?php
/**
 * Retailcrm Integration.
 *
 * @package  WC_Retailcrm_Base
 * @category Integration
 * @author   Retailcrm
 */

if ( ! class_exists( 'WC_Retailcrm_Base' ) ) :

    /**
     * Class WC_Retailcrm_Base
     */
    class WC_Retailcrm_Base extends WC_Integration {

    protected $api_url;
    protected $api_key;

    /**
     * Init and hook in the integration.
     */
    public function __construct() {
        //global $woocommerce;

        $this->id                 = 'integration-retailcrm';
        $this->method_title       = __( 'Retailcrm', 'woocommerce-integration-retailcrm' );
        $this->method_description = __( 'Интеграция с системой управления Retailcrm.', 'woocommerce-integration-retailcrm' );

        // Load the settings.

        $this->init_form_fields();
        $this->init_settings();

        // Actions.
        add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Initialize integration settings form fields.
     */
    public function init_form_fields() {

        $this->form_fields = array(
            array( 'title' => __( 'Общие настройки', 'woocommerce' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            'api_url' => array(
                'title'             => __( 'API URL', 'woocommerce-integration-retailcrm' ),
                'type'              => 'text',
                'description'       => __( 'Введите адрес вашей CRM (https://yourdomain.retailcrm.ru).', 'woocommerce-integration-retailcrm' ),
                'desc_tip'          => true,
                'default'           => ''
            ),
            'api_key' => array(
                'title'             => __( 'API Ключ', 'woocommerce-integration-retailcrm' ),
                'type'              => 'text',
                'description'       => __( 'Введите ключ API. Вы можете найти его в интерфейсе администратора Retailcrm.', 'woocommerce-integration-retailcrm' ),
                'desc_tip'          => true,
                'default'           => ''
            )
        );

        if ($this->get_option( 'api_url' ) != '' && $this->get_option( 'api_key' ) != '') {

            if ( ! class_exists( 'WC_Retailcrm_Client' ) ) {
                include_once( __DIR__ . '/api/class-wc-retailcrm-client.php' );
            }

            $retailcrm = new WC_Retailcrm_Client(
                $this->get_option( 'api_url' ),
                $this->get_option( 'api_key' )
            );

            /**
             * Shipping options
             */

            $shipping_option_list = array();
            $retailcrm_shipping_list = $retailcrm->deliveryTypesList();

            foreach ($retailcrm_shipping_list['deliveryTypes'] as $retailcrm_shipping_type) {
                $shipping_option_list[$retailcrm_shipping_type['code']] = $retailcrm_shipping_type['name'];
            }

            $wc_shipping = new WC_Shipping();
            $wc_shipping_list = $wc_shipping->get_shipping_methods();

            $this->form_fields[] = array(
                'title' => __( 'Способы доставки', 'woocommerce' ),
                'type' => 'title',
                'description' => '',
                'id' => 'shipping_options'
            );

            foreach ( $wc_shipping_list as $shipping ) {
                if ( isset( $shipping->enabled ) && $shipping->enabled == 'yes' ) {
                    $key = $shipping->id;
                    $name = $key;
                    $this->form_fields[$name] = array(
                        'title'          => __( $shipping->method_title, 'textdomain' ),
                        'description' => __( $shipping->method_description, 'textdomain' ),
                        'css'            => 'min-width:350px;',
                        'class'          => 'select',
                        'type'           => 'select',
                        'options'        => $shipping_option_list,
                        'desc_tip'    =>  true,
                    );
                }
            }

            /**
             * Payment options
             */

            $payment_option_list = array();
            $retailcrm_payment_list = $retailcrm->paymentTypesList();

            foreach ($retailcrm_payment_list['paymentTypes'] as $retailcrm_payment_type) {
                $payment_option_list[$retailcrm_payment_type['code']] = $retailcrm_payment_type['name'];
            }

            $wc_payment = new WC_Payment_Gateways();
            $wc_payment_list = $wc_payment->get_available_payment_gateways();

            $this->form_fields[] = array(
                'title' => __( 'Способы оплаты', 'woocommerce' ),
                'type' => 'title',
                'description' => '',
                'id' => 'payment_options'
            );

            foreach ( $wc_payment_list as $payment ) {
                if ( isset( $payment->enabled ) && $payment->enabled == 'yes' ) {
                    $key = $payment->id;
                    $name = $key;
                    $this->form_fields[$name] = array(
                        'title'          => __( $payment->method_title, 'textdomain' ),
                        'description' => __( $payment->method_description, 'textdomain' ),
                        'css'            => 'min-width:350px;',
                        'class'          => 'select',
                        'type'           => 'select',
                        'options'        => $payment_option_list,
                        'desc_tip'    =>  true,
                    );
                }
            }

            /**
             * Statuses options
             */
            $statuses_option_list = array();
            $retailcrm_statuses_list = $retailcrm->statusesList();

            foreach ($retailcrm_statuses_list['statuses'] as $retailcrm_status) {
                $statuses_option_list[$retailcrm_status['code']] = $retailcrm_status['name'];
            }

            $wc_statuses = wc_get_order_statuses();

            $this->form_fields[] = array(
                'title' => __( 'Статусы', 'woocommerce' ),
                'type' => 'title',
                'description' => '',
                'id' => 'statuses_options'
            );

            foreach ( $wc_statuses as $idx => $name ) {
                $uid = str_replace('wc-', '', $idx);
                $this->form_fields[$uid] = array(
                    'title'          => __( $name, 'textdomain' ),
                    'css'            => 'min-width:350px;',
                    'class'          => 'select',
                    'type'           => 'select',
                    'options'        => $statuses_option_list,
                    'desc_tip'    =>  true,
                );
            }

            $this->form_fields[] = array(
                'title' => __( 'Настройки остатков', 'woocommerce' ),
                'type' => 'title',
                'description' => '',
                'id' => 'invent_options'
            );

            $this->form_fields['sync'] = array(
                'label'          => __( 'Выгружать остатки из CRM', 'textdomain' ),
                'title'       => 'Остатки',
                'class'          => 'checkbox',
                'type'           => 'checkbox',
                'description' => 'Отметьте данный пункт, если хотите выгружать остатки товаров из CRM в магазин.'
            );

            $options = array_filter(get_option( 'woocommerce_integration-ecomlogic_settings' ));

            if (!isset($options['uploads'])) {
                $this->form_fields[] = array(
                    'title' => __( 'Выгрузка клиентов и заказов', 'woocommerce' ),
                    'type' => 'title',
                    'description' => '',
                    'id' => 'upload_options'
                );
               
                $this->form_fields['upload-button'] = array(
                    'label'             => 'Выгрузить',
                    'title'             => __( 'Выгрузка клиентов и заказов', 'woocommerce-integration-ecomlogic' ),
                    'type'              => 'button',
                    'description'       => __( 'Пакетная выгрузка существующих клиентов и заказов.', 'woocommerce-integration-ecomlogic' ),
                    'desc_tip'          => true,
                    'id'                => 'uploads-ecomlogic'
                );
            }
        }
    }

    public function generate_button_html( $key, $data ) {
        $field    = $this->plugin_id . $this->id . '_' . $key;
        $defaults = array(
            'class'             => 'button-secondary',
            'css'               => '',
            'custom_attributes' => array(),
            'desc_tip'          => false,
            'description'       => '',
            'title'             => '',
        );

        $data = wp_parse_args( $data, $defaults );

        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
                <?php echo $this->get_tooltip_html( $data ); ?>
            </th>
            <td class="forminp">
                <fieldset>
                    <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['label'] ); ?></span></legend>
                    <button id="<?php echo $data['id']; ?>" class="<?php echo esc_attr( $data['class'] ); ?>" type="button" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo wp_kses_post( $data['label'] ); ?></button>
                    <?php echo $this->get_description_html( $data ); ?>
                </fieldset>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }
}

endif;