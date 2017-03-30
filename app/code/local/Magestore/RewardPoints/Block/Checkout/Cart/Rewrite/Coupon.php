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
 * @package     Magestore_RewardPoints
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * RewardPoints Rewrite Checkout Cart Coupon Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Checkout_Cart_Rewrite_Coupon
    extends Mage_Checkout_Block_Cart_Coupon
{
    /**
     * get current used coupon code
     * 
     * @return string
     */
    public function getCouponCode()
    {
        $container = new Varien_Object(array(
            'coupon_code'   => '',
        ));
        Mage::dispatchEvent('rewardpoints_rewrite_coupon_block_get_coupon_code', array(
            'block'     => $this,
            'container' => $container,
        ));
        if ($container->getCouponCode()) {
            return $container->getCouponCode();
        }
        return parent::getCouponCode();
    }
    
     /**
     * render coupon block
     * 
     * @return html
     */
    protected function _toHtml()
    {
        $container = new Varien_Object(array(
            'html'          => '',
            'rewrite_core'  => false,
        ));
        Mage::dispatchEvent('rewardpoints_rewrite_coupon_block_to_html', array(
            'block'     => $this,
            'container' => $container,
        ));
        if ($container->getRewriteCore() && $container->getHtml()) {
            return $container->getHtml();
        }
        if ($this->getChild('checkout.cart.rewardpoints')) {
            $html=$this->getChildHtml('checkout.cart.rewardpoints');
            $this->unsetChild('checkout.cart.rewardpoints');
            return $container->getHtml()
                . $html
                . parent::_toHtml();
        }
        return $container->getHtml() . parent::_toHtml();
    }
}
