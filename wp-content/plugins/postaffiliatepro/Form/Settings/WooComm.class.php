<?php
/**
 *   @copyright Copyright (c) 2016 Quality Unit s.r.o.
 *   @author Martin Pullmann
 *   @package WpPostAffiliateProPlugin
 *   @since version 1.0.0
 *
 *   Licensed under GPL2
 */
class postaffiliatepro_Form_Settings_WooComm extends postaffiliatepro_Form_Base {
    const WOOCOMM_COMMISSION_ENABLED = 'woocomm-commission-enabled';
    const WOOCOMM_CONFIG_PAGE = 'woocomm-config-page';
    const WOOCOMM_PERPRODUCT = 'woocomm-per-product';
    const WOOCOMM_PRODUCT_ID = 'woocomm-product-id';
    const WOOCOMM_DATA1 = 'woocomm-data1';
    const WOOCOMM_DATA2 = 'woocomm-data2';
    const WOOCOMM_DATA3 = 'woocomm-data3';
    const WOOCOMM_DATA4 = 'woocomm-data4';
    const WOOCOMM_DATA5 = 'woocomm-data5';
    const WOOCOMM_CAMPAIGN = 'woocomm-campaign';
    const WOOCOMM_STATUS_UPDATE = 'woocomm-status-update';
    const WOOCOMM_AFFILIATE_APPROVAL = 'woocomm-affiliate-approval';
    const WOOCOMM_TRACK_RECURRING_TOTAL = 'woocomm-track-recurring-total';

    public function __construct() {
        parent::__construct(self::WOOCOMM_CONFIG_PAGE, 'options.php');
    }

    protected function getTemplateFile() {
        return WP_PLUGIN_DIR . '/postaffiliatepro/Template/WooCommConfig.xtpl';
    }

    protected function initForm() {
        $this->addCheckbox(self::WOOCOMM_PERPRODUCT);
        $this->addCheckbox(self::WOOCOMM_TRACK_RECURRING_TOTAL);
        $this->addSelect(self::WOOCOMM_PRODUCT_ID, array(
                '0' => ' ',
                'id' => 'product ID',
                'title' => 'product name',
                'var' => 'variation ID',
                'sku' => 'SKU',
                'categ' => 'product category',
                'role' => 'user role'
        ));
        $this->addCheckbox(self::WOOCOMM_STATUS_UPDATE);

        $productOptions = array(
                '0' => ' ',
                'id' => 'customer ID',
                'email' => 'customer email',
                'name' => 'customer name', // $billing_first_name $billing_last_name
                'pmethod' => 'payment method', // $payment_method_title
                'discount' => 'cart discount', // $cart_discount
                'coupon' => 'coupon code',
                'title' => 'product name',
                'order' => 'order name'

        );
        $this->addSelect(self::WOOCOMM_DATA1, $productOptions);
        $this->addSelect(self::WOOCOMM_DATA2, $productOptions);
        $this->addSelect(self::WOOCOMM_DATA3, $productOptions);
        $this->addSelect(self::WOOCOMM_DATA4, $productOptions);
        $this->addSelect(self::WOOCOMM_DATA5, $productOptions);
        $this->addTextBox(self::WOOCOMM_AFFILIATE_APPROVAL);

        $campaignHelper = new postaffiliatepro_Util_CampaignHelper();
        $campaignList = $campaignHelper->getCampaignsList();

        $campaigns = array(
                '0' => ' '
        );
        foreach ($campaignList as $row) {
            $campaigns[$row->get('campaignid')] = htmlspecialchars($row->get('name'));
        }
        $this->addSelect(self::WOOCOMM_CAMPAIGN, $campaigns);

        $this->addSubmit();
    }

