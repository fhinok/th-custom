<?php

/** 
 * add custom fields to profiles
 */

    defined('ABSPATH') or die("not allowed");

    /**
     * define all custom user fields and display
     */

    add_action('show_user_profile', 'th_custom_user_fields');
    add_action('edit_user_profile', 'th_custom_user_fields');
    add_action('user_new_form', 'th_custom_user_fields');

    function th_custom_user_fields($user) {
        $customer_number = get_the_author_meta('customer_number', $user->ID);
        $customer_shipping = get_the_author_meta('customer_shipping', $user->ID);
        $customer_shipping_desc = get_the_author_meta('customer_shipping_desc', $user->ID);
        $crm_contact = get_the_author_meta('crm_contact', $user->ID);
        $can_buy_categories = get_the_author_meta('can_buy_categories', $user->ID);
        if ( $can_buy_categories ) {
            $can_buy_categories = implode(', ', $can_buy_categories);
        }

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
                <th><label for="can_buy_categories"><?php esc_html_e( 'Kann Kaufen', 'crf' ); ?></label></th>
                <td>
                    <input type="text"
                        name = "can_buy_categories"
                        id = "can_buy_categories"
                        value = "<?php echo esc_attr( $can_buy_categories ); ?>"
                        class = "regular-text"
                    />
                </td>
            </tr>
            <tr>
                <th><label for="customer_shipping"><?php esc_html_e( 'Standard - Versandoption', 'crf' ); ?></label></th>
                <td>                    
                    <select name="customer_shipping" id="customer_shipping">
                    <option value="0" <?php selected( 0 , $customer_shipping ); ?> ></option>
                    <option value="1" <?php selected( 1 , $customer_shipping ); ?> >Lieferung durch Töpferhaus</option>
                    <option value="2" <?php selected( 2 , $customer_shipping ); ?> >Abholung durch Kunden</option>
                    <option value="3" <?php selected( 3 , $customer_shipping ); ?> >Postversand</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="customer_shipping_desc"><?php esc_html_e( 'Bemerkungen Versand', 'crf' ); ?></label></th>
                <td>                    
                    <textarea type="text"
                        name = "customer_shipping_desc"
                        id = "customer_shipping_desc"
                        rows = "2" cols = "30"
                    ><?php echo esc_attr( $customer_shipping_desc ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="crm_contact"><?php esc_html_e( 'Kontaktperson', 'crf' ); ?></label></th>
                <td>
                <textarea type="text"
                        name = "crm_contact"
                        id = "crm_contact"
                        rows = "5" cols = "30"
                    ><?php echo esc_attr( $crm_contact ); ?></textarea>
                </td>
            </tr>
        </table>


    <?php
    
        }

    /**
     * save custom user fields on save and update
     */
    add_action( 'personal_options_update', 'save_extra_profile_fields' );
    add_action( 'edit_user_profile_update', 'save_extra_profile_fields' );
    add_action( 'user_register', 'save_extra_profile_fields');
    
    function save_extra_profile_fields( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        $customer_number = '530';
        if ( isset( $_POST['customer_number'] ) && !empty( $_POST['customer_number'] ) ) {
            $customer_number = $_POST['customer_number'];
        }

        update_user_meta( $user_id, 'customer_number', sanitize_text_field( $customer_number ) );
        update_user_meta( $user_id, 'customer_shipping', sanitize_text_field( $_POST['customer_shipping'] ) );
        update_user_meta( $user_id, 'customer_shipping_desc', sanitize_textarea_field( $_POST['customer_shipping_desc'] ) );
        update_user_meta( $user_id, 'crm_contact', sanitize_textarea_field( $_POST['crm_contact'] ) );
        update_user_meta( $user_id, 'can_buy_categories', explode(', ', sanitize_textarea_field( $_POST['can_buy_categories'] ) ) );
        

    }

    /**
     * show custom user fields in customers account info as readonly
     */
    add_action( 'woocommerce_edit_account_form', 'th_custom_user_fields_customer' );

    function th_custom_user_fields_customer( ) {
        $user = $user = wp_get_current_user();
        $customer_number = get_the_author_meta('customer_number', $user->ID);
        $customer_shipping = get_the_author_meta('customer_shipping', $user->ID);
        $customer_shipping_desc = get_the_author_meta('customer_shipping_desc', $user->ID);
        $crm_contact = get_the_author_meta('crm_contact', $user->ID);
        $can_buy_categories = get_the_author_meta('can_buy_categories', $user->ID);
        if ( $can_buy_categories ) {
            $can_buy_categories = implode(', ', $can_buy_categories);
        }

?>

        <br><h3>Töpferhaus Infos</h3>

        <table class="woocommerce-EditAccountForm edit-account">
        <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
            <label for="th_customer_number"><?php esc_html_e( 'Ihre Kundennummer', 'crf' ); ?></label>
            <input type="text" 
                class="woocommerce-Input woocommerce-Input--text input-text" 
                name="th_customer_number" id="th_customer_number" 
                value="<?php echo esc_attr( $customer_number ); ?>"
                disabled="disabled"
            >
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
            <label for="th_crm_contact"><?php esc_html_e( 'Kontaktperson', 'crf' ); ?></label>
            <label style='white-space: pre-line; font-weight: 400'><?php echo $crm_contact; ?></label>
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="th_customer_shipping"><?php esc_html_e( 'Standard - Versandoption', 'crf' ); ?></label>                
            <select name="th_customer_shipping" id="th_customer_shipping" disabled="disabled">
                <option value="0" <?php selected( 0 , $customer_shipping ); ?> ></option>
                <option value="1" <?php selected( 1 , $customer_shipping ); ?> >Lieferung durch Töpferhaus</option>
                <option value="2" <?php selected( 2 , $customer_shipping ); ?> >Abholung durch Kunden</option>
                <option value="3" <?php selected( 3 , $customer_shipping ); ?> >Postversand</option>
            </select>
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="customer_shipping_desc"><?php esc_html_e( 'Standardbemerkungen Versand', 'crf' ); ?></label>              
            <textarea type="text"
                name = "customer_shipping_desc"
                id = "customer_shipping_desc"
                rows = "2" cols = "30"
            ><?php echo esc_attr( $customer_shipping_desc ); ?></textarea>
        </p>
        </table>

    <?php
        
    }

    /**
     * allow user to save customer_shipping_desc
     */
    add_action( 'woocommerce_save_account_details', 'th_save_account_fields' );
    function th_save_account_fields( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        update_user_meta( $user_id, 'customer_shipping_desc', sanitize_textarea_field( $_POST['customer_shipping_desc'] ) );

        if ( $_POST['customer_shipping_desc'] != get_user_meta( $user_id,  'customer_shipping_desc', true ) ) {
            wp_die( __( 'An error occurred', 'textdomain' ) );
        }
    }

    
    /**
     * register custom user fields in rest API
     */
    add_action( 'rest_api_init', 'th_custom_user_api' );
    function th_custom_user_api ($user) {
        // Check, if user is allowed to see meta fields
        if( !current_user_can( 'edit_user', $user ) ){
            return;
        }

        // register meta fields
        $meta = array('customer_number', 'customer_shipping', 'customer_shipping_desc', 'crm_contact', 'can_buy_categories');
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
        return update_user_meta($user['id'], $field, $value, false);
    }


    /**
     * allow user roles to be retrieved via API
     */

    function th_get_user_roles( $request ) {
        $user_meta = get_userdata( $request->get_param('id') );
        if ( empty( $user_meta ) ) {
            return new WP_Error( 'no_customer', 'Invalid customer', array( 'status' => 404 ) );
          }
        $user_roles = $user_meta->roles;
        $response = array(
            'id' => $user_meta->ID,
            'roles' => $user_roles
        );
        return $response;
    }

    function th_add_user_roles( $request ) {
        $roles = $request->get_param('roles');
        
        $user = new WP_User( $request->get_param('id') );
        $user->set_role('');
        
        foreach( $roles as $role ) {
            $user->add_role($role);
        }
        
        return $user;
    }

    /**
     * add roles endpoint to set user roles via API
     */
    add_action( 'rest_api_init', 'th_roles_route' );
    function th_roles_route() {
        register_rest_route( 'wc/v3', 'customers/(?P<id>\d+)/roles/', array(
            'methods' => 'GET',
            'callback' => 'th_get_user_roles',
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                    return is_numeric( $param );
                    }
                ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'administrator' );
            }
        ) );

        register_rest_route( 'wc/v3', 'customers/(?P<id>\d+)/roles/', array(
            'methods' => array('POST', 'PUT'),
            'callback' => 'th_add_user_roles',
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                    return is_numeric( $param );
                    }
                )
            ),
            'permission_callback' => function () {
                return current_user_can( 'administrator' );
            }
        ) );
    }

?>
