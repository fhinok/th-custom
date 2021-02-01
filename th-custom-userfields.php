<?php

    defined('ABSPATH') or die("not allowed");

    add_action('show_user_profile', 'th_custom_user_fields');
    add_action('edit_user_profile', 'th_custom_user_fields');
    add_action( "user_new_form", 'th_custom_user_fields');

    function th_custom_user_fields($user) {
        $customer_number = get_the_author_meta('customer_number', $user->ID);
        $customer_shipping = get_the_author_meta('customer_shipping', $user->ID);

?>

        <h3>Töpferhaus Infos</h3>

        <table class="form-table">
            <tr>
                <th><label for="customer_number"><?php esc_html_e( 'Kundennummer', 'crf' ); ?></label></th>
                <td>
                    <input type="text"
                        name = "customer_number"
                        id = "customer_number"
                        value = "<?php echo esc_attr( $customer_number ); ?>"
                        class = "regular-text"
                    />
                </td>
            </tr>
            <tr>
                <th><label for="customer_shipping"><?php esc_html_e( 'Versandoption', 'crf' ); ?></label></th>
                <td>                    
                    <select name="customer_shipping" id="customer_shipping">
                        <option value="0" <?php selected( 0 , $customer_shipping ); ?> >Berechnung nach PLZ</option>
                        <option value="1" <?php selected( 1 , $customer_shipping ); ?> >Spezialdeal</option>
                        <option value="2" <?php selected( 2 , $customer_shipping ); ?> >Abholung</option>
                    </select>
                </td>
            </tr>
        </table>


    <?php
    
        }

    add_action( 'personal_options_update', 'save_extra_profile_fields' );
    add_action( 'edit_user_profile_update', 'save_extra_profile_fields' );
    add_action( 'user_register', 'save_extra_profile_fields');
    
    function save_extra_profile_fields( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        update_user_meta( $user_id, 'customer_number', $_POST['customer_number']);
        update_user_meta( $user_id, 'customer_shipping', $_POST['customer_shipping'] );

        if ( $_POST['customer_number'] != get_user_meta( $user_id,  'customer_number', true ) ) {
            wp_die( __( 'An error occurred', 'textdomain' ) );
        }
    }


    // Add to REST API

    add_action( 'rest_api_init', 'th_custom_user_api' );

    function th_custom_user_api ($user) {

        // Check, if user is allowed to see meta fields

        if( !current_user_can( 'edit_user', $user ) ){
            return;
        }

        // register meta fields to API

        $meta = array('customer_number', 'customer_shipping');
        foreach ( $meta as $item ) {
            register_rest_field('user', $item, array(
                'get_callback' => 'th_get_custom_user_api',
                'update_callback' => 'th_update_custom_user_api',
                'schema' => null
            ));
        }
    }

    // GET from API
    function th_get_custom_user_api( $user, $field, $request ) {
        return get_user_meta($user['id'], $field, true);
    }

    // POST to API
    function th_update_custom_user_api ($value, $user, $field) {
        update_user_meta($user->ID, $field, $value);
    }

?>
