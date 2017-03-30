<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpointsreferfriends Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Block_Rewardpointsreferfriends extends Mage_Core_Block_Template {

    /**
     * prepare block's layout
     *
     * @return Magestore_RewardPointsReferFriends_Block_Rewardpointsreferfriends
     */
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    /**
     * Call helper RewardpointsReferfriends Data
     * @return type
     */
    function callHelper() {
        return Mage::helper('rewardpointsreferfriends');
    }

    /**
     * Check Enable default config to refer friends
     * @return boolean value
     */
    function isEnableDefault() {
        return $this->callHelper()->getReferConfig('use_default_config');
    }

    /**
     * Get default discount for invited customer
     * @return type
     */
    function getDefaultDiscount() {
        return $this->callHelper()->getDiscountDefault();
    }

    /**
     * Get default point for referal customer
     * @return type
     */
    function getDefaultPoint() {
        return $this->callHelper()->getPointDefault();
    }

    /**
     * Get special offer per store
     * @param type $store_id
     * @return type
     */
    function getSpecialOffer($store_id = null) {
        return $this->callHelper()->getSpecialOffer($store_id);
    }

    /**
     * Get Link to share to friends
     * if Default link is not set, return policy link
     * @return type
     */
    function getShareLink() {
        $default = $this->callHelper()->getDefaultLink();
        if ($default)
            return $default;
        else
            return $this->callHelper()->getPolicyLink();
    }

    /**
     * GET KEY ?k=
     * @return string
     */
    function getKey() {
        return $this->callHelper()->getLinkKey();
    }

    /**
     * Get Coupon
     * @return type
     */
    function getShareCoupon() {
        return $this->callHelper()->getCoupon();
    }

    /**
     * Get customer email
     * @return type
     */
    function getCustomerEmail() {
        $customer = Mage::helper('customer')->getCustomer();
        return $customer->getEmail();
    }

    /**
     * Get email subject
     * @return type
     */
    function getEmailSubject() {
        return $this->callHelper()->getReferConfig('sharing_subject');
    }

    /**
     * Get email content
     * @return type
     */
    function getEmailContent() {
        return $this->strReplace();
    }

    /**
     * Get facebook content
     * @return type
     */
    function getFacebookContent() {
        return $this->strReplace('facebook');
    }

    /**
     * Get Twitter content
     * @return type
     */
    function getTwitterContent() {
        return $this->strReplace('twitter');
    }

    /**
     * replace parameter in share message
     * @param type $method
     * @return type
     */
    function strReplace($method = 'sharing') {
        if ($this->callHelper()->getReferConfig('refer_method') == 'link') {
            $content = $this->callHelper()->getReferConfig($method . '_message_for_link');
        } elseif ($this->callHelper()->getReferConfig('refer_method') == 'coupon') {
            $content = $this->callHelper()->getReferConfig($method . '_message_for_coupon');
        } else {
            $content = $this->callHelper()->getReferConfig($method . '_message_for_both');
        }
        $linkshare = $this->callHelper()->getDefaultLink();
        if ($method != 'share')
            $config = $this->callHelper()->getReferConfig('link_to_share_' . $method);
        else
            $config = $this->callHelper()->getReferConfig('link_to_share_email');
        if ($config == 'policy_link' || !$linkshare)
            $linkshare = $this->callHelper()->getPolicyLink();
        return str_replace(
                array(
            '{{store_name}}',
            '{{coupon_share}}',
            '{{link_to_share}}',
            '{{policy_description}}',
            '{{link_to_site}}'
                ), array(
            Mage::app()->getStore()->getFrontendName(),
            $this->callHelper()->getCoupon(),
            $linkshare,
            $this->getPolicyDescription(),
            $this->getUrl(''),
                ), $content
        );
    }

    /**
     * Get policy to insert to message
     * Used in strReplace()
     * @return string
     */
    public function getPolicyDescription() {
		$message = '';
        if ($this->isEnableDefault()) {
            $message = "\n\nDiscount Information:\n";
            $message .= "\nDiscount up to " . $this->getDefaultDiscount() . " for each order at " . Mage::app()->getStore()->getName() . ".\n";
            //$message = "\nGet discount up to ".$this->getDefaultDiscount()." for each order at ".Mage::app()->getStore()->getName()." when purchase using this special friends only offer.\n";
        }
        $offer = $this->getSpecialOffer();
        if ($offer->getSize()) {
            foreach ($offer as $value) {
                $message .= "\n" . $value->getTitle() . " " . $this->callHelper()->getDateExpire($value->getFromDate(), $value->getToDate()) . "\n";
                $message .= $value->getDescriptionInvited() . "\n";

                $type = $value->getDiscountType();
                if ($type == 1)
                    $discount = Mage::helper('core')->formatPrice($value->getDiscountValue());
                else
                    $discount = (float) $value->getDiscountValue() . '%';

                if ($value->getDiscountValue() > 0)
                    $message .= "Get " . $discount . " discount when purchase products in this event.\n";
            }
        }
        return strip_tags($message);
    }

    /**
     * Get link to retrieve contact from mail server
     * @return type
     */
    public function getJsonEmail() {
        $result = array(
            'yahoo' => $this->getUrl('*/*/yahoo'),
            'gmail' => $this->getUrl('*/*/gmail'),
        );
        return Zend_Json::encode($result);
    }

    /**
     * Get link share on google plus
     * @return type
     */
    function getGoogleplusShareLink() {
        $conf = $this->callHelper()->getReferConfig('link_to_share_google');
        if ($conf == 'default_link' && $this->callHelper()->getDefaultLink()) {
            return $this->callHelper()->getDefaultLink();
        } else {
            return $this->callHelper()->getPolicyLink();
        }
    }

    /**
     * Save email sent
     * @return type
     */
    public function getEmailFormData() {
        if (!$this->hasData('email_form_data')) {
            $data = Mage::getSingleton('core/session')->getEmailFormData();
            Mage::getSingleton('core/session')->setEmailFormData(null);
            $dataObj = new Varien_Object($data);
            $this->setData('email_form_data', $dataObj);
        }
        return $this->getData('email_form_data');
    }

    public function canEditKey() {
        return $this->callHelper()->getReferConfig('customer_can_change_key');
    }

    public function canEditCoupon() {
        return $this->callHelper()->getReferConfig('customer_can_change_coupon');
    }

}
