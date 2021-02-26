<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package WpPostAffiliateProPlugin
 *   @since version 1.0.0
 *
 *   Licensed under GPL2
 */

class Shortcode_Affiliate extends postaffiliatepro_Base {
    const SHORTCODES_SETTINGS_PAGE_NAME = 'shortcodes-settings-page';
    const AFFILAITE_SHORTCODE_CACHE = 'affiliate-shortcode_cache';
    /**
     *
     * @var Shortcode_Cache
     */
    private static $cache = null;

    public function __construct() {
        if (self::$cache === null) {
            self::$cache = new Shortcode_Cache();
        }
    }

    public function getAffiliateShortCode($attr, $content = null) {
        return $this->getCode($attr, $content);
    }

    public function getParentAffiliateShortCode($attr, $content = null) {
        return $this->getCode($attr, $content, true);
    }

    /**
     * @return Pap_Api_Affiliate
     */
    private function loadAffiliate(Gpf_Api_Session $session, $parent = false) {
        global $current_user;
        $affiliate = new Pap_Api_Affiliate($session);
        $affiliate->setRefid($current_user->user_nicename, Pap_Api_Affiliate::OPERATOR_EQUALS);
        try {
            $affiliate->load();
        } catch (Exception $e) {
            // try it with notification email as well
            $this->_log(__('Unable to load affiliate').' '.__('by referral ID'));
            try {
                $affiliate->setRefid('');
                $affiliate->setNotificationEmail($current_user->user_email);
                $affiliate->load();
            } catch (Exception $e) {
                // last try - username
                $this->_log(__('Unable to load affiliate').' '.__('by notification email'));
                try {
                    $affiliate->setNotificationEmail('');
                    $affiliate->setUsername($current_user->user_email, Pap_Api_Affiliate::OPERATOR_EQUALS);
                    $affiliate->load();
                } catch (Exception $e) {
                    $this->_log(__('Unable to load affiliate').' '.__('by username'));
                    $this->_log(__('Loading user %s failed', $current_user->nickname));
                    return null;
                }
            }
        }

        if ($parent) {
            $parentId = $affiliate->getParentUserId();
            if ($parentId) {
                $parentAffiliate = new Pap_Api_Affiliate($session);
                $parentAffiliate->setUserid($parentId);
                $parentAffiliate->load();
                return $parentAffiliate;
            }
            return null;
        }
        return $affiliate;
    }

    public function getCode($atts, $content = null, $parent = false) {
        global $current_user;
        if ($current_user->ID == 0) {
            return;
        }
        $session = $this->getApiSession();
        if ($session === null || $session === '0') {
            $this->_log('Error getting session for login to PAP. Check WP logs for details.');
            return;
        }
        $affiliate = $this->loadAffiliate($session, $parent);
        if ($affiliate == null) {
            $this->_log('Error getting affiliate');
            return;
        }
        if (array_key_exists('item', $atts)) {
            if ($atts['item'] == 'name') {
                return $affiliate->getFirstname() . ' ' . $affiliate->getLastname();
            }
            if ($atts['item'] == 'loginurl') {
                if (array_key_exists('caption', $atts)) {
                    return '<a href="'.$this->getLoginUrl($affiliate->getField('authtoken')).'" target="_blank">' . $atts['caption'] . '</a>';
                } else {
                    return '<a href="'.$this->getLoginUrl($affiliate->getField('authtoken')).'" target="_blank">Affiliate panel</a>';
                }
            }
            if ($atts['item'] == 'loginurl_raw') {
                return $this->getLoginUrl($affiliate->getField('authtoken'));
            }
            return $affiliate->getField($atts['item']);
        }
    }

    private function getLoginUrl($authToken) {
        return rtrim(get_option(postaffiliatepro::PAP_URL_SETTING_NAME), '/') . '/affiliates/login.php?authToken=' . $authToken;
    }

    public function initSettings() {
        register_setting(self::SHORTCODES_SETTINGS_PAGE_NAME, self::AFFILAITE_SHORTCODE_CACHE);
    }
}

$shortcodeAffiliate = new Shortcode_Affiliate();
add_action('admin_init', array($shortcodeAffiliate, 'initSettings'), 99);
add_shortcode('affiliate', array($shortcodeAffiliate, 'getAffiliateShortCode'));
add_shortcode('parent', array($shortcodeAffiliate, 'getParentAffiliateShortCode'));