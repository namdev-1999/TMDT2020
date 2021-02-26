<?php
/**
 *   @copyright Copyright (c) 2017 Quality Unit s.r.o.
 *   @author Martin Pullmann
 *   @package WpPostAffiliateProPlugin
 *   @since version 1.0.0
 *
 *   Licensed under GPL2
 */
class postaffiliatepro_Form_Settings_EDD extends postaffiliatepro_Form_Base {
    const EDD_COMMISSION_ENABLED = 'edd-commission-enabled';
    const EDD_CONFIG_PAGE = 'edd-config-page';
    const EDD_PERPRODUCT = 'edd-per-product';
    const EDD_PRODUCT_ID = 'edd-product-id';
    const EDD_DATA1 = 'edd-data1';
    const EDD_CAMPAIGN = 'edd-campaign';
    const EDD_STATUS_UPDATE = 'edd-status-update';

    public function __construct() {
        parent::__construct(self::EDD_CONFIG_PAGE, 'options.php');
    }

    protected function getTemplateFile() {
        return WP_PLUGIN_DIR . '/postaffiliatepro/Template/EDD.xtpl';
    }

    protected function initForm() {
        $this->addCheckbox(self::EDD_PERPRODUCT);
        $this->addSelect(self::EDD_PRODUCT_ID, array(
                '0' => ' ',
                'id' => 'product ID',
                'name' => 'product name'
        ));
        $this->addSelect(self::EDD_DATA1, array(
                '0' => ' ',
                'id' => 'customer ID',
                'email' => 'customer email'
        ));

        $campaignHelper = new postaffiliatepro_Util_CampaignHelper();
        $campaignList = $campaignHelper->getCampaignsList();

        $campaigns = array('0' => ' ');
        foreach ($campaignList as $row) {
            $campaigns[$row->get('campaignid')] = htmlspecialchars($row->get('name'));
        }
        $this->addSelect(self::EDD_CAMPAIGN, $campaigns);

        $this->addSubmit();
    }

    public function initSettings() {
        register_setting(postaffiliatepro::INTEGRATIONS_SETTINGS_PAGE_NAME, self::EDD_COMMISSION_ENABLED);
        register_setting(self::EDD_CONFIG_PAGE, self::EDD_PERPRODUCT);
        register_setting(self::EDD_CONFIG_PAGE, self::EDD_PRODUCT_ID);
        register_setting(self::EDD_CONFIG_PAGE, self::EDD_DATA1);
        register_setting(self::EDD_CONFIG_PAGE, self::EDD_STATUS_UPDATE);
        register_setting(self::EDD_CONFIG_PAGE, self::EDD_CAMPAIGN);
    }

    public function addPrimaryConfigMenu() {
        if (get_option(self::EDD_COMMISSION_ENABLED) == 'true') {
            add_submenu_page('integrations-config-page-handle', __('Easy Digital Downloads', 'pap-integrations'), __('Easy Digital Downloads', 'pap-integrations'), 'manage_options', 'eddintegration-settings-page', array(
                    $this,
                    'printConfigPage'
            ));
        }
    }

    public function printConfigPage() {
        $this->render();
        return;
    }

    public function eddAddThankYouPageTrackSale($payment, $edd_receipt_args) {
        if (get_option(self::EDD_COMMISSION_ENABLED) != 'true') {
            echo "<!-- Post Affiliate Pro sale tracking error - tracking not enabled -->\n";
            return;
        }

        $payment = new EDD_Payment($payment->ID);
        $cart_details = $payment->cart_details;

        if (is_array($cart_details) && get_option(self::EDD_PERPRODUCT) == 'true') {
            $count = count($cart_details);
            $i = 1;
            foreach ($cart_details as $n => $download) {
                $deleteCookies = false;
                $order['id'] = $payment->ID.'('.($i).')';
                $order['total'] = $download['subtotal'];
                $order['product'] = $this->getProductID($download);
                $order['data1'] = $this->getTrackingData1($payment);
                $order['currency'] = $payment->currency;
                if ($count == $i) {
                    $deleteCookies = true;
                }
                $this->trackOrder($order, $deleteCookies);
                $i++;
            }
        } else {
            // per order
            $order['id'] = $payment->ID.'(1)';
            $order['total'] = $payment->subtotal;
            $order['product'] = '';
            $order['data1'] = $this->getTrackingData1($payment);
            $order['currency'] = $payment->currency;
            $this->trackOrder($order);
        }

        return;
    }

    private function trackOrder($order, $deleteCookie = false) {
        echo "<!-- Post Affiliate Pro sale tracking -->\n";
        echo postaffiliatepro::getPAPTrackJSDynamicCode();
        echo '<script type="text/javascript">';
        echo 'PostAffTracker.setAccountId(\'' . postaffiliatepro::getAccountName() . '\');';
        echo "var sale = PostAffTracker.createSale();\n";
        echo "sale.setTotalCost('" . $order['total'] . "');\n";
        echo "sale.setOrderID('" . $order['id'] . "');\n";
        echo "sale.setProductID('" . $order['product'] . "');\n";
        echo "sale.setData1('" . $order['data1'] . "');\n";
        echo "sale.setCurrency('" . $order['currency'] . "');\n";
        if (!$deleteCookie) {
            "if (typeof sale.doNotDeleteCookies === 'function') {sale.doNotDeleteCookies();}\n";
        }
        if (get_option(self::EDD_CAMPAIGN) !== '' && get_option(self::EDD_CAMPAIGN) !== null && get_option(self::EDD_CAMPAIGN) !== 0 && get_option(self::EDD_CAMPAIGN) !== '0') {
            echo "sale.setCampaignID('" . get_option(self::EDD_CAMPAIGN) . "');\n";
        }
        echo "PostAffTracker.register();\n";
        echo '</script>';
        return true;
    }

    private function getProductID($order) {
        switch (get_option(self::EDD_PRODUCT_ID)) {
            case 'id':
                return $order['id'];
            case 'name':
                return $order['name'];
            default: return '';
        }
    }

    private function getTrackingData1($payment) {
        switch (get_option(self::EDD_DATA1)) {
            case 'id': return $payment->user_id; break;
            case 'email': return $payment->email; break;
            default: return '';
        }
    }
}

$submenuPriority = 95;
$integration = new postaffiliatepro_Form_Settings_EDD();
add_action('admin_init', array(
        $integration,
        'initSettings'
), 99);
add_action('admin_menu', array(
        $integration,
        'addPrimaryConfigMenu'
), $submenuPriority);

add_action('edd_payment_receipt_after_table', array(
        $integration,
        'eddAddThankYouPageTrackSale'
), 99, 2);