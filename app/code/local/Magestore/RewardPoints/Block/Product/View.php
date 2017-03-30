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
 * Rewrite Product View Page for Magento version 1.4 Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Product_View extends Magestore_RewardPoints_Block_Template
{
    /**
     * prepare product info.extrahint block information
     *
     * @return Magestore_RewardPoints_Block_Template
     */
    public function _prepareLayout()
    {
        if ($this->isEnable() && version_compare(Mage::getVersion(), '1.4.1.0', '<')) {
            $productInfo = $this->getLayout()->getBlock('product.info');
            $productInfo->setTemplate('rewardpoints/product/view.phtml');
            $extrahints = $this->getLayout()->createBlock('core/text_list', 'product.info.extrahint');
            $productInfo->setChild('extrahint', $extrahints);
        }
        return parent::_prepareLayout();
    }
}
