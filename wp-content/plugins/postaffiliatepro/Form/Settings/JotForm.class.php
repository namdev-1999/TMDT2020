<?php
/**
 *   @copyright Copyright (c) 2016 Quality Unit s.r.o.
 *   @author Martin Pullmann
 *   @package WpPostAffiliateProPlugin
 *   @since version 1.0.0
 *
 *   Licensed under GPL2
 */

class postaffiliatepro_Form_Settings_JotForm extends postaffiliatepro_Form_Base {
    const JOTFORM_COMMISSION_ENABLED = 'jotform-commission-enabled';
    const JOTFORM_CONFIG_PAGE = 'jotform-config-page';
    const JOTFORM_TOTAL_COST = 'jotform-total-cost';
    const JOTFORM_COMMISSION_CAMPAIGN = 'jotform-commission-campaign';
    const JOTFORM_PRODUCTID = 'jotform-product-id';
    const JOTFORM_DATA1 = 'jotform-data-1';
    const JOTFORM_DATA2 = 'jotform-data-2';
    const JOTFORM_DATA3 = 'jotform-data-3';
    const JOTFORM_DATA4 = 'jotform-data-4';
    const JOTFORM_DATA5 = 'jotform-data-5';

    public function __construct() {
        parent::__construct(self::JOTFORM_CONFIG_PAGE, 'options.php');
    }

    protected function getTemplateFile() {
        return WP_PLUGIN_DIR . '/postaffiliatepro/Template/JotFormConfig.xtpl';
    }

    protected function initForm() {
        $this->addTextBox(self::JOTFORM_TOTAL_COST);
        $this->addTextBox(self::JOTFORM_COMMISSION_CAMPAIGN);
        $this->addTextBox(self::JOTFORM_PRODUCTID);
        $this->addTextBox(self::JOTFORM_DATA1);
        $this->addTextBox(self::JOTFORM_DATA2);
        $this->addTextBox(self::JOTFORM_DATA3);
        $this->addTextBox(self::JOTFORM_DATA4);
        $this->addTextBox(self::JOTFORM_DATA5);
        $this->addSubmit();
    }

    public function initSettings() {
        register_setting(postaffiliatepro::INTEGRATIONS_SETTINGS_PAGE_NAME, self::JOTFORM_COMMISSION_ENABLED);
        register_setting(self::JOTFORM_CONFIG_PAGE, self::JOTFORM_TOTAL_COST);
        register_setting(self::JOTFORM_CONFIG_PAGE, self::JOTFORM_COMMISSION_CAMPAIGN);
        register_setting(self::JOTFORM_CONFIG_PAGE, self::JOTFORM_PRODUCTID);
        register_setting(self::JOTFORM_CONFIG_PAGE, self::JOTFORM_DATA1);
        register_setting(self::JOTFORM_CONFIG_PAGE, self::JOTFORM_DATA2);
        register_setting(self::JOTFORM_CONFIG_PAGE, self::JOTFORM_DATA3);
        register_setting(self::JOTFORM_CONFIG_PAGE, self::JOTFORM_DATA4);
        register_setting(self::JOTFORM_CONFIG_PAGE, self::JOTFORM_DATA5);
    }

    public function addPrimaryConfigMenu() {
        if (get_option(self::JOTFORM_COMMISSION_ENABLED) == 'true') {
            add_submenu_page(
                'integrations-config-page-handle',
                __('JotForm','pap-integrations'),
                __('JotForm','pap-integrations'),
                'manage_options',
                'jotform-settings-page',
                array($this, 'printConfigPage')
            );
        }
    }

    public function printConfigPage() {
        $this->render();
        return;
    }
}

$submenuPriority = 60;
$integration = new postaffiliatepro_Form_Settings_JotForm();
add_action('admin_init', array($integration, 'initSettings'), 99);
add_action('admin_menu', array($integration, 'addPrimaryConfigMenu'), $submenuPriority);
