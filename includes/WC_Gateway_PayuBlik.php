<?php

class WC_Gateway_PayuBlik extends WC_PayUGateways
{
    protected $paytype = 'blik';

    function __construct()
    {
        parent::__construct('payublik');

        if ($this->is_enabled()) {
            $this->show_terms_info = true;
            $this->icon = apply_filters('woocommerce_payu_icon', plugins_url('/assets/images/blik.svg', PAYU_PLUGIN_FILE));

            if (!is_admin()) {
                if (!$this->try_retrieve_banks()) {
                    add_filter('woocommerce_available_payment_gateways', [$this, 'unset_gateway']);
                }
            }
        }
    }

    /**
     * setup additional checkbox for BLIK T6 in admin panel
     * @return array
     */

     protected function get_additional_blik_fields()
    {
        return [
            'BLIK_T6_enabled' => [
                'title' => __('BLIK with code', 'woo-payu-payment-gateway'),
                'type' => 'checkbox',
                'label' => __('Enabled for T6 Token', 'woo-payu-payment-gateway'),
                'default' => 'no'
            ],
        ];
    }

    /**
     * display blik widget 
     */
    private function show_blik_widget()
    {
?>
        <div class="payu-blik-container">
            <label for="BLIK_AUTHORIZATION_CODE">
                <?php esc_html_e__('Enter the BLIK code: ', 'woo-payu-payment-gateway') ?></label>
            <input type="text" inputType="numeric" id="BLIK_AUTHORIZATION_CODE" name="BLIK_AUTHORIZATION_CODE" maxLength="6" placeholder="kod blik" autocorrect="off" spellcheck="false">
            <ul class="blik-error woocommerce-error" role="alert">
                <li><?php esc_html_e__('Enter exactly 6 digits.', 'woo-payu-payment-gateway') ?></li>
            </ul>
        </div>
        
<?php
    }


    /**
     * display widget in payment_fields or show standard blik flow
     */
    public function payment_fields()
    {
        if (get_option('woocommerce_payublik_settings')['BLIK_T6_enabled'] === 'yes') {
            $this->show_blik_widget();
        } else {
            parent::payment_fields();
        }
        $this->agreements_field();
    }
    
    /**
     * check if blik code was send in POST
     * set pay method for BLIK with code or BLIK standard 
     * @return array value
     */
    function get_payu_pay_method()
    {
        // check if blik code was send in POST
        if (!empty($_POST['BLIK_AUTHORIZATION_CODE'])) {
            $authorizationCode = sanitize_text_field($_POST['BLIK_AUTHORIZATION_CODE']);
            //blik with code
            return $this->get_payu_pay_method_array('BLIK_AUTHORIZATION_CODE', $authorizationCode);
        } else {
            //standard blik request
            return $this->get_payu_pay_method_array('PBL', $this->paytype);
        }
    }
}
