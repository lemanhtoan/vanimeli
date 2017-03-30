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
 * RewardPoints Update Top Link Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Toplink extends Magestore_RewardPoints_Block_Template
{
    /**
     * prepare block's layout
     *
     * @return Magestore_RewardPoints_Block_Name
     */
    public function _prepareLayout()
    {
        $helper = Mage::helper('rewardpoints/customer');
        if (!Mage::getStoreConfig('advanced/modules_disable_output/Magestore_RewardPoints')
            && $this->isEnable() && $helper->getCustomerId() && $helper->showOnToplink()
        ) {
            $block = $this->getLayout()->getBlock('top.links');
            
            $accountUrl  = Mage::helper('customer')->getAccountUrl();
            $nameBlock = Mage::getBlockSingleton('rewardpoints/name');
            if(is_object($block)){
		$block->removeLinkByUrl($accountUrl);
		$block->addLink(
                    $this->__('My Account') . ' (' . $nameBlock->toHtml() . ')',
                    $accountUrl,
                    $this->__('My Account'),
                    '', '', 10
		);
            }
        }
        
        return parent::_prepareLayout();
    }
    
    /**
     * functional block - using to change other block information
     * 
     * @return string
     */
    protected function _toHtml()
    {
        return '';
    }
}
