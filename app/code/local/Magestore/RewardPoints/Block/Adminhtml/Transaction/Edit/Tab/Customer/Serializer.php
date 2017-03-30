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
 * Rewardpoints Customer Grid Serializer Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tab_Customer_Serializer
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tab_Customer_Serializer constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('rewardpoints/transaction/customer/serializer.phtml');
        return $this;
    }
    
    /**
     * init serializer block, called from layout
     * 
     * @param string $gridName
     * @param string $hiddenInputName
     */
    public function initSerializerBlock($gridName, $hiddenInputName)
    {
        $grid = $this->getLayout()->getBlock($gridName);
        $this->setGridBlock($grid)
            ->setInputElementName($hiddenInputName);
    }
}