    public function initSettings() {
        register_setting(postaffiliatepro::INTEGRATIONS_SETTINGS_PAGE_NAME, self::WOOCOMM_COMMISSION_ENABLED);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_PERPRODUCT);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_PRODUCT_ID);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_STATUS_UPDATE);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_DATA1);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_DATA2);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_DATA3);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_DATA4);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_DATA5);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_CAMPAIGN);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_AFFILIATE_APPROVAL);
        register_setting(self::WOOCOMM_CONFIG_PAGE, self::WOOCOMM_TRACK_RECURRING_TOTAL);
    }

    public function addPrimaryConfigMenu() {
        if (get_option(self::WOOCOMM_COMMISSION_ENABLED) == 'true') {
            add_submenu_page('integrations-config-page-handle', __('WooCommerce', 'pap-integrations'), __('WooCommerce', 'pap-integrations'), 'manage_options', 'woocommintegration-settings-page', array(
                    $this,
                    'printConfigPage'
            ));
        }
    }

    public function printConfigPage() {
        $this->render();
        return;
    }

    public function YITHToFooter($content) {
        if (isset($_GET['ctpw']) && ($_GET['ctpw'] != '') &&
                isset($_GET['key']) && ($_GET['key'] != '') &&
                isset($_GET['order']) && ($_GET['order'] != '') ) {
            // track sale
            $this->wooAddThankYouPageTrackSale($_GET['order']);
        }
        return $content;
    }

    public function wooAddThankYouPageTrackSale($order_id) {
        $order = wc_get_order($order_id);
        if (get_option(self::WOOCOMM_COMMISSION_ENABLED) != 'true') {
            echo "<!-- Post Affiliate Pro sale tracking error - tracking not enabled -->\n";
            return $order_id;
        }
        if (empty($order)) {
            echo '<!-- Post Affiliate Pro sale tracking error - no order loaded for order ID ' . $order_id . " -->\n";
            return $order_id;
        }
        if (isset($_GET['customGateway'])) {
            if (empty($_REQUEST['cm']) || empty($_REQUEST['tx']) || empty($_REQUEST['st'])) {
                // SKIP THANK YOU PAGE, SALE WILL BE TRACKED FROM PAYPAL IPN
                echo "<!-- Post Affiliate Pro sale tracking - no sale tracker needed -->\n";
                return $order_id;
            } else {
                // THIS IS A PDT RESPONSE, NO IPN WILL BE USED/VALIDATED/TRIGGERED, CONTINUE TO THANK YOU PAGE
            }
        }
        $this->trackWooOrder($order);

        return $order_id;
    }

    private function trackWooOrder($order) {
        $orderId = $order->get_id();
        echo "<!-- Post Affiliate Pro sale tracking -->\n";
        echo postaffiliatepro::getPAPTrackJSDynamicCode();
        echo '<script type="text/javascript">';
        echo 'PostAffTracker.setAccountId(\'' . postaffiliatepro::getAccountName() . '\');';

        if (function_exists('wcs_get_subscriptions_for_order')) {
            $subscriptions = wcs_get_subscriptions_for_order($orderId);
            if (!empty($subscriptions)) {
                foreach ($subscriptions as $key => $value) { // take the first and leave
                    $orderId = $key;
                    break;
                }
            }
        }

        $status = '';
        if (get_option(self::WOOCOMM_STATUS_UPDATE) === 'true') {
        	$orderStatus = $order->get_status();
        	switch ($orderStatus) {
        		case 'completed': $status = 'A'; break;
        		case 'cancelled': 
        		case 'refunded':
        		case 'failed': $status = 'D'; break;
        		default: $status = 'P';
        	}
        }
        if ($status == 'D') {
        	$this->_log('The order was not successful, no commissions will be created.');
        	return false;
        }

        try {
            if (method_exists($order, 'get_coupon_codes')) { // for newer versions
                $coupons = $order->get_coupon_codes();
            } else {
                $coupons = $order->get_used_coupons();
            }
            $couponCode = implode(',', $coupons);
        } catch (Exception $e) {
            //
        }

        if (get_option(self::WOOCOMM_PERPRODUCT) === 'true') {
            $i = 1;
            $count = count($order->get_items());
            foreach ($order->get_items() as $item) {
                $itemprice = $item['line_total'];

                echo "var sale$i = PostAffTracker.createSale();\n";
                echo "sale$i.setTotalCost('" . $itemprice . "');\n";
                echo "sale$i.setOrderID('$orderId($i)');\n";
                echo "sale$i.setProductID('" . str_replace("'","\'",$this->getTrackingProductID($order, $item)) . "');\n";
                echo "sale$i.setCurrency('" . $order->get_currency() . "');\n";
                echo "sale$i.setCoupon('" . $couponCode . "');\n";

                for ($d = 1; $d <=5; $d++) {
                    echo "sale$i.setData$d('" . str_replace("'","\'",$this->getTrackingData($order, $d, $item, $couponCode)) . "');\n";
                }
                if (get_option(self::WOOCOMM_CAMPAIGN) !== '' && get_option(self::WOOCOMM_CAMPAIGN) !== null && get_option(self::WOOCOMM_CAMPAIGN) !== 0 && get_option(self::WOOCOMM_CAMPAIGN) !== '0') {
                    echo "sale$i.setCampaignID('" . get_option(self::WOOCOMM_CAMPAIGN) . "');\n";
                }
                if ($status != '') {
                    echo "sale$i.setStatus('" . $status . "');\n";
                }

                if ($i != $count) { // delete cookie after sale fix
                    echo "if (typeof sale$i.doNotDeleteCookies=== 'function') {sale$i.doNotDeleteCookies();}
                    PostAffTracker.register();";
                } else {
                    echo "if (typeof PostAffTracker.registerOnAllFinished === 'function') {
                            PostAffTracker.registerOnAllFinished();
                        } else {
                            PostAffTracker.register();
                        }";
                }
                $i++;
            }
        } else {
            echo "var sale = PostAffTracker.createSale();\n";
            echo "sale.setTotalCost('" . ($order->get_total() - $order->get_total_tax() - $order->get_shipping_total()) . "');\n";
            echo "sale.setOrderID('$orderId(1)');\n";
            echo "sale.setCurrency('" . $order->get_currency() . "');\n";
            echo "sale.setProductID('" . str_replace("'","\'",$this->getTrackingProductIDsLine($order)) . "');\n";
            for ($d = 1; $d <=5; $d++) {
                echo "sale.setData$d('" . str_replace("'","\'",$this->getTrackingData($order, $d, null, $couponCode)) . "');\n";
            }
            echo "sale.setCoupon('" . $couponCode . "');\n";

            if (get_option(self::WOOCOMM_CAMPAIGN) !== '' && get_option(self::WOOCOMM_CAMPAIGN) !== null && get_option(self::WOOCOMM_CAMPAIGN) !== 0 && get_option(self::WOOCOMM_CAMPAIGN) !== '0') {
                echo "sale.setCampaignID('" . get_option(self::WOOCOMM_CAMPAIGN) . "');\n";
            }
            if ($status != '') {
                echo "sale.setStatus('" . $status . "');\n";
            }

            echo 'PostAffTracker.register();';
        }
        echo '</script>';

        // affiliate approval?
        if (get_option(self::WOOCOMM_AFFILIATE_APPROVAL) != '') {
            $approvalProducts = explode(';', get_option(self::WOOCOMM_AFFILIATE_APPROVAL));
            $orderedProducts = explode(', ', $this->getTrackingProductIDsLine($order));
            foreach ($orderedProducts as $item) {
                if (in_array($item, $approvalProducts)) {
                    // approve the customer/affiliate
                    $this->changeAffiliateStatus($order->get_billing_email(), 'A');
                    break;
                }
            }
        }

        return true;
    }

    private function trackWooOrderRemote($order, $affiliateId = null) {
        if (get_option(self::WOOCOMM_PERPRODUCT) === 'true') {
            $i = 1;
            $count = count($order->get_items());
            
            $status = '';
            if (get_option(self::WOOCOMM_STATUS_UPDATE) === 'true') {
            	$orderStatus = $order->get_status();
            	switch ($orderStatus) {
            		case 'completed': $status = 'A'; break;
            		case 'cancelled':
            		case 'refunded':
            		case 'failed': $status = 'D'; break;
            		default: $status = 'P';
            	}
            }
	        if ($status == 'D') {
	        	$this->_log('The order was not successful, no commissions will be created.');
	        	return false;
	        }
            
            foreach ($order->get_items() as $item) {
                $itemprice = $item['line_total'];
                $couponCode = '';

                try { //if coupon has been used, set the last one in the setCoupon() parameter
                    if (method_exists($order, 'get_coupon_codes')) { // for newer versions
                        $coupons = $order->get_coupon_codes();
                    } else {
                        $coupons = $order->get_used_coupons();
                    }

                    $couponCode = implode(',', $coupons);
                } catch (Exception $e) {
                    //echo "<!--Error: ".$e->getMessage()."-->";
                }

                $query = "TotalCost=$itemprice&OrderID=" . $order->get_id() . "($i)";
                $query .= '&ProductID=' . urlencode($this->getTrackingProductID($order, $item));
                $query .= '&Currency=' . $order->get_currency() . "&Coupon=$couponCode";

                for ($d = 1; $d <=5; $d++) {
                    $query .= '&Data$d=' . urlencode($this->getTrackingData($order, $d, $item, $couponCode));
                }

                if (get_option(self::WOOCOMM_CAMPAIGN) !== '' && get_option(self::WOOCOMM_CAMPAIGN) !== null && get_option(self::WOOCOMM_CAMPAIGN) !== 0 && get_option(self::WOOCOMM_CAMPAIGN) !== '0') {
                    $query .= '&CampaignID=' . get_option(self::WOOCOMM_CAMPAIGN);
                }

                if ($affiliateId != null) {
                    $query .= "&AffiliateID=$affiliateId";
                }

                if ($i != $count) { // delete cookie after sale fix
                    $query .= '&DoNotDeleteCookies=Y';
                }
                self::sendRequest(postaffiliatepro::parseSaleScriptPath(), $query);
                $i++;
            }
        } else {
            if (method_exists($order, 'get_coupon_codes')) { // for newer versions
                $coupons = $order->get_coupon_codes();
            } else {
                $coupons = $order->get_used_coupons();
            }
            $couponCode = implode(',', $coupons);
            $query = '&TotalCost=' . ($order->get_total() - $order->get_total_tax() - $order->get_shipping_total()) . '&OrderID=' . $order->get_id() . '(1)';
            $query .= '&ProductID=' . urlencode($this->getTrackingProductIDsLine($order));
            $query .= '&Currency=' . $order->get_currency() . '&Coupon=' . $couponCode;

            for ($d = 1; $d <=5; $d++) {
                $query .= "&Data$d=" . urlencode($this->getTrackingData($order, $d, null, $couponCode));
            }

            if (get_option(self::WOOCOMM_CAMPAIGN) !== '' && get_option(self::WOOCOMM_CAMPAIGN) !== null && get_option(self::WOOCOMM_CAMPAIGN) !== 0 && get_option(self::WOOCOMM_CAMPAIGN) !== '0') {
                $query .= '&CampaignID=' . get_option(self::WOOCOMM_CAMPAIGN);
            }

            if ($affiliateId != null) {
                $query .= "&AffiliateID=$affiliateId";
            }

            self::sendRequest(postaffiliatepro::parseSaleScriptPath(), $query);
        }
    }

    private function getTrackingProductID($order, $item) {
        $product = $item->get_product();

        switch (get_option(self::WOOCOMM_PRODUCT_ID)) {
            case 'id':
                return $product->get_id();
            case 'sku':
                if (!empty($product->get_sku())) {
                    return $product->get_sku();
                } else {
                    return $product->get_id();
                }
            case 'var':
                if ($product->is_type('variation')) {
                    return $product->get_variation_id();
                } else {
                    return $product->get_id();
                }
            case 'categ':
                $categories = explode(',', wc_get_product_category_list($product->get_id(),','));
                return strip_tags($categories[0]);
            case 'role':
                try {
                    $user = new WP_User($order->get_user_id());
                    if (isset($user->roles[0])) {
                        return $user->roles[0];
                    } else {
                        break;
                    }
                } catch (Exception $e) {
                    break;
                }
            case 'title':
                return get_the_title($product->get_id());
        }
        return '';
    }

    private function getTrackingProductIDsLine($order) {
        $productSelection = get_option(self::WOOCOMM_PRODUCT_ID);
        if (empty($productSelection)) {
            return '';
        }

        $line = '';
        foreach ($order->get_items() as $item) {
            $line .= $this->getTrackingProductID($order, $item) . ', ';
        }
        if (!empty($line)) {
            $line = substr($line, 0, -2);
        }
        return $line;
    }

    private function getTrackingData($order, $n, $item = null, $coupon = '') {
        $product = null;
        if ($item != null) {
            $product = $item->get_product();
        }
        $data = get_option(constant('self::WOOCOMM_DATA'.$n));
        switch ($data) {
            case 'id':
                return $order->get_user_id();
                break;
            case 'email':
                return $order->get_billing_email();
                break;
            case 'name':
                return $order->get_billing_first_name().' '.$order->get_billing_last_name();
                break;
            case 'pmethod':
                return $order->get_payment_method_title();
                break;
            case 'discount':
                return $order->get_total_discount();
                break;
            case 'coupon':
                return $coupon;
                break;
            case 'title':
                if ($product != null) {
                    return get_the_title($product->get_id());
                } else {
                    return '';
                }
            case 'order':
                return '#'.$order->get_order_number();
                break;
            default: return '';
        }
    }

    public function wooOrderStatusChanged($orderId, $old_status, $new_status) {
        if (get_option(self::WOOCOMM_STATUS_UPDATE) !== 'true') {
            return false;
        }

        $this->_log('Received status: ' . $new_status);

        switch ($new_status) {
            case 'completed':
                $status = 'A';
                break;
            case 'processing':
            case 'on-hold':
                $status = 'P';
                break;
            case 'cancelled':
            case 'failed':
                $status = 'D';
                break;
            case 'refunded':
                return $this->refundTransaction($orderId);
                break;
            default:
                $status = '';
        }

        if ($status == '') {
            $this->_log('Unsupported status ' . $new_status);
            return false;
        }

        if (function_exists('wcs_get_subscriptions_for_order')) {
            $subscriptions = wcs_get_subscriptions_for_order($orderId);
            if (!empty($subscriptions)) {
                $this->_log('This is a subscription');
                foreach ($subscriptions as $key => $value) { // take the first and leave
                    $orderId = $key;
                    break;
                }
            }
        }

        return $this->changeOrderStatus($orderId, $status);
    }

    private function refundTransaction($orderId) {
        $limit = 100;
        if (function_exists('wcs_get_subscriptions_for_order')) { // we will have to refund one of the recurring commissions
            $subscriptions = wcs_get_subscriptions_for_order($orderId);
            if (!empty($subscriptions)) {
                foreach ($subscriptions as $key => $value) { // take the first and leave
                    $orderId = $key;
                    $limit = 1;
                    break;
                }
            }
        }

        $session = $this->getApiSession();
        if ($session === null || $session === '0') {
            $this->_log(__('We have no session to PAP installation! Transaction status change failed.'));
            return false;
        }
        $ids = $this->getTransactionIDsByOrderID($orderId, $session, 'A,P', $limit);
        if (empty($ids)) {
            $this->_log(__('Nothing to change, the commission does not exist in PAP'));
            return true;
        }

        $request = new Gpf_Rpc_FormRequest('Pap_Merchants_Transaction_TransactionsForm', 'makeRefundChargeback', $session);
        $request->addParam('ids', new Gpf_Rpc_Array($ids));
        $request->addParam('status', 'R');
        $request->addParam('merchant_note', 'Refunded automatically from WooCommerce');
        $request->addParam('refund_multitier', 'Y');
        try {
            $request->sendNow();
        } catch (Exception $e) {
            $this->_log(__('A problem occurred while transaction status change with API: ') . $e->getMessage());
            return false;
        }

        return true;
    }

    public function wooSubscriptionStatusChanged($orderId, $old_status, $new_status) {
        if ($new_status != 'cancelled') {
            return false;
        }
        $session = $this->getApiSession();
        if ($session === null || $session === '0') {
            $this->_log(__('We have no session to PAP installation! Transaction status change failed.'));
            return;
        }
        // load recurring order ID
        $request = new Gpf_Rpc_GridRequest('Pap_Features_RecurringCommissions_RecurringCommissionsGrid', 'getRows', $session);
        $request->addFilter('orderid', 'L', $orderId . '(%');
        $recurringIds = array();
        try {
            $request->sendNow();
            $grid = $request->getGrid();
            $recordset = $grid->getRecordset();
            foreach ($recordset as $rec) {
                $recurringIds[] = $rec->get('orderid');
            }
        } catch (Exception $e) {
            $this->_log(__('A problem occurred while loading recurring commissions: ') . $e->getMessage());
            return false;
        }

        if (empty($recurringIds)) {
            $this->_log(__('Nothing to change, the commission does not exist in PAP'));
            return false;
        }

        $request = new Gpf_Rpc_FormRequest('Pap_Features_RecurringCommissions_RecurringCommissionsForm', 'changeStatus', $session);
        $request->addParam('ids', new Gpf_Rpc_Array($recurringIds));
        $request->addParam('status', 'D');
        try {
            $request->sendNow();
        } catch (Exception $e) {
            $this->_log(__('A problem occurred while transaction status change with API: ') . $e->getMessage());
            return false;
        }

        return true;
    }

    public function wooRecurringCommission($renewal_order, $subscription) {
        if (!is_object($subscription)) {
            $subscription = wcs_get_subscription($subscription);
        }

        if (!is_object($renewal_order)) {
            $renewal_order = wc_get_order($renewal_order);
        }

        // try to recurr a commission with order ID $subscription->id
        $session = $this->getApiSession();
        if ($session === null || $session === '0') {
            $this->_log(__('We have no session to PAP installation! Recurring commission failed.'));
            return $renewal_order;
        }

        $recurringSubtotal = false;
        if (get_option(self::WOOCOMM_TRACK_RECURRING_TOTAL) === 'true') {
            $recurringSubtotal = $renewal_order->get_total() - $renewal_order->get_total_tax() - $renewal_order->get_shipping_total();
        }
        if (!$this->fireRecurringCommissions($subscription->id . '(1)', $recurringSubtotal, $session)) {
            // creating recurring commissions failed, create a new commission instead
            $this->_log(__('Creating new commissions with order ID ') . $renewal_order->id . '(1)');
            $this->trackWooOrderRemote($renewal_order);
        }

        return $renewal_order;
    }

    public function wooAutoshipCommission($order_id, $schedule_id) {
        // can work only with lifetime relations, no way to use recurring commissions
        $renewal_order = wc_get_order($order_id);
        $this->_log(__('Creating new commissions with order ID ') . $renewal_order->id . '(1)');
        $this->trackWooOrderRemote($renewal_order);
        return $renewal_order;
    }

    public function wooModifyPaypalArgs($array) {
        if (strpos($array['notify_url'], '?')) {
            $array['notify_url'] .= '&';
        } else {
            $array['notify_url'] .= '?';
        }
        $array['notify_url'] .= 'pap_custom=' . $_REQUEST['pap_custom'];
        if (isset($_REQUEST['pap_IP'])) {
            $array['notify_url'] .= '&pap_IP=' . $_REQUEST['pap_IP'];
        }
        if (strpos($array['return'], '?')) {
            $array['return'] .= '&';
        } else {
            $array['return'] .= '?';
        }
        $array['return'] .= 'customGateway=paypal';
        return $array;
    }

    public function wooProcessPaypalIPN($post_data) {
        $this->_log('PayPal IPN received: '.print_r($post_data,true));
        $post_data['payment_status'] = strtolower($post_data['payment_status']);
        if (empty($post_data['custom'])) {
            $this->_log('PayPal IPN received but didn\'t find anything in custom, expected WooCommerce order details, stopping.');
            return false;
        }
        if (!$order = $this->get_paypal_order($post_data['custom'])) {
            $this->_log('PayPal IPN received but couldn\'t load the WooCommerce order for the IPN, stopping. Content of custom was '.$post_data['custom']);
            return false;
        }

        if ($post_data['payment_status'] === 'completed') {
            if (get_option(self::WOOCOMM_PERPRODUCT) === 'true') {
                $i = 1;
                $count = count($order->get_items());
                foreach ($order->get_items() as $item) {
                    $itemprice = $item['line_total'];
                    $couponCode = '';

                    try { //if coupon has been used, set the last one in the setCoupon() parameter
                        if (method_exists($order, 'get_coupon_codes')) { // for newer versions
                            $coupons = $order->get_coupon_codes();
                        } else {
                            $coupons = $order->get_used_coupons();
                        }
                        $couponCode = $this->resolveCoupons($coupons);
                    } catch (Exception $e) {
                        //echo "<!--Error: ".$e->getMessage()."-->";
                    }

                    $query = 'AccountId=' . substr($_GET['pap_custom'], 0, 8) . '&visitorId=' . substr($_GET['pap_custom'], -32);
                    if (isset($_GET['pap_IP'])) {
                        $query .= '&ip=' . $_GET['pap_IP'];
                    }
                    $query .= "&TotalCost=$itemprice&OrderID=" . $order->get_id() . "($i)";
                    $query .= '&ProductID=' . urlencode($this->getTrackingProductID($order, $item));
                    $query .= '&Currency=' . $order->get_currency() . "&Coupon=$couponCode";

                    for ($d = 1; $d <= 5; $d++) {
                        $query .= "&Data$d=" . urlencode($this->getTrackingData($order, $d, $item, $couponCode));
                    }

                    if (get_option(self::WOOCOMM_CAMPAIGN) !== '' && get_option(self::WOOCOMM_CAMPAIGN) !== null && get_option(self::WOOCOMM_CAMPAIGN) !== 0 && get_option(self::WOOCOMM_CAMPAIGN) !== '0') {
                        $query .= '&CampaignID=' . get_option(self::WOOCOMM_CAMPAIGN);
                    }

                    if ($i != $count) { // delete cookie after sale fix
                        $query .= '&DoNotDeleteCookies=Y';
                    }
                    $this->_log('PayPal sending tracking request to PAP for '.$query);
                    self::sendRequest(postaffiliatepro::parseSaleScriptPath(), $query);
                    $i++;
                }
            } else {
                if (method_exists($order, 'get_coupon_codes')) { // for newer versions
                    $coupons = $order->get_coupon_codes();
                } else {
                    $coupons = $order->get_used_coupons();
                }
                $couponCode = implode(',', $coupons);
                $query = 'AccountId=' . substr($_GET['pap_custom'], 0, 8) . '&visitorId=' . substr($_GET['pap_custom'], -32);
                if (isset($_GET['pap_IP'])) {
                    $query .= '&ip=' . $_GET['pap_IP'];
                }
                $query .= '&TotalCost=' . ($order->get_total() - $order->get_total_tax() - $order->get_shipping_total()) . '&OrderID=' . $order->get_id() . '(1)';
                $query .= '&ProductID=' . urlencode($this->getTrackingProductIDsLine($order));
                $query .= '&Currency=' . $order->get_currency() . '&Coupon=' . $couponCode;

                for ($d = 1; $d <= 5; $d++) {
                    $query .= "&Data$d=" . urlencode($this->getTrackingData($order, $d, null, $couponCode));
                }

                if (get_option(self::WOOCOMM_CAMPAIGN) !== '' && get_option(self::WOOCOMM_CAMPAIGN) !== null && get_option(self::WOOCOMM_CAMPAIGN) !== 0 && get_option(self::WOOCOMM_CAMPAIGN) !== '0') {
                    $query .= '&CampaignID=' . get_option(self::WOOCOMM_CAMPAIGN);
                }
                $this->_log('PayPal sending tracking request to PAP for '.$query);
                self::sendRequest(postaffiliatepro::parseSaleScriptPath(), $query);
            }

            // affiliate approval?
            if (get_option(self::WOOCOMM_AFFILIATE_APPROVAL) != '') {
                $approvalProducts = explode(';', get_option(self::WOOCOMM_AFFILIATE_APPROVAL));
                $orderedProducts = explode(', ', $this->getTrackingProductIDsLine($order));
                foreach ($orderedProducts as $item) {
                    if (in_array($item, $approvalProducts)) {
                        // approve the customer/affiliate
                        $this->changeAffiliateStatus($order->get_billing_email(), 'A');
                        break;
                    }
                }
            }

            return true;
        }
        return false;
    }

    private function get_paypal_order($raw_custom) {
        if (($custom = json_decode($raw_custom)) && is_object($custom)) {
            $order_id = $custom->order_id;
            $order_key = $custom->order_key;
        } elseif (preg_match('/^a:2:{/', $raw_custom) && !preg_match('/[CO]:\+?[0-9]+:"/', $raw_custom) && ($custom = maybe_unserialize($raw_custom))) {
            $order_id = $custom[0];
            $order_key = $custom[1];
        } else {
            $this->_log('PayPal IPN handling: Order ID and key were not found in "custom".');
            return false;
        }

        if (!$order = wc_get_order($order_id)) {
            // We have an invalid $order_id, probably because invoice_prefix has changed.
            $order_id = wc_get_order_id_by_order_key($order_key);
            $order = wc_get_order($order_id);
        }

        if (!$order || $order->get_order_key() !== $order_key) {
            $this->_log('PayPal IPN handling: Order keys do not match.');
            return false;
        }

        return $order;
    }

    private function resolveCoupons(array $coupons) {
        $version = get_option(postaffiliatepro::PAP_VERSION);
        $versionArray = explode('.', $version);
        $minVersion = array(0 => '5', 1 => '9', 2 => '8', 3 => '8');
        $problem = false;

        if ($version != '') {
            if ($versionArray[0] < $minVersion[0]) {
                $problem = true;
            } elseif ($versionArray[0] == $minVersion[0]) {
                if ($versionArray[1] < $minVersion[1]) {
                    $problem = true;
                } elseif ($versionArray[1] == $minVersion[1]) {
                    if ($versionArray[2] < $minVersion[2]) {
                        $problem = true;
                    } elseif ($versionArray[2] == $minVersion[2]) {
                        if ($versionArray[3] < $minVersion[3]) {
                            $problem = true;
                        }
                    }
                }
            }
        } else {
            $problem = true;
        }

        if ($problem) {
            $couponToBeUsed = (count($coupons) > 1 ? count($coupons) - 1 : 0);
            return $coupons[$couponToBeUsed];
        }
        return implode(',', $coupons);
    }

    public function addHiddenFieldToPaymentForm($return = false) {
        postaffiliatepro::addHiddenFieldToPaymentForm($return);
        postaffiliatepro::addHiddenFieldToRegistrationForm(); // to support parent affiliate for signup
        return;
    }

    public function addRecomputionMetaBox($post_type, $post) {
        if ($post_type != 'shop_order') return;

        $papwc = new postaffiliatepro_Form_Settings_WooComm();
        add_meta_box('wc-pap-recompute-commissions',
            'Post Affiliate Pro',
            array(
                $papwc,
                'papRecomputeCommissions'
            ),
            'shop_order',
            'side',
            'high'
        );

        wc_enqueue_js(
            "$('#pap-recompute').on('click', function() {
                $('#papRecomputionNote.p').hide();
                $('#pap-recompute').val('...working');
                papRecomputeCommissions($('#pap-recompute-orderid').val());
        	});

            function papRecomputeCommissions(orderid) {
                var d = {
                	action: 'pap_recompute_ajax',
                	order_id: orderid
                };

                $.post(ajaxurl, d, function(response) {
                	if (response.success === true) {
                        $('#pap-recompute').css('background-color','#DDFFAA');
                        $('#pap-recompute').css('color','#44AA55');
                        $('#pap-recompute').css('border-color','#AAFF44');
                        $('#pap-recompute').val('Done');
                	} else {
                		$('#pap-recompute').val('Error');
                        $('#papRecomputionNote').html(response.data.error);
                        $('#papRecomputionNote').show();
                	}
                });
            }"
        );
    }

    public function papRecomputeCommissions(WP_Post $post) {
    	echo '
    	    <div id="papRecomputionContent">
    	        <div id="papRecomputionText">
    	           If number of products or their cost or order cost changed, you can recompute commission for the order. Clicking the button will load, decline/refund and re-create the commission.
    	        </div>
    	        <div class="note_content" id="papRecomputionNote">
			    </div>
    	            <input type="hidden" id="pap-recompute-orderid" name="pap-recompute-orderid" value="'.$post->ID.'" />
                    <input id="pap-recompute" type="button" class="button" value="Recompute Commission" />
            </div>';
    }

    public function recomputeCommissionAjaxCallback() {
        try {
            if (!current_user_can('edit_shop_orders')) {
                throw new Exception(__('You do not have enough permissions to manupulate orders!'));
            }

            if (!isset($_POST['order_id']) || ($_POST['order_id'] == '')) {
                throw new Exception(__('Order ID is missing in the request. Something is wrong! Contact the plugin developer.'));
            }
            $orderId = $_POST['order_id'];

            // try to load commission
            $session = $this->getApiSession();
            if ($session === null || $session === '0') {
                throw new Exception(__('We have no session to PAP installation! Recompution is not possible right now.'));
            }
            $ids = $this->getTransactionIDsByOrderID($orderId, $session, 'A,P', $limit);
            if (empty($ids)) {
                throw new Exception(__('There is no approved or pending commission for this order. Nothing to recompute.'));
            }

            $commissionRecordset = $this->loadTransactionsByOrderID($orderId, $session, 'A,P');

            $affiliateId = null;
            foreach ($commissionRecordset as $row) {
                $affiliateId = $row->get('userid');
                break;
            }
            if ($affiliateId == null) {
                throw new Exception(__('Could not load affiliate info from commission.'));
            }

            // refund and track again
            if ($this->refundTransaction($orderId)) {
                $this->trackWooOrderRemote(wc_get_order($orderId), $affiliateId);
            } else {
                throw new Exception('Refunding was not successful!');
            }

            wp_send_json_success();
        } catch (Exception $e) {
            wp_send_json_error(array('error' => $e->getMessage()));
        }
        wp_die();
    }
}

