<?php 

/**
 * base to add a shippment method for pickwings in the future
 * 
 * THIS PART IS CURRENTLY UNUSED
 */
    if ( ! defined( 'WPINC' ) ) {
        die;
    }

    if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

        function th_shipping_pickwings() {
            if( ! class_exists( 'Th_Shipping_Pickwings' ) ) {

                class Th_Shipping_Pickwings extends WC_Shipping_Method {

                    public function __construct( $instance_id = 0) {
                        $this->id = 'pickwings';
                        $this->instance_id = absint( $instance_id );
                        $this->method_title = __( 'Pickwings Lieferung', 'pickwings' );
                        $this->method_description = __( 'Berechne den Preis der Pickwingslieferung', 'pickwings' );
                        $this->availability = 'including';
                        $this->supports = array(
                            'shipping-zones',
                            'instance-settings',
                        );

                        $this->init();

                        $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                        $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Pickwings Lieferung', 'pickwings' );
                    }

                    function init() {
                        $this->init_form_fields();
                        $this->init_settings();

                        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                    }

                    function init_form_fields() {
                        $this->form_fields = array(
                            'enabled' => array(
                                'title' => __( 'Aktivieren', 'pickwings' ),
                                'type' => 'checkbox',
                                'description' => __('Pickwings aktivieren?', 'pickwings'),
                                'default' => 'yes'
                            ),
                            'title' => array(
                                'title' => __( 'Title', 'pickwings' ),
                                'type' => 'text',
                                'description' => __( 'Angezeigte Bezeichnung', 'pickwings' ),
                                'default' => __( 'Lieferung mit Pickwings', 'pickwings' )
                            ),
                            'min_weight' => array(
                                'title' => __( 'Min. Gewicht (kg)', 'pickwings' ),
                                'type' => 'number',
                                'description' => __( 'Minimales Gewicht, damit Pickwings zur Auswahl steht', 'pickwings' ),
                                'default' => 25
                            ),
                            'max_weight' => array(
                                'title' => __( 'Max. Gewicht (kg)', 'pickwings' ),
                                'type' => 'number',
                                'description' => __( 'Maximal erlaubtes Gewicht', 'pickwings' ),
                                'default' => 25
                            ),
                        );
                    }

                    public function calculate_shipping( $package = array() ) {
                        $weight = 0;
                        $cost = 0;
                        $country = $package['destination']['country'];

                        foreach( $package['contents'] as $item_id => $values ) {
                            $_product = $values['data'];
                            $weight = $weight + $_product->get_weight() * $values['quantity'];

                        }
                        
                        $weight = wc_get_weight( $weight, 'kg' );

                        if( $weight >= 20 ) {
                            $cost = 49;
                        }

                        if( $weight >= 30 ) {
                            $cost = 59;
                        }
                        
                        $this->add_rate( array(
                            'id'    => $this->id . $this->instance_id,
                            'label' => $this->title . " (Bedingt Absprache mit uns) ab",
                            'cost'  => $cost,
                        ) );
                        
                    }
                }
            }
        }

        add_action( 'woocommerce_shipping_init', 'th_shipping_pickwings' );

        function add_th_shipping_pickwings( $methods ) {
            $methods['pickwings'] = 'Th_Shipping_Pickwings';
            return $methods;
        }

        add_filter( 'woocommerce_shipping_methods', 'add_th_shipping_pickwings' );
    }

?>