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
 * @package     Magestore_RewardPointsBehavior
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpointsbehavior Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_Block_Showrewardpoint extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function isEnabled() {
        return $this->_helper()->isEnable($this->_getStore());
    }

    public function _helper() {
        return Mage::helper('rewardpointsbehavior');
    }

    public function _getStore() {
        return Mage::app()->getStore()->getId();
    }

    public function getProduct() {
        return Mage::registry('product');
    }

    /**
     * get login meassage to show on frontend
     */
    public function getLoginInfo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getLoginConfig('logging_in'));
        return $this->_helper()->__('Earn %s for logging in', $point);
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getRewardNewsletterInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getSignConfig('newsletter'));
        return $this->_helper()->__('Earn %s for subscribing newsletter', $point);
    }

    /**
     * get message earn points for signing up account
     * @return string
     */
    public function getSignupInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getSignConfig('signing_up'));
        return $this->_helper()->__('Earn %s for registering a new account', $point);
    }

    public function getSignupInfoNew() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getSignConfig('signing_up'));
        return $this->_helper()->__('Earn %s for registering an account.', $point);
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getRewardPollInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getPollConfig('poll'));
        return $this->_helper()->__('Earn %s for taking poll. ', $point);
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getRewardTagProductInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getTagConfig('tag'));
        return $this->_helper()->__('You will earn %s for writing a tag for this product.', $point);
    }

    /**
     * check rate, review is enable
     * @return int
     */
    public function isRateReview() {
        $count = 0;
        if ($this->_helper()->getRateConfig('rate') && $this->_helper()->getRateConfig('show_rate_product'))
            $count++;
        if ($this->_helper()->getReviewConfig('review') && $this->_helper()->getReviewConfig('show_reviewing'))
            $count++;
        return $count;
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getRewardViewProductInFo() {
        $pointrate = Mage::helper('rewardpoints/point')->format($this->_helper()->getRateConfig('rate'));
        $pointreview = Mage::helper('rewardpoints/point')->format($this->_helper()->getReviewConfig('review'));
        $message = '';
        if ($this->isRateReview() == 2) {
            $message = $this->_helper()->__('You will earn %s for writing a review and %s for rating this product.', $pointreview, $pointrate);
        } else if ($this->isRateReview() == 1) {
            if ($this->_helper()->getRateConfig('show_rate_product') && $this->_helper()->getRateConfig('rate')) {
                $message = $this->_helper()->__('You will earn %s for rating this product.', $pointrate);
            } else {
                $message = $this->_helper()->__('You will earn %s for writing a review this product.', $pointreview);
            }
        }
        return $message;
    }

    /**
     * get earn message to show on frontend
     * @return string
     */
    public function getBirthdayInFo() {
        $point = Mage::helper('rewardpoints/point')->format($this->_helper()->getBirthdayConfig('customer_birthday'));
        return $this->_helper()->__('You will earn %s on your birthday.', $point);
    }

    /**
     * get Social Information
     * @return string
     */
    public function getSocialInfo($chanel = null) {
        $buttonShows = array();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        foreach($this->getButtonShows() as $tool => $value){
            if(!$this->_helper()->canEarnPoint($tool, $customer, $this->getShareUrl())) continue;
            if($point = $this->_helper()->getEarnPoint($tool, $customer))
                $buttonShows[$tool] = Mage::helper('rewardpoints/point')->format($point);
        }
        if(!$chanel){
            if(count($buttonShows) > 1) return $this->_helper()->__('Like or share to receive points.');
            else if(count($buttonShows) == 0) return '';
            else{
				$arrayKey = array_keys($buttonShows);
                $chanel = $arrayKey[0];
                return $this->getMessageSocial($buttonShows, $chanel);
            }
        }
        if(array_key_exists($chanel, $buttonShows)){
            return $this->getMessageSocial($buttonShows, $chanel);
        }
        return '';
    }

    public function getMessageSocial($buttonShows, $chanel = null) {
        if (array_key_exists($chanel, $buttonShows)) {
            switch ($chanel) {
                case 'facebook':
                    return $this->_helper()->__('You will earn %s for a Facebook like.', $buttonShows[$chanel]);
                case 'facebook_share':
                    return $this->_helper()->__('You will earn %s for a Facebook share.', $buttonShows[$chanel]);
                case 'twitter':
                    return $this->_helper()->__('You will earn %s for a Twitter tweet.', $buttonShows[$chanel]);
                case 'googleplus':
                    return $this->_helper()->__('You will earn %s for a Google plus +1.', $buttonShows[$chanel]);
                case 'pinterest':
                    return $this->_helper()->__('You will earn %s for a Pinterest pin.', $buttonShows[$chanel]);
                case 'linkedin':
                    return $this->_helper()->__('You will earn %s for a LinkedIn share.', $buttonShows[$chanel]);
                default:
                    return '';
            }
        }
        return '';
    }

    public function getErrorMessage() {
        return $this->_helper()->__('Cannot connect to server.');
    }

    public function showButton($button, $facebookShare = false) {
        $show = $facebookShare ? 'show_button_share' : 'show_button';
        return $this->_helper()->getSocialConfig($button, $show);
    }

    public function showCount($button) {
        return $this->_helper()->getSocialConfig($button, 'show_count');
    }

    public function getButtonShows() {
        $activeTool = array();
        foreach ($this->_helper()->getSharingTools() as $tool => $action) {
            $isShare = ($tool == 'facebook_share') ? true : false;
            $group = ($tool == 'facebook_share') ? 'facebook' : $tool;
            if ($this->showButton($group, $isShare)) {
                $activeTool[$tool] = 0;
            }
        }
        return $activeTool;
    }

    public function getReferfriendsKey() {
        if (Mage::getSingleton('customer/session')->isLoggedIn() && Mage::getConfig()->getModuleConfig('Magestore_RewardPointsReferFriends')->is('active', 'true') && Mage::helper('rewardpointsreferfriends')->isEnable()) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $model = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load(Mage::getSingleton('customer/session')->getCustomerId(), 'customer_id');
            if ($key = $model->getKey())
                return '?k=' . $key;
        }
        return '';
    }

    public function getShareUrl() {
        return Mage::helper('core/url')->getCurrentUrl() . $this->getReferfriendsKey();
    }

    public function getLoginLink($text) {
        $urlString = base64_encode(Mage::helper('core/url')->getCurrentUrl());
        $url = $this->getUrl('rewardpoints/index/redirectLogin', array('redirect' => $urlString));
        $text = str_replace("[login_link]", "<a href='{$url}'>", $text);
        $text = str_replace("[/login_link]", "</a>", $text);
        return $text;
    }

    public function showSocial() {
        $enable = Mage::getStoreConfig('rewardpoints/group_social_setting/sc_display');
        $positions = explode(',', Mage::getStoreConfig('rewardpoints/group_social_setting/sc_position_display'));
        switch (Mage::app()->getRequest()->getControllerName()) {
            case 'product':
                $position = '2';
                break;
            case 'category':
                $position = '1';
                break;
            default:
                $position = '0';
                break;
        }
        return count($this->getButtonShows()) && $enable && $this->isEnabled() && in_array($position, $positions);
    }

}
