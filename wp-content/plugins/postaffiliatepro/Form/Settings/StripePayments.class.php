<?php
/**
 *   @copyright Copyright (c) 2019 Quality Unit s.r.o.
 *   @author Martin Svitek
 *   @package WpPostAffiliateProPlugin
 *   @since version 1.17.0
 *
 *   Licensed under GPL2
 */
class postaffiliatepro_Form_Settings_StripePayments extends postaffiliatepro_Form_Base {
    const STRIPEPAYMENTS_COMMISSION_ENABLED = 'stripepay-commission-enabled';
    const STRIPEPAYMENTS_CONFIG_PAGE = 'stripepay-config-page';

    public function __construct() {
        parent::__construct(self::STRIPEPAYMENTS_CONFIG_PAGE, 'options.php');
    }

    public function initSettings() {
        register_setting(postaffiliatepro::INTEGRATIONS_SETTINGS_PAGE_NAME, self::STRIPEPAYMENTS_COMMISSION_ENABLED);
    }

    protected function initForm() {
        '';
    }
    protected function getTemplateFile() {
        return WP_PLUGIN_DIR . '/postaffiliatepro/Template/StripePaymentsConfig.xtpl';
    }

    public function addPrimaryConfigMenu() {
        if (get_option(self::STRIPEPAYMENTS_COMMISSION_ENABLED) == 'true') {
            add_submenu_page('integrations-config-page-handle', __('Stripe Payments', 'pap-integrations'), __('Stripe Payments', 'pap-integrations'), 'manage_options', 'stripepayintegration-settings-page', array(
                $this,
                'printConfigPage'
            ));
        }
    }

    public function printConfigPage() {
        $this->render();
        return;
    }

    public function addCookieToStripeCustomer($customer_data) {
        $papCookie = (isset($_POST['papCookie'])) ? $_POST['papCookie'] : 'noCookieRecognized';
        $customer_data['description'] = $papCookie;
        return $customer_data;
    }

    public function addHiddenCookieFieldToCheckout($output, $data) {
        $output .= '<input id="papCookieForStripePayments" type="hidden" name="papCookie" value="">';
        return $output;
    }

    public function addWriteCookieToFieldToFooter($content) {
        if (get_option(self::STRIPEPAYMENTS_COMMISSION_ENABLED) != 'true') {
            return false;
        }
        if (is_feed()) {
            return false;
        }
        $content .= '<script type="text/javascript">
    try {
        PostAffTracker.writeCookieToCustomField("papCookieForStripePayments", "", "", false);
    } catch (err) { }
    </script>';
        echo $content;
    }
}
$submenuPriority = 87;
$integration = new postaffiliatepro_Form_Settings_StripePayments();
add_action('admin_init', array(
    $integration,
    'initSettings'
), 99);
add_action('admin_menu', array(
    $integration,
    'addPrimaryConfigMenu'
), $submenuPriority);

//asp_ng_before_customer_create_update

add_filter('asp_customer_data_before_create', array(
    $integration,
    'addCookieToStripeCustomer'
), 99);
add_filter('asp_button_output_before_custom_field', array(
    $integration,
    'addHiddenCookieFieldToCheckout'
), 99, 2);
add_filter('wp_footer', array(
    $integration,
    'addWriteCookieToFieldToFooter'
), 100);