$submenuPriority = 95;
$integration = new postaffiliatepro_Form_Settings_WooComm();
add_action('admin_init', array(
        $integration,
        'initSettings'
), 99);
add_action('admin_menu', array(
        $integration,
        'addPrimaryConfigMenu'
), $submenuPriority);
add_filter('wp_footer', array(
        $integration,
        'YITHToFooter'
), 99);
add_action('woocommerce_thankyou', array(
        $integration,
        'wooAddThankYouPageTrackSale'
));
add_action('woocommerce_checkout_before_order_review', array(
        $integration,
        'addHiddenFieldToPaymentForm'
));
add_action('woocommerce_order_status_changed', array(
        $integration,
        'wooOrderStatusChanged'
), 99, 3);
add_action('woocommerce_subscription_status_changed', array(
        $integration,
        'wooSubscriptionStatusChanged'
), 99, 3);
add_filter('wcs_renewal_order_created', array(
        $integration,
        'wooRecurringCommission'
), 99, 2);
add_filter('wc_autoship_payment_complete', array(
        $integration,
        'wooAutoshipCommission'
), 99, 2);
add_action('add_meta_boxes', array(
        $integration,
        'addRecomputionMetaBox'
), 99, 2);
// WooCommerce PayPal
add_filter('woocommerce_paypal_args', array(
        $integration,
        'wooModifyPaypalArgs'
), 99);
add_action('valid-paypal-standard-ipn-request', array(
        $integration,
        'wooProcessPaypalIPN'
));
// AJAX
add_action('wp_ajax_pap_recompute_ajax', array(
        $integration,
        'recomputeCommissionAjaxCallback'
), 99);