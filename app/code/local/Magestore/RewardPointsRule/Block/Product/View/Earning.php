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
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Product View Earning Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Product_View_Earning extends Magestore_RewardPoints_Block_Product_View_Earning
{
    public function enableDisplay()
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return false;
        }
        return Mage::helper('rewardpoints/point')->showOnProduct()&& !Mage::registry('product')->getRewardpointsSpend();
    }
    
    /**
     * get earning points for product on product detail page
     * 
     * @return int
     */
    public function getEarningPoints()
    {
        if ($this->hasData('earning_points')) {
            return $this->getData('earning_points');
        }
        if ($this->getAction()->getFullActionName() == 'checkout_cart_configure') {
            $item = Mage::getSingleton('checkout/session')->getQuote()->getItemById(
                $this->getRequest()->getParam('id')
            );
            $points = Mage::helper('rewardpointsrule/calculation_earning')->getCatalogItemEarningPoints($item);
            $this->setData('earning_points', $points / $item->getQty());
        } else {
            $points = Mage::helper('rewardpointsrule/calculation_earning')->getCatalogEarningPoints(
                Mage::registry('product')
            );
            $this->setData('earning_points', $points);
        }
        return $this->getData('earning_points');
    }
    public function getGroupedEarningPoints(){
        $_product = Mage::registry('product');
        if($_product->getTypeId() == 'grouped'){
            $_associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
            $pointSum = array();
            foreach($_associatedProducts as $aProduct){
                $point = Mage::helper('rewardpointsrule/calculation_earning')->getCatalogEarningPoints(
                            $aProduct
                        );
                $pointSum[$aProduct->getId()] = $point; 
            }
            return $pointSum;
        }
        return 0;
    }
}
