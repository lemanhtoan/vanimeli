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
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpointsrule Renderer Shopping Cart Item Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */

class Magestore_RewardPointsRule_Block_Cart_Item_Renderer extends Mage_Core_Block_Template
{
    protected $_item;
    public function setItem(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $this->_item = $item;
        return $this;
    }
    public function getItem()
    {
        return $this->_item;
    }
    public function getPointSpending()
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return false;
        }
        $item = $this->getItem();
        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (!is_array($catalogRules)) return false;
        if (isset($catalogRules[$item->getId()])) {
            return new Varien_Object($catalogRules[$item->getId()]);
        }
        return false;
    }
    
    public function getPointEarning()
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return false;
        }
        $item = $this->getItem();
        return Mage::helper('rewardpointsrule/calculation_earning')
            ->getCatalogItemEarningPoints($item);
    }
}